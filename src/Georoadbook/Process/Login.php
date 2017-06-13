<?php

namespace Georoadbook\Process;

use Georoadbook\Api as GeocachingApi;
use Geocaching\OAuth\OAuth;
use Geocaching\OAuth\GeocachingOAuthException;
use GuzzleHttp\Client;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Login
{
    private $app = null;
    private $request = null;

    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    public function authenticate()
    {
        try {
            if (is_null($this->request->get('oauth_verifier')) && is_null($this->request->get('oauth_token'))) {
                $this->requestToken();
            }
            if ($this->request->get('oauth_verifier') && $this->request->get('oauth_token') && $this->app['session']->get('request_token')) {
                $this->getToken();
            }
        } catch (GeocachingOAuthException $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    public function logout()
    {
        $this->app['session']->clear();
    }

    protected function requestToken()
    {
        $consumer = new OAuth(new Client(), $this->app['oauth_key'], $this->app['oauth_secret'], $this->app['oauth_callback_url'], $this->app['oauth_url']);
        $token    = $consumer->getRequestToken();
        $this->app['session']->set('request_token', serialize($token));
        $consumer->redirect();
    }

    protected function getToken()
    {
        $consumer = new OAuth(new Client(), $this->app['oauth_key'], $this->app['oauth_secret'], $this->app['oauth_callback_url'], $this->app['oauth_url']);
        $token    = $consumer->getAccessToken($_GET, unserialize($this->app['session']->get('request_token')));
        if (isset($token['oauth_error_message'])) {
            throw new GeocachingOAuthException($token['oauth_error_message']);
        }

        $this->app['session']->set('access_token', $token['oauth_token']);

        $api = new GeocachingApi($this->app);

        $user = $api->getGeocachingProfile();
        $this->app['session']->set('user', $user);
    }
}
