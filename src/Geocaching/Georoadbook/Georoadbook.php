<?php
/**
 * Georoadbook Class
 *
 * @author  Surfoo <surfooo@gmail.com>
 * @link    https://github.com/Surfoo/georoadbook
 * @license http://opensource.org/licenses/eclipse-2.0.php
 * @package georoadbook
 */

class Georoadbook
{
    const ID_LENGTH = 7;

    public $id     = null;

    public $gpx    = null;

    public $html   = null;

    public $locale = null;

    public $debug  = false;

    protected $bbcode_smileys = array(':)'  => 'icon_smile.gif',
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
                                );
    protected $bbcode_colors = array('black',
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
                                );

    /**
     * ajaxRequestOnly
     * @return void
     */
    public static function ajaxRequestOnly()
    {
        if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
            header("HTTP/1.0 400 Bad Request");
            exit(0);
        }
    }

    /**
     * __construct
     * @param string $id
     */
    public function __construct($id = '')
    {
        if (empty($id)) {
            $this->id = self::setId();
        } else {
            if (!ctype_alnum($id)) {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
            $this->id = basename($id);
        }

        if (empty($this->id)) {
            return false;
        }
        $this->gpx_file  = ROADBOOKS_DIR . sprintf('%s.%s', $this->id, 'gpx');
        $this->html_file = ROADBOOKS_DIR . sprintf('%s.%s', $this->id, 'html');
        $this->json_file = ROADBOOKS_DIR . sprintf('%s.%s', $this->id, 'json');
        $this->pdf_file  = ROADBOOKS_DIR . 'pdf/' . sprintf('%s.%s', $this->id, 'pdf');
    }

    /**
     * create
     * @param  string  $gpx
     * @return boolean
     */
    public function create($gpx)
    {
        self::ajaxRequestOnly();

        $this->gpx = $gpx;

        if (!$this->saveFile($this->gpx_file, $this->gpx)) {
            return false;
        }

        return true;
    }

    /**
     * delete
     * @return boolean
     */
    public function delete()
    {
        self::ajaxRequestOnly();

        $pattern = ROADBOOKS_DIR . $this->id . '.*';

        foreach (glob($pattern) as $file) {
            @unlink($file);
        }

        @unlink($this->pdf_file);

        return true;
    }

    /**
     * getLastSavedDate
     * @return string
     */
    public function getLastSavedDate()
    {
        return date('Y-m-d H:i:s', filemtime($this->html_file));
    }

    /**
     * export
     * @return boolean
     */
    public function export()
    {
        self::ajaxRequestOnly();

        $cmd = escapeshellcmd('/usr/bin/phantomjs ' . ROOT . '/html2pdf.js ' . $this->id . ' ' . $_SERVER['HTTP_HOST']);
        if ($this->debug) {
            $cmd .= ' 2>&1';
        }
        $this->result = shell_exec($cmd);
        if (!is_null($this->result)) {
            return false;
        }

        return true;
    }

    /**
     * getCustomCss
     * @return string
     */
    public function getCustomCss()
    {
        $this->options_css = json_decode(file_get_contents($this->json_file), true);
        if (is_null($this->options_css)) {
            return false;
        }

        $this->options_css = array_map('trim', $this->options_css);

        $customCSS_format = "@page{%s}";
        $customHeaderFooter = '@%s{%s:"%s"}';
        $pageOptions = '';

        $globalOptions['size'] = sprintf('%s %s', $this->options_css['page_size'], $this->options_css['orientation']);
        $globalOptions['margin'] = (int) $this->options_css['margin_top'] . 'mm ' .
                                   (int) $this->options_css['margin_right'] . 'mm ' .
                                   (int) $this->options_css['margin_bottom'] . 'mm ' .
                                   (int) $this->options_css['margin_left'] . 'mm';
        foreach ($globalOptions as $key => $value) {
            $pageOptions.= sprintf('%s: %s;', $key, $value);
        }

        if (!empty($this->options_css['header_text'])) {
            $pageOptions.= sprintf($customHeaderFooter, 'top-' . $this->options_css['header_align'], 'content', htmlspecialchars($this->options_css['header_text']));
        }
        if (!empty($this->options_css['footer_text'])) {
            $pageOptions.= sprintf($customHeaderFooter, 'bottom-' . $this->options_css['footer_align'], 'content', htmlspecialchars($this->options_css['footer_text']));
        }

        return sprintf($customCSS_format, $pageOptions);
    }

    /**
     * saveOptions
     * @param  string  $options
     * @return boolean
     */
    public function saveOptions($options)
    {
        self::ajaxRequestOnly();

        $this->options_css = $options;
        $hd = fopen($this->json_file, 'w');
        if (!$hd) {
            return false;
        }
        fwrite($hd, json_encode($this->options_css));
        fclose($hd);

        return true;
    }

    /**
     * downloadPdf
     * @return void
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
        header('Content-Length: ' . filesize($this->pdf_file));
        ob_clean();
        flush();
        readfile($this->pdf_file);
        exit(0);
    }

    /**
     * downloadZip
     * @return void
     */
    public function downloadZip()
    {
        if(!class_exists('ZipArchive')) {
            return false;
        }
        $zip = new \ZipArchive();
        $filename_zip = sprintf('%s.%s', $this->id, 'zip');

        if ($zip->open($filename_zip, \ZIPARCHIVE::CREATE) !== true) {
            exit('Unable to open ' . basename($filename_zip));
        }

        Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(TEMPLATE_DIR);
        $twig   = new \Twig_Environment($loader, array('debug' => false, 'cache' => TEMPLATE_COMPILED_DIR));
        $twig_vars = array('style' => $this->getCustomCss(),
                           'content' => file_get_contents($this->html_file));
        $zip->addFromString('roadbook/' . basename($this->html_file), $twig->render('raw.tpl', $twig_vars));

        $zip->addFile(ROOT . '/web/design/roadbook.css', 'design/roadbook.css');
        $this->recurse_zip(ROOT . '/web/img', $zip);
        $zip->close();

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=roadbook.zip');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename_zip));
        ob_clean();
        flush();
        readfile($filename_zip);
        unlink($filename_zip);
        exit(0);
    }

    /**
     * recurseZip
     * @param  string     $src
     * @param  ZipArchive &$zip
     * @return void
     */
    private function recurseZip($src, ZipArchive &$zip)
    {
        $dir = opendir($src);
        while (false !== ($file=readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseZip($src . '/' . $file, $zip);
                } else {
                    $zip->addFile($src . '/' . $file, substr($src . '/' . $file, strlen(dirname(__DIR__) . '/web/')));
                }
            }
        }
        closedir($dir);
    }

    /**
     * setId
     * @return string
     */
    protected static function setId()
    {
        return substr(md5(uniqid(mt_rand(), true)), 0, self::ID_LENGTH);
    }

    /**
     * saveFile
     * @param  string  $filename
     * @param  string  $content
     * @return boolean
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
     * convertXmlToHtml
     * @param  string $locale
     * @param  array  $options
     * @return object $this
     */
    public function convertXmlToHtml($locale, $options)
    {
        $this->locale = $locale;

        $xsldoc = new \DOMDocument();
        $xsldoc->load(ROOT . '/templates/xslt/roadbook.xslt');
        $xsl = new \XSLTProcessor();
        $xsl->importStyleSheet($xsldoc);
        $xsl->setParameter('', 'locale_filename', ROOT . '/locales/' . sprintf('%s.%s', $this->locale, 'xml'));
        $xsl->setParameter('', 'icon_cache_dir', ICON_CACHE_DIR);
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
     * cleanHtml
     * @return void
     */
    public function cleanHtml()
    {
        // http://tidy.sourceforge.net/docs/quickref.html
        if (is_null($this->html)) {
            return false;
        }

        $config = array('doctype'      => 'html',
                        'output-xhtml' => true,
                        'wrap'         => 0);
        $tidy = new \tidy;
        $tidy->parseString($this->html, $config, 'utf8');
        $tidy->cleanRepair();
        //echo $tidy->errorBuffer . "\n";
        $this->html = $tidy;
    }

    /**
     * getOnlyBody
     * @return void
     */
    public function getOnlyBody()
    {
        if(preg_match('/<body>(.*)<\/body>/msU', $this->html, $match)) {
            $this->html = $match[1];
        }
    }

    /**
     * addToC
     * @return void
     */
    public function addToC()
    {
        $dom = new \DomDocument();
        $dom->loadHTML($this->html);

        $finder = new \DomXPath($dom);

        $nodes  = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheTitle ')]");
        $gccodeNode = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheGCode ')]");

        $toc_content = array();
        foreach ($nodes as $node) {
            $toc_content[] = array('icon'   => $node->firstChild->getAttribute('src'),
                                   'gccode' => false,
                                   'title'  => $node->textContent);
        }
        foreach ($gccodeNode as $key => $node) {
            $toc_content[$key]['gccode'] = $node->textContent;
        }

        if (!empty($toc_content)) {
            $toc = new \DomDocument();
            $toc->load(ROOT . '/locales/' . sprintf('%s.xml', $this->locale));
            $xPath = new \DOMXPath($toc);
            $toc_i18n['title'] = $xPath->query("text[@id='toc_title']")->item(0)->nodeValue;
            $toc_i18n['name']  = $xPath->query("text[@id='toc_name']")->item(0)->nodeValue;
            $toc_i18n['page']  = $xPath->query("text[@id='toc_page']")->item(0)->nodeValue;

            \Twig_Autoloader::register();
            $loader = new \Twig_Loader_Filesystem(TEMPLATE_DIR);
            $twig   = new \Twig_Environment($loader, array('cache' => false));

            $toc_html = $twig->render('toc.tpl', array('i18n'    => $toc_i18n,
                                                       'content' => $toc_content));

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
     * removeImages
     * @return void
     */
    public function removeImages($display_short_desc)
    {
        $dom = new \DomDocument();
        $dom->loadHTML($this->html);

        if ($display_short_desc) {
            $finder = new \DomXPath($dom);
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' short_description ')]");
            foreach ($nodes as $node) {
                $imagesNodeList = $node->getElementsByTagName('img');
                $domElemsToRemove = array();
                foreach ($imagesNodeList as $domElement) {
                    $domElemsToRemove[] = $domElement;
                }
                foreach ($domElemsToRemove as $domElement) {
                    $domElement->parentNode->removeChild($domElement);
                }
            }

            $this->html = $dom->saveHtml();
        }

        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' long_description ')]");
        foreach ($nodes as $node) {
            $imagesNodeList = $node->getElementsByTagName('img');
            $domElemsToRemove = array();
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
     * addSpoilers
     * @return void
     */
    public function addSpoilers()
    {
        $xml = new \DOMDocument();
        $xml->loadXML($this->gpx);
        $waypoints = $xml->getElementsByTagName('wpt');
        $dom = new \DomDocument();
        $dom->loadHTML($this->html);
        $finder = new \DomXPath($dom);
        foreach($waypoints as $waypoint) {
            $long_description = $waypoint->getElementsByTagNameNS('http://www.groundspeak.com/cache/1/0/1', 'long_description');
            if(empty($long_description->length)) {
                continue;
            }
            if(preg_match_all('/<!-- Spoiler4Gpx \[([^]]*)\]\(([^)]*)\) -->/', $long_description->item(0)->nodeValue, $spoilers, PREG_SET_ORDER)) {
                $gccode = $waypoint->getElementsByTagName('name')->item(0)->nodeValue;
                foreach ($spoilers as $spoiler) {
                    $nodes = $finder->query("//div[@data-cache-id='".$gccode."']/div[@class='cacheSpoilers']");
                    if(empty($nodes->length)) {
                        continue;
                    }
                    $frag = $dom->createDocumentFragment();
                    $frag->appendXML('<p>Spoilers</p>'."\n");
                    foreach($nodes as $node) {
                        $node->appendChild($frag);
                        $frag = $dom->createDocumentFragment();
                        $frag->appendXML('<![CDATA[<img src="' . $spoiler[2] . '" alt="' . $spoiler[1] . '"/><br />'."\n]]>");
                        $node->appendChild($frag);
                    }
                }
            }
        }
        $this->html = $dom->saveHtml();
    }

    /**
     * addWaypoints
     * @return void
     */
    public function addWaypoints()
    {
        $xml = new \DOMDocument();
        $xml->loadXML($this->gpx);
        $waypoints = $xml->getElementsByTagName('wpt');
        $dom = new \DomDocument();
        $dom->loadHTML($this->html);
        $finder = new \DomXPath($dom);
        foreach($waypoints as $waypoint) {
            $long_description = $waypoint->getElementsByTagNameNS('http://www.groundspeak.com/cache/1/0/1', 'long_description');
            if(empty($long_description->length)) {
                continue;
            }

            if(!preg_match('#<p>Additional [Hidden\s]+?Waypoints</p>#i', $long_description->item(0)->nodeValue, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $data = substr($long_description->item(0)->nodeValue, $matches[0][1] + strlen($matches[0][0]));
            if(!$data) {
                continue;
            }

            $details_waypoints = explode('<br />', $data);
            array_pop($details_waypoints);
            $details_waypoints = array_chunk($details_waypoints, 3);

            $gccode = $waypoint->getElementsByTagName('name')->item(0)->nodeValue;
            $nodes  = $finder->query("//div[@data-cache-id='".$gccode."']//*[@class='cacheWaypoints']");
            $frag   = $dom->createDocumentFragment();
            $frag->appendXML('<p>Waypoints</p>'."\n");

            if(!empty($nodes->length)) {
                $nodes->item(0)->appendChild($frag);
            }

            foreach($details_waypoints as $wpt_data) {
                $title       = preg_replace('/ GC[\w]+/', ' ', $wpt_data[0]);
                $coordinates = '';
                if($wpt_data[1] !== '' && strpos($wpt_data[1], 'N/S') !== 0) {
                    $coordinates = ' - ' . trim(html_entity_decode($wpt_data[1]));
                }
                $comment     = $wpt_data[2];

                $frag_wpt = $dom->createDocumentFragment();
                $frag_wpt->appendXML('<![CDATA[<p><strong>' . $title . $coordinates . '</strong><br />' . $comment . "</p>\n]]>");

                if(!empty($nodes->length)) {
                    $nodes->item(0)->appendChild($frag_wpt);
                }
            }
        }
        $this->html = $dom->saveHtml();
    }

    /**
     * encryptHints
     * @return void
     */
    public function encryptHints()
    {
        $dom = new \DomDocument();
        $dom->loadHTML($this->html);
        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheHintContent ')]");
        foreach ($nodes as $node) {
            $chars = str_split($node->textContent);
            $encode = true;
            foreach($chars as &$char) {
                if(in_array($char, array('['))) {
                    $encode = false;
                    continue;
                }
                if(in_array($char, array(']'))) {
                    $encode = true;
                    continue;
                }
                if($encode) {
                    $char = str_rot13($char);
                }
            }
            $node->nodeValue = implode('', $chars);
        }
        $this->html = $dom->saveHtml();
    }

    /**
     * parseMarkdown
     * @return object
     */
    public function parseMarkdown()
    {
        $dom = new \DomDocument();
        $dom->loadHTML($this->html);

        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheLogText ')]");


        $MdParser = new \cebe\markdown\Markdown();
        foreach ($nodes as $node) {
            $raw_log = $node->ownerDocument->saveHTML($node);
            $raw_log = trim(str_replace(array('<td class="cacheLogText" colspan="2">', '</td>'), '', $raw_log));
            $log = preg_replace('/<br>$/', '', $raw_log);
            
            $node->nodeValue = $MdParser->parse($log);
        }

        $this->html = htmlspecialchars_decode($dom->saveHtml());

        return $this;
    }

    /**
     * parseBBcode
     * @return void
     */
    public function parseBBcode()
    {
        $dom = new \DomDocument();
        $dom->loadHTML($this->html);

        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' cacheLogText ')]");

        $parser = new JBBCode\Parser();
        $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());

        foreach ($this->bbcode_colors as $color) {
            $builder = new JBBCode\CodeDefinitionBuilder($color, '<span style="color: '.$color.';">{param}</span>');
            $parser->addCodeDefinition($builder->build());
        }

        foreach ($nodes as $node) {
            $raw_log = $node->ownerDocument->saveHTML($node);
            $raw_log = trim(str_replace(array('<td class="cacheLogText" colspan="2">', '</td>'), '', $raw_log));
            $log = preg_replace('/<br>$/', '', $raw_log);
            $parser->parse($log);
            $node->nodeValue = $parser->getAsHtml();
        }

        //$smileyVisitor = new JBBCode\visitors\SmileyVisitor();
        //$parser->accept($smileyVisitor);
        $this->html = htmlspecialchars_decode($dom->saveHtml());

        $bbcodes = array_keys($this->bbcode_smileys);
        $images  = array_values($this->bbcode_smileys);
        foreach ($images as $k=>&$image) {
            $image = '<img src="../images/icons/' . $image . '" alt="' . $bbcodes[$k] . '" />';
        }
        foreach ($bbcodes as &$bbcode) {
            $bbcode = '[' . $bbcode . ']';
        }

        $this->html = str_replace($bbcodes, $images, $this->html);
    }
}
