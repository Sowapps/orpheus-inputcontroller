<?php
/**
 * HTTPController
 */

namespace Orpheus\InputController\HTTPController;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\Controller;

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
		return HTMLHTTPResponse::generateFromUserException($exception);
	}
	
	/**
	 * @param Exception $exception
	 * @param array $values
	 * @return HTTPResponse
	 */
	public function processException(Exception $exception, $values = []) {
		log_error($exception, 'Processing response', false);
		return HTMLHTTPResponse::generateFromException($exception);
	}
	
	/**
	 * Get the HTTP request
	 *
	 * @return HTTPRequest
	 */
	public function getRequest() {
		return $this->request;
	}
}
