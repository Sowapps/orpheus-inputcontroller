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
	public function __construct($body=null) {
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
}
