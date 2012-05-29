<?php

App::uses('SmartyBaseHelper', 'SmartyView.View/Helper');

/**
 * SmartyHtml Helper class for wrapping HtmlHelper methods
 *
 * @package 	smartyview
 * @subpackage	view.helper
 */
class SmartyJavascriptHelper extends SmartyBaseHelper {
    public $name = 'javascript';
    public $helpers = array('Javascript');
}
