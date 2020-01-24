<?php
/**
 * CLIResponse
 */

namespace Orpheus\InputController\CLIController;


use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\OutputResponse;

/**
 * The CLIResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class CLIResponse extends OutputResponse {
	
	/**
	 * The returned response code
	 *
	 * @var int
	 */
	protected $code;
	
	/**
	 * The HTML body of the response
	 *
	 * @var string
	 */
	protected $body;
	
	/**
	 * Constructor
	 *
	 * @param string $body
	 */
	public function __construct($code = 0, $body = null) {
		if( is_string($code) ) {
			$body = $code;
			$code = 0;
		}
		$this->setCode($code);
		$this->setBody($body);
	}
	
	/**
	 * Process the response
	 */
	public function process() {
		if( isset($this->body) ) {
			if( $this->isSuccess() ) {
				echo $this->getBody();
			} else {
				fwrite(STDERR, $this->getBody() . PHP_EOL);
			}
		}
	}
	
	public function isSuccess() {
		return !$this->getCode();
	}
	
	/**
	 * Get the body
	 *
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}
	
	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return \Orpheus\InputController\CLIController\CLIResponse
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}
	
	/**
	 * Collect response data from parameters
	 *
	 * @param string $layout
	 * @param array $values
	 * @return NULL
	 */
	public function collectFrom($layout, $values = []) {
		return null;
	}
	
	/**
	 * Get the code
	 *
	 * @return int
	 */
	public function getCode() {
		return $this->code;
	}
	
	/**
	 * Set the code
	 *
	 * @param int
	 * @return \Orpheus\InputController\CLIController\CLIResponse
	 */
	public function setCode($code) {
		$this->code = (int) $code;
		return $this;
	}
	
	/**
	 * Generate CLIResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return static
	 */
	public static function generateFromUserException(UserException $exception, $values = []) {
		return static::generateFromException($exception);
	}
	
	/**
	 * Generate CLIResponse from Exception
	 *
	 * @param Exception $exception
	 * @param string $action
	 * @return static
	 */
	public static function generateFromException(\Exception $exception, $action = 'Handling the request') {
		return new static(1, convertExceptionAsText($exception, 0, $action));
	}
}
