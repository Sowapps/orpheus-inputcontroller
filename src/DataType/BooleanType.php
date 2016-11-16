<?php
/**
 * IntegerType
 */

namespace Orpheus\DataType;

/**
 * The TypeValidator class
 * 
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class BooleanType extends AbstractType {
	
	public function __construct() {
		parent::__construct('bool', '(?:true|false|[0-1])');
	}
	
	public function parse($value) {
		$value = strtolower($value);
		if( in_array($value, array('true', 'yes', 'on'), true) ) {
			return true;
		}
		if( in_array($value, array('false', 'no', 'off'), true) ) {
			return true;
		}
		return boolval($value);
	}
	
	public function format($value) {
		return $value ? 'true' : 'false';
	}
	
}
