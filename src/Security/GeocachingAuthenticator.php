<?php

namespace App\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\GeocachingClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\Geocaching as GeocachingProvider;
use League\OAuth2\Client\Provider\GeocachingResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GeocachingAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $apiLogger,
    ) {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'app_callback';
    }

    public function authenticate(Request $request): Passport
    {
        $session = $this->requestStack->getSession();

        // Anti-CSRF: the OAuth `state` must match the value stored in session at /login.
        // KnpU validates it too, but this keeps the guarantee explicit and one-time-use.
        $expectedState = $session->get('oauth2_state');
        $session->remove('oauth2_state');
        $actualState = $request->query->get('state');
        if (!is_string($expectedState) || !is_string($actualState) || !hash_equals($expectedState, $actualState)) {
            throw new AuthenticationException('Invalid OAuth state parameter.');
        }

        $client   = $this->getGeocachingClient();
        $provider = $client->getOAuth2Provider();
        if (!$provider instanceof GeocachingProvider) {
            throw new \LogicException(sprintf('Expected a Geocaching OAuth provider, got "%s".', $provider::class));
        }

        // PKCE: hand the verifier stored at /login back to the provider so it is
        // sent along with the authorization code during the token exchange.
        $pkceCode = $session->get('oauth2_pkce_code');
        $session->remove('oauth2_pkce_code');
        if (is_string($pkceCode)) {
            $provider->setPkceCode($pkceCode);
        }

        $accessToken = $this->fetchAccessToken($client, [
            'code' => $request->query->get('code'),
        ]);

        return new SelfValidatingPassport(new UserBadge($accessToken->getToken(), fn () => $this->getUser($accessToken)));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if ($user instanceof User) {
            $this->apiLogger->info('onAuthenticationSuccess', [
                'referenceCode'     => $user->getReferenceCode(),
                'userId'            => $user->getUserId(),
                'username'          => $user->getUserIdentifier(),
                'membershipLevelId' => $user->getMembershipLevelId(),
            ]);
        }

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $session = $this->requestStack->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('error', $exception->getMessageKey());
        }

        $this->apiLogger->error('onAuthenticationFailure', [
            'key'     => $exception->getMessageKey(),
            'message' => $exception->getMessageData(),
        ]);

        return new RedirectResponse($this->router->generate('app_homepage'));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    private function getUser(AccessToken $credentials): User
    {
        $client   = $this->getGeocachingClient();
        $provider = $client->getOAuth2Provider();
        if ($provider instanceof GeocachingProvider) {
            $provider->setResourceOwnerFields(
                [...$provider->getResourceOwnerFields(), 'avatarUrl']
            );
        }

        $geocachingResourceOwner = $client->fetchUserFromToken($credentials);
        if (!$geocachingResourceOwner instanceof GeocachingResourceOwner) {
            throw new \LogicException(sprintf('Expected a Geocaching resource owner, got "%s".', $geocachingResourceOwner::class));
        }
        $user = new User();
        $user->setUserId(\Geocaching\Utils::referenceCodeToId($geocachingResourceOwner->getId()))
             ->setReferenceCode($geocachingResourceOwner->getId())
             ->setJoinedDateUtc(new \DateTime($geocachingResourceOwner->getJoinedDate()))
             ->setUsername($geocachingResourceOwner->getUsername())
             ->setAvatarUrl($geocachingResourceOwner->getAvatarUrl())
             ->setMembershipLevelId((string) $geocachingResourceOwner->getMembershipLevelId())
             ->setCredentials($credentials->getToken(), (string) $credentials->getRefreshToken(), (int) $credentials->getExpires())
        ;

        return $user;
    }

    private function getGeocachingClient(): GeocachingClient
    {
        $client = $this->clientRegistry->getClient('geocaching_main');
        if (!$client instanceof GeocachingClient) {
            throw new \LogicException('The "geocaching_main" OAuth client is not a GeocachingClient.');
        }

        return $client;
    }
}
