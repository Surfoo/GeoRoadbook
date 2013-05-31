<?php

// load Smarty library
require 'smarty/Smarty.class.php';

// The setup.php file is a good place to load
// required application library files, and you
// can do that right here. An example:
// require('guestbook/guestbook.lib.php');

class Smarty_Georoadbook extends Smarty
{
   public function __construct()
   {
        // Class Constructor.
        // These automatically get set with each new instance.

        parent::__construct();

        $this->setTemplateDir(TEMPLATE_DIR);
        $this->setCompileDir(TEMPLATE_COMPILED_DIR);
        //$this->setConfigDir('/web/www.example.com/guestbook/configs/');
        //$this->setCacheDir('/web/www.example.com/guestbook/cache/');

        //$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
        //$this->assign('app_name', 'Guest Book');
   }

}
$smarty = new Smarty_Georoadbook();
