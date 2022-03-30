<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Controller;

use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;

class EmptyDefaultHttpController extends HttpController {
	
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
	public function run($request): HtmlHttpResponse {
		return new HtmlHttpResponse('An error occurred');
	}
	
}
