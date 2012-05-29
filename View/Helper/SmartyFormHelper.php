<?php
App::uses('SmartyBaseHelper', 'SmartyView.View/Helper');

/**
 * SmartyForm Helper class for wrapping FormHelper methods.
 *
 * @package 	smartyview
 * @subpackage	view.helper
 */

class SmartyFormHelper extends SmartyBaseHelper {
	public $name = 'form';
	public $helpers = array('Form');
}
