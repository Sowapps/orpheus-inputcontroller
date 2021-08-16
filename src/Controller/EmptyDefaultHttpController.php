<?php

namespace Orpheus\Controller;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\Rendering\HTMLRendering;
use Throwable;

/**
 * Class EmptyDefaultController
 *
 * @package Orpheus\Controller
 */
class EmptyDefaultHttpController extends HttpController {
	
	/**
	 * Run the controller
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @return HtmlHttpResponse The output HTTP response
	 * @see HttpController::run()
	 */
	public function run($request): HtmlHttpResponse {
		return new HtmlHttpResponse('An error occurred');
	}
	
	/**
	 * @param UserException $exception
	 * @param array $values
	 * @return HtmlHttpResponse
	 */
	public function processUserException(UserException $exception, $values = []): HtmlHttpResponse {
		if( !DEV_VERSION ) {
			$code = $exception->getCode();
			if( !$code ) {
				$code = HTTP_BAD_REQUEST;
			}
			
			return $this->renderGentlyException($exception, $code, $values, 'user');
		}
		
		return parent::processUserException($exception, $values);
	}
	
	/**
	 * @param Exception $exception
	 * @param array $values
	 * @return HtmlHttpResponse
	 */
	public function processException(Throwable $exception, $values = []): HtmlHttpResponse {
		if( !DEV_VERSION ) {
			$code = $exception->getCode();
			if( $code < 100 ) {
				$code = HTTP_INTERNAL_SERVER_ERROR;
			}
			log_error($exception, 'Processing response', false);
			
			return $this->renderGentlyException($exception, $code, $values, null);
		}
		
		return parent::processException($exception, $values);
	}
	
	public function renderGentlyException(Throwable $exception, $code, $values, $type): HtmlHttpResponse {
		$rendering = HTMLRendering::getCurrent();
		
		// Test layouts' availability to get the more specific one
		$layoutValues = [
			'{type}' => $type,
			'{code}' => $code,
		];
		
		// Type's layouts
		$layouts = $type ? ['error/error-{type}-{code}', 'error/error-{type}'] : [];
		// Global ones
		$layouts = array_merge($layouts, ['error/error-{code}', 'error/error']);
		$layout = null;
		foreach( $layouts as &$testLayout ) {
			$testLayout = strtr($testLayout, $layoutValues);
			if( $rendering->existsLayoutPath($testLayout) ) {
				$layout = $testLayout;
				break;
			}
		}
		if( !$layout ) {
			// Fatal error, no way to display user friendly error and we don't want to display debug error on prod.
			$typeText = $type ?: 'exception';
			$layoutList = implode(', ', $layouts);
			$response = new HtmlHttpResponse(<<<EOF
A fatal error occurred.<br />
Message: {$exception->getMessage()}<br />
Type: {$typeText}<br />
<br />
No error template was found to display a friendly error.<br />
We looked for templates: {$layoutList}<br />
EOF
			);
			$response->setCode($code);
			
			return $response;
		}
		
		$values['titleRoute'] = $layout;
		$values['content'] = '';
		$values['exception'] = $exception;
		$values['code'] = $code;
		$values['type'] = $type;
		
		$response = $this->renderHtml($layout, $values);
		$response->setCode($code);
		
		return $response;
	}
	
}

