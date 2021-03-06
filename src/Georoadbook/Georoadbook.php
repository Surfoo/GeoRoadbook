<?php

/**
 * Georoadbook Class.
 *
 * @author  Surfoo <surfooo@gmail.com>
 *
 * @link    https://github.com/Surfoo/georoadbook
 *
 * @license http://opensource.org/licenses/eclipse-2.0.php
 */

namespace Georoadbook;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Georoadbook
{
    protected $app = null;

    const ID_LENGTH = 7;

    public $id = null;

    public $gpx = null;

    public $html = null;

    public $locale = null;

    protected $bbcode_smileys = [':)'  => 'icon_smile.gif',
                                 ':D'  => 'icon_smile_big.gif',
                                 '8D'  => 'icon_smile_cool.gif',
                                 ':I'  => 'icon_smile_blush.gif',
                                 ':P'  => 'icon_smile_tongue.gif',
                                 '}:)' => 'icon_smile_evil.gif',
                                 ';)'  => 'icon_smile_wink.gif',
                                 ':o)' => 'icon_smile_clown.gif',
                                 'B)'  => 'icon_smile_blackeye.gif',
                                 '8'   => 'icon_smile_8ball.gif',
                                 ':('  => 'icon_smile_sad.gif',
                                 '8)'  => 'icon_smile_shy.gif',
                                 ':O'  => 'icon_smile_shock.gif',
                                 ':(!' => 'icon_smile_angry.gif',
                                 'xx(' => 'icon_smile_dead.gif',
                                 '|)'  => 'icon_smile_sleepy.gif',
                                 ':X'  => 'icon_smile_kisses.gif',
                                 '^'   => 'icon_smile_approve.gif',
                                 'V'   => 'icon_smile_dissapprove.gif',
                                 '?'   => 'icon_smile_question.gif',
                                ];
    protected $bbcode_colors = ['black',
                                'blue',
                                'gold',
                                'green',
                                'maroon',
                                'navy',
                                'orange',
                                'pink',
                                'purple',
                                'red',
                                'teal',
                                'white',
                                'yellow',
                                ];

