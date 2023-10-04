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
	 */
	public function __construct(int|string $code = 0, ?string $body = null) {
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
	public function process(): void {
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
	 */
	public function getBody(): ?string {
		return $this->body;
	}
	
	/**
	 * Set the body
	 */
	public function setBody(?string $body): CliResponse {
		$this->body = $body;
		
		return $this;
	}
	
	/**
	 * Collect response data from parameters
	 */
	public function collectFrom(string $layout, array $values = []): void {
	}
	
	/**
	 * Get the code
	 */
	public function getCode(): int {
		return $this->code;
	}
	
	/**
	 * Set the code
	 */
	public function setCode(int $code): CliResponse {
		$this->code = $code;
		
		return $this;
	}
	
	/**
	 * Generate CliResponse from UserException
	 *
	 * @return static
	 */
	public static function generateFromUserException(UserException $exception, array $values = []): CliResponse {
		return static::generateFromException($exception);
	}
	
	/**
	 * Generate CliResponse from Exception
	 *
	 * @param Exception $exception
	 * @return static
	 */
	public static function generateFromException(Throwable $exception, array $values = []): CliResponse {
		return new static(1, convertExceptionAsText($exception, 0));
	}
	
}
