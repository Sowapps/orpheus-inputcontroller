<?php
/**
 * ResourceController
 */

namespace Orpheus\Controller;

use Orpheus\Exception\NotFoundException;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\LocalFileHTTPResponse;

/**
 * The RedirectController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class ResourceController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return LocalFileHTTPResponse The output HTTP response
	 * @throws NotFoundException
	 */
	public function run($request) {
		
		$options = $request->getRoute()->getOptions();
		if( empty($options['package']) ) {
			throw new NotFoundException('invalidRoutePackage');
		}
		
		return new LocalFileHTTPResponse($this->resolveResource($request->getPathValue('resource'), $options['package']));
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
