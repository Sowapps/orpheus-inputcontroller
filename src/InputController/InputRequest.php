<?php

namespace Orpheus\InputController;

use Orpheus\Core\Route;
use Orpheus\Exception\ForbiddenException;
use Orpheus\Exception\NotFoundException;

/**
 * The InputRequest class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
abstract class InputRequest {
	
	/**
	 * The current main request
	 *
	 * @var InputRequest
	 */
	protected static $mainRequest;
	/**
	 * The path
	 *
	 * @var string
	 */
	protected $path;
	
	/**
	 * The input parameters (inline parameters)
	 *
	 * @var array
	 */
	protected $parameters;
	
	/**
	 * The input (like stdin)
	 *
	 * @var array
	 */
	protected $input;
	
	/**
	 * The found route for this request
	 *
	 * @var ControllerRoute $route
	 */
	protected $route;
	
	/**
	 * Constructor
	 *
	 * @param string $path
	 * @param array $parameters
	 * @param array $input
	 */
	public function __construct($path, $parameters, $input) {
		$this->path = $path;
		$this->parameters = $parameters;
		$this->input = $input;
	}
	
	/**
	 * Process the request by finding a route and processing it
	 *
	 * @return OutputResponse
	 * @throws NotFoundException
	 */
	public function process(): OutputResponse {
		$route = $this->findFirstMatchingRoute();
		if( !$route ) {
			// Not found, look for an alternative (with /)
			$route = $this->findFirstMatchingRoute(true);
			if( $route ) {
				// Alternative found, try to redirect to this one
				$r = $this->redirect($route);
				if( $r ) {
					// Redirect
					return $r;
				}
				// Unable to redirect, throw not found
				$route = null;
			}
		}
		
		return $this->processRoute($route);
	}
	
	/**
	 * Find a matching route according to the request
	 *
	 * @param boolean $alternative
	 * @return Route
	 */
	public function findFirstMatchingRoute($alternative = false): ?ControllerRoute {
		/* @var ControllerRoute $route */
		foreach( $this->getRoutes() as $route ) {
			if( $route->isMatchingRequest($this, $alternative) ) {
				return $route;
			}
		}
		
		return null;
	}
	
	/**
	 * Get all available routes
	 *
	 * @return ControllerRoute[]
	 */
	public abstract function getRoutes(): array;
	
	/**
	 * Redirect response to $route
	 *
	 * @param ControllerRoute $route
	 * @return NULL
	 *
	 * Should be overridden to be used
	 */
	public function redirect(ControllerRoute $route) {
		return null;
	}
	
	/**
	 * Process the given route
	 *
	 * @param ControllerRoute $route
	 * @return OutputResponse
	 * @throws NotFoundException
	 * @throws ForbiddenException
	 */
	public function processRoute($route): OutputResponse {
		if( !$route ) {
			throw new NotFoundException('No route matches the current request ' . $this);
		}
		$this->setRoute($route);
		
		return $this->route->run($this);
	}
	
	/**
	 * Get the path
	 *
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}
	
	/**
	 * Set the path
	 *
	 * @param string $path
	 * @return InputRequest
	 */
	protected function setPath(string $path): InputRequest {
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * Test if parameter $key exists in this request
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasParameter(string $key): bool {
		return $this->getParameter($key, null) !== null;
	}
	
	/**
	 * Get the parameter by $key, assuming $default value
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParameter(string $key, $default = null) {
		return apath_get($this->parameters, $key, $default);
	}
	
	/**
	 * Get all parameters
	 *
	 * @return array
	 */
	public function getParameters(): array {
		return $this->parameters;
	}
	
	/**
	 * Set the parameters
	 *
	 * @param array
	 * @return InputRequest
	 */
	protected function setParameters(array $parameters): InputRequest {
		$this->parameters = $parameters;
		
		return $this;
	}
	
	/**
	 * Test if request has any input
	 *
	 * @return boolean
	 */
	public function hasInput(): bool {
		return !!$this->input;
	}
	
	/**
	 * Get input
	 *
	 * @return array
	 */
	public function getInput(): array {
		return $this->input;
	}
	
	/**
	 * Set the input
	 *
	 * @param array
	 * @return InputRequest
	 */
	protected function setInput(array $input): InputRequest {
		$this->input = $input;
		
		return $this;
	}
	
	/**
	 * Test if input $key exists in this request
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasInputValue(string $key): bool {
		return $this->getInputValue($key, null) !== null;
	}
	
	/**
	 * Get the input by $key, assuming $default value
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getInputValue(string $key, $default = null) {
		return apath_get($this->input, $key, $default);
	}
	
	/**
	 * Get the route name to this request
	 *
	 * @return string
	 */
	public function getRouteName(): string {
		return $this->route->getName();
	}
	
	/**
	 * Get the route to this request
	 *
	 * @return ControllerRoute
	 */
	public function getRoute(): ControllerRoute {
		return $this->route;
	}
	
	/**
	 * Set the route to this request
	 *
	 * @param ControllerRoute $route
	 * @return InputRequest
	 */
	public function setRoute(ControllerRoute $route): InputRequest {
		$this->route = $route;
		
		return $this;
	}
	
	/**
	 * Get running controller
	 *
	 * @return Controller
	 */
	public function getController(): Controller {
		return $this->getRoute() ? $this->getRoute()->getController() : static::getDefaultController();
	}
	
	/**
	 * Get the main input request
	 *
	 * @return InputRequest
	 */
	public static function getMainRequest(): InputRequest {
		return static::$mainRequest;
	}
	
	public abstract static function getDefaultController();
	
}
