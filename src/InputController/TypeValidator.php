<?php
/**
 * TypeValidator
 */

namespace Orpheus\InputController;

/**
 * The TypeValidator class
 * 
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class TypeValidator {
	
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
	 * The callback checker to validate a value
	 * 
	 * @var Callable
	 */
	protected $checker;
	
	/**
	 * Constructor
	 * 
	 * @param string $name
	 * @param string $regex
	 * @param Callable $checker
	 */
	public function __construct($name, $regex, $checker) {
		$this->name		= $name;
		$this->regex	= $regex;
		$this->checker	= $checker;
	}
	
	/**
	 * Make a TypeValidator
	 * 
	 * @param string $name
	 * @param string|Callable $regex
	 * @param Callable $checker
	 * @return \Orpheus\InputController\TypeValidator
	 * 
	 * You $regex is optional if you pass a callback instead.
	 * So you could use regex, callable or both.
	 */
	public static function make($name, $regex, $checker=null) {
		if( !$checker && is_callable($regex) ) {
			$checker = $regex;
			$regex = null;
		}
		return new static($name, $regex, $checker);
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
			$this->checkByChecker($value);
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
	 * Check this value if valid using the checker callable
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function checkByChecker($value) {
		return !$this->checker || call_user_func($this->checker, $value);
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
