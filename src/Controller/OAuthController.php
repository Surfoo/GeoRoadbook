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
        $response = $clientRegistry->getClient('geocaching_main')->redirect([], []);
        $pkceCode = $clientRegistry->getClient('geocaching_main')->getOAuth2Provider()->getPkceCode();

        $session->set('oauth2_pkce_code', $pkceCode);

        return $response;
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/callback', name: 'app_callback')]
    public function callback()
    {
        return $this->redirectToRoute('app_homepage');
    }
}
