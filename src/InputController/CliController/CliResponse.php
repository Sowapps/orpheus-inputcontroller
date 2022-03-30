<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\CliController;


use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\OutputResponse;
use Throwable;

class CliResponse extends OutputResponse {
	
	/**
	 * The returned response code
	 *
	 * @var int
	 */
	protected int $code;
	
	/**
	 * The HTML body of the response
	 *
	 * @var string|null
	 */
	protected ?string $body = null;
	
	/**
	 * Constructor
	 *
	 * @param int|string $code
	 * @param string|null $body
	 */
	public function __construct($code = 0, ?string $body = null) {
		if( is_string($code) ) {
			$body = $code;
			$code = 0;
		}
		$this->setCode($code);
		$this->setBody($body ? $body . "\n" : null);
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
	
	public function isSuccess(): bool {
		return !$this->getCode();
	}
	
	/**
	 * Get the body
	 *
	 * @return string|null
	 */
	public function getBody(): ?string {
		return $this->body;
	}
	
	/**
	 * Set the body
	 *
	 * @param string|null $body
	 * @return CliResponse
	 */
	public function setBody(?string $body): CliResponse {
		$this->body = $body;
		
		return $this;
	}
	
	/**
	 * Collect response data from parameters
	 *
	 * @param string $layout
	 * @param array $values
	 */
	public function collectFrom(string $layout, array $values = []) {
	}
	
	/**
	 * Get the code
	 *
	 * @return int
	 */
	public function getCode(): int {
		return $this->code;
	}
	
	/**
	 * Set the code
	 *
	 * @param int
	 * @return CliResponse
	 */
	public function setCode(int $code): CliResponse {
		$this->code = $code;
		
		return $this;
	}
	
	/**
	 * Generate CliResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return static
	 */
	public static function generateFromUserException(UserException $exception, array $values = []): CliResponse {
		return static::generateFromException($exception);
	}
	
	/**
	 * Generate CliResponse from Exception
	 *
	 * @param Exception $exception
	 * @param array $values
	 * @return static
	 */
	public static function generateFromException(Throwable $exception, array $values = []): CliResponse {
		return new static(1, convertExceptionAsText($exception, 0));
	}
	
}
