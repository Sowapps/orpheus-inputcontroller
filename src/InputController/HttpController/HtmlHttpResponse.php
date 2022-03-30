<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Exception;
use Orpheus\Config\Config;
use Orpheus\Exception\ForbiddenException;
use Orpheus\Exception\UserException;
use Orpheus\Rendering\HtmlRendering;
use Throwable;

class HtmlHttpResponse extends HttpResponse {
	
	/**
	 * The layout to use ot generate HTML
	 *
	 * @var string|null
	 */
	protected ?string $layout = null;
	
	/**
	 * The values to send to the layout
	 *
	 * @var array|null
	 */
	protected ?array $values = null;
	
	/**
	 * Constructor
	 *
	 * @param string $body
	 */
	public function __construct($body = null, $contentType = 'text/html; charset="UTF-8"') {
		parent::__construct($body, $contentType);
	}
	
	/**
	 * @return bool|void
	 */
	public function run(): bool {
		if( parent::run() ) {
			return;
		}
		$rendering = HtmlRendering::getCurrent();
		
		$env = $this->values;
		$env['CONTROLLER_OUTPUT'] = $this->getControllerOutput();
		
		$rendering->display($this->layout, $env);
		
		// In case of error, display was aborted and any output is lost
		
		return true;
	}
	
	/**
	 * Generate HtmlResponse from Exception
	 *
	 * @param Exception $exception
	 * @param array $values
	 * @return HtmlHttpResponse
	 */
	public static function generateFromException(Throwable $exception, array $values = []): HttpResponse {
		if( Config::get('forbidden_to_home', true) && $exception instanceof ForbiddenException ) {
			return new RedirectHttpResponse(u(DEFAULT_ROUTE));
		}
		$code = $exception->getCode();
		if( $code < 100 ) {
			$code = HTTP_INTERNAL_SERVER_ERROR;
		}
		
		return static::generateExceptionHtmlResponse($exception, $code, $values);
	}
	
	/**
	 * Generate HtmlResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return static
	 */
	public static function generateFromUserException(UserException $exception, array $values = []): HttpResponse {
		reportError($exception);
		$code = $exception->getCode();
		if( !$code ) {
			$code = HTTP_BAD_REQUEST;
		}
		
		return static::generateExceptionHtmlResponse($exception, $code, $values, 'user');
	}
	
	/**
	 * @param Exception $exception
	 * @param int $code
	 * @param array $values
	 * @param string|null $type
	 * @return static
	 */
	public static function generateExceptionHtmlResponse(Throwable $exception, $code, array $values = [], $type = null): HttpResponse {
		if( DEV_VERSION ) {
			$response = new static(convertExceptionAsHTMLPage($exception, $code));
			$response->setCode($code);
			
			return $response;
		}
		$rendering = HtmlRendering::getCurrent();
		
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
			// Fatal error, no way to display user-friendly error, and we don't want to display debug error on prod.
			$typeText = $type ?: 'classic';
			$layoutList = implode(', ', $layouts);
			
			return new static(<<<EOF
A fatal error occurred.<br />
Message: {$exception->getMessage()}<br />
Type: {$typeText}<br />
<br />
No error template was found to display a friendly error.<br />
We looked for templates: {$layoutList}<br />
EOF
			);
		}
		
		return static::render($layout, array_merge($values, [
			'exception' => $exception,
			'code'      => $code,
			'type'      => $type,
		]));
	}
	
	/**
	 * Render the $layout with these $values
	 *
	 * @param string $layout
	 * @param array $values
	 * @return HtmlHttpResponse
	 * @see HtmlHttpResponse::run()
	 */
	public static function render($layout, $values = []): HtmlHttpResponse {
		$response = new static();
		$response->collectFrom($layout, $values);
		
		return $response;
	}
	
	/**
	 * @param string $layout
	 * @param array $values
	 * @return void
	 */
	public function collectFrom(string $layout, array $values = []) {
		$this->layout = $layout;
		$this->values = $values;
	}
	
}
