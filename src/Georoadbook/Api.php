<?php

namespace Georoadbook;

use Geocaching\Api\GeocachingApi;
use GuzzleHttp\Client;
use Silex\Application;

class Api
{
    protected $api = null;
    protected $app = null;


    public function __construct(Application $app)
    {
        $this->app = $app;
        if ($this->app['session']->get('accessToken')) {
            $this->api = new GeocachingApi(new Client(), $this->app['session']->get('accessToken'), true);
        }
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
