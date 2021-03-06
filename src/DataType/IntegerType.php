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
class IntegerType extends AbstractType {
	
	public function __construct() {
		parent::__construct('int', '\d+');
	}
	
	public function parse($value) {
		return (int) $value;
	}
}
