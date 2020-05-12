<?php

namespace Orpheus\Controller;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Rendering\HTMLRendering;
use Throwable;

/**
 * Class EmptyDefaultController
 *
 * @package Orpheus\Controller
 */
class EmptyDefaultHttpController extends HTTPController {
	
	/**
	 * Run the controller
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTMLHTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run($request) {
		return new HTMLHTTPResponse('An error occurred');
	}
	
	/**
	 * @param UserException $exception
	 * @param array $values
	 * @return HTMLHTTPResponse
	 */
	public function processUserException(UserException $exception, $values = []) {
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
	 * @return HTMLHTTPResponse
	 */
	public function processException(Throwable $exception, $values = []) {
		if( !DEV_VERSION ) {
			$code = $exception->getCode();
			if( $code < 100 ) {
				$code = HTTP_INTERNAL_SERVER_ERROR;
			}
			return $this->renderGentlyException($exception, $code, $values, null);
		}
		return parent::processException($exception, $values);
	}
	
	public function renderGentlyException(Throwable $exception, $code, $values, $type) {
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
			$response = new HTMLHTTPResponse(<<<EOF
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
		$values['Content'] = '';
		$values['exception'] = $exception;
		$values['code'] = $code;
		$values['type'] = $type;
		
		$response = $this->renderHTML($layout, $values);
		$response->setCode($code);
		return $response;
	}
	
}

