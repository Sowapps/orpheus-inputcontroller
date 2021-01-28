<?php

namespace Orpheus\InputController\HTTPController;

use Exception;
use Orpheus;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\InputRequest;

/**
 * The HTTPRoute class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class HTTPRoute extends ControllerRoute {
	
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	
	/**
	 * Registered regex for a type
	 *
	 * @var array
	 */
	protected static $typesRegex = [];
	
	/**
	 * Registered routes
	 *
	 * @var array
	 */
	protected static $routes = [];
	
	/**
	 * Registered response class for output option
	 *
	 * @var array
	 */
	protected static $outputResponses = [];
	
	/**
	 * All known methods
	 *
	 * @var array
	 */
	protected static $knownMethods = [
		self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE,
	];
	
	/**
	 * The method to reach this route
	 *
	 * @var string
	 */
	protected $method;
	
	/**
	 * Path with converted regex
	 *
	 * @var string
	 */
	protected $pathRegex;
	
	/**
	 * Variables in path
	 *
	 * @var string[]
	 */
	protected $pathVariables;
	
	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $path
	 * @param string $controller
	 * @param string $method
	 * @param array $restrictTo
	 * @param string $defaultResponse
	 * @param array $options
	 */
	protected function __construct($name, $path, $controller, $method, $restrictTo, $defaultResponse, $options) {
		parent::__construct($name, $path, $controller, $restrictTo, $defaultResponse, $options);
		$this->method = $method;
		$this->generatePathRegex();
	}
	
	/**
	 * Generate all regex of the path from extracted variables
	 */
	protected function generatePathRegex() {
		if( $this->pathRegex ) {
			return;
		}
		$variables = [];
		$this->pathRegex = preg_replace_callback(
			'#\{([^\}]+)\}#sm',
			function ($matches) use (&$variables) {
				// 				debug('$matches', $matches);
				$regex = $var = null;
				static::extractVariable(str_replace('\.', '.', $matches[1]), $var, $regex);
				$variables[] = $var;
				return '(' . $regex . ')';
			},
			str_replace('.', '\.', $this->path)
		);
		$this->pathVariables = $variables;
	}
	
	/**
	 * Extract variable from configuration string
	 *
	 * @param string $str
	 * @param string $var
	 * @param string $regex
	 */
	protected static function extractVariable($str, &$var = null, &$regex = null) {
		[$p1, $p2] = explodeList(':', $str, 2);
		// Optionnal only if there is a default value
		if( $p2 ) {
			// {regex|type:variable}
			$var = $p2;
			$regex = $p1;
			if( ctype_alpha($regex) && isset(static::$typesRegex[$regex]) ) {
				$regex = static::$typesRegex[$regex];
			}
		} else {
			// {variable}, regex=[^\/]+
			$var = $p1;
			$regex = '[^\/]+';
		}
	}
	
	/**
	 * Format the current route to get an URL from path
	 *
	 * @param string[] $values
	 * @return string
	 * @throws Exception
	 */
	public function formatURL($values = []) {
		$path = preg_replace_callback(
			'#\{([^\}]+)\}#sm',
			function ($matches) use ($values) {
				$var = $regex = null;
				static::extractVariable($matches[1], $var, $regex);
				if( !isset($values[$var]) ) {
					throw new Exception('The variable "' . $var . '" is missing to generate URL for route ' . $this->name);
				}
				$value = $values[$var] . '';
				if( !preg_match('#^' . $regex . '$#', $value) ) {
					throw new Exception('The given value "' . $value . '" of variable "' . $var . '" is not matching the regex requirements to generate URL for route ' . $this->name);
				}
				return $value;
			},
			$this->path
		);
		return WEB_ROOT . '/' . (isset($path[0]) && $path[0] === '/' ? substr($path, 1) : $path);
	}
	
	/**
	 * Get route as string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->method . '("' . $this->path . '")';
	}
	
	/**
	 * Test current route is matching request
	 *
	 * {@inheritDoc}
	 * @param HTTPRequest $request
	 * @param array $values
	 * @param boolean $alternative
	 * @see ControllerRoute::isMatchingRequest()
	 */
	public function isMatchingRequest(InputRequest $request, &$values = [], $alternative = false) {
		// Method match && Path match (variables included)
		if( $this->method !== $request->getMethod() ) {
			return false;
		}
		$regex = $this->pathRegex;
		if( $alternative ) {
			// If last char is / or not, it will end with /? (optional /)
			$regex .= str_last($regex) === '/' ? '?' : '/?';
		}
		$matches = null;
		if( preg_match('#^' . $regex . '$#i', urldecode($request->getPath()), $matches) ) {
			unset($matches[0]);
			$values = array_combine($this->pathVariables, $matches);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Register route by $name from config
	 *
	 * @param string $name
	 * @param array $config
	 * @throws Exception
	 */
	public static function registerConfig($name, array $config) {
		if( empty($config['path']) ) {
			throw new Exception('Missing a valid `path` in configuration of route "' . $name . '"');
		}
		if( empty($config['response']) ) {
			$config['response'] = !empty($config['output']) ? static::getOutputResponse($config['output']) : 'Orpheus\InputController\HTTPController\HTMLHTTPResponse';
		}
		if( empty($config['controller']) ) {
			if( !empty($config['redirect']) ) {
				$config['controller'] = 'Orpheus\\Controller\\RedirectController';
			} elseif( !empty($config['render']) ) {
				$config['controller'] = 'Orpheus\\Controller\\StaticPageController';
			} else {
				throw new Exception('Missing a valid `controller` in configuration of route "' . $name . '"');
			}
		}
		if( !isset($config['restrictTo']) ) {
			$config['restrictTo'] = null;
		}
		$options = $config;
		unset($options['path'], $options['controller'], $options['method'], $options['restrictTo']);
		static::register($name, $config['path'], $config['controller'], isset($config['method']) ? $config['method'] : null, $config['restrictTo'], $config['response'], $options);
	}
	
	/**
	 * Get the output response
	 *
	 * @param string $output
	 * @return mixed
	 */
	public static function getOutputResponse($output) {
		return static::$outputResponses[$output];
	}
	
	/**
	 * Register route by $name
	 *
	 * @param string $name
	 * @param string $path
	 * @param string $controller
	 * @param string|array|null $methods
	 * @param array|null $restrictTo
	 * @param string $defaultResponse
	 * @param array $options
	 */
	public static function register($name, $path, $controller, $methods, $restrictTo, $defaultResponse, $options = []) {
		if( $methods && !is_array($methods) ) {
			$methods = [$methods];
		}
		foreach( static::$knownMethods as $method ) {
			if( (!$methods && !empty(static::$routes[$name][$method])) || ($methods && !in_array($method, $methods)) ) {
				continue;
			}
			static::$routes[$name][$method] = new static($name, $path, $controller, $method, $restrictTo, $defaultResponse, $options);
		}
	}
	
	/**
	 * Set the output response
	 *
	 * @param string $output
	 * @param string $responseClass
	 */
	public static function setOutputResponse($output, $responseClass) {
		static::$outputResponses[$output] = $responseClass;
	}
	
	/**
	 * Set the regex of a type, used to parse paths
	 *
	 * @param string $type
	 * @param string $regex
	 */
	public static function setTypeRegex($type, $regex) {
		static::$typesRegex[$type] = $regex;
	}
	
	/**
	 * Get the route object for the $route name
	 *
	 * @param string $route
	 * @param string $method
	 * @return HTTPRoute
	 */
	public static function getRoute($route, $method = null) {
		$routes = static::getRoutes();
		if( $method ) {
			return isset($routes[$route][$method]) ? $routes[$route][$method] : null;
		}
		foreach( static::getKnownMethods() as $method ) {
			if( isset($routes[$route][$method]) ) {
				return $routes[$route][$method];
			}
		}
		return null;
	}
	
	/**
	 * Get the known HTTP methods
	 *
	 * @return string[]
	 */
	public static function getKnownMethods() {
		return static::$knownMethods;
	}
	
}

//http://fr.php.net/manual/fr/regexp.reference.escape.php
//http://fr.php.net/manual/fr/regexp.reference.character-classes.php
// Case Insensitive
HTTPRoute::setTypeRegex('int', '\d+');
HTTPRoute::setTypeRegex('id', '[1-9]\d*');
HTTPRoute::setTypeRegex('slug', '[a-z0-9\-_]+');

HTTPRoute::setOutputResponse('html', 'Orpheus\InputController\HTTPController\HTMLHTTPResponse');
HTTPRoute::setOutputResponse('json', 'Orpheus\InputController\HTTPController\JSONHTTPResponse');