    /**
     * @param Application $app
     * @param string      $id
     */
    public function __construct(Application $app, $id = null)
    {
        $this->app = $app;

        if (empty($id)) {
            $this->id = self::setId();
        } else {
            if (!ctype_alnum($id)) {
                return new Response('Not found', 404);
            }
            $this->id = basename($id);
        }

        if (empty($this->id)) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getRoadbookPath()
    {
        return $this->app['roadbook_dir'] . sprintf('/%s', $this->id);
    }

    /**
     * @return string
     */
    public function getGpxFile()
    {
        return $this->app['roadbook_dir'] . sprintf('/%s.gpx', $this->id);
    }

    /**
     * @return string
     */
    public function getHtmlFile()
    {
        return $this->app['roadbook_dir'] . sprintf('/%s.html', $this->id);
    }

    /**
     * @return string
     */
    public function getJsonFile()
    {
        return $this->app['roadbook_dir'] . sprintf('/%s.json', $this->id);
    }

    /**
     * @return string
     */
    public function getPdfFile()
    {
        return $this->app['roadbook_dir'] . '/pdf/' . sprintf('/%s.pdf', $this->id);
    }

    /**
     * create
     *
     * @param string $gpx
     *
     * @return bool
     */
    public function create($gpx)
    {
        $this->gpx = $gpx;

        if (!$this->saveFile($this->getGpxFile(), $this->gpx)) {
            return false;
        }

        return true;
    }

    /**
     * delete.
     *
     * @return bool
     */
    public function delete()
    {
        $pattern = $this->app['roadbook_dir'] . '/' . $this->id . '.*';

        foreach (glob($pattern) as $file) {
            @unlink($file);
        }

        @unlink($this->getPdfFile());

        return true;
    }

    /**
     * getLastSavedDate.
     *
     * @return string
     */
    public function getLastSavedDate()
    {
        return date('Y-m-d H:i:s', filemtime($this->getHtmlFile()));
    }

    /**
     * @param  boolean $experimental
     * 
     * @return bool
     */
    public function handleExport($experimental = false): bool
    {
        if (!$experimental) {
            return $this->phantomJSexport();
        } else {
            return $this->chromeExport();
        }
    }

    /**
     * @return bool
     */
    protected function phantomJSexport()
    {
        $cmd = escapeshellcmd('/usr/bin/phantomjs ' . $this->app['root_directory'] . '/html2pdf.js ' . $this->id . ' ' . $_SERVER['HTTP_HOST']);
        if ($this->app['debug']) {
            $cmd .= ' 2>&1';
        }
        $result = shell_exec($cmd);
        if (!is_null($result)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function chromeExport()
    {
        $tempDirectory = $this->app['root_directory'] . '/app/tmp/' . $this->id;
        if (!file_exists($tempDirectory)) {
            mkdir($tempDirectory);
        }
        chdir($tempDirectory);

        $cmd = escapeshellcmd('/usr/bin/google-chrome-stable --headless --disable-gpu --print-to-pdf http://' . $_SERVER['HTTP_HOST'] . '/roadbook/' . $this->id . '?raw');
        if ($this->app['debug']) {
            $cmd .= ' 2>&1';
        }
        exec($cmd, $output);

        $tempOutput = $tempDirectory . '/output.pdf';
        if (!file_exists($tempOutput)) {
            rmdir($tempDirectory);
            return false;
        }

        rename($tempOutput, $this->app['root_directory'] . '/web/roadbook/pdf/' . $this->id . '.pdf');
        rmdir($tempDirectory);
        return true;
    }

    /**
     * getCustomCss.
     *
     * @return string
     */
    public function getCustomCss()
    {
        $cssOptions = json_decode(file_get_contents($this->getJsonFile()), true);
        if (is_null($cssOptions)) {
            return '';
        }

        $cssOptions = array_map('trim', $cssOptions);

        $customCSS_format = '@page{%s}';
        $customHeaderFooter = '@%s{%s:"%s"}';
        $pageOptions = '';

        $globalOptions['size'] = sprintf('%s %s', $cssOptions['page_size'], $cssOptions['orientation']);
        $globalOptions['margin'] = (int) $cssOptions['margin_top'].'mm '.
                                   (int) $cssOptions['margin_right'].'mm '.
                                   (int) $cssOptions['margin_bottom'].'mm '.
                                   (int) $cssOptions['margin_left'].'mm';
        foreach ($globalOptions as $key => $value) {
            $pageOptions .= sprintf('%s: %s;', $key, $value);
        }

        if (!empty($cssOptions['header_text'])) {
            $pageOptions .= sprintf($customHeaderFooter, 'top-'.$cssOptions['header_align'], 'content', htmlspecialchars($cssOptions['header_text']));
        }
        if (!empty($cssOptions['footer_text'])) {
            $pageOptions .= sprintf($customHeaderFooter, 'bottom-'.$cssOptions['footer_align'], 'content', htmlspecialchars($cssOptions['footer_text']));
        }

        return sprintf($customCSS_format, $pageOptions);
    }

    /**
     * saveOptions.
     *
     * @param string $options
     *
     * @return bool
     */
    public function saveOptions($options)
    {
        $cssOptions = $options;
        $hd = fopen($this->getJsonFile(), 'w');
        if (!$hd) {
            return false;
        }
        fwrite($hd, json_encode($cssOptions));
        fclose($hd);

        return true;
    }

    /**
     * downloadPdf.
     */
    public function downloadPdf()
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=roadbook.pdf');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($this->getPdfFile()));
        ob_clean();
        flush();
        readfile($this->getPdfFile());
        exit(0);
    }

    /**
     * downloadZip.
     */
    public function downloadZip()
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }
        $zip = new \ZipArchive();
        $filename_zip = sprintf($this->app['root_directory'] . '/web/roadbook/zip/%s.%s', $this->id, 'zip');

        if ($zip->open($filename_zip, \ZipArchive::CREATE) !== true) {
            exit('Unable to open '.basename($filename_zip));
        }

        $params = ['style' => $this->getCustomCss(),
                        'content' => file_get_contents($this->getHtmlFile()), ];
        $zip->addFromString('roadbook/'.basename($this->getHtmlFile()), $this->app['twig']->render('raw.twig.html', $params));

        $zip->addFile($this->app['root_directory'] . '/web/design/roadbook.css', 'design/roadbook.css');
        $this->recurseZip($this->app['root_directory'] . '/web/img', $zip);
        $zip->close();

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=roadbook.zip');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($filename_zip));
        ob_clean();
        flush();
        readfile($filename_zip);
        unlink($filename_zip);
        exit(0);
    }

