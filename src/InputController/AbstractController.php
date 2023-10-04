<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController;

use Orpheus\Exception\UserException;
use Orpheus\Initernationalization\TranslationService;

abstract class AbstractController {
	
	/**
	 * The request calling this controller
	 *
	 * @var InputRequest|null
	 */
	private ?InputRequest $request = null;
	
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
	 */
	public function prepare($request): ?OutputResponse {
		$this->request = $request;
		return null;
	}
	
	/**
	 * Process the $request
	 *
	 * @uses ControllerRoute::run()
	 * @see  AbstractController::preRun()
	 * @see  AbstractController::run()
	 * @see  AbstractController::postRun()
	 *
	 * preRun() and postRun() are not declared in this class because PHP does not handle inheritance of parameters
	 * if preRun() is declared getting a InputRequest, we could not declare a preRun() using a HttpRequest
	 */
	public function process(): ?OutputResponse {
		// run, preRun and postRun take parameter depending on Controller, request may be of a child class of InputRequest
		$request = $this->request;
		
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
	 */
	public function preRun($request): ?OutputResponse {
		return null;
	}
	
	/**
	 * Process the given UserException
	 *
	 * @return mixed
	 * @throws UserException
	 */
	public function processUserException(UserException $exception): OutputResponse {
		throw $exception;// Throw to request
	}
	
	/**
	 * Run this controller
	 */
	abstract public function run($request): OutputResponse;
	
	/**
	 * After running the controller
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function postRun($request, $response): ?OutputResponse {
		return $response;
	}
	
	/**
	 * Get the route name
	 */
	public function getRouteName(): ?string {
		$route = $this->getRoute();
		
		return $route?->getName();
	}
	
	/**
	 * Render the given $layout in $response using $values
	 *
	 * @return mixed The $response
	 */
	public function render(OutputResponse $response, string $layout, array $values = []): OutputResponse {
		$this->fillValues($values);
		$response->collectFrom($layout, $values);
		
		return $response;
	}
	
	/**
	 * Fill array with default values
	 */
	public function fillValues(array &$values = []): void {
		if( class_exists(TranslationService::class) ) {
			$values['translator'] = TranslationService::getActive();
		}
		$values['controller'] = $this;
		$values['request'] = $this->getRequest();
		$values['route'] = $this->getRoute();
	}
	
	/**
	 * Get parameter values of this controller
	 * Use it to generate routes (as for menus) with path parameters & you can get the current context
	 */
	public function getValues(): array {
		return [];
	}
	
	/**
	 * Get the request
	 */
	public function getRequest(): ?InputRequest {
		return $this->request;
	}
	
	/**
	 * Get the route
	 */
	public function getRoute(): ?ControllerRoute {
		if( $this->route ) {
			return $this->route;
		}
		
		return $this->request?->getRoute();
	}
	
	/**
	 * Get an option by $key
	 *
	 * @param mixed|null $default
	 * @return string|mixed
	 */
	public function getOption(string $key, mixed $default = null): mixed {
		return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
	}
	
	/**
	 * Set an option by $key
	 */
	public function setOption(string $key, mixed $value): AbstractController {
		$this->options[$key] = $value;
		
		return $this;
	}
	
	/**
	 * Set an option by $key
	 */
	public function setOptions(array $options): AbstractController {
		$this->options = $options;
		
		return $this;
	}
	
}
