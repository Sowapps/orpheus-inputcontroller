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
	
	protected $catchControllerOuput = true;
	
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
	 * @param UserException $exception
	 * @param array $values
	 */
	public function processUserException(UserException $exception, $values=array()) {
		return $this->getRoute()->processUserException($exception, $values);
	}
	
	/**
	 * Get the HTTP request
	 *
	 * @return \Orpheus\InputController\HTTPController\HTTPRequest
	 */
	public function getRequest() {
		return $this->request;
	}
}
