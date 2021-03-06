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
class FileType extends AbstractType {
	
	public function __construct() {
		parent::__construct('file');
	}
	
	public function check($value) {
		return is_readable($value);
	}
}
