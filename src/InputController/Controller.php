<?php
/**
 * Controller
 */

namespace Orpheus\InputController;

use Orpheus\Exception\UserException;

/**
 * The Controller class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
abstract class Controller {
	
	/**
	 * The request calling this controller
	 *
	 * @var InputRequest
	 */
	protected $request;
	
	/**
	 * The route calling this controller
	 * A controller could be called without any route and any request
	 * This variable comes to get the route without any request
	 *
	 * @var ControllerRoute
	 */
	protected $route;
	
	/**
	 * Running options for this controller
	 *
	 * @var array
	 */
	protected $options = [];
	
	/**
	 * Catch controller output when running it
	 *
	 * @var boolean
	 */
	protected $catchControllerOuput = false;
	
	/**
	 * Controller constructor
	 *
	 * @param ControllerRoute $route
	 * @param array $options
	 */
	public function __construct(?ControllerRoute $route, array $options) {
		$this->route = $route;
		$this->options = $options;
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
	 * if preRun() is declared getting a InputRequest, we could not declare a preRun() using a HTTPRequest
	 */
	public function process(InputRequest $request) {
		// run, preRun and postRun take parameter depending on Controller, request may be of a child class of InputRequest
		$this->request = $request;
		
		if( $this->catchControllerOuput ) {
			ob_start();
		}
		$result = null;
		try {
			// Could prevent Run & PostRun
			// We recommend that PreRun only return Redirects and Exceptions
			$result = $this->preRun($request);
		} catch( UserException $e ) {
			$result = $this->processUserException($e);
		}
		if( !$result ) {
			// PreRun could prevent Run & PostRun
			try {
				$result = $this->run($request);
			} catch( UserException $e ) {
				$result = $this->processUserException($e);
			}
			$this->postRun($request, $result);
		}
		if( $this->catchControllerOuput ) {
			$result->setControllerOutput(ob_get_clean());
		}
		
		return $result;
	}
	
	/**
	 * Before running controller
	 *
	 * @param InputRequest $request
	 * @return OutputResponse|null
	 */
	public function preRun($request) {
		return null;
	}
	
	/**
	 * Process the given UserException
	 *
	 * @param UserException $e
	 * @return mixed
	 * @throws UserException
	 */
	public function processUserException(UserException $e) {
		throw $e;// Throw to request
	}
	
	/**
	 * Run this controller
	 *
	 * @param InputRequest $request
	 * @return OutputResponse|null
	 */
	public abstract function run($request);
	
	/**
	 * After running the controller
	 *
	 * @param InputRequest $request
	 * @return OutputResponse|null
	 */
	public function postRun($request, $response) {
		return $response;
	}
	
	/**
	 * Get the route name
	 *
	 * @return string
	 */
	public function getRouteName() {
		$route = $this->getRoute();
		return $route ? $route->getName() : null;
	}
	
	/**
	 * Get the route
	 *
	 * @return ControllerRoute
	 */
	public function getRoute() {
		return $this->route ? $this->route : ($this->request ? $this->request->getRoute() : null);
	}
	
	/**
	 * Set the route
	 *
	 * @param ControllerRoute
	 */
	public function setRoute($route) {
		$this->route = $route;
		return $this;
	}
	
	/**
	 * Render the given $layout in $response using $values
	 *
	 * @param mixed $response
	 * @param string $layout
	 * @param array $values
	 * @return mixed The $response
	 */
	public function render($response, $layout, $values = []) {
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
		$values['Controller'] = $this;
		$values['Request'] = $this->getRequest();
		$values['Route'] = $this->getRoute();
	}
	
	/**
	 * Get parameter values of this controller
	 * Use it to generate routes (as for menus) with path parameters & you can get the current context
	 *
	 * @param array $values
	 */
	public function getValues() {
		return [];
	}
	
	/**
	 * Get the request
	 *
	 * @return InputRequest
	 */
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * Get an option by $key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return string|mixed
	 */
	public function getOption($key, $default = null) {
		return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
	}
	
	/**
	 * Set an option by $key
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Controller
	 */
	public function setOption($key, $value) {
		$this->options[$key] = $value;
		return $this;
	}
	
	/**
	 * Set an option by $key
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Controller
	 */
	public function setOptions($options) {
		$this->options[$key] = $value;
		return $this;
	}
	
}
