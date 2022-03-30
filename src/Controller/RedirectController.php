<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Controller;

use Exception;
use Orpheus\Config\AppConfig;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\RedirectHttpResponse;

class RedirectController extends HttpController {
	
	/**
	 * Run the controller
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @return RedirectHttpResponse The output HTTP response
	 * @throws Exception
	 */
	public function run($request): HttpResponse {
		$options = $request->getRoute()->getOptions();
		if( !empty($options['url_config']) ) {
			$url = AppConfig::instance()->get($options['url_config']);
			if( !$url ) {
				throw new Exception('The RedirectController requires a valid url_config option, please check your configuration.');
			}
		} elseif( empty($options['redirect']) ) {
			throw new Exception('The RedirectController requires a redirect option, add it to your route configuration.');
		} else {
			$url = u($options['redirect'], $request->getPathValues());
		}
		
		return new RedirectHttpResponse($url);
	}

}

