<?php

namespace App\Security;

use Geocaching\Lib\Utils\Utils;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\GeocachingClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
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
        $session     = $this->requestStack->getSession();
        $accessToken = $this->fetchAccessToken($this->getGeocachingClient(), [
            'code'          => $request->get('code'),
            'code_verifier' => $session->get('codeVerifier'),
        ]);

        return new SelfValidatingPassport(new UserBadge($accessToken->getToken(), fn() => $this->getUser($accessToken)));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        $this->apiLogger->info('onAuthenticationSuccess', [
            'referenceCode'     => $token->getUser()->getReferenceCode(),
            'userId'            => $token->getUser()->getUserId(),
            'username'          => $token->getUser()->getUserIdentifier(),
            'membershipLevelId' => $token->getUser()->getMembershipLevelId(),
        ]);

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add(
            'error',
            $exception->getMessageKey(),
        );

        $this->apiLogger->error('onAuthenticationFailure', [
            'key'     => $exception->getMessageKey(),
            'message' => $exception->getMessageData(),
        ]);

        return new RedirectResponse($this->router->generate('app_signin'));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_signin'));
    }

    private function getUser(AccessToken $credentials): User
    {
        $geocachingResourceOwner = $this->getGeocachingClient()->fetchUserFromToken($credentials);

        $user = new User();
        $user->setUserId(Utils::referenceCodeToId($geocachingResourceOwner->getId()))
             ->setReferenceCode($geocachingResourceOwner->getId())
             ->setJoinedDateUtc(new \DateTime($geocachingResourceOwner->getJoinedDate()))
             ->setUsername($geocachingResourceOwner->getUsername())
             ->setAvatarUrl($geocachingResourceOwner->getAvatarUrl())
             ->setMembershipLevelId($geocachingResourceOwner->getMembershipLevelId())
             ->setCredentials($credentials->getToken(), $credentials->getRefreshToken(), $credentials->getExpires())
            ;

        return $user;
    }

    private function getGeocachingClient(): GeocachingClient
    {
        return $this->clientRegistry->getClient('geocaching_main');
    }
}
