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
use Throwable;

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
	public static function generateFromException(Throwable $exception, $action = null) {
		if( Config::get('forbidden_to_home', true) && $exception instanceof ForbiddenException ) {
			return new RedirectHTTPResponse(u(DEFAULT_ROUTE));
		}
		$code = $exception->getCode();
		if( $code < 100 ) {
			$code = HTTP_INTERNAL_SERVER_ERROR;
		}
		return static::generateExceptionHtmlResponse($exception, $code, $action, null);
	}
	
	/**
	 * @param Exception $exception
	 * @param int $code
	 * @param string|null $action
	 * @param string|null $type
	 * @return static
	 * @throws Exception
	 */
	protected static function generateExceptionHtmlResponse(Throwable $exception, $code, $action, $type = null) {
		if( DEV_VERSION ) {
			$response = new static(convertExceptionAsHTMLPage($exception, $code, $action));
			$response->setCode($code);
			return $response;
		}
		$rendering = HTMLRendering::getCurrent();
		
		// Test layouts' availability to get the more specific one
		$values = [
			'{type}' => $type,
			'{code}' => $code,
		];
		// Type's layouts
		$layouts = $type ? ['error/error-{type}-{code}', 'error/error-{type}'] : [];
		// Global ones
		$layouts = array_merge($layouts, ['error/error-{code}', 'error/error']);
		$layout = null;
		foreach( $layouts as &$testLayout ) {
			$testLayout = strtr($testLayout, $values);
			if( $rendering->existsLayoutPath($testLayout) ) {
				$layout = $testLayout;
				break;
			}
		}
		if( !$layout ) {
			// Fatal error, no way to display user friendly error and we don't want to display debug error on prod.
			$actionText = $action ?: 'controller';
			$typeText = $type ?: 'classic';
			$layoutList = implode(', ', $layouts);
			return new static(<<<EOF
A fatal error occurred.<br />
Message: {$exception->getMessage()}<br />
Type: {$typeText}<br />
Action: {$actionText}<br />
<br />
No error template was found to display a friendly error.<br />
We looked for templates: {$layoutList}<br />
EOF
			);
		}
		return static::render($layout, [
			'exception' => $exception,
			'code'      => $code,
			'type'      => $type,
			'action'    => $action,
		]);
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
	
	/**
	 * Generate HTMLResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return static
	 */
	public static function generateFromUserException(UserException $exception) {
		reportError($exception);
		$code = $exception->getCode();
		if( !$code ) {
			$code = HTTP_BAD_REQUEST;
		}
		return static::generateExceptionHtmlResponse($exception, $code, null, 'user');
	}
	
}
