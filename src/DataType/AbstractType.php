<?php
/**
 * AbstractType
 */

namespace Orpheus\DataType;

/**
 * The TypeValidator class
 * 
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
abstract class AbstractType {
	
	/**
	 * The name
	 * 
	 * @var string
	 */
	protected $name;
	
	/**
	 * The regex to validate a value
	 * 
	 * @var string
	 */
	protected $regex;
	
	/**
	 * Constructor
	 * 
	 * @param string $name
	 * @param string $regex
	 */
	public function __construct($name, $regex=null) {
		$this->name = $name;
		$this->regex = $regex;
	}
	
	/**
	 * Validate the given value with this type
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value) {
		return
			$this->checkByRegex($value) &&
			$this->check($value);
	}
	
	/**
	 * Check this value if valid using the regex
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function checkByRegex($value) {
		return !$this->regex || preg_match('#^'.$this->regex.'$#', $value);
	}
	
	/**
	 * Check this value is valid
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function check($value) {
		return true;
	}
	
	/**
	 * From human to machine
	 * 
	 * @param mixed $value
	 * @return mixed
	 */
	public function parse($value) {
		return $value;
	}
	
	/**
	 * From machine to human
	 * 
	 * @param mixed $value
	 * @return mixed
	 */
	public function format($value) {
		return $value;
	}
	
	/**
	 * Get the name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
}
