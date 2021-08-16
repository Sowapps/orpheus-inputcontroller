<?php
/**
 * ResourceController
 */

namespace Orpheus\Controller;

use Orpheus\Exception\NotFoundException;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\LocalFileHttpResponse;

/**
 * The RedirectController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
class ResourceController extends HttpController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return LocalFileHttpResponse The output HTTP response
	 * @throws NotFoundException
	 */
	public function run($request): HttpResponse {
		
		$options = $request->getRoute()->getOptions();
		if( empty($options['package']) ) {
			throw new NotFoundException('invalidRoutePackage');
		}
		
		return new LocalFileHttpResponse($this->resolveResource($request->getPathValue('resource'), $options['package']));
	}
	
	/**
	 *
	 * @param string $path
	 * @return string The absolute path to resource
	 */
	public function resolveResource($webPath, $package) {
		return VENDORPATH . $package . '/res/' . $webPath;
	}
	
	
}
