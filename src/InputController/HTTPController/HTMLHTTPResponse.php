<?php
/**
 * HTMLHTTPResponse
 */

namespace Orpheus\InputController\HTTPController;

use Exception;
use Orpheus\Config\Config;
use Orpheus\Exception\ForbiddenException;
use Orpheus\Exception\UserException;
use Orpheus\Rendering\HTMLRendering;

/**
 * The HTMLHTTPResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class HTMLHTTPResponse extends HTTPResponse {
	
	/**
	 * The layout to use ot generate HTML
	 *
	 * @var string
	 */
	protected $layout;
	
	/**
	 * The values to send to the layout
	 *
	 * @var array
	 */
	protected $values;
	
	/**
	 * Constructor
	 *
	 * @param string $body
	 */
	public function __construct($body = null, $contentType = 'text/html; charset="UTF-8"') {
		parent::__construct($body, $contentType);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see HTTPResponse::run()
	 */
	public function run() {
		if( parent::run() ) {
			return;
		}
		$rendering = HTMLRendering::getCurrent();
		
		$env = $this->values;
		$env['CONTROLLER_OUTPUT'] = $this->getControllerOutput();
		
		$rendering->display($this->layout, $env);
	}
	
	/**
	 * Generate HTMLResponse from Exception
	 *
	 * @param Exception $exception
	 * @param string $action
	 * @return HTMLHTTPResponse
	 */
	public static function generateFromException(Exception $exception, $action = 'Handling the request') {
		if( Config::get('forbidden_to_home', true) && $exception instanceof ForbiddenException ) {
			return new RedirectHTTPResponse(u(DEFAULT_ROUTE));
		}
		$code = $exception->getCode();
		if( $code < 100 ) {
			$code = HTTP_INTERNAL_SERVER_ERROR;
		}
		$response = new static(convertExceptionAsHTMLPage($exception, $code, $action));
		$response->setCode($code);
		return $response;
	}
	
	/**
	 * Generate HTMLResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return HTMLHTTPResponse
	 */
	public static function generateFromUserException(UserException $exception, $values = []) {
		$code = $exception->getCode();
		if( !$code ) {
			$code = HTTP_BAD_REQUEST;
		}
		reportError($exception);
		$defaultLayout = 'user_error';
		$layout = $defaultLayout . '_' . $code;
		if( !HTMLRendering::getCurrent()->existsLayoutPath($layout) ) {
			$layout = $defaultLayout;
		}
		$values['titleRoute'] = $layout;
		$values['Content'] = '';
		$values['exception'] = $exception;
		$response = static::render($layout, $values);
		$response->setCode($code);
		return $response;
	}
	
	/**
	 * Render the $layout with these $values
	 *
	 * @param string $layout
	 * @param array $values
	 * @return HTMLHTTPResponse
	 * @see HTMLHTTPResponse::run()
	 */
	public static function render($layout, $values = []) {
		$response = new static();
		$response->collectFrom($layout, $values);
		return $response;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @param string $layout
	 * @param array $values
	 * @return NULL
	 * @see HTTPResponse::collectFrom()
	 */
	public function collectFrom($layout, $values = []) {
		$this->layout = $layout;
		$this->values = $values;
		return null;
	}
	
}
