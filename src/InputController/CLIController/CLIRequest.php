<?php
/**
 * CLIRequest
 */

namespace Orpheus\InputController\CLIController;

use Exception;
use Orpheus\InputController\InputRequest;

/**
 * The CLIRequest class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class CLIRequest extends InputRequest {
	
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
	public function getURL() {
		return $this->route->formatURL($this->parameters);
	}
	
	/**
	 * Get all available routes
	 *
	 * @return CLIRoute[]
	 * @see \Orpheus\InputController\InputRequest::getRoutes()
	 */
	public function getRoutes() {
		return CLIRoute::getRoutes();
	}
	
	/**
	 * Get all input data
	 *
	 * @return array
	 */
	public function getAllData() {
		return $this->getInput();
	}
	
	/**
	 * Get the data by key with array as default
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getArrayData($key) {
		return $this->getInputValue($key, []);
	}
	
	/**
	 * Test if data $key is an array
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasArrayData($key = null) {
		return is_array($this->getData($key));
	}
	
	/**
	 * Get a data by $key, assuming $default
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getData($key, $default = null) {
		return $this->getInputValue($key, $default);
	}
	
	/**
	 * Test if data contains the $key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasData($key = null) {
		return $key ? $this->hasInputValue($key) : $this->hasInput();
	}
	
	/**
	 * Test if path contains a value and return it as parameter
	 *
	 * @param string $path The path to get the value
	 * @param string $value The value as ouput parameter
	 * @return boolean
	 */
	public function hasDataKey($path = null, &$value = null) {
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
	 * @return \Orpheus\InputController\CLIController\CLIRequest
	 */
	protected function setContent($content) {
		return $this->setInput($content);
	}
	
	/**
	 * Handle the current request as a CLIRequest one
	 * This method ends the script
	 */
	public static function handleCurrentRequest() {
		try {
			CLIRoute::initialize();
			static::$mainRequest = static::generateFromEnvironment();
			$response = static::$mainRequest->process();
		} catch( Exception $e ) {
			$response = CLIResponse::generateFromException($e);
		}
		$response->process();
		exit($response->getCode());
	}
	
	/**
	 * Generate CLIRequest from environment
	 *
	 * @return CLIRequest
	 */
	public static function generateFromEnvironment() {
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
	 * Parse console arguments to parameters with option
	 *
	 * @param array $args
	 * @return array
	 */
	protected static function parseArguments(array $args) {
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
					// true is a default value but ware are not sure this is the expected value
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
						$parameters[$name] = $defaultValue;
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
	
	/**
	 * Get the name of the route class associated to a CLIRequest
	 *
	 * @return string
	 */
	public static function getRouteClass() {
		return '\Orpheus\InputController\CLIController\CLIRoute';
	}
}
