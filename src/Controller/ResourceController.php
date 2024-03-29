<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Controller;

use Orpheus\Exception\NotFoundException;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\LocalFileHttpResponse;

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
	 * @return string The absolute path to resource
	 */
	public function resolveResource(string $webPath, string $package): string {
		return VENDOR_PATH . $package . '/res/' . $webPath;
	}
	
}
