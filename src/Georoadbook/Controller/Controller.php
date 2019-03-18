<?php

namespace Georoadbook\Controller;

use Geocaching\Exception\GeocachingSdkException;
use Geocaching\GeocachingFactory;
use Georoadbook\Georoadbook;
use League\OAuth2\Client\Provider\Exception\GeocachingIdentityProviderException;
use League\OAuth2\Client\Provider\Geocaching as GeocachingProvider;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class Controller
{

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return string       
     */
    public function indexAction(Application $app, Request $request): string 
    {
        $params = [
            'locales'  => $app['locales'],
            'language' => $app['language'],
        ];

        if ($app['session']->get('accessToken')) {

            $geocachingApi = GeocachingFactory::createSdk($app['session']->get('accessToken'), $app['environment'], 
                                                    [
                                                        'debug'   => false,
                                                        'timeout' => 10,
                                                    ]);
            try {
                if (!$app['session']->get('user')) {
                    $profileResponse = $geocachingApi->getUser('me', ['fields' => 'referenceCode,username,hideCount,findCount,favoritePoints,membershipLevelId,avatarUrl,bannerUrl,url,homeCoordinates,geocacheLimits']);
                    $profile = $profileResponse->getBody();
                    
                    $app['session']->set('user', ['username' => $profile->username, 'avatarUrl' => $profile->avatarUrl]);
                }
                $params['pocketqueryList'] = $geocachingApi->getUserLists('me', ['types' => 'pq', 'fields' => 'referenceCode,name'])->getBody();

                usort($params['pocketqueryList'], function ($k,$v) {
                    return $k->name <=> $v->name;
                });

            } catch(GeocachingSdkException $e) {
                $app['monolog']->error($e->getMessage());
                $twig_vars['exception'] = $e->getMessage();
                $app['session']->clear();
            }
        }

        return $app['twig']->render('index.twig.html', $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return RedirectResponse
     */
    public function loginAction(Application $app, Request $request): RedirectResponse
    {
    	$provider = $this->getProvider($app);
        $authorizationUrl = $provider->getAuthorizationUrl();

        // Get the state generated for you and store it to the session.
        $_SESSION['oauth2state'] = $provider->getState();
    
        // Redirect the user to the authorization URL.
        return $app->redirect($authorizationUrl);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return RedirectResponse
     */
    public function logoutAction(Application $app, Request $request): RedirectResponse
    {
        $app['session']->clear();
        $redirect = !empty($request->headers->get('referer')) ? $request->headers->get('referer') : '/';
        return $app->redirect($redirect);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return RedirectResponse
     */
	public function callbackAction(Application $app, Request $request): RedirectResponse
 	{
        $provider = $this->getProvider($app);
        $state = $request->get('state');

        $code = $request->get('code');
        if (!$app['session']->get('accessToken') && !is_null($state)) {

            $oauth2state = $app['session']->get('oauth2state');
            if (isset($oauth2state) && $state !== $oauth2state) {
                $app['session']->forget('oauth2state');
                //die('state error');
            } else {
                try {
                    // Try to get an access token using the authorization code grant.
                    $accessToken = $provider->getAccessToken('authorization_code', [
                        'code' => $code
                    ]);
                    // We have an access token, which we may use in authenticated
                    // requests against the service provider's API.
                    $app['session']->set('accessToken', $accessToken->getToken());
                    $app['session']->set('refreshToken', $accessToken->getRefreshToken());
                    $app['session']->set('expiredTimestamp', $accessToken->getExpires());
                    $app['session']->set('object', serialize($accessToken));

                } catch (GeocachingIdentityProviderException $e) {
                    // Failed to get the access token or user details.
                    //echo $e->getMessage();
                    $app['monolog']->error($e->getMessage());
                    //die;
                }
            }
        }
        return $app->redirect('/');
	}

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function uploadAction(Application $app, Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $app->json(['success' => false]);
        }

        $gpx = $request->get('gpx', '');
        $referenceCode = $request->get('referenceCode', '');

        $locale = $request->get('locale', null);

        if (empty($gpx) && empty($referenceCode)) {
            return $app->json(['success' => false, 'message' => 'A GPX file or a Pocket Query is missing.']);
        }

        if (is_null($locale)) {
            return $app->json(['success' => false, 'message' => 'Roadbook language is missing.']);
        }

        if (!in_array($locale, array_keys($app['locales']))) {
            return $app->json(['success' => false, 'message' => 'Roadbook language is invalid.', 'lang' => $app['locales']]);
        }

        if ($app['session']->get('accessToken') && !empty($referenceCode)) {

            $geocachingApi = GeocachingFactory::createSdk($app['session']->get('accessToken'), $app['environment'], 
                                                    [
                                                        'debug'   => false,
                                                        'timeout' => 10,
                                                    ]);
            try {
                $tmpDirectory = $app['root_directory'] . '/app/tmp';
                $zipFilePath = sprintf('%s/%s.zip', $tmpDirectory, $referenceCode);

                $geocachingApi->getZippedPocketQuery($referenceCode, $tmpDirectory);
                $zip = new \ZipArchive;
                if (($res = $zip->open($zipFilePath))) {
                    $gpxFileName = $zip->statIndex(1)['name'];
                    $zip->extractTo(sprintf('%s/%s', $tmpDirectory, $referenceCode), [$gpxFileName]);
                    $zip->close();
                    $gpx = file_get_contents(sprintf('%s/%s/%s', $tmpDirectory, $referenceCode, $gpxFileName));
                } else {
                    throw new \Exception('unzipping archive failed, code:' . $res);
                }
            } catch(\Throwable $e) {
                return $app->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        try {
            $sxe = new \SimpleXMLElement($gpx);
        } catch (\Exception $e) {
            return $app->json(['success' => false, 'message' => 'Not a XML file.']);
        }
        $schemaLocation = (string) $sxe->attributes('xsi', true)->schemaLocation;
        preg_match('!http://www.groundspeak.com/cache/([0-9/]*)!i', $schemaLocation, $matche);

        if (!array_key_exists(1, $matche)) {
            return $app->json(['success' => false, 'message' => 'GPX type is incorrect.']);
        }
        if ($matche[1] == '1/0') {
            return $app->json(['success' => false, 'message' => 'GPX version 1/0 is not supported, please use version 1/0/1. '.
                                                                     '<a href="https://www.geocaching.com/account/settings/preferences">Check your preferences</a>', ]);
        }

        $display_toc = ($request->get('toc') === 'true') ? true : false;
        $display_note = ($request->get('note') === 'true') ? true : false;
        $display_short_desc = ($request->get('short_desc') === 'true') ? true : false;
        $display_long_desc = ($request->get('long_desc') === 'true') ? true : false;
        $display_hint = ($request->get('hint') === 'true') ? true : false;
        $display_logs = ($request->get('logs') === 'true') ? true : false;
        $display_spoilers = ($request->get('spoilers') === 'true') ? true : false;
        $hint_encrypted = ($request->get('hint_encrypted') === 'true') ? true : false;
        $display_waypoints = ($request->get('waypoints') === 'true') ? true : false;
        $sort_by = $request->get('sort_by');
        if (!in_array($sort_by, $app['available_sorts'])) {
            $sort_by = $app['available_sorts'][0];
        }
        $pagebreak = ($request->get('pagebreak') === 'true') ? true : false;
        $images = ($request->get('images') === 'true') ? true : false;

        $roadbook = new Georoadbook($app);

        if (!$roadbook->create($gpx)) {
            return $app->json(['success' => false]);
        }

        $options = ['display_note' => $display_note,
                    'display_short_desc' => $display_short_desc,
                    'display_long_desc' => $display_long_desc,
                    'display_hint' => $display_hint,
                    'display_logs' => $display_logs,
                    'display_waypoints' => $display_waypoints,
                    'display_spoilers' => $display_spoilers,
                    'sort_by' => $sort_by,
                    'pagebreak' => $pagebreak,
                ];
        $roadbook->convertXmlToHtml($locale, $options)->cleanHtml();

        // Table of content
        if ($display_toc) {
            $roadbook->addToc();
        }

        // remove images from short and long description
        if ($images) {
            $roadbook->removeImages($display_short_desc);
        }

        // Hint
        if ($display_hint && $hint_encrypted) {
            $roadbook->encryptHints();
        }

        // Spoilers
        if ($display_spoilers) {
            $roadbook->addSpoilers();
        }

        // Waypoints
        if ($display_waypoints) {
            $roadbook->addWaypoints();
        }

        // Parse logs
        if ($display_logs) {
            $roadbook->parseMarkdown()->parseBBcode();
        }

        $roadbook->getOnlyBody();

        $roadbook->saveFile($roadbook->getHtmlFile(), $roadbook->html);
        $roadbook->saveFile($roadbook->getJsonFile());

        return $app->json(['success' => true, 'redirect' => 'roadbook/'. basename($roadbook->getRoadbookPath())]);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $id
     *
     * @return string
     */
    public function editAction(Application $app, Request $request, $id): string
    {
        if ($this->checkLogout($app, $request)) {
            $redirect = !empty($request->headers->get('referer')) ? $request->headers->get('referer') : '/';
            return $app->redirect($redirect);
        }

        $roadbook = new Georoadbook($app, $id);

        if (!file_exists($roadbook->getHtmlFile()) || !is_readable($roadbook->getHtmlFile())) {
            return new Response('This roadbook doesn\'t exist.', 404);
        }

        if (!is_null($request->get('raw'))) {
            $params = ['style'   => $roadbook->getCustomCss(),
                       'content' => file_get_contents($roadbook->getHtmlFile())
                    ];

            return $app['twig']->render('raw.twig.html', $params);
        }

        if (!is_null($request->get('pdf'))) {
            $roadbook->downloadPdf();
        }

        if (!is_null($request->get('zip'))) {
            $roadbook->downloadZip();
        }

        $params = [
            'roadbook_id' => $id,
            'language' => $app['language'],
            'roadbook_content' => file_get_contents($roadbook->getHtmlFile()),
            'last_modification' => 'Last saved: ' . $roadbook->getLastSavedDate(),
        ];

        if (class_exists('ZipArchive')) {
            $params['available_zip'] = true;
        }

        if (file_exists($roadbook->getPdfFile())) {
            $params['available_pdf'] = true;
        }

        return $app['twig']->render('edit.twig.html', $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function saveAction(Application $app, Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $app->json(['success' => false]);
        }

        $id = $request->get('id');
        $content = $request->get('content');

        if (empty($id) || empty($content)) {
            return new Response('Bad Request', 400);
        }

        $roadbook = new Georoadbook($app, $id);

        //hack, bug in TinyMCE
        $html = preg_replace('/<head>\s*<\/head>/m', '<head><meta charset="utf-8" /><title>My roadbook</title><link type="text/css" rel="stylesheet" href="../design/roadbook.css" media="all" /></head>', $content, 1);
        $roadbook->saveFile($roadbook->getHtmlFile(), $html);

        if (!$roadbook->saveFile($roadbook->getHtmlFile(), $_POST['content'])) {
            $app->json(['success' => false]);
        }

        return $app->json(['success' => true, 'last_modification' => 'Last saved: '.$roadbook->getLastSavedDate()]);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function exportAction(Application $app, Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $app->json(['success' => false]);
        }

        $roadbook_id = $request->get('id', null);
        $real_export = $request->get('real_export', null);
        $experimental = $request->get('experimental', null);

        if (is_null($roadbook_id)) {
            return $app->json(['success' => false]);
        }

        $roadbook = new Georoadbook($app, $roadbook_id);

        if (!file_exists($roadbook->getHtmlFile()) || !is_readable($roadbook->getHtmlFile())) {
            return new Response('This roadbook doesn\'t exist.', 404);
        }

        $options_css = ['page_size' => $request->get('page-size', null),
                        'orientation' => $request->get('orientation', null),
                        'margin_left' => (int) $request->get('margin-left', 0),
                        'margin_right' => (int) $request->get('margin-right', 0),
                        'margin_top' => (int) $request->get('margin-top', 0),
                        'margin_bottom' => (int) $request->get('margin-bottom', 0),
                        'header_align' => $request->get('header-align', null),
                        'header_text' => $request->get('header-text', null),
                        'header_pagination' => ($request->get('header-pagination') === 'true') ? 1 : 0,
                        'footer_align' => $request->get('footer-align', null),
                        'footer_text' => $request->get('footer-text', null),
                        'footer_pagination' => ($request->get('footer-pagination') === 'true') ? 1 : 0,
                    ];

        $roadbook->saveOptions($options_css);

        if (!$real_export) {
            return $app->json(['success' => true]);
        }

        if (!$roadbook->handleExport($experimental)) {
            return $app->json(['success' => false, 'error' => $roadbook->result]);
        }

        return $app->json([
                         'success' => true,
                         'size' => round(filesize($roadbook->getPdfFile()) / 1024 / 1024, 2),
                         // 'command'=> $pdf->command,
                         'link' => '<a href="/roadbook/'.basename($roadbook->getRoadbookPath()).'?pdf">Download your roadbook now</a>',
                        ]);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return mixed
     */
    public function deleteAction(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $app->json(['success' => false]);
        }

        $id = $request->get('id', null);

        if (is_null($id)) {
            return $app->json(['success' => false]);
        }

        (new Georoadbook($app, $id))->delete();
        $app['session']->getFlashBag()->add('deleted', 'Your roadbook has been deleted!');

        return $app['url_generator']->generate('index');
    }

    /**
     * @param Application $app
     * @param Request $request
     * 
     * @return bool
     */
    protected function checkLogout(Application $app, Request $request): bool
    {
        if ($request->get('logout') === '') {
            $app['session']->clear();
            return true;
        }

        return false;
    }

    /**
     * @param Application $app
     * 
     * @return GeocachingProvider
     */
    private function getProvider(Application $app): GeocachingProvider
    {
        return new GeocachingProvider([
        	'clientId'       => $app['oauth_key'],
        	'clientSecret'   => $app['oauth_secret'],
        	'redirectUri'    => $app['callback_url'],
        	'response_type'  => 'code',
        	'scope'          => '*',
        	'environment'    => $app['environment'],
        ]);
    }
}
