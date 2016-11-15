<?php
/**
 * TypeValidator
 */

namespace Orpheus\InputController\CLIController;

/**
 * The CLIArgument class
 * 
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class CLIArgument {
	
	/**
	 * The long name
	 * 
	 * @var string
	 */
	protected $longName;
	
	/**
	 * The short name
	 * 
	 * @var string
	 */
	protected $shortName;
	
	/**
	 * The type
	 * 
	 * @var string
	 */
	protected $type;
	
	/**
	 * Constructor
	 * 
	 * @param string $longName
	 * @param string $shortName
	 * @param string $type
	 */
	public function __construct($longName, $shortName, $type) {
		$this->longName = $longName;
		$this->shortName = $shortName;
		$this->type = $type;
	}
	
	/**
	 * Make a CLIArgument from config
	 * 
	 * @param string $name
	 * @param string $config
	 * @return \Orpheus\InputController\CLIArgument
	 */
	public static function make($name, $config) {
		list($shortName, $type) = explodeList(':', $config, 2);
		return new static($name, $shortName, $type);
	}
	
	public function getLongCommand($value) {
		if( $value === false ) {
			return '';
		}
		$command = '--'.$this->getLongName();
		if( $value !== true ) {
			$command .= '="'.$value.'"';
		}
		return $command;
	}
	
	
	/**
	 * Get the long name
	 * 
	 * @return string
	 */
	public function getLongName() {
		return $this->longName;
	}
	
	/**
	 * Get the short name
	 * 
	 * @return string
	 */
	public function getShortName() {
		return $this->shortName;
	}
	
	/**
	 * Test argument has a short name
	 * 
	 * @return boolean
	 */
	public function hasShortName() {
		return !!$this->shortName;
	}
	
	/**
	 * Get the type
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
}
