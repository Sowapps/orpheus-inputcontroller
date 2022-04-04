<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController;

use Orpheus\Exception\UserException;

abstract class Controller {
	
	/**
	 * The request calling this controller
	 *
	 * @var InputRequest|null
	 */
	protected ?InputRequest $request = null;
	
	/**
	 * The route calling this controller
	 * A controller could be called without any route and any request
	 * This variable comes to get the route without any request
	 *
	 * @var ControllerRoute|null
	 */
	protected ?ControllerRoute $route = null;
	
	/**
	 * Running options for this controller
	 *
	 * @var array
	 */
	protected array $options = [];
	
	/**
	 * Catch controller output when running it
	 *
	 * @var boolean
	 */
	protected bool $catchControllerOutput = false;
	
	/**
	 * Controller constructor
	 *
	 * @param ControllerRoute|null $route
	 * @param array $options
	 */
	public function __construct(?ControllerRoute $route, array $options) {
		$this->route = $route;
		$this->setOptions($options);
	}
	
	/**
	 * Get this controller as string
	 *
	 * @return string
	 */
	public function __toString() {
		return get_called_class();
	}
	
	/**
	 * Prepare environment for this route
	 *
	 * @param InputRequest $request
	 */
	public function prepare($request) {
	}
	
	/**
	 * Process the $request
	 *
	 * @param InputRequest $request
	 * @return OutputResponse
	 * @uses ControllerRoute::run()
	 * @see  Controller::preRun()
	 * @see  Controller::run()
	 * @see  Controller::postRun()
	 *
	 * preRun() and postRun() are not declared in this class because PHP does not handle inheritance of parameters
	 * if preRun() is declared getting a InputRequest, we could not declare a preRun() using a HttpRequest
	 */
	public function process(InputRequest $request): ?OutputResponse {
		// run, preRun and postRun take parameter depending on Controller, request may be of a child class of InputRequest
		$this->request = $request;
		
		if( $this->catchControllerOutput ) {
			ob_start();
		}
		try {
			// Could prevent Run & PostRun
			// We recommend that PreRun only return Redirects and Exceptions
			$response = $this->preRun($request);
		} catch( UserException $e ) {
			$response = $this->processUserException($e);
		}
		if( !$response ) {
			// PreRun could prevent Run & PostRun
			try {
				$response = $this->run($request);
			} catch( UserException $e ) {
				$response = $this->processUserException($e);
			}
			$response = $this->postRun($request, $response);
		}
		if( $this->catchControllerOutput ) {
			$response->setControllerOutput(ob_get_clean());
		}
		
		return $response;
	}
	
	/**
	 * Before running controller
	 *
	 * @param InputRequest $request
	 * @return OutputResponse|null
	 */
	public function preRun($request): ?OutputResponse {
		return null;
	}
	
	/**
	 * Process the given UserException
	 *
	 * @param UserException $exception
	 * @return mixed
	 * @throws UserException
	 */
	public function processUserException(UserException $exception) {
		throw $exception;// Throw to request
	}
	
	/**
	 * Run this controller
	 *
	 * @param InputRequest $request
	 * @return OutputResponse|null
	 */
	abstract public function run($request): OutputResponse;
	
	/**
	 * After running the controller
	 *
	 * @param InputRequest $request
	 * @param $response
	 * @return OutputResponse|null
	 */
	public function postRun($request, $response): ?OutputResponse {
		return $response;
	}
	
	/**
	 * Get the route name
	 *
	 * @return string
	 */
	public function getRouteName(): ?string {
		$route = $this->getRoute();
		
		return $route ? $route->getName() : null;
	}
	
	/**
	 * Render the given $layout in $response using $values
	 *
	 * @param OutputResponse $response
	 * @param string $layout
	 * @param array $values
	 * @return mixed The $response
	 */
	public function render($response, string $layout, array $values = []): OutputResponse {
		$this->fillValues($values);
		$response->collectFrom($layout, $values);
		
		return $response;
	}
	
	/**
	 * Fill array with default values
	 *
	 * @param array $values
	 */
	public function fillValues(&$values = []) {
		$values['controller'] = $this;
		$values['request'] = $this->getRequest();
		$values['route'] = $this->getRoute();
	}
	
	/**
	 * Get parameter values of this controller
	 * Use it to generate routes (as for menus) with path parameters & you can get the current context
	 *
	 * @return array
	 */
	public function getValues(): array {
		return [];
	}
	
	/**
	 * Get the request
	 *
	 * @return InputRequest|null
	 */
	public function getRequest(): ?InputRequest {
		return $this->request;
	}
	
	/**
	 * Get the route
	 *
	 * @return ControllerRoute
	 */
	public function getRoute(): ?ControllerRoute {
		if( $this->route ) {
			return $this->route;
		}
		
		return $this->request ? $this->request->getRoute() : null;
	}
	
	/**
	 * Get an option by $key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return string|mixed
	 */
	public function getOption(string $key, $default = null) {
		return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
	}
	
	/**
	 * Set an option by $key
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Controller
	 */
	public function setOption(string $key, $value): Controller {
		$this->options[$key] = $value;
		
		return $this;
	}
	
	/**
	 * Set an option by $key
	 *
	 * @param array $options
	 * @return Controller
	 */
	public function setOptions(array $options): Controller {
		$this->options = $options;
		
		return $this;
	}
	
}
