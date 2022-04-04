<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\Controller;
use Throwable;

/**
 * @method HttpResponse run($request)
 * @method HttpResponse|null preRun($request)
 */
abstract class HttpController extends Controller {
	
	protected bool $catchControllerOutput = true;
	
	/**
	 * Render the given $layout with $values
	 *
	 * @param string $layout
	 * @param array $values
	 * @return HtmlHttpResponse
	 */
	public function renderHtml(string $layout, $values = []): HtmlHttpResponse {
		return $this->render(new HtmlHttpResponse(), $layout, $values);
	}
	
	/**
	 * @param UserException $exception
	 * @param array $values
	 * @return HtmlHttpResponse
	 */
	public function processUserException(UserException $exception, $values = []): HttpResponse {
		$this->fillValues($values);
		
		return HtmlHttpResponse::generateFromUserException($exception, $values);
	}
	
	/**
	 * @param Exception $exception
	 * @param array $values
	 * @return HttpResponse
	 */
	public function processException(Throwable $exception, $values = []): HttpResponse {
		log_error($exception, 'Processing response', false);
		$this->fillValues($values);
		
		return HtmlHttpResponse::generateFromException($exception, $values);
	}
	
	/**
	 * Get the HTTP request
	 *
	 * @return HttpRequest
	 */
	public function getRequest(): HttpRequest {
		return $this->request;
	}
	
	/**
	 * Prepare environment for this request
	 *
	 * @param HttpRequest $request
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
