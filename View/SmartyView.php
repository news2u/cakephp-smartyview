<?php
/**
 * CakePHP Smarty view class
 *
 * This class will allow using Smarty with CakePHP
 *
 * Original code is http://cakeforge.org/snippet/detail.php?type=snippet&id=6
 * and modified to work with CakePHP 2.0 and later as plugin.
 * 
 * @author       Daiji Hirata
 * @package      smartyview
 * @subpackage   view
 * @since        CakePHP v 2.0
 * @license      MIT License
 */

/**
 * Include Smarty.
 */
App::import('Vendor', 'Smarty', array('file' => 'smarty'.DS.'Smarty.class.php'));

/**
 * CakePHP Smarty view class
 *
 * This class will allow using Smarty with CakePHP
 *
 * @package      smartyview
 * @subpackage   view
 * @since        CakePHP v 2.0
 */
class SmartyView extends View
{
    public $loaded = array();
    public $ext = '.tpl';
    public $pluginName = 'SmartyView';

    public $smartyVersion;
    public $smartyMajorVersion;

    /**
     * SmartyView constructor
     *
     * @param  $controller instance of calling controller
     */
	function __construct (&$controller)
	{
		parent::__construct($controller);

		$this->Smarty = new Smarty();
		$this->_smartyVersion();

		$this->ext= '.tpl';
		$this->Smarty->compile_dir = TMP.'smarty'.DS.'compile'.DS;
		$this->Smarty->cache_dir = TMP.'smarty'.DS.'cache'.DS;
		$this->Smarty->error_reporting = 'E_ALL & ~E_NOTICE';
		$this->Smarty->debugging = true;
		$this->Smarty->caching = 0;
        switch ($this->smartyMajorVersion) {
            case 2:
                $this->Smarty->clear_compiled_tpl();
                $this->Smarty->plugins_dir[] = APP . 'View' . DS.'smarty_plugins'.DS;
                break;
            case 3:
                $this->Smarty->clearCompiledTemplate();
                $this->Smarty->setPluginsDir(array(APP . 'View' . DS.'smarty_plugins'.DS));
                break;
        }

        // Loading base class of Smarty Helpers
        App::uses('SmartyBaseHelper', $this->pluginName.'.'.'View/Helper');
	}

/**
 * Overrides the View::_render()
 * Sets variables used in CakePHP to Smarty variables
 *
 * @param string $___viewFn
 * @param string $___data_for_view
 * @param string $___play_safe
 * @param string $loadHelpers
 * @return rendered views
 */
    function _render($___viewFn, $___data_for_view = array())
	{
        // use cake's render for .ctp files. 
        if (preg_match('/\.ctp$/', $___viewFn)) { 
            return parent::_render($___viewFn, $___data_for_view);
        } 
        if ($this->helpers != false)
		{
            $this->loadHelpers();
            $loadedHelpers = $this->helpers;

			foreach(array_keys($loadedHelpers) as $helper)
			{
				$replace = strtolower(substr($helper, 0, 1));
				$camelBackedHelper = preg_replace('/\\w/', $replace, $helper, 1);

				${$camelBackedHelper} =& $loadedHelpers[$helper];
				if(isset(${$camelBackedHelper}->helpers) && is_array(${$camelBackedHelper}->helpers))
				{
					foreach(${$camelBackedHelper}->helpers as $subHelper)
					{
						${$camelBackedHelper}->{$subHelper} =& $loadedHelpers[$subHelper];
					}
				}
				$this->loaded[$camelBackedHelper] = (${$camelBackedHelper});
                switch ($this->smartyMajorVersion) {
                    case 2:
                        $this->Smarty->assign_by_ref($camelBackedHelper, ${$camelBackedHelper});
                        break;
                    case 3:
                        $this->Smarty->assignByRef($camelBackedHelper, ${$camelBackedHelper});
                        break;
                }
			}
		}

		$this->registerFunctions();

		if (empty($___data_for_view)) {
			$___data_for_view = $this->viewVars;
		}
		foreach($___data_for_view as $data => $value)
		{
			if(!is_object($data))
			{
				$this->Smarty->assign($data, $value);
			}
		}

        switch ($this->smartyMajorVersion) {
            case 2:
                $this->Smarty->assign_by_ref('view', $this);
                $this->Smarty->assign_by_ref('this', $this);
                break;
            case 3:
                $this->Smarty->assignByRef('view', $this);
                $this->Smarty->assignByRef('this', $this);
                break;
        }

		$res = $this->Smarty->fetch($___viewFn);
        
        return $res;
	}
	
	/**
	 * checks for existence of special method on loaded helpers, invoking it if it exists
	 * this allows helpers to register smarty functions, modifiers, blocks, etc.
	 */
	function registerFunctions() {
        /* check vector or hash tabe. */
        if (array_values($this->helpers) === $this->helpers) {
            $helpers = $this->helpers;
        } else {
            $helpers = array_keys($this->helpers);
        }
		foreach($helpers as $helper) {
            list($plugin, $helper) = $this->pluginSplit($helper);
            $helperClass = $helper. 'Helper';
            if (property_exists($this, $helper) && method_exists($this->{$helper}, '_registerSmartyFunctions')) {
                $this->{$helper}->_registerSmartyFunctions(&$this->Smarty);
			}
		}
	}

    /**
     * Set version of smarty
     */
    private function _smartyVersion() {
        // Version 2.6.x
        if (isset($this->Smarty->_version)) {
            $this->smartyVersion = $this->Smarty->_version;
        } 
        // Version 3.1.x
        if (defined('Smarty::SMARTY_VERSION')) {
            $v = constant('Smarty::SMARTY_VERSION');
            $v = preg_replace("/^Smarty-/", '', $v);
            $this->smartyVersion = $v;
        }
        if ($this->smartyVersion) {
            $this->smartyMajorVersion = intval(substr($this->smartyVersion, 0, 1));
        }
        return $this->smartyVersion;
    }
}
?>
