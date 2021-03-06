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
	 * Get value from input array
	 * 
	 * @param array $values
	 * @param string $argName
	 * @param string $argAltName
	 * @return mixed
	 * 
	 * Getting $values from getopt()
	 */
	public function getValueFrom(array $values, $argName, $argAltName) {
		if( isset($values[$argName]) ) {
			return $values[$argName];
		}
		if( $argAltName && isset($values[$argAltName]) ) {
			return $values[$argAltName];
		}
		return null;
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

	/**
	 * Is this type falsable ?
	 * 
	 * @return boolean
	 * 
	 * Only boolean should be
	 */
	public function isFalsable() {
		return false;
	}
	
}
