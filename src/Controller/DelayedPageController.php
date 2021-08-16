<?php
/**
 * DelayedPageController
 */

namespace Orpheus\Controller;

use Exception;
use Orpheus\Cache\APCache;
use Orpheus\Exception\ForbiddenException;
use Orpheus\Exception\NotFoundException;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;

/**
 * The DelayedPageController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
class DelayedPageController extends HttpController {
	
	/**
	 * Run the controller
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @return HtmlHttpResponse The output HTTP response
	 * @see HttpController::run()
	 */
	public function run($request): HttpResponse {
		if( !DEV_VERSION ) {
			throw new ForbiddenException("You're not allowed to access to this content.");
		}
		$pathValues = $request->getPathValues();
		$cache = new APCache('delayedpage', $pathValues->page);
		$content = null;
		if( !$cache->get($content) ) {
			$cache->clear();
			throw new NotFoundException('The delayed page "' . $pathValues->page . '" was not found');
		}
		
		return new HtmlHttpResponse($content);
	}
	
	/**
	 * Storen the $content associated to the $page
	 * 
	 * @param string $page
	 * @param string $content
	 * @throws Exception
	 * @return string
	 */
	public static function store($page, $content) {
		// Do it and in some case, routes will not be loaded
		// Case this is not loaded will lead to infinite loop
		if( !ControllerRoute::isInitialized() ) {
			throw new Exception('Routes not initialized, application is not able to show content, it will fail again & again...');
		}
		
		$cache	= new APCache('delayedpage', $page, 60);
		$cache->set($content);
		return u('delayedpage', array('page'=>$page));
	}
}

