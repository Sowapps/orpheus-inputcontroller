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
	 */
	public function getControllerOutput(): ?string {
		return $this->controllerOutput;
	}
	
	/**
	 * Set the controller output
	 */
	public function setControllerOutput(string $controllerOutput): void {
		$this->controllerOutput = $controllerOutput;
	}
	
	public function collectFrom(string $layout, array $values = []): void {
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
	 * @return static
	 */
	public static function generateFromException(Throwable $exception, array $values = []): self {
		return new static();
	}
	
	/**
	 * Generate OutputResponse from UserException
	 *
	 * @return static
	 */
	public static function generateFromUserException(UserException $exception, array $values = []): self {
		return new static();
	}
	
}
