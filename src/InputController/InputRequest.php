<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController;

use Exception;
use Orpheus\Exception\NotFoundException;
use Orpheus\InputController\HttpController\RedirectHttpResponse;

abstract class InputRequest {
	
	/**
	 * The current main request
	 *
	 * @var static|null
	 */
	protected static ?InputRequest $mainRequest = null;
	
	/**
	 * The path
	 *
	 * @var string
	 */
	protected string $path;
	
	/**
	 * The input parameters (inline parameters)
	 *
	 * @var array
	 */
	protected array $parameters;
	
	/**
	 * The input (like stdin)
	 *
	 * @var array|string|null
	 */
	protected mixed $input;
	
	/**
	 * The found route for this request
	 * A request could exist without any route (e.g. Route initialization failed)
	 *
	 * @var ControllerRoute|null $route
	 */
	protected ?ControllerRoute $route = null;
	
	/**
	 * The values in path
	 *
	 * @var array
	 */
	protected array $pathValues;
	
	/**
	 * Constructor
	 */
	public function __construct(string $path, array $parameters, array|string|null $input) {
		$this->path = $path;
		$this->parameters = $parameters;
		$this->input = $input;
	}
	
	/**
	 * Process the request by finding a route and processing it
	 *
	 * @throws Exception
	 */
	public function process(): OutputResponse {
		[$route, $values] = $this->findFirstMatchingRoute();
		if( !$route ) {
			throw new NotFoundException(sprintf('No route matches the current request "%s"', $this));
		}
		
		return $this->processRoute($route, $values);
	}
	
	/**
	 * Find a matching route according to the request
	 */
	public function findFirstMatchingRoute(): array {
		foreach( $this->getRoutes() as $route ) {
			$values = [];
			if( $route->isMatchingRequest($this, $values) ) {
				return [$route, $values];
			}
		}
		
		return [null, null];
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
	 * @return RedirectHttpResponse|null
	 *
	 * Should be overridden to be used
	 */
	public function redirect(ControllerRoute $route): ?RedirectHttpResponse {
		return null;
	}
	
	/**
	 * Process the given route
	 *
	 * @throws Exception
	 */
	public function processRoute(ControllerRoute $route, array $values): OutputResponse {
		$this->setRoute($route, $values);
		
		return $this->route->run($this);
	}
	
	/**
	 * Test path is matching regex
	 */
	public function matchPath(string $regex, ?array &$matches): string {
		return preg_match($regex, urldecode($this->path), $matches);
	}
	
	/**
	 * Get the path
	 */
	public function getPath(): string {
		return $this->path;
	}
	
	/**
	 * Set the path
	 */
	protected function setPath(string $path): InputRequest {
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * Test if parameter $key exists in this request
	 */
	public function hasParameter(string $key): bool {
		return $this->getParameter($key) !== null;
	}
	
	/**
	 * Get the parameter by $key, assuming $default value
	 *
	 * @param mixed|null $default
	 */
	public function getParameter(string $key, mixed $default = null): mixed {
		return array_path_get($this->parameters, $key, $default);
	}
	
	/**
	 * Get all parameters
	 */
	public function getParameters(): array {
		return $this->parameters;
	}
	
	/**
	 * Set the parameters
	 */
	protected function setParameters(array $parameters): InputRequest {
		$this->parameters = $parameters;
		
		return $this;
	}
	
	/**
	 * Test if request has any input
	 */
	public function hasInput(): bool {
		return !!$this->input;
	}
	
	/**
	 * Get input
	 */
	public function getInput(): array {
		return $this->input;
	}
	
	/**
	 * Set the input
	 */
	protected function setInput(array|string|null $input): InputRequest {
		$this->input = $input;
		
		return $this;
	}
	
	/**
	 * Test if input $key exists in this request
	 */
	public function hasInputValue(string $key): bool {
		return $this->getInputValue($key) !== null;
	}
	
	/**
	 * Get the input by $key, assuming $default value
	 */
	public function getInputValue(string $key, mixed $default = null): mixed {
		return array_path_get($this->input, $key, $default);
	}
	
	/**
	 * Get the route name to this request
	 */
	public function getRouteName(): string {
		return $this->route->getName();
	}
	
	/**
	 * Get the route to this request
	 */
	public function getRoute(): ?ControllerRoute {
		return $this->route;
	}
	
	/**
	 * Set the route to this request
	 */
	public function setRoute(ControllerRoute $route, array $values): InputRequest {
		$this->route = $route;
		$this->pathValues = $values;
		
		return $this;
	}
	
	/**
	 * Get running controller
	 */
	public function getController(): AbstractController {
		return $this->getRoute() ? $this->getRoute()->getController() : static::getDefaultController();
	}
	
	/**
	 * Get the main input request
	 */
	public static function getMainRequest(): ?InputRequest {
		return static::$mainRequest;
	}
	
	/**
	 * Clone this request using another path (expecting a sub path)
	 */
	public function cloneWithPath(string $path): InputRequest {
		$clone = clone $this;
		$clone->setPath($path);
		return $clone;
	}
	
	public abstract static function getDefaultController();
	
	public abstract static function getRouteClass(): string;
	
}
