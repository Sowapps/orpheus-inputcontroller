<?php
/**
 * HTTPController
 */

namespace Orpheus\InputController\HTTPController;

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
	 *
	 * {@inheritDoc}
	 * @param UserException $exception
	 * @param array $values
	 * @see Controller::processUserException()
	 */
	public function processUserException(UserException $exception, $values = []) {
		$this->fillValues($values);
		return $this->getRoute()->processUserException($exception, $values);
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
