<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Controller;

use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;

class EmptyDefaultHttpController extends HttpController {
	
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
