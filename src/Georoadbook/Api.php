<?php

namespace Georoadbook;

use Geocaching\Api\GeocachingApi;
use GuzzleHttp\Client;
use Silex\Application;

class Api
{
    protected $api = null;
    protected $app = null;

    protected $profile = null;
    protected $pocketqueryList = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        if ($this->app['session']->get('access_token')) {
            $this->api = new GeocachingApi(new Client(), $this->app['session']->get('access_token'), true);
        }
    }

    public function getGeocachingProfile()
    {
        if (is_null($this->profile)) {
            $profile = $this->api->getYourUserProfile(['GeocacheData' => false, 'PublicProfileData' => false]);
            $this->profile = $profile->Profile->User;
        }
        return $this->profile;
    }

    public function getPocketQueryList()
    {
        if (is_null($this->pocketqueryList)) {
            $pocketqueryList = $this->api->getPocketQueryList();
            $this->pocketqueryList = $pocketqueryList->PocketQueryList;
        }
        return $this->pocketqueryList;
    }

    public function getPocketQueryZippedFile($guid)
    {
        $pocketquery = $this->api->getPocketQueryZippedFile(['pocketQueryGuid' => $guid]);

        $zipFilename = $this->app['root_directory'] . '/app/tmp/' . $guid . '.zip';

        $hd = fopen($zipFilename, 'w');
        fwrite($hd, base64_decode($pocketquery->ZippedFile));

        $zip = new \ZipArchive;
        if (!$zip->open($zipFilename)) {
            return false;
        }
        $zip->extractTo($this->app['root_directory'] . '/app/tmp/' . $guid);
        $zip->close();
        @unlink($zipFilename);

        foreach (glob($this->app['root_directory'] . '/app/tmp/' . $guid . "/*.gpx") as $absolute_filename) {
            $fileinfo = pathinfo($absolute_filename);
            if (preg_match('/-wpts\.gpx$/', $fileinfo['basename'])) {
                unlink($absolute_filename);
                continue;
            }
            $content = file_get_contents($absolute_filename);
            unlink($absolute_filename);
        }

        rmdir($this->app['root_directory'] . '/app/tmp/' . $guid);
        return $content;
    }
}
