<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\DataType;

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
	 */
	public function __construct(string $name, ?string $regex = null) {
		$this->name = $name;
		$this->regex = $regex;
	}
	
	/**
	 * Get value from input array
	 */
	public function getValueFrom(array $values, string $argName, ?string $argAltName): mixed {
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
	 */
	public function validate(mixed $value): bool {
		return
			$this->checkByRegex($value) &&
			$this->check($value);
	}
	
	/**
	 * Check this value if valid using the regex
	 *
	 * @param mixed $value
	 */
	public function checkByRegex(string $value): bool {
		return !$this->regex || preg_match('#^' . $this->regex . '$#', $value);
	}
	
	/**
	 * Check this value is valid
	 */
	public function check(mixed $value): bool {
		return true;
	}
	
	/**
	 * From human to machine
	 * Expecting a string but must be idempotent
	 */
	public function parse(mixed $value): mixed {
		return $value;
	}
	
	/**
	 * From machine to human
	 *
	 * @return mixed
	 */
	public function format(mixed $value): string {
		return $value;
	}
	
	/**
	 * Get the name
	 */
	public function getName(): string {
		return $this->name;
	}
	
	/**
	 * Is this type falsable ?
	 * Only boolean should be
	 */
	public function isFalsable(): bool {
		return false;
	}
	
}
