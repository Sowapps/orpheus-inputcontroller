<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Controller;

use Orpheus\InputController\CliController\CliController;
use Orpheus\InputController\CliController\CliResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;

class EmptyDefaultCliController extends CliController {
	
	/**
	 * Run the controller
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @see HttpController::run()
	 */
	public function run($request): CliResponse {
		return new CliResponse();
	}
	
}

