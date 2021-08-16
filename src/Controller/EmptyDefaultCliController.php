<?php

namespace Orpheus\Controller;

use Orpheus\InputController\CLIController\CliController;
use Orpheus\InputController\CLIController\CliResponse;
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

