<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\DataType;

class IntegerType extends AbstractType {
	
	public function __construct() {
		parent::__construct('int', '\d+');
	}
	
	public function parse(mixed $value): int {
		return (int) $value;
	}
	
}
