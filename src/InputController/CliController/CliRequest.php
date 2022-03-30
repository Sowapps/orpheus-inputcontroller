<?php
/**
 * CliRequest
 */

namespace Orpheus\InputController\CliController;

use Exception;
use Orpheus\Config\IniConfig;
use Orpheus\InputController\InputRequest;

/**
 * The CliRequest class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
class CliRequest extends InputRequest {
	
	// Inspired from Symfony\Component\Console\Formatter\OutputFormatterInterface\OutputInterface
	public const VERBOSITY_QUIET = 16;
	public const VERBOSITY_NORMAL = 32;
	public const VERBOSITY_VERBOSE = 64;
	public const VERBOSITY_VERY_VERBOSE = 128;
	public const VERBOSITY_DEBUG = 256;
	
	protected int $verbosity = self::VERBOSITY_NORMAL;
	
	protected bool $dryRun = false;
	
	public function __construct($path, $parameters, $input) {
		parent::__construct($path, $parameters, $input);
		
		$this->setDryRun(!empty($parameters['dry-run']));
		
		if( isset($parameters['v']) ) {
			$this->verbosity = pow(2, 5 + intval($parameters['v']));
		}
	}
	
	public function isVerbose(): bool {
		return $this->verbosity >= self::VERBOSITY_VERBOSE;
	}
	
	public function isVeryVerbose(): bool {
		return $this->verbosity >= self::VERBOSITY_VERY_VERBOSE;
	}
	
	public function isDebugVerbose(): bool {
		return $this->verbosity >= self::VERBOSITY_DEBUG;
	}
	
	/**
	 * @return bool
	 */
	public function isDryRun(): bool {
		return $this->dryRun;
	}
	
	/**
	 * @param bool $dryRun
	 */
	public function setDryRun(bool $dryRun): void {
		$this->dryRun = $dryRun;
		if( $dryRun ) {
			$this->verbosity = self::VERBOSITY_DEBUG;
		}
	}
	
	/**
	 * @return int
	 */
	public function getVerbosity(): int {
		return $this->verbosity;
	}
	
	/**
	 * @param int $verbosity
	 */
	public function setVerbosity(int $verbosity): void {
		$this->verbosity = $verbosity;
	}
	
	/**
	 * Get this request as string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->path;
	}
	
	/**
	 * Get the URL used for this request
	 *
	 * @return string
	 */
	public function getUrl(): string {
		return $this->route->formatURL($this->parameters);
	}
	
	/**
	 * Get all available routes
	 *
	 * @return CliRoute[]
	 * @see \Orpheus\InputController\InputRequest::getRoutes()
	 */
	public function getRoutes(): array {
		return CliRoute::getRoutes();
	}
	
	/**
	 * Get all input data
	 *
	 * @return array
	 */
	public function getAllData(): array {
		return $this->getInput();
	}
	
	/**
	 * Get the data by key with array as default
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getArrayData(string $key) {
		return $this->getInputValue($key, []);
	}
	
	/**
	 * Test if data $key is an array
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasArrayData(?string $key = null): bool {
		return is_array($this->getData($key));
	}
	
	/**
	 * Get a data by $key, assuming $default
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getData(string $key, $default = null) {
		return $this->getInputValue($key, $default);
	}
	
	/**
	 * Test if data contains the $key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasData(?string $key = null): bool {
		return $key ? $this->hasInputValue($key) : $this->hasInput();
	}
	
	/**
	 * Test if path contains a value and return it as parameter
	 *
	 * @param string $path The path to get the value
	 * @param string $value The value as ouput parameter
	 * @return boolean
	 */
	public function hasDataKey(?string $path = null, &$value = null): bool {
		$v = $this->getData($path);
		if( !$v || !is_array($v) ) {
			return false;
		}
		$value = key($v);
		
		return true;
	}
	
	/**
	 * Set the content (input & input type)
	 *
	 * @param string $content
	 * @param string $contentType
	 * @return \Orpheus\InputController\CliController\CliRequest
	 * @deprecated Function is wrongly implemented
	 */
	protected function setContent(string $content): CliRequest {
		return $this->setInput($content);
	}
	
	/**
	 * @return CliController
	 */
	public static function getDefaultController(): CliController {
		$class = IniConfig::get('default_cli_controller', 'Orpheus\Controller\EmptyDefaultCliController');
		
		return new $class(null, []);
	}
	
	/**
	 * Handle the current request as a CliRequest one
	 * This method ends the script
	 */
	public static function handleCurrentRequest() {
		try {
			CliRoute::initialize();
			static::$mainRequest = static::generateFromEnvironment();
			$response = static::$mainRequest->process();
		} catch( Exception $e ) {
			$response = static::getDefaultController()->processException($e);
		}
		$response->process();
		exit($response->getCode());
	}
	
	/**
	 * Generate CliRequest from environment
	 *
	 * @return CliRequest
	 */
	public static function generateFromEnvironment(): CliRequest {
		global $argc, $argv;
		
		$stdin = defined('STDIN') ? STDIN : fopen('php://stdin', 'r');
		$data = stream_get_meta_data($stdin);
		$input = null;
		if( empty($data['seekable']) && !empty($data['unread_bytes']) ) {
			stream_set_blocking($stdin, false);
			$input = trim(stream_get_contents($stdin));
		}
		$path = $argv[1];
		$parameters = static::parseArguments(array_slice($argv, 2));
		
		$request = new static($path, $parameters, $input);
		
		return $request;
	}
	
	/**
	 * Get the name of the route class associated to a CliRequest
	 *
	 * @return string
	 */
	public static function getRouteClass(): string {
		return '\Orpheus\InputController\CliController\CliRoute';
	}
	
	/**
	 * Parse console arguments to parameters with option
	 *
	 * @param array $args
	 * @return array
	 */
	protected static function parseArguments(array $args): array {
		$parameters = [];
		$pendingOption = null;
		
		while( ($arg = array_shift($args)) !== null ) {
			if( $arg === '--' ) {
				break;
			}
			$previousOption = $pendingOption;
			$pendingOption = null;
			if( strpos($arg, '--') === 0 ) {
				// Long argument
				[$name, $value] = explodeList('=', substr($arg, 2), 2);
				if( $value !== null ) {
					$parameters[$name] = $value;
				} elseif( preg_match('#not?-(.+)#is', $arg, $matches) ) {
					// We are sure about its value
					$name = $matches[1];
					$parameters[$name] = false;
				} else {
					// true is a default value, but we are not sure if this is the expected value
					$parameters[$name] = true;
					$pendingOption = $name;
				}
			} elseif( strpos($arg, '-') === 0 ) {
				// Short argument
				[$argString, $value] = explodeList('=', substr($arg, 1), 2);
				$name = null;
				$defaultValue = true;
				foreach( str_split($argString) as $name ) {
					if( $name === '!' ) {
						$defaultValue = false;
					} else {
						if( empty($parameters[$name]) || !$defaultValue ) {
							// Undefined, null or false, we set new value
							$parameters[$name] = $defaultValue;
						} else {
							// Increment the current value when repeating short argument
							$parameters[$name] = $parameters[$name] + 1;
						}
						// If default value is false, we are not expecting another value
						$pendingOption = $defaultValue ? $name : null;
						// Reset default value to true
						$defaultValue = true;
					}
				}
				// Provided value applies to the last element
				if( $value !== null && $name !== null ) {
					$parameters[$name] = $value;
					$pendingOption = null;
				}
			} elseif( $previousOption !== null ) {
				$parameters[$previousOption] = $arg;
			} else {
				// Option
				array_unshift($args, $arg);
				break;
			}
		}
		
		// All arguments left are options
		$parameters['options'] = &$args;
		
		return $parameters;
	}
	
}
