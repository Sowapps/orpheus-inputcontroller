<?php
namespace Orpheus\InputController;

use Orpheus\Exception\UserException;

abstract class Controller {

	/* @var $request InputRequest */
	protected $request;
	
	/**
	 * The route calling this controller
	 * A controller could be called without any route and any request
	 * This variable comes to get the route without any request
	 * 
	 * @var \Orpheus\InputController\ControllerRoute
	 */
	protected $route;
	
	/**
	 * Running options for this controller
	 * 
	 * @var array
	 */
	protected $options = array();
	
	public function __toString() {
		return get_called_class();
	}

	/**
	 * Process the $request
	 *
	 * @param InputRequest $request
	 * @return OutputResponse
	 * @uses ControllerRoute::run()
	 * @see Controller::preRun()
	 * @see Controller::run()
	 * @see Controller::postRun()
	 */
	public function process(InputRequest $request) {
		// run, preRun and postRun take parameter depending on Controller, request may be of a child class of InputRequest
		$this->request	= $request;
		
		ob_start();
		$result	= null;
		$values = array();
		$this->fillValues($values);
		try {
			// Could prevent Run & PostRun
			// We recommend that PreRun only return Redirections and Exceptions
			$result	= $this->preRun($request);
		} catch( UserException $e ) {
			$result	= $this->processUserException($e, $values);
		}
		if( !$result ) {
			// PreRun could prevent Run & PostRun
			try {
				$result	= $this->run($request);
			} catch( UserException $e ) {
				$result	= $this->processUserException($e, $values);
			}
			$this->postRun($request, $result);
		}
		$result->setControllerOutput(ob_get_clean());
		
		return $result;
	}
	
	public function processUserException(UserException $e) {
		throw $e;// Throw to request
	}
	
	/**
	 * 
	 * @return \Orpheus\InputController\InputRequest
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Set the route
	 *
	 * @param \Orpheus\InputController\ControllerRoute
	 */
	public function setRoute($route) {
		$this->route = $route;
		return $this;
	}
	
	/**
	 * Get the route
	 * 
	 * @return \Orpheus\InputController\ControllerRoute
	 */
	public function getRoute() {
		return $this->route ? $this->route : ($this->request ? $this->request->getRoute() : null);
// 		return $this->request->getRoute();
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
	 * Fill array with default values
	 * 
	 * @param array $values
	 */
	public function fillValues(&$values=array()) {
		$values['Controller']	= $this;
		$values['Request']		= $this->getRequest();
		$values['Route']		= $this->getRoute();
	}
	
	/**
	 * Render the given $layout in $response using $values
	 * 
	 * @param mixed $response
	 * @param string $layout
	 * @param array $values
	 * @return mixed The $response
	 */
	public function render($response, $layout, $values=array()) {
		$this->fillValues($values);
		$response->collectFrom($layout, $values);
		return $response;
	}
	
	/**
	 * Get an option by $key
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return string|mixed
	 */
	public function getOption($key, $default=null) {
		return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
	}
	
	/**
	 * Set an option by $key
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return \Orpheus\InputController\Controller
	 */
	public function setOption($key, $value) {
		$this->options[$key] = $value;
		return $this;
	}
	
}
