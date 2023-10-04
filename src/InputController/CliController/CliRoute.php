<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\CliController;

use Exception;
use Orpheus\Core\Route;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\InputRequest;

class CliRoute extends ControllerRoute {
	
	/**
	 * Registered routes
	 *
	 * @var array
	 */
	protected static array $routes = [];
	
	/*
	 * Available parameters by short name
	 *
	 * @var CliArgument[]
	 */
	// 	protected $parametersBySN = array();
	/*
	 * Available parameters
	 *
	 * @var CliArgument[]
	 */
	//	protected $parameters = [];
	
	/**
	 * Constructor
	 *
	 * @param string[] $parameters
	 */
	protected function __construct(string $name, string $path, string $controller, array $parameters, array $options) {
		parent::__construct($name, $path, $controller, null, 'Orpheus\InputController\CliController\CliResponse', $options);
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
	 * Format the current route to get a URL from path
	 *
	 * @param string[] $values
	 */
	public function formatUrl(array $values = [], array $parameters = []): string {
		$params = '';
		if( $values ) {
			$params = implode(' ', $values);
		}
		
		return static::getRootCommand() . ' ' . $this->getPath() . $params;
	}
	
	public static function getRootCommand(): string {
		return 'php -f app/console/run.php';
	}
	
	//	public function getUsageCommand(): string {
	//		$params = '';
	//		foreach( $this->parameters as $arg ) {
	//			$params .= ' ' . $arg->getUsageCommand();
	//		}
	//		return static::getRootCommand() . ' ' . $this->getPath() . $params;
	//	}
	
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
	 * @see ControllerRoute::isMatchingRequest
	 */
	public function isMatchingRequest(InputRequest $request, array &$values = [], bool $alternative = false): bool {
		// CLI does not use alternative
		return $request->getPath() === $this->getPath();
	}
	
	/**
	 * Register route by $name from config
	 *
	 * @throws Exception
	 */
	public static function registerConfig(string $name, array $config): void {
		if( empty($config['path']) ) {
			throw new Exception('Missing a valid "path" in configuration of route "' . $name . '"');
		}
		
		$options = $config;
		unset($options['path'], $options['controller'], $options['parameters']);
		static::register($name, $config['path'], $config['controller'], [], $options);
	}
	
	/**
	 * Register route by $name
	 */
	public static function register(string $name, string $path, string $controller, array $parameters, array $options = []): void {
		static::$routes[$name] = new static($name, $path, $controller, $parameters, $options);
	}
	
	/**
	 * Get registered routes
	 */
	public static function getRoutes(): array {
		return static::$routes;
	}
	
	/**
	 * Get the route object for the $route name
	 *
	 * @return CliRoute
	 */
	public static function getRoute(string $name): Route {
		return static::$routes[$name];
	}
	
}

//http://fr.php.net/manual/fr/regexp.reference.escape.php
//http://fr.php.net/manual/fr/regexp.reference.character-classes.php
// Case Insensitive
/*
CliRoute::addTypeValidator(TypeValidator::make('int', '\d+'));
CliRoute::addTypeValidator(TypeValidator::make('boolean', '(?:true|false|[0-1])', function(&$value) {
	$value = boolval($value);
	return true;
}));
CliRoute::addTypeValidator(TypeValidator::make('file', function($value) {
	return is_readable($value);
}));
*/

