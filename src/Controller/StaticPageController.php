<?php
/**
 * StaticPageController
 */
namespace Orpheus\Controller;

use Exception;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

/**
 * The StaticPageController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
class StaticPageController extends HttpController {
	
	/**
	 * Run the controller
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @return HtmlHttpResponse The output HTTP response
	 * @throws Exception
	 */
	public function run($request): HttpResponse {
		$options = $request->getRoute()->getOptions();
		if( empty($options['render']) ) {
			throw new Exception('The StaticPageController requires a render option, add it to your routes configuration.');
		}
		
		return $this->renderHtml($options['render']);
	}
	
}

