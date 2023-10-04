<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Controller;

use Exception;
use Orpheus\Cache\ApcCache;
use Orpheus\Exception\ForbiddenException;
use Orpheus\Exception\NotFoundException;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\Service\ApplicationKernel;

class DelayedPageController extends HttpController {
	
	/**
	 * Run the controller
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @see HttpController::run()
	 */
	public function run($request): HttpResponse {
		if( !ApplicationKernel::get()->isDebugEnabled() ) {
			throw new ForbiddenException("You're not allowed to access to this content.");
		}
		$pathValues = $request->getPathValues();
		$cache = new ApcCache('DelayedPage', $pathValues['page']);
		$content = null;
		if( !$cache->get($content) ) {
			$cache->clear();
			throw new NotFoundException(sprintf("The delayed page \"%s\" was not found", $pathValues['page']));
		}
		
		return new HtmlHttpResponse($content);
	}
	
	/**
	 * Store the $content associated to the $page
	 *
	 * @throws Exception
	 */
	public static function store(string $page, string $content): string {
		// Do it and in some case, routes will not be loaded
		// Case this is not loaded will lead to infinite loop
		if( !ControllerRoute::isInitialized() ) {
			throw new Exception('Routes not initialized, application is not able to show content, it will fail again & again...');
		}
		
		$cache = new ApcCache('DelayedPage', $page, 60);
		$cache->set($content);
		
		return u('delayed_page', ['page' => $page]);
	}
	
}

