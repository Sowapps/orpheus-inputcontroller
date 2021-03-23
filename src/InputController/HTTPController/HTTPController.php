<?php
/**
 * HTTPController
 */

namespace Orpheus\InputController\HTTPController;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\Controller;
use Throwable;

/**
 * The HTTPController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
abstract class HTTPController extends Controller {
	
	protected $catchControllerOuput = true;
	
	/**
	 * Render the given $layout with $values
	 *
	 * @param string $layout
	 * @param array $values
	 * @return HTMLHTTPResponse
	 */
	public function renderHTML($layout, $values = []) {
		return $this->render(new HTMLHTTPResponse(), $layout, $values);
	}
	
	/**
	 * @param UserException $exception
	 * @param array $values
	 * @return HTMLHTTPResponse
	 */
	public function processUserException(UserException $exception, $values = []) {
		$this->fillValues($values);
		
		return HTMLHTTPResponse::generateFromUserException($exception, $values);
	}
	
	/**
	 * @param Exception $exception
	 * @param array $values
	 * @return HTTPResponse
	 */
	public function processException(Throwable $exception, $values = []) {
		log_error($exception, 'Processing response', false);
		$this->fillValues($values);
		
		return HTMLHTTPResponse::generateFromException($exception, $values);
	}
	
	/**
	 * Get the HTTP request
	 *
	 * @return HTTPRequest
	 */
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * Prepare environment for this request
	 *
	 * @param HTTPRequest $request
	 * @throws UserException
	 */
	public function prepare($request) {
		parent::prepare($request);
		$routeOptions = $this->getRoute()->getOptions();
		if( !isset($routeOptions['session']) || $routeOptions['session'] ) {
			startSession();
		}
	}
}
