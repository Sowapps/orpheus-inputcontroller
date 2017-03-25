<?php
/**
 * ControllerRoute
 */

namespace Orpheus\InputController;

use Orpheus\Exception\NotFoundException;
use Orpheus\Exception\ForbiddenException;
use Orpheus\Exception\UserException;
use Orpheus\Config\YAML\YAML;
use Orpheus\Core\Route;
use Orpheus\Core\RequestHandler;

/**
 * The ControllerRoute class
 * 
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
abstract class ControllerRoute extends Route {
	
	/**
	 * A route is identified by its name
	 * 
	 * @var string The name
	 */
	protected $name;
	
	/**
	 * The path determine how to access this route
	 * 
	 * @var string The path
	 */
	protected $path;
	
	/**
	 * The class of called controller associated to this route
	 * 
	 * @var string The controller class
	 */
	protected $controllerClass;
	
	/**
	 * Non-processed options in route configuration
	 * 
	 * @var array
	 */
	protected $options;
	
	/**
	 * Restrictions to access this route
	 * 
	 * @var array
	 */
	protected $restrictTo;
	
	/**
	 * Default response if controller returns is invalid
	 * 
	 * @var OutputResponse
	 */
	protected $defaultResponse;
	
	/**
	 * Registered routes
	 *
	 * @var array
	 */
	protected static $routes = array();
	
	/**
	 * Registered route restrictions
	 * 
	 * @var array
	 */
	protected static $routesRestrictions = array();

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $path
	 * @param string $controller
	 * @param array $restrictTo
	 * @param string $defaultResponse
	 * @param array $options
	 */
	protected function __construct($name, $path, $controller, $restrictTo, $defaultResponse, $options) {
		$this->name			= $name;
		$this->path			= $path;
		$this->controllerClass	= $controller;
		$this->restrictTo	= $restrictTo;
		$this->options		= $options;
		$this->defaultResponse	= $defaultResponse;
	}
	
	/**
	 * Test if the route matches the given $request
	 * 
	 * @param \Orpheus\InputController\InputRequest $request
	 * @param array $values
	 * @param boolean $alternative True if we are looking for an alternative route, because we did not find any primary one
	 */
	public abstract function isMatchingRequest(InputRequest $request, &$values=array(), $alternative=false);
	
	/**
	 * Get all registered routes
	 * Routes are commonly stored in the configuration
	 * 
	 * @return \Orpheus\InputController\ControllerRoute[]
	 */
	public static function getRoutes() {
		static::initialize();
		return static::$routes;
// 		throw new Exception('The class "'.get_called_class().'" should override the `getRoutes()` static method from "'.get_class().'"');
	}
	
	/**
	 * Register a route configuration
	 * 
	 * @param string $name
	 * @param array $config
	 * @throws \Exception
	 */
	public static function registerConfig($name, array $config) {
		throw new \Exception('The class "'.get_called_class().'" should override the `registerConfig()` static method from "'.get_class().'"');
	}
	
	/**
	 * Run the $request by processing the matching controller
	 * 
	 * @param \Orpheus\InputController\InputRequest $request
	 * @throws NotFoundException
	 * @throws ForbiddenException
	 * @return \Orpheus\InputController\OutputResponse
	 * @uses \Orpheus\InputController\InputRequest::processRoute()
	 */
	public function run(InputRequest $request) {
		try {
			if( !$this->controllerClass || !class_exists($this->controllerClass, true) ) {
				throw new NotFoundException('The controller "'.$this->controllerClass.'" was not found');
			}
			$request->setRoute($this);
			
			//Wow, we made it to handle session, ok ?
			$this->prepare($request);
			
			if( !$this->isAccessible() ) {
				throw new ForbiddenException('This route is not accessible in this context');
			}
			$controller = $this->instanciateController();
// 			$controllerClass = $this->controllerClass;
// 			$controller = new $controllerClass();
// 			if( !($controller instanceof Controller) ) {
// 				throw new NotFoundException('The controller "'.$this->controllerClass.'" is not a valid controller, the class must inherit from "'.get_class().'"');
// 			}
			$result	= $controller->process($request);
			return $result;
		} catch( Exception $exception ) {
			return $this->processException($exception);
		}
	}
	
	/**
	 * Process the given $exception with the default response
	 * 
	 * @param \Orpheus\Exception\UserException $exception
	 * @param array $values
	 * @return \Orpheus\InputController\OutputResponse
	 */
	public function processUserException(UserException $exception, $values=array()) {
		// This exception is a user one, we use an app page
		$response = $this->defaultResponse;
		return $response::generateFromUserException($exception, $values);
	}


	/**
	 * Process the given $exception with the default response
	 *
	 * @param \Orpheus\Exception\UserException $exception
	 * @return \Orpheus\InputController\OutputResponse
	 */
	public function processException(\Exception $exception) {
		// This exception is fatal, this is an Orpheus page
		$response = $this->defaultResponse;
		return $response::generateFromException($exception);
	}
	
	/**
	 * Define this class initialized
	 * 
	 * @var string
	 */
	protected static $initialized = false;
	
	/**
	 * Initialize the route class by loading the configuration (once only)
	 */
	public static function initialize() {
// 		debug('ControllerRoute::initialize()');
		if( static::isInitialized() ) { return; }
		static::$initialized = true;
		
		$routes = array();
		
		// TODO: Add cache, but what kind of ?
		
		static::loadRoutes($routes);
		
		// Register routes
		foreach( $routes as $type => $typeRoutes ) {
			$routeClass = RequestHandler::getRouteClass($type);
			if( !class_exists($routeClass, true) || !in_array(get_class(), class_parents($routeClass)) ) {
				continue;
			}
			foreach( $typeRoutes as $routeName => $routeConfig ) {
				$routeClass::registerConfig($routeName, $routeConfig);
			}
		}
	}
	
	const REQUIREMENTS_KEY = 'require-packages';
	
	protected static function loadRoutes(&$routes, $package=null) {
		
		$packageRoutes = array();
		// Load prod routes (all environments routes)
		static::populateRoutesFromFile($packageRoutes, 'routes', $package);
		// Load dev routes
		if( DEV_VERSION ) {
			// If there is not file routes_dev, we get an empty array
			try {
				// Optional
				static::populateRoutesFromFile($packageRoutes, 'routes_dev', $package);
			} catch( \Exception $e ) {
				
			}
		}
		
		if( !empty($packageRoutes[self::REQUIREMENTS_KEY]) && is_array($packageRoutes[self::REQUIREMENTS_KEY]) ) {
			$packageAndRequiredRoutes = array();
			foreach( $packageRoutes[self::REQUIREMENTS_KEY] as $requirement ) {
				if( is_string($requirement) ) {
					$requirement = array(
						'name' => $requirement
					);
				}
				if( empty($requirement['name']) ) {
					continue;
				}
				static::loadRoutes($packageAndRequiredRoutes, $requirement['name']);
			}
			unset($packageRoutes[self::REQUIREMENTS_KEY]);
			static::mergeRoutes($packageAndRequiredRoutes, $packageRoutes);
			unset($packageRoutes);
			
		} else {
			
			// Remove invalid requirements
			unset($packageRoutes[self::REQUIREMENTS_KEY]);
			$packageAndRequiredRoutes = &$packageRoutes;
			
		}
		
		static::mergeRoutes($routes, $packageAndRequiredRoutes);
	}
	
	protected static function populateRoutesFromFile(&$routes, $file, $package=null) {
		$conf = YAML::buildFrom($package, $file, true);
		static::mergeRoutes($routes, $conf->asArray());
	}
	
	protected static function mergeRoutes(&$routes, $added) {
		// First level (only) is merged
		foreach( $added as $type => $typeRoutes ) {
			if( isset($routes[$type]) ) {
				$routes[$type]	= array_merge($typeRoutes, $routes[$type]);
			} else {
				$routes[$type]	= $typeRoutes;
			}
		}
	}
	
	/**
	 * Test if the class is initialized
	 * 
	 * @return boolean
	 */
	public static function isInitialized() {
		return static::$initialized;
	}
	
	/**
	 * Get the name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Get the path
	 * 
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * Get the controller class
	 * 
	 * @return string
	 */
	public function getControllerClass() {
		return $this->controllerClass;
	}
	
	/**
	 * Instanciate the controller and return it
	 * 
	 * @return \Orpheus\InputController\Controller
	 */
	public function instanciateController() {
		$class = $this->controllerClass;
		/* @var Controller $controller */
		$controller = new $class();
		if( !($controller instanceof Controller) ) {
			throw new NotFoundException('The controller "'.$this->controllerClass.'" is not a valid controller, the class must inherit from "'.get_class().'"');
		}
		$controller->setRoute($this);
		return $controller;
	}
	
	/**
	 * Get route options
	 * 
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Orpheus\Core\Route::isAccessible()
	 */
	public function isAccessible() {
		if( !CHECK_MODULE_ACCESS ) {
			return true;
		}
		if( $this->restrictTo ) {
			foreach( $this->restrictTo as $type => $options ) {
				if( empty(static::$routesRestrictions[$type]) ) {
					throw new \Exception('Unknown route access type "'.$type.'" in config file');
				}
				if( !call_user_func(static::$routesRestrictions[$type], $this, $options)) {
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Orpheus\Core\Route::getLink()
	 */
	public function getLink() {
		return $this->formatURL();
	}
	
	/**
	 * Format the URL to this route using $values
	 * 
	 * @param array $values
	 */
	public abstract function formatURL($values=array());
	
	/**
	 * Register the access restriction $type
	 * This will be used by isAccessible()
	 * 
	 * @param string $type
	 * @param callable $callable
	 * @uses isAccessible()
	 */
	public static function registerAccessRestriction($type, $callable) {
		static::$routesRestrictions[$type] = $callable;
	}
	
	/**
	 * Get the current route name
	 * 
	 * @return string
	 */
	public static function getCurrentRouteName() {
		return InputRequest::getMainRequest()->getRouteName();
	}
	
	
}
