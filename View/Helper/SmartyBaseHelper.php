<?php
/**
 * Smarty Helper Abstruct class for wrapping Html/Form/.. Helper methods
 *
 * The original work is http://bakery.cakephp.org/articles/view/138 by tclineks
 * and modified to work with CakePHP 2.0 and Smarty 3.1
 *
 * @author		Daiji Hirata
 * @license		http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package 	smartyview
 * @subpackage	view.helper
 */

App::uses('Helper', 'View');
abstract class SmartyBaseHelper extends Helper {

/**
 * The name of helper to wrap with Smarty.
 *
 * @var string
 */
    protected $name;

/**
 * function to register wrappers with Smarty object
 *  - called from SmartyView
 */
	public function _registerSmartyFunctions(&$smarty) {
        $name = $this->name;
        switch ($this->_View->smartyMajorVersion) {
        case 2:
            $smarty->register_function($name, array(&$this, 'wrapper'));
            break;
        case 3:
            // only once to register fuction
            if (!isset($smarty->registered_plugins['function']) or !array_key_exists($name, $smarty->registered_plugins['function'])) {
                $smarty->registerPlugin('function', $name, array(&$this, 'wrapper'));
            }
            break;
        }
	}

/**
 * Smarty wrapper for Helpers
 *
 * @param mixed $params params from Smarty template call
 * @param Smarty $smarty Smarty object
 * @return mixed
 */
	public function wrapper($params, &$smarty) {
        App::uses('Inflector', 'Utility');
        $helperName = 'Smarty' . Inflector::camelize($this->name);
        $className = Inflector::camelize($this->name);

		// sanity check for php version
		if (!class_exists('ReflectionClass')) {
			$smarty->trigger_error($helperName . ": Error - requires php 5.0 or above", E_USER_NOTICE);
			return;
		}

		$functionName = $params['func'];
		$assign = array_key_exists('assign', $params) ? $params['assign'] : null;
		$showCall = array_key_exists('__show_call', $params) ? $params['__show_call'] : false;
    	unset($params['func']);
		unset($params['assign']);
		unset($params['__show_call']);

		$parameters = array(); // our final array of function parameters

		if (empty($functionName)) {
			$smarty->trigger_error($helperName . ": missing 'func' parameter", E_USER_NOTICE);
			return;
		}

		// process our params array to look for array representations
		// based on key names separated by underscores
		$processedParams = $this->_processParams($params);
		$arrayParams = array();

		$classReflector = new ReflectionClass($this->$className);
		if ($classReflector->hasMethod($functionName)) { // quick sanity check
			$funcReflector = $classReflector->getMethod($functionName);
			$funcParams = $funcReflector->getParameters(); // returns an array of parameter names
			foreach ($funcParams as $param) {
				$paramName = $param->getName();
				if (isset($processedParams[$paramName])) {
					$parameters[$paramName] =  $processedParams[$paramName];
					unset($processedParams[$paramName]);
				} else {
					if ($param->isDefaultValueAvailable()) {
						$parameters[$paramName] = $param->getDefaultValue();
						// mark the index of array parameters for potential later population
						if (is_array($parameters[$paramName])) {
							$arrayParams[] = $paramName;
						}
					} else if (!$param->isOptional()) {
						$smarty->trigger_error($helperName.": Error ".$paramName." parameter is required for method ".$functionName, E_USER_NOTICE);
					} else {
						$parameters[$paramName] = null;
					}
				}
			}
            
            // check for unfilled array parameters and populate the first with remaining $params
            if (count($arrayParams)) {
                $parameters[$arrayParams[0]] = $processedParams;
            }
            
		} else {
			$smarty->trigger_error($helperName.": Error " . $classReflector->name . "::" . $functionName . " is not defined", E_USER_NOTICE);
			return;
		}

		if ($showCall) {
			echo '<pre>'.$helperName.' calling $this->'.$className.'->' . $functionName . ' with these parameters: <br />';
			var_dump($parameters);
			echo '</pre>';
		}

        
		$result = call_user_func_array(array($this->$className,$functionName),$parameters);
		
		if (!empty($assign)) {
			$smarty->assign($assign, $result);
		} else {
			return $result;
		}
	}

	/**
	 * scans an associative array looking for array keys
	 * that represent nested arrays through the use of the delimiter
	 * parameter (by default an underscore)
	 *
	 * @param array associative array of values
	 * @param string delimiter
	 * @return array
	 */
	public function _processParams($params = array(), $delimiter = '_') {
		$result = array();
		foreach ($params as $key => $value) {
			$a = explode($delimiter,$key);
			if (count($a) > 1) {
				$this->_recursivelyAssign($result,$a,$value);
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * recursive method to build nested associative arrays
	 * from delimited key names.  fancy!
	 *
	 * @param array result array, passed by reference
	 * @param array array of key name components, split by the delimiter in _processParams
	 * @param string the value to ultimately assign to the nested array
	 */
	public function _recursivelyAssign(&$result,$keyArray,$value) {
		$k = array_shift($keyArray);
		if (count($keyArray) > 1) {
			$this->_recursivelyAssign($result[$k],$keyArray,$value);
		} else {
			$kk = $keyArray[0];
			$result[$k][$kk] = $value;
		}
	}

}
