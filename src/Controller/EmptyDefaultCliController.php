<?php

namespace Orpheus\Controller;

use Orpheus\Exception\UserException;
use Orpheus\InputController\CLIController\CLIController;
use Orpheus\InputController\CLIController\CLIResponse;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;

/**
 * Class EmptyDefaultCliController
 *
 * @package Orpheus\Controller
 */
class EmptyDefaultCliController extends CLIController {
	
	/**
	 * Run the controller
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTMLHTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run($request) {
		return new CLIResponse();
	}
	
	/**
	 * @param UserException $exception
	 * @param array $values
	 * @see Controller::processUserException()
	 */
	public function processUserException(UserException $exception, $values = []) {
		return CLIResponse::generateFromUserException($exception);
	}
	
}

