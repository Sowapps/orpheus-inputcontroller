<?php
/**
 * StaticPageController
 */
namespace Orpheus\Controller;

use Exception;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;

/**
 * The StaticPageController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class StaticPageController extends HTTPController {
	
	/**
	 * Run the controller
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTMLHTTPResponse The output HTTP response
	 * @throws Exception
	 */
	public function run($request) {
		$options = $request->getRoute()->getOptions();
		if( empty($options['render']) ) {
			throw new Exception('The StaticPageController requires a render option, add it to your routes configuration.');
		}
		return $this->renderHTML($options['render']);
	}
	
}

