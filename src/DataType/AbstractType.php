<?php
/**
 * AbstractType
 */

namespace Orpheus\DataType;

/**
 * The TypeValidator class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
abstract class AbstractType {
	
	/**
	 * The name
	 *
	 * @var string
	 */
	protected string $name;
	
	/**
	 * The regex to validate a value
	 *
	 * @var string|null
	 */
	protected ?string $regex;
	
	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string|null $regex
	 */
	public function __construct(string $name, ?string $regex = null) {
		$this->name = $name;
		$this->regex = $regex;
	}
	
	/**
	 * Get value from input array
	 *
	 * @param array $values
	 * @param string $argName
	 * @param string|null $argAltName
	 * @return mixed
	 */
	public function getValueFrom(array $values, string $argName, ?string $argAltName) {
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
	public function validate($value): bool {
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
	public function checkByRegex(string $value): bool {
		return !$this->regex || preg_match('#^' . $this->regex . '$#', $value);
	}
	
	/**
	 * Check this value is valid
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function check($value): bool {
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
	public function getName(): string {
		return $this->name;
	}
	
	/**
	 * Is this type falsable ?
	 * Only boolean should be
	 *
	 * @return boolean
	 */
	public function isFalsable(): bool {
		return false;
	}
	
}
