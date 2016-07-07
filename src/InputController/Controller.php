<?php
namespace Orpheus\InputController;

use Orpheus\Exception\UserException;

abstract class Controller {

	/* @var $request InputRequest */
	protected $request;
	
	protected $options = array();
	
	public function __toString() {
		return get_called_class();
	}

	/**
	 *
	 * @param InputRequest $request
	 * @return OutputResponse
	 * @uses ControllerRoute::run()
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
	
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * @return ControllerRoute
	 */
	public function getRoute() {
		return $this->request->getRoute();
	}
	
	public function getRouteName() {
		return $this->request->getRouteName();
	}
	
	public function fillValues(&$values=array()) {
		$values['Controller']	= $this;
		$values['Request']		= $this->getRequest();
		$values['Route']		= $this->getRoute();
	}
	
	public function render($response, $layout, $values=array()) {
		$this->fillValues($values);
		$response->collectFrom($layout, $values);
		return $response;
	}
	
	public function getOption($key, $default=null) {
		return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
	}
	
	public function setOption($key, $value) {
		$this->options[$key] = $value;
		return $this;
	}
	
	
	
	
}
