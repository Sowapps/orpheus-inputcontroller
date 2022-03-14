<?php

namespace Orpheus\Controller;

use Orpheus\Exception\UserException;
use Orpheus\InputController\CliController\CliController;
use Orpheus\InputController\CliController\CliResponse;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;

/**
 * Class EmptyDefaultCliController
 *
 * @package Orpheus\Controller
 */
class EmptyDefaultCliController extends CliController {
	
	/**
	 * Prepare environment for this request
	 *
	 * @param HttpRequest $request
	 * @throws UserException
	 */
	public function prepare($request) {
		$this->request = $request;
	}
	
	/**
	 * Run the controller
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @return HtmlHttpResponse The output HTTP response
	 * @see HttpController::run()
	 */
	public function run($request): CliResponse {
		return new CliResponse();
	}
	
}

