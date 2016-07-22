<?php
/**
 * HTTPController
 */

namespace Orpheus\InputController\HTTPController;

use Orpheus\InputController\Controller;
use Orpheus\Exception\UserException;

/**
 * The HTTPController class
 * 
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
abstract class HTTPController extends Controller {
	
	/**
	 * 
	 * @param HTTPRequest $request
	 * @return HTTPResponse
	 */
	public abstract function run(HTTPRequest $request);

	/**
	 * Before running controller
	 * 
	 * @param HTTPRequest $request
	 */
	public function preRun(HTTPRequest $request) {
	}
	
	/**
	 * After running the controller
	 * 
	 * @param HTTPRequest $request
	 * @param HTTPResponse $response
	 */
	public function postRun(HTTPRequest $request, HTTPResponse $response) {
	}
	
	/**
	 * Render the given $layout with $values
	 * 
	 * @param string $layout
	 * @param array $values
	 * @return HTMLHTTPResponse
	 */
	public function renderHTML($layout, $values=array()) {
		return $this->render(new HTMLHTTPResponse(), $layout, $values);
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Orpheus\InputController\Controller::processUserException()
	 */
	public function processUserException(UserException $exception, $values=array()) {
		return $this->getRoute()->processUserException($exception, $values);
	}
}
