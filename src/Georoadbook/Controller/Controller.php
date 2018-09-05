<?php

namespace Georoadbook\Controller;

use Georoadbook\Georoadbook;
use Georoadbook\Process\Login;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Georoadbook\Api as GeocachingApi;

class Controller
{

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return mixed
     */
    public function indexAction(Application $app, Request $request)
    {
        $params = [
            'locales'  => $app['locales'],
            'language' => $app['language'],
        ];

        if ($this->checkLogout($app, $request)) {
            $redirect = !empty($request->headers->get('referer')) ? $request->headers->get('referer') : '/';
            return $app->redirect($redirect);
        }

        if ($app['session']->get('access_token')) {
            $api = new GeocachingApi($app);
            $params['pocketqueryList'] = $api->getPocketQueryList();
        }

        return $app['twig']->render('index.twig.html', $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return mixed
     */
    public function loginAction(Application $app, Request $request)
    {
        try {
            if (is_null($request->get('oauth_verifier')) && is_null($request->get('oauth_token'))) {
                $app['session']->set('loginRedirect', !empty($request->headers->get('referer')) ? $request->headers->get('referer') : '/');
            }
            (new Login($app, $request))->authenticate();
            $redirect = !is_null($app['session']->get('loginRedirect')) ? $app['session']->get('loginRedirect') : '/';
            return $app->redirect($redirect);
        }
        catch(GeocachingOAuthException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return mixed
     */
    public function uploadAction(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $app->json(['success' => false]);
        }

        $gpx = $request->get('gpx', '');
        $pocket_guid = $request->get('pocket_guid', '');

        $locale = $request->get('locale', null);

        if (empty($gpx) && empty($pocket_guid)) {
            return $app->json(['success' => false, 'message' => 'A GPX file or a Pocket Query is missing.']);
        }

        if (is_null($locale)) {
            return $app->json(['success' => false, 'message' => 'Roadbook language is missing.']);
        }
        if (!in_array($locale, array_keys($app['locales']))) {
            return $app->json(['success' => false, 'message' => 'Roadbook language is invalid.', 'lang' => $app['locales']]);
        }

        if ($app['session']->get('access_token') && !empty($pocket_guid)) {
            $api = new GeocachingApi($app);
            $gpx = $api->getPocketQueryZippedFile($pocket_guid);
        }

        try {
            $sxe = new \SimpleXMLElement($gpx);
        } catch (Exception $e) {
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
    public function editAction(Application $app, Request $request, $id)
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
     * @return mixed
     */
    public function saveAction(Application $app, Request $request)
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
     * @return mixed
     */
    public function exportAction(Application $app, Request $request)
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

    protected function checkLogout(Application $app, Request $request)
    {
        if ($request->get('logout') === '') {
            (new Login($app, $request))->logout();
            return true;
        }

        return false;
    }
}