    /**
     * recurseZip.
     *
     * @param string     $src
     * @param \ZipArchive $zip
     */
    private function recurseZip($src, \ZipArchive &$zip)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurseZip($src.'/'.$file, $zip);
                } else {
                    $zip->addFile($src.'/'.$file, substr($src.'/'.$file, strlen(dirname(__DIR__).'/web/roadbook/zip')));
                }
            }
        }
        closedir($dir);
    }

    /**
     * setId.
     *
     * @return string
     */
    protected static function setId(): string
    {
        return substr(md5(uniqid((string) mt_rand(), true)), 0, self::ID_LENGTH);
    }

    /**
     * saveFile.
     *
     * @param string $filename
     * @param string $content
     *
     * @return bool
     */
    public function saveFile($filename, $content = '')
    {
        $hd = fopen($filename, 'w');
        if (!$hd) {
            return false;
        }
        fwrite($hd, $content);
        fclose($hd);

        return true;
    }

    /**
     * convertXmlToHtml.
     *
     * @param string $locale
     * @param array  $options
     *
     * @return object $this
     */
    public function convertXmlToHtml($locale, $options)
    {
        $this->locale = $locale;

        $xsldoc = new \DOMDocument();
        $xsldoc->load($this->app['root_directory'] . '/app/templates/xslt/roadbook.xslt');
        $xsl = new \XSLTProcessor();
        $xsl->importStyleSheet($xsldoc);
        $xsl->setParameter('', 'locale_filename', $this->getLocaleFile());
        $xsl->setParameter('', 'icon_cache_dir', $this->app['icon_cache_dir']);
        $xsl->setParameter('', $options);

        $xml = new \DOMDocument();
        $xml->loadXML($this->gpx);
        $this->html = $xsl->transformToXML($xml);
        $this->html = preg_replace('/<\?xml[^>]*\?>/i', '', $this->html);
        $this->html = htmlspecialchars_decode($this->html);

        // Remove comments
        $this->html = preg_replace('#<!--.*-->#msU', '', $this->html);

        // Remove waypoints
        $this->html = preg_replace('#<p>Additional [Hidden\s]+?Waypoints</p>.*(</div>)#msU', '$1', $this->html);

        return $this;
    }

    /**
     * cleanHtml.
     */
    public function cleanHtml()
    {
        // http://tidy.sourceforge.net/docs/quickref.html
        if (is_null($this->html)) {
            return false;
        }

        $config = ['doctype' => 'html',
                   'output-xhtml' => true,
                   'wrap' => 0, ];
        $tidy = new \tidy();
        $tidy->parseString($this->html, $config, 'utf8');
        $tidy->cleanRepair();
        //echo $tidy->errorBuffer . "\n";
        $this->html = $tidy;
    }

    /**
     * getOnlyBody.
     */
    public function getOnlyBody()
    {
        if (preg_match('/<body>(.*)<\/body>/msU', $this->html, $match)) {
            $this->html = $match[1];
        }
    }

    /**
     * addToC.
     */
    public function addToC()
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);

        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheTitle ')]");
        $gccodeNode = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheGCode ')]");

        $toc_content = [];
        foreach ($nodes as $node) {
            $toc_content[] = ['icon' => $node->firstChild->getAttribute('src'),
                                   'gccode' => false,
                                   'title' => $node->textContent, ];
        }
        foreach ($gccodeNode as $key => $node) {
            $toc_content[$key]['gccode'] = $node->textContent;
        }

        if (!empty($toc_content)) {
            $toc = new \DOMDocument();
            $toc->load($this->getLocaleFile());
            $xPath = new \DOMXPath($toc);
            $toc_i18n['title'] = $xPath->query("text[@id='toc_title']")->item(0)->nodeValue;
            $toc_i18n['name'] = $xPath->query("text[@id='toc_name']")->item(0)->nodeValue;
            $toc_i18n['page'] = $xPath->query("text[@id='toc_page']")->item(0)->nodeValue;

            $toc_html = $this->app['twig']->render('toc.twig.html', ['i18n' => $toc_i18n,
                                                                   'content' => $toc_content, ]);

            $frag = $dom->createDocumentFragment();
            $frag->appendXML($toc_html);

            $body = $dom->getElementsByTagName('body')->item(0);
            $first = $body->getElementsByTagName('div')->item(0);
            $body->insertBefore($frag, $first);
            // $body->appendChild($frag);
            $this->html = $dom->saveHtml();
        }
    }

    /**
     * removeImages.
     */
    public function removeImages($display_short_desc)
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        if ($display_short_desc) {
            $finder = new \DOMXPath($dom);
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' short_description ')]");
            foreach ($nodes as $node) {
                $imagesNodeList = $node->getElementsByTagName('img');
                $domElemsToRemove = [];
                foreach ($imagesNodeList as $domElement) {
                    $domElemsToRemove[] = $domElement;
                }
                foreach ($domElemsToRemove as $domElement) {
                    $domElement->parentNode->removeChild($domElement);
                }
            }

            $this->html = $dom->saveHtml();
        }

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' long_description ')]");
        foreach ($nodes as $node) {
            $imagesNodeList = $node->getElementsByTagName('img');
            $domElemsToRemove = [];
            foreach ($imagesNodeList as $domElement) {
                $domElemsToRemove[] = $domElement;
            }
            foreach ($domElemsToRemove as $domElement) {
                $domElement->parentNode->removeChild($domElement);
            }
        }

        $this->html = $dom->saveHtml();
    }

    /**
     * addSpoilers.
     */
    public function addSpoilers()
    {
        $xml = new \DOMDocument();
        $xml->loadXML($this->gpx);
        $waypoints = $xml->getElementsByTagName('wpt');
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        foreach ($waypoints as $waypoint) {
            $long_description = $waypoint->getElementsByTagNameNS('http://www.groundspeak.com/cache/1/0/1', 'long_description');
            if (empty($long_description->length)) {
                continue;
            }
            if (preg_match_all('/<!-- Spoiler4Gpx \[([^]]*)\]\(([^)]*)\) -->/', $long_description->item(0)->nodeValue, $spoilers, PREG_SET_ORDER)) {
                $gccode = $waypoint->getElementsByTagName('name')->item(0)->nodeValue;
                foreach ($spoilers as $spoiler) {
                    $nodes = $finder->query("//div[@data-cache-id='".$gccode."']/div[@class='cacheSpoilers']");
                    if (empty($nodes->length)) {
                        continue;
                    }
                    $frag = $dom->createDocumentFragment();
                    $frag->appendXML('<p>Spoilers</p>'."\n");
                    foreach ($nodes as $node) {
                        $node->appendChild($frag);
                        $frag = $dom->createDocumentFragment();
                        $frag->appendXML('<![CDATA[<img src="'.$spoiler[2].'" alt="'.$spoiler[1].'"/><br />'."\n]]>");
                        $node->appendChild($frag);
                    }
                }
            }
        }
        $this->html = $dom->saveHtml();
    }

    /**
     * addWaypoints.
     */
    public function addWaypoints()
    {
        $xml = new \DOMDocument();
        $xml->loadXML($this->gpx);
        $waypoints = $xml->getElementsByTagName('wpt');
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        foreach ($waypoints as $waypoint) {
            $long_description = $waypoint->getElementsByTagNameNS('http://www.groundspeak.com/cache/1/0/1', 'long_description');
            if (empty($long_description->length)) {
                continue;
            }

            if (!preg_match('#<p>Additional [Hidden\s]+?Waypoints</p>#i', $long_description->item(0)->nodeValue, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $data = substr($long_description->item(0)->nodeValue, $matches[0][1] + strlen($matches[0][0]));
            if (!$data) {
                continue;
            }

            $details_waypoints = explode('<br />', $data);
            array_pop($details_waypoints);
            $details_waypoints = array_chunk($details_waypoints, 3);

            $gccode = $waypoint->getElementsByTagName('name')->item(0)->nodeValue;
            $nodes = $finder->query("//div[@data-cache-id='".$gccode."']//*[@class='cacheWaypoints']");
            $frag = $dom->createDocumentFragment();
            $frag->appendXML('<p>Waypoints</p>'."\n");

            if (!empty($nodes->length)) {
                $nodes->item(0)->appendChild($frag);
            }

            foreach ($details_waypoints as $wpt_data) {
                $title = preg_replace('/ GC[\w]+/', ' ', $wpt_data[0]);
                $coordinates = '';
                if ($wpt_data[1] !== '' && strpos($wpt_data[1], 'N/S') !== 0) {
                    $coordinates = ' - '.trim(html_entity_decode($wpt_data[1]));
                }
                $comment = $wpt_data[2];

                $frag_wpt = $dom->createDocumentFragment();
                $frag_wpt->appendXML('<![CDATA[<p><strong>'.$title.$coordinates.'</strong><br />'.$comment."</p>\n]]>");

                if (!empty($nodes->length)) {
                    $nodes->item(0)->appendChild($frag_wpt);
                }
            }
        }
        $this->html = $dom->saveHtml();
    }

    /**
     * encryptHints.
     */
    public function encryptHints()
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheHintContent ')]");
        foreach ($nodes as $node) {
            $chars = str_split($node->textContent);
            $encode = true;
            foreach ($chars as &$char) {
                if (in_array($char, ['['])) {
                    $encode = false;
                    continue;
                }
                if (in_array($char, [']'])) {
                    $encode = true;
                    continue;
                }
                if ($encode) {
                    $char = str_rot13($char);
                }
            }
            $node->nodeValue = implode('', $chars);
        }
        $this->html = $dom->saveHtml();
    }

    /**
     * parseMarkdown.
     *
     * @return object
     */
    public function parseMarkdown()
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheLogText ')]");

        $MdParser = new \cebe\markdown\Markdown();
        foreach ($nodes as $node) {
            $raw_log = $node->ownerDocument->saveHTML($node);
            $raw_log = trim(str_replace(['<td class="cacheLogText" colspan="2">', '</td>'], '', $raw_log));
            $log = preg_replace('/<br>$/', '', $raw_log);

            $node->nodeValue = $MdParser->parse($log);
        }

        $this->html = htmlspecialchars_decode($dom->saveHtml());

        return $this;
    }

    /**
     * parseBBcode.
     */
    public function parseBBcode()
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($this->html);
        libxml_clear_errors();

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheLogText ')]");

        $parser = new \JBBCode\Parser();
        $parser->addCodeDefinitionSet(new \JBBCode\DefaultCodeDefinitionSet());

        foreach ($this->bbcode_colors as $color) {
            $builder = new \JBBCode\CodeDefinitionBuilder($color, '<span style="color: '.$color.';">{param}</span>');
            $parser->addCodeDefinition($builder->build());
        }

        foreach ($nodes as $node) {
            $raw_log = $node->ownerDocument->saveHTML($node);
            $raw_log = trim(str_replace(['<td class="cacheLogText" colspan="2">', '</td>'], '', $raw_log));
            $log = preg_replace('/<br>$/', '', $raw_log);
            $parser->parse($log);
            $node->nodeValue = $parser->getAsHtml();
        }

        //$smileyVisitor = new \JBBCode\visitors\SmileyVisitor();
        //$parser->accept($smileyVisitor);
        $this->html = htmlspecialchars_decode($dom->saveHtml());

        $bbcodes = array_keys($this->bbcode_smileys);
        $images = array_values($this->bbcode_smileys);
        foreach ($images as $k => &$image) {
            $image = '<img src="../images/icons/'.$image.'" alt="'.$bbcodes[$k].'" />';
        }
        foreach ($bbcodes as &$bbcode) {
            $bbcode = '['.$bbcode.']';
        }

        $this->html = str_replace($bbcodes, $images, $this->html);
    }

    /**
     * Return the path of the locale file
     * @return string
     */
    protected function getLocaleFile()
    {
        return $this->app['root_directory'] . '/app/locales/' . sprintf('%s.%s', $this->locale, 'xml');
    }
}
