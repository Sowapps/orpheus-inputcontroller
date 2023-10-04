<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\Exception;

use Exception;
use Orpheus\InputController\InputRequest;
use Orpheus\InputController\OutputResponse;

class ForceResponseException extends Exception {
	
	private OutputResponse $response;
	private InputRequest $request;
	
	/**
	 * ForceResponseException constructor
	 */
	public function __construct(string $message, OutputResponse $response, InputRequest $request) {
		parent::__construct($message);
		$this->response = $response;
		$this->request = $request;
	}
	
	public function getResponse(): OutputResponse {
		return $this->response;
	}
	
	public function getRequest(): InputRequest {
		return $this->request;
	}
	
}
