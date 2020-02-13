<?php
/**
 * HTTPResponse
 */

namespace Orpheus\InputController\HTTPController;


use Orpheus\InputController\OutputResponse;

/**
 * The HTTPResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class HTTPResponse extends OutputResponse {
	
	/**
	 * @var string The content type to send to client
	 */
	protected $contentType;
	
	/**
	 * The HTML body of the response
	 *
	 * @var string
	 */
	protected $body;
	
	/**
	 * The HTTP response code
	 *
	 * @var int
	 */
	protected $code;
	
	/**
	 * Constructor
	 *
	 * @param string $body
	 * @param string $contentType
	 */
	public function __construct($body = null, $contentType = null) {
		$this->setBody($body);
		$this->setContentType($contentType);
	}
	
	/**
	 * Process the response
	 */
	public function process() {
		if( $this->code ) {
			http_response_code($this->code);
		}
		if( $this->contentType && !headers_sent() ) {
			header('Content-Type: ' . $this->contentType);
		}
		$this->run();
	}
	
	/**
	 * Process response to client
	 *
	 * @return bool
	 */
	public function run() {
		if( $this->body !== null ) {
			// if already generated we display the body
			echo $this->getBody();
			return true;
		}
		return false;
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
	 * @return HTMLHTTPResponse
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
	 * @return HTTPResponse
	 */
	public function setCode($code) {
		$this->code = (int) $code;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}
	
	/**
	 * @param string $contentType
	 */
	public function setContentType(string $contentType) {
		$this->contentType = $contentType;
	}
	
}
