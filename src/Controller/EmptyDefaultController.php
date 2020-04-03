<?php

namespace Orpheus\Controller;

use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;

/**
 * Class EmptyDefaultController
 *
 * @package Orpheus\Controller
 */
class EmptyDefaultController extends HTTPController {
	
	/**
	 * Run the controller
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTMLHTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run($request) {
		return new HTMLHTTPResponse('An error occurred');
	}
	
}

