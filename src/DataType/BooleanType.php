<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\DataType;

class BooleanType extends AbstractType {
	
	public function __construct() {
		parent::__construct('bool', '(?:true|false|[0-1])');
	}
	
	public function getValueFrom(array $values, $argName, $argAltName) {
		if( isset($values[$argName]) ) {
			return $values[$argName] === false ? 'true' : $values[$argName];
		}
		if( isset($values['not-' . $argName]) ) {
			// We don't verify the "not" value, this is not possible by the usual way
			return $values['not-' . $argName] === false ? 'false' : $this->format(!$this->parse($values['not-' . $argName]));
		}
		if( $argAltName && isset($values[$argAltName]) ) {
			return $values[$argAltName];
		}
		
		// We might check not value of short option by choosing one lowercase and the other is the uppercase but this may create conflicts
		return null;
	}
	
	public function format($value): string {
		return $value ? 'true' : 'false';
	}
	
	public function parse($value) {
		$value = strtolower($value);
		if( in_array($value, ['true', 'yes', 'on'], true) ) {
			return true;
		}
		if( in_array($value, ['false', 'no', 'off'], true) ) {
			return false;
		}
		return boolval($value);
	}
	
	public function isFalsable(): bool {
		return true;
	}
	
}
