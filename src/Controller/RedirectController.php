<?php
/**
 * RedirectController
 */

namespace Orpheus\Controller;

use Exception;
use Orpheus\Config\AppConfig;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\RedirectHTTPResponse;

/**
 * The RedirectController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class RedirectController extends HTTPController {
	
	/**
	 * Run the controller
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return RedirectHTTPResponse The output HTTP response
	 * @throws Exception
	 */
	public function run($request) {
		$options = $request->getRoute()->getOptions();
		if( !empty($options['url_config']) ) {
			$url = AppConfig::instance()->get($options['url_config']);
			if( !$url ) {
				throw new Exception('The RedirectController requires a valid url_config option, please check your configuration.');
			}
		} elseif( empty($options['redirect']) ) {
			throw new Exception('The RedirectController requires a redirect option, add it to your route configuration.');
		} else {
			$url = u($options['redirect'], (array) $request->getPathValues());
		}
		return new RedirectHTTPResponse($url);
	}

}

