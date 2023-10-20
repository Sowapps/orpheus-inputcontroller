<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\AbstractController;
use Orpheus\Service\SecurityService;
use Throwable;

/**
 * @method HttpResponse run($request)
 * @method HttpResponse|null preRun($request)
 */
abstract class HttpController extends AbstractController {
	const OPTION_PAGE_TITLE = 'pageTitle';
	const OPTION_PAGE_DESCRIPTION = 'pageDescription';
	
	protected bool $catchControllerOutput = true;
	
	/**
	 * Render the given $layout with $values
	 */
	public function renderHtml(string $layout, array $values = []): HtmlHttpResponse {
		return $this->render(new HtmlHttpResponse(), $layout, $values);
	}
	
	/**
	 * @return HtmlHttpResponse
	 */
	public function processUserException(UserException $exception, array $values = []): HttpResponse {
		$this->fillValues($values);
		
		return HtmlHttpResponse::generateFromUserException($exception, $values);
	}
	
	/**
	 * @param Exception $exception
	 */
	public function processException(Throwable $exception, array $values = []): HttpResponse {
		log_report($exception, $exception instanceof UserException ? $exception->getChannel() : LOGFILE_SYSTEM, 'Processing response');
		$this->fillValues($values);
		
		return HtmlHttpResponse::generateFromException($exception, $values);
	}
	
	/**
	 * Prepare environment for this request
	 *
	 * @param HttpRequest $request
	 * @throws UserException
	 */
	public function prepare($request): ?HttpResponse {
		parent::prepare($request);
		// May be empty if request failed to generate
		$routeOptions = $this->getRoute()?->getOptions() ?? [];
		if( $routeOptions['session'] ?? true ) {
			startSession();
		}
		$security = SecurityService::get();
		$security->loadUserAuthentication($request);
		
		return null;
	}
	
	protected function redirectToSelf(): RedirectHttpResponse {
		return new RedirectHttpResponse($this->getCurrentUrl());
	}
	
	public function getCurrentUrl(): string {
		return $this->getRequest()->getUrl();// With QueryString
	}
	
}
