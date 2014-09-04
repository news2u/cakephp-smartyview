<?php

App::uses('SmartyBaseHelper', 'SmartyView.View/Helper');

/**
 * SmartyHtml Helper class for wrapping HtmlHelper methods
 *
 * @package 	smartyview
 * @subpackage	view.helper
 */
class SmartyHtmlHelper extends SmartyBaseHelper {
	public $helpers = array('Html');
    public $name = 'html';
}
