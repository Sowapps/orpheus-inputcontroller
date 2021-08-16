<?php
/**
 * CLIRoute
 */

namespace Orpheus\InputController\CLIController;

use Exception;
use Orpheus\Core\Route;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\InputRequest;

/**
 * The CLIRoute class
 *
 * @author Florent Hazard <contact@sowapps.com>
 * TODO: Process options
 */
class CliRoute extends ControllerRoute {
	
	/**
	 * Registered routes
	 *
	 * @var array
	 */
	protected static array $routes = [];
	
	/**
	 * Available parameters by short name
	 *
	 * @var CliArgument[]
	 */
	// 	protected $parametersBySN = array();
	/**
	 * Available parameters
	 *
	 * @var string[]
	 */
	protected $parameters = [];
	
	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $path
	 * @param string $controller
	 * @param string[] $parameters
	 * @param array $options
	 */
	protected function __construct($name, $path, $controller, $parameters, $options) {
		parent::__construct($name, $path, $controller, null, 'Orpheus\InputController\CLIController\CliResponse', $options);
		// 		$this->parameters = $parameters;
		// TODO : Process options
		// 		foreach( $parameters as $arg ) {
		// 			$this->parameters[$arg->getLongName()] = &$arg;
		// 			if( $arg->hasShortName() ) {
		// 				$this->parametersBySN[$arg->getShortName()] = &$arg;
		// 			}
		// 		}
	}
	
	/**
	 * Format the current route to get an URL from path
	 *
	 * @param string[] $values
	 * @return string
	 * @throws Exception
	 */
	public function formatURL($values = []) {
		$params = '';
		if( $values ) {
			$params = implode(' ', $values);
		}
		return static::getRootCommand() . ' ' . $this->getPath() . $params;
	}
	
	public static function getRootCommand() {
		return 'php -f app/console/run.php';
	}
	
	public function getUsageCommand() {
		$params = '';
		foreach( $this->parameters as $arg ) {
			$params .= ' ' . $arg->getUsageCommand();
		}
		return static::getRootCommand() . ' ' . $this->getPath() . $params;
	}
	
	/**
	 * Get route as string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getPath();
	}
	
	/**
	 * Test current route is matching request
	 *
	 * {@inheritDoc}
	 * @param CliRequest $request
	 * @param array $values
	 * @param boolean $alternative
	 * @see \Orpheus\InputController\ControllerRoute::isMatchingRequest()
	 */
	public function isMatchingRequest(InputRequest $request, &$values = [], $alternative = false) {
		// CLI does not use alternative
		return $request->getPath() === $this->getPath();
	}
	
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * Register route by $name from config
	 *
	 * @param string $name
	 * @param array $config
	 * @throws \Exception
	 */
	public static function registerConfig($name, array $config) {
		if( empty($config['path']) ) {
			throw new \Exception('Missing a valid "path" in configuration of route "' . $name . '"');
		}
		
		// 		$parameters = array();
		// 		if( isset($config['parameters']) && is_array($config['parameters']) ) {
		// 			foreach( $config['parameters'] as $paramName => $paramConfig ) {
		// 				$parameters[] = CLIArgument::make($paramName, $paramConfig);
		// 			}
		// 		}
		$options = $config;
		unset($options['path'], $options['controller'], $options['parameters']);
		static::register($name, $config['path'], $config['controller'], [], $options);
	}
	
	/**
	 * Register route by $name
	 *
	 * @param string $name
	 * @param string $path
	 * @param string $controller
	 * @param array $parameters
	 * @param array $options
	 */
	public static function register($name, $path, $controller, $parameters, $options = []) {
		static::$routes[$name] = new static($name, $path, $controller, $parameters, $options);
	}
	
	/**
	 * Get registered routes
	 *
	 * @return array
	 */
	public static function getRoutes() {
		return static::$routes;
	}
	
	/**
	 * Get the route object for the $route name
	 *
	 * @param string $route
	 * @return CliRoute
	 */
	public static function getRoute(string $route): Route {
		return static::$routes[$route];
	}
	
}

//http://fr.php.net/manual/fr/regexp.reference.escape.php
//http://fr.php.net/manual/fr/regexp.reference.character-classes.php
// Case Insensitive
/*
CLIRoute::addTypeValidator(TypeValidator::make('int', '\d+'));
CLIRoute::addTypeValidator(TypeValidator::make('boolean', '(?:true|false|[0-1])', function(&$value) {
	$value = boolval($value);
	return true;
}));
CLIRoute::addTypeValidator(TypeValidator::make('file', function($value) {
	return is_readable($value);
}));
*/

