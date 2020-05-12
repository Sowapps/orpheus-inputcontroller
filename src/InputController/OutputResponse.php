<?php
/**
 * OutputResponse
 */

namespace Orpheus\InputController;

use Exception;
use Orpheus\Exception\UserException;
use Throwable;

/**
 * The OutputResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
abstract class OutputResponse {
	
	/**
	 * The output of the controller when running it
	 *
	 * @var string
	 */
	protected $controllerOutput;
	
	/**
	 * Get the controller output
	 *
	 * @return string
	 */
	public function getControllerOutput() {
		return $this->controllerOutput;
	}
	
	/**
	 * Set the controller output
	 *
	 * @param string $controllerOutput
	 */
	public function setControllerOutput($controllerOutput) {
		$this->controllerOutput = $controllerOutput;
	}
	
	/**
	 * Get this response as string
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
	 * @return static
	 */
	public static function generateFromException(Throwable $exception, $action = null) {
		return new static();
	}
	
	/**
	 * Generate OutputResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return static
	 */
	public static function generateFromUserException(UserException $exception) {
		return new static();
	}
	
}
