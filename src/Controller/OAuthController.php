<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class OAuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(ClientRegistry $clientRegistry, SessionInterface $session): Response
    {
        $client   = $clientRegistry->getClient('geocaching_main');
        $response = $client->redirect([], []);

        $provider = $client->getOAuth2Provider();
        $session->set('oauth2_pkce_code', $provider->getPkceCode());
        // Anti-CSRF (login CSRF / fixation): remember the generated state to revalidate it at the callback.
        $session->set('oauth2_state', $provider->getState());

        return $response;
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/callback', name: 'app_callback')]
    public function callback(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->redirectToRoute('app_homepage');
    }
}
