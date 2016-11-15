<?php
/**
 * CLIResponse
 */

namespace Orpheus\InputController\CLIController;


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
	public function __construct($code=0, $body=null) {
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
			echo $this->getBody();
		}
	}
	
	/**
	 * Collect response data from parameters
	 * 
	 * @param string $layout
	 * @param array $values
	 * @return NULL
	 */
	public function collectFrom($layout, $values=array()) {
		return null;
	}

	/**
	 * Generate HTMLResponse from Exception
	 *
	 * @param Exception $exception
	 * @param string $action
	 * @return \Orpheus\InputController\HTTPController\HTMLHTTPResponse
	 */
	public static function generateFromException(\Exception $exception, $action='Handling the request') {
		$response = new static(convertExceptionAsText($exception, 0, $action));
		return $response;
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
}
