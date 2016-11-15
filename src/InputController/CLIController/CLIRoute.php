<?php
/**
 * CLIRoute
 */

namespace Orpheus\InputController\CLIController;

use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\InputRequest;
use Orpheus\InputController\TypeValidator;
use Orpheus\InputController\CLIController\CLIArgument;

/**
 * The CLIRoute class
 * 
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class CLIRoute extends ControllerRoute {
	
	/**
	 * Available parameters
	 * 
	 * @var CLIArgument[]
	 */
	protected $parameters = array();
	
	/**
	 * Available parameters by short name
	 * 
	 * @var CLIArgument[]
	 */
	protected $parametersBySN = array();
	
	/**
	 * Registered regex for a type
	 * 
	 * @var array
	 */
	protected static $typeValidators = array();
	
	/**
	 * Registered routes
	 * 
	 * @var array
	 */
	protected static $routes = array();
	
	/**
	 * Constructor
	 * 
	 * @param string $name
	 * @param string $path
	 * @param string $controller
	 * @param CLIArgument[] $parameters
	 * @param array $options
	 */
	protected function __construct($name, $path, $controller, $parameters, $options) {
		parent::__construct($name, $path, $controller, null, null, $options);
// 		$this->parameters = $parameters;
		foreach( $parameters as $arg ) {
			$this->parameters[$arg->getLongName()] = &$arg;
			if( $arg->hasShortName() ) {
				$this->parameters[$arg->getShortName()] = &$arg;
			}
		}
	}
	
	/**
	 * Format the current route to get an URL from path
	 * 
	 * @param string[] $values
	 * @return string
	 * @throws Exception
	 */
	public function formatURL($values=array()) {
		$params = '';
		if( $values ) {
			foreach( $values as $key => $value ) {
				if( !isset($this->parameters[$key]) ) {
					continue;
				}
				$arg = $this->parameters[$key];
				$type = $arg->getType();
				if( !static::validateParameter($type, $value) ) {
					throw new \Exception('The given value "'.$value.'" of parameter "'.$key.'" is not a valid value of type "'.$type.'" to generate command for route '.$this->name);
				}
				$params .= ' '.$arg->getLongCommand($value);
			}
		}
		return static::getRootCommand().' '.$this->getPath().$params;
	}
	
	public static function getRootCommand() {
		return 'php -f app/console/run.php';
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
	 * @param CLIRequest $request
	 * @param array $values
	 * @param boolean $alternative
	 * @see \Orpheus\InputController\ControllerRoute::isMatchingRequest()
	 */
	public function isMatchingRequest(InputRequest $request, &$values=array(), $alternative=false) {
		// CLI does not use alternative
		return $request->getPath() === $this->getPath();
	}
	
	/**
	 * Register route by $name from config
	 * 
	 * @param string $name
	 * @param array $config
	 * @throws \Exception
	 */
	public static function registerConfig($name, array $config) {
		debug('Config '.$name, $config);
		if( empty($config['path']) ) {
			throw new \Exception('Missing a valid "path" in configuration of route "'.$name.'"');
		}
		
		$parameters = array();
		if( isset($config['parameters']) && is_array($config['parameters']) ) {
			foreach( $config['parameters'] as $name => $config ) {
				$parameters[] = CLIArgument::make($name, $config);
			}
		}
// 		if( empty($config['response']) ) {
// 			$config['response']	= !empty($config['output']) ? static::getOutputResponse($config['output']) : 'Orpheus\InputController\CLIController\HTMLCLIResponse';
// 		}
// 		if( empty($config['controller']) ) {
// 			if( !empty($config['redirect']) ) {
// 				$config['controller'] = 'Orpheus\\Controller\\RedirectController';
// 			} else
// 			if( !empty($config['render']) ) {
// 				$config['controller'] = 'Orpheus\\Controller\\StaticPageController';
// 			} else {
// 				throw new \Exception('Missing a valid `controller` in configuration of route "'.$name.'"');
// 			}
// 		}
// 		if( !isset($config['restrictTo']) ) {
// 			$config['restrictTo'] = null;
// 		}
		$options = $config;
		unset($options['path'], $options['controller'], $options['parameters']);
		static::register($name, $config['path'], $config['controller'], $parameters, $options);
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
	public static function register($name, $path, $controller, $parameters, $options=array()) {
		static::$routes[$name] = new static($name, $path, $controller, $parameters, $options);
	}
	
	/**
	 * Add the type validator to validate parameters
	 * 
	 * @param TypeValidator $validator
	 * @return boolean
	 */
	public static function validateParameter($type, $value) {
		$validator = static::getTypeValidator($type);
		return $validator->validate($value);
	}
	
	/**
	 * Get a type validator by type name
	 * 
	 * @param string $type
	 * @return TypeValidator
	 */
	public static function getTypeValidator($type) {
		return static::$typeValidators[$type];
	}
	
	/**
	 * Add the type validator to validate parameters
	 * 
	 * @param TypeValidator $validator
	 */
	public static function addTypeValidator(TypeValidator $validator) {
		static::$typeValidators[$validator->getName()] = $validator;
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
	 * @param string $method
	 * @return CLIRoute
	 */
	public static function getRoute($route) {
		return static::$routes[$route];
	}
	
}

//http://fr.php.net/manual/fr/regexp.reference.escape.php
//http://fr.php.net/manual/fr/regexp.reference.character-classes.php
// Case Insensitive
CLIRoute::addTypeValidator(TypeValidator::make('file', function($value) {
	return is_readable($value);
}));
// CLIRoute::setTypeRegex('id',	'[1-9]\d*');
// CLIRoute::setTypeRegex('slug',	'[a-z0-9\-_]+');

