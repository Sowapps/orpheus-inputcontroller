<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController;

use Exception;
use Orpheus\Config\Yaml\Yaml;
use Orpheus\Core\RequestHandler;
use Orpheus\Core\Route;
use Orpheus\Exception\ForbiddenRouteException;
use Orpheus\Exception\NotFoundException;
use Orpheus\Service\ApplicationKernel;
use RuntimeException;

abstract class ControllerRoute extends Route {
	
	const REQUIREMENTS_KEY = 'require-packages';
	const PROVIDERS_KEY = 'providers';
	
	/**
	 * Registered routes
	 *
	 * @var array
	 */
	protected static array $routes = [];
	
	/**
	 * Registered route restrictions
	 *
	 * @var array
	 */
	protected static array $routesRestrictions = [];
	
	/**
	 * Define this class initialized
	 */
	protected static bool $initialized = false;
	
	/**
	 * A route is identified by its name
	 *
	 * @var string The name
	 */
	protected string $name;
	
	/**
	 * The path determine how to access this route
	 *
	 * @var string The path
	 */
	protected string $path;
	
	/**
	 * The class of called controller associated to this route
	 *
	 * @var string The controller class
	 */
	protected string $controllerClass;
	
	/**
	 * The running controller
	 */
	protected AbstractController $controller;
	
	/**
	 * Restrictions to access this route
	 *
	 * @var array
	 */
	protected array $restrictTo;
	
	/**
	 * Default response if controller returns is invalid
	 *
	 * @var string
	 */
	protected string $defaultResponse;
	
	/**
	 * Non-processed options in route configuration
	 *
	 * @var array
	 */
	protected array $options;
	
	/**
	 * Constructor
	 */
	protected function __construct(string $name, string $path, string $controller, ?array $restrictTo, string $defaultResponse, array $options) {
		$this->name = $name;
		$this->path = $path;
		$this->controllerClass = $controller;
		$this->restrictTo = $restrictTo ?? [];
		$this->defaultResponse = $defaultResponse;
		$this->options = $options;
	}
	
	/**
	 * Test if the route matches the given $request
	 */
	public abstract function isMatchingRequest(InputRequest $request, array &$values = []);
	
	/**
	 * Run the $request by processing the matching controller
	 *
	 * @throws Exception
	 * @uses InputRequest::processRoute()
	 */
	public function run(InputRequest $request): OutputResponse {
		if( !$this->controllerClass || !class_exists($this->controllerClass) ) {
			throw new NotFoundException(sprintf('The controller "%s" was not found', $this->controllerClass));
		}
		// Controller should be available now, we could need it to prepare request
		$this->controller = $this->instantiateController();
		
		// Wow, we made it to handle session, ok ?
		$this->controller->prepare($request);
		
		if( !$this->isAccessible() ) {
			throw new ForbiddenRouteException($this);
		}
		
		return $this->controller->process();
	}
	
