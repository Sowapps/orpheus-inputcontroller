<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController;

use Orpheus\Exception\UserException;
use Throwable;

abstract class OutputResponse {
	
	/**
	 * The output of the controller when running it
	 *
	 * @var string|null
	 */
	protected ?string $controllerOutput = null;
	
	/**
	 * Get the controller output
	 *
	 * @return string|null
	 */
	public function getControllerOutput(): ?string {
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
	
	public function collectFrom(string $layout, array $values = []) {
		// Do nothing
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
	 * @param Throwable $exception
	 * @param array $values
	 * @return static
	 */
	public static function generateFromException(Throwable $exception, array $values = []): self {
		return new static();
	}
	
	/**
	 * Generate OutputResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return static
	 */
	public static function generateFromUserException(UserException $exception, array $values = []): self {
		return new static();
	}
	
}
