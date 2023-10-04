<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\DataType;

class FileType extends AbstractType {
	
	public function __construct() {
		parent::__construct('file');
	}
	
	public function check(mixed $value): bool {
		return is_readable($value);
	}
	
}