	/**
	 * @throws Exception
	 */
	public function isAccessible(): bool {
		if( !CHECK_MODULE_ACCESS ) {
			return true;
		}
		if( $this->restrictTo ) {
			foreach( $this->restrictTo as $type => $options ) {
				if( empty(static::$routesRestrictions[$type]) ) {
					throw new RuntimeException(sprintf('Unknown route access type "%s" in config file', $type));
				}
				if( !call_user_func(static::$routesRestrictions[$type], $this, $options) ) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Instantiate the controller and return it
	 */
	public function instantiateController(): AbstractController {
		$class = $this->controllerClass;
		/* @var AbstractController $controller */
		$controller = new $class($this, $this->getOptions());
		if( !($controller instanceof AbstractController) ) {
			throw new NotFoundException(sprintf('The controller "%s" is not a valid controller, the class must inherit from "%s"', $this->controllerClass, get_class()));
		}
		
		return $controller;
	}
	
	/**
	 * Get the name
	 */
	public function getName(): string {
		return $this->name;
	}
	
	/**
	 * Get the path
	 */
	public function getPath(): string {
		return $this->path;
	}
	
	/**
	 * Get the controller class
	 */
	public function getControllerClass(): string {
		return $this->controllerClass;
	}
	
	/**
	 * Get route options
	 */
	public function getOptions(): array {
		return $this->options;
	}
	
	/**
	 * Get link of route
	 *
	 * @see formatUrl()
	 */
	public function getLink(array $values = []): string {
		return $this->formatUrl($values);
	}
	
	/**
	 * Format the URL to this route using $values
	 */
	public abstract function formatUrl(array $values = [], array $parameters = []): string;
	
	/**
	 * Get all registered routes
	 * These one are commonly stored in the configuration (routes.yaml)
	 *
	 * @return ControllerRoute[]
	 */
	public static function getRoutes(): array {
		static::initialize();
		
		return static::$routes;
	}
	
	/**
	 * Initialize the route class by loading the configuration (once only)
	 */
	public static function initialize(): void {
		if( static::isInitialized() ) {
			return;
		}
		static::$initialized = true;
		$routes = [];
		static::loadRoutes($routes);
		
		// Register routes
		foreach( $routes as $type => $typeRoutes ) {
			/** @var class-string $routeClass */
			$routeClass = RequestHandler::getRouteClass($type);
			if( !class_exists($routeClass) || !in_array(get_class(), class_parents($routeClass)) ) {
				continue;
			}
			foreach( $typeRoutes as $routeName => $routeConfig ) {
				$routeClass::registerConfig($routeName, $routeConfig);
			}
		}
	}
	
	/**
	 * Test if the class is initialized
	 */
	public static function isInitialized(): bool {
		return static::$initialized;
	}
	
	/**
	 * Load routes from $package or app (if null)
	 */
	protected static function loadRoutes(array &$routes, ?string $package = null): void {
		// TODO: Protect against loop
		
		$packageRoutes = [];
		// Load prod routes (all environments routes)
		static::populateRoutesFromFile($packageRoutes, 'routes', $package);
		$kernel = ApplicationKernel::get();
		// Load dev routes
		if( $kernel->isDebugEnabled() ) {
			// If there is no routes_dev file, we get an empty array
			static::populateRoutesFromFile($packageRoutes, 'routes_dev', $package, true);
		}
		
		if( !empty($packageRoutes[self::REQUIREMENTS_KEY]) && is_array($packageRoutes[self::REQUIREMENTS_KEY]) ) {
			$packageAndRequiredRoutes = [];
			foreach( $packageRoutes[self::REQUIREMENTS_KEY] as $requirement ) {
				if( is_string($requirement) ) {
					$requirement = [
						'name' => $requirement,
					];
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
		
		// Handle new providers for custom routing
		if( !empty($packageAndRequiredRoutes[self::PROVIDERS_KEY]) && is_array($packageAndRequiredRoutes[self::PROVIDERS_KEY]) ) {
			foreach( $packageAndRequiredRoutes[self::PROVIDERS_KEY] as $providerClass ) {
				$provider = new $providerClass();
				static::mergeRoutes($packageAndRequiredRoutes, $provider->getRoutes());
			}
			unset($packageAndRequiredRoutes[self::PROVIDERS_KEY]);
		}
		
		static::mergeRoutes($routes, $packageAndRequiredRoutes);
	}
	
	/**
	 * Populate given array with routes from $file in $package (or app if null)
	 */
	protected static function populateRoutesFromFile(array &$routes, string $file, ?string $package = null, bool $optional = false): void {
		$conf = Yaml::buildFrom($package, $file, true, $optional);
		if( $conf ) {
			static::mergeRoutes($routes, $conf->asArray());
		}
	}
	
	/**
	 * Merge routes array with routes' logic
	 */
	protected static function mergeRoutes(array &$routes, array $added): void {
		// First level (only) is merged
		foreach( $added as $type => $typeRoutes ) {
			if( isset($routes[$type]) ) {
				$routes[$type] = array_merge($typeRoutes, $routes[$type]);
			} else {
				$routes[$type] = $typeRoutes;
			}
		}
	}
	
	/**
	 * Register a route configuration
	 */
	public static function registerConfig(string $name, array $config) {
		throw new RuntimeException(sprintf("The class \"%s\" should override the `registerConfig()` static method from \"%s\"", get_called_class(), get_class()));
	}
	
	/**
	 * Register the access restriction $type
	 * This will be used by isAccessible()
	 *
	 * @uses isAccessible()
	 */
	public static function registerAccessRestriction(string $type, callable $callable): void {
		static::$routesRestrictions[$type] = $callable;
	}
	
	/**
	 * Get the current route name
	 */
	public static function getCurrentRouteName(): string {
		return InputRequest::getMainRequest()->getRouteName();
	}
	
	public function getController(): AbstractController {
		return $this->controller;
	}
	
}
