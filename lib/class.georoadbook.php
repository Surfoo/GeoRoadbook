<?php

/**
 * 
 */
class geoRoadbook {

    const ID_LENGTH = 16;

    public $id     = null;

    public $gpx    = null;

    public $html   = null;

    public $locale = null;

    public static function ajaxRequestOnly() {
        if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
            header("HTTP/1.0 400 Bad Request");
            exit(0);
        }
    }

    /**
     * __construct
     * @param string $id
     */
    public function __construct($id = '') {
        if(empty($id)) {
            $this->id = self::setId();
        }
        else {
            if(!ctype_alnum($id)) {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
            $this->id = basename($id);
        }

        if(empty($this->id)) {
            return false;
        }
        $this->gpx_file  = ROADBOOKS_DIR . sprintf('%s.%s', $this->id, 'gpx');
        $this->html_file = ROADBOOKS_DIR . sprintf('%s.%s', $this->id, 'html');
        $this->json_file = ROADBOOKS_DIR . sprintf('%s.%s', $this->id, 'json');
        $this->pdf_file  = ROADBOOKS_DIR . '/pdf/' . sprintf('%s.%s', $this->id, 'pdf');
    }

    /**
     * create
     * @param  [type] $gpx
     * @return [type]
     */
    public function create($gpx) {
        self::ajaxRequestOnly();

        $this->gpx = $gpx;

        if(!$this->saveFile($this->gpx_file, $this->gpx)) {
            return false;
        }

        return true;
    }

    /**
     * delete
     * @return [type]
     */
    public function delete() {
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
     * @return [type]
     */
    public function getLastSavedDate() {
        return date('Y-m-d H:i:s', filemtime($this->html_file));
    }

    /**
     * export
     * @return [type]
     */
    public function export() {
        self::ajaxRequestOnly();

        require LIB_DIR . 'class.weasyprint.php';

        $pdf = new WeasyPrint($this->html_file);
        if (!$pdf->saveAs($this->pdf_file, $this->getCustomCss())) {
            $this->export_errors = $pdf->getError();
            return false;
        }
        return true;
    }

    /**
     * getCustomCss
     * @return [type]
     */
    public function getCustomCss() {
        $this->options_css = json_decode(file_get_contents($this->json_file), true);
        if(is_null($this->options_css)) {
            return false;
        }

        $customCSS_format = "@page{%s}";
        $customHeaderFooter = '@%s{%s:"%s"}';
        $pageOptions = '';

        $globalOptions['size'] = sprintf('%s %s', $this->options_css['page_size'], $this->options_css['orientation']);
        $globalOptions['margin'] = (int) $this->options_css['margin_top'] . 'mm ' . (int) $this->options_css['margin_right'] . 'mm ' .
                                   (int) $this->options_css['margin_bottom'] . 'mm ' . (int) $this->options_css['margin_left'] . 'mm';
        foreach ($globalOptions as $key => $value) {
            $pageOptions.= sprintf('%s: %s;', $key, $value);
        }

        foreach ($this->options_css as &$value) {
            $value = str_replace('[page]', '"counter(page)"', $value);
            $value = str_replace('[topage]', '"counter(pages)"', $value);
            $value = preg_replace('/"{1,}/', '"', $value);
        }

        if (!empty($this->options_css['header_left'])) {
            $pageOptions.= sprintf($customHeaderFooter, 'top-left', 'content', $this->options_css['header_left']);
        }
        if (!empty($this->options_css['header_center'])) {
            $pageOptions.= sprintf($customHeaderFooter, 'top-center', 'content', $this->options_css['header_center']);
        }
        if (!empty($this->options_css['header_right'])) {
            $pageOptions.= sprintf($customHeaderFooter, 'top-right', 'content', $this->options_css['header_right']);
        }
        if (!empty($this->options_css['footer_left'])) {
            $pageOptions.= sprintf($customHeaderFooter, 'bottom-left', 'content', $this->options_css['footer_left']);
        }
        if (!empty($this->options_css['footer_center'])) {
            $pageOptions.= sprintf($customHeaderFooter, 'bottom-center', 'content', $this->options_css['footer_center']);
        }
        if (!empty($this->options_css['footer_right'])) {
            $pageOptions.= sprintf($customHeaderFooter, 'bottom-right', 'content', $this->options_css['footer_right']);
        }

        return sprintf($customCSS_format, $pageOptions);
    }

    /**
     * saveOptions
     * @param  [type] $options
     * @return [type]
     */
    public function saveOptions($options) {
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
     * @return [type]
     */
    public function downloadPdf() {
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
     * @return [type]
     */
    public function downloadZip() {
        $zip = new ZipArchive();
        $filename_zip = sprintf('%s.%s', $this->id, 'zip');

        if ($zip->open($filename_zip, ZIPARCHIVE::CREATE) !== true) {
            exit('Unable to open ' . basename($filename_zip));
        }

        $zip->addFromString('roadbook/' . basename($this->html_file), file_get_contents($this->html_file));
        $zip->addFile(dirname(__DIR__) . '/www/css/roadbook.css', 'css/roadbook.css');
        recurse_zip(dirname(__DIR__) . '/www/img', $zip);
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
     * setId
     */
    protected static function setId() {
        return substr(md5(uniqid(mt_rand(), true)), 0, self::ID_LENGTH);
    }

    /**
     * saveFile
     * @param  [type] $filename
     * @param  string $content
     * @return [type]
     */
    public function saveFile($filename, $content = '') {

        $hd = fopen($filename, 'w');
        if(!$hd) {
            return false;
        }
        fwrite($hd, $content);
        fclose($hd);

        return true;
    }

    /**
     * convertXmlToHtml
     * @param  [type] $locale
     * @param  [type] $options
     * @return [type]
     */
    public function convertXmlToHtml($locale, $options) {
        $this->locale = $locale;

        $xsldoc = new DOMDocument();
        $xsldoc->load(ROOT . '/templates/xslt/roadbook.xslt');
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet($xsldoc);
        $xsl->setParameter('', 'locale_filename', ROOT . '/locales/' . sprintf('%s.%s', $this->locale, 'xml'));
        $xsl->setParameter('', 'icon_cache_dir', ICON_CACHE_DIR);
        if(array_key_exists('note', $options))
            $xsl->setParameter('', 'display_note', $options['note']);
        if(array_key_exists('short_desc', $options))
            $xsl->setParameter('', 'display_short_desc', $options['short_desc']);
        if(array_key_exists('hint', $options))
            $xsl->setParameter('', 'display_hint', $options['hint']);
        if(array_key_exists('logs', $options))
            $xsl->setParameter('', 'display_logs', $options['logs']);

        $xml = new DOMDocument();
        $xml->loadXML($this->gpx);
        $this->html = $xsl->transformToXML($xml);
        $this->html = preg_replace('/<\?xml[^>]*\?>/i', '', $this->html);
        $this->html = preg_replace('/^<!DOCTYPE.*\s<html[^>]*>$/mi', '<!DOCTYPE html>'."\n".'<html lang="' . $this->locale . '">', trim($this->html));
        $this->html = htmlspecialchars_decode($this->html);
        return $this->html;
    }

    /**
     * cleanHtml
     * @return [type]
     */
    public function cleanHtml() {
        if(is_null($this->html)) {
            return false;
        }
        // http://tidy.sourceforge.net/docs/quickref.html
        $config = array(
                   'doctype'        => 'html',
                   'output-xhtml'   => true,
                   'wrap'           => 0);

        $tidy = new tidy;
        $tidy->parseString($this->html, $config, 'utf8');
        $tidy->cleanRepair();
        $this->html = $tidy;
        return $this->html;
    }

    /**
     * addToC
     */
    public function addToC() {
        $dom = new DomDocument();
        $dom->loadHTML($this->html);
        $finder = new DomXPath($dom);
        $classname="cacheTitle";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        $toc_content = array();
        foreach ($nodes as $node) {
            $icon  = $node->firstChild->getAttribute('src');
            $title = $node->textContent;
            $toc_content[] = array('icon' => $icon, 'title' => $title);
        }

        if (!empty($toc_content)) {
            $toc = new DomDocument();
            $toc->load(ROOT . '/locales/' . sprintf('%s.%s', $this->locale, 'xml'));
            $xPath = new DOMXPath($toc);
            $toc_i18n['title'] = $xPath->query("text[@id='toc_title']")->item(0)->nodeValue;
            $toc_i18n['name']  = $xPath->query("text[@id='toc_name']")->item(0)->nodeValue;
            $toc_i18n['page']  = $xPath->query("text[@id='toc_page']")->item(0)->nodeValue;

            require LIB_DIR . 'class.smarty_georoadbook.php';
            $smarty->assign('i18n', $toc_i18n);
            $smarty->assign('content', $toc_content);
            $toc_html = $smarty->fetch('toc.tpl');

            $frag = $dom->createDocumentFragment();
            $frag->appendXML($toc_html);

            $body = $dom->getElementsByTagName('body')->item(0);
            $first = $body->getElementsByTagName('div')->item(0);
            $body->insertBefore($frag, $first);
            // $body->appendChild($frag);
            $this->html = $dom->saveHtml();
            return $this->html;
        }
    }

    /**
     * encryptHints
     * @return [type]
     */
    public function encryptHints() {
        $dom = new DomDocument();
        $dom->loadHTML($this->html);
        $finder = new DomXPath($dom);
        $classname="cacheHintContent";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        foreach ($nodes as $node) {
            $encoded_hint = str_rot13($node->textContent);
            $node->nodeValue = $encoded_hint;
        }
        $this->html = $dom->saveHtml();
        return $this->html;
    }
}
