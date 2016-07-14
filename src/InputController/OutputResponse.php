<?php
namespace Orpheus\InputController;

use Orpheus\Exception\UserException;

abstract class OutputResponse {

	/**
	 * The output of the controller when running it
	 * 
	 * @var string
	 */
	protected $controllerOutput;
	
	/**
	 * Set the controller output
	 * 
	 * @param string $controllerOutput
	 */
	public function setControllerOutput($controllerOutput) {
// 		debug('Set controller output response with '.strlen($controllerOutput).' characters');
		$this->controllerOutput	= $controllerOutput;
	}
		
	/**
	 * Get the controller output
	 * 
	 * @return string
	 */
	public function getControllerOutput() {
		return $this->controllerOutput;
	}

	/**
	 * Return this response as string
	 * 
	 * @return string
	 */
	public function __toString() {
		return get_called_class();
	}

	/**
	 * Generate OutputResponse from Exception
	 *
	 * @param Exception $exception
	 * @param string $action
	 * @return \Orpheus\InputController\OutputResponse
	 */
	public static function generateFromException(\Exception $exception, $action=null) {
		return new static();
	}

	/**
	 * Generate OutputResponse from UserException
	 *
	 * @param Exception $exception
	 * @param string $action
	 * @return \Orpheus\InputController\OutputResponse
	 */
	public static function generateFromUserException(UserException $exception, $values=array()) {
		return new static();
	}
	
}
