<?php

namespace Orpheus\Controller;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Rendering\HTMLRendering;

/**
 * Class EmptyDefaultController
 *
 * @package Orpheus\Controller
 */
class EmptyDefaultHttpController extends HTTPController {
	
	protected $defaultExceptionLayout = 'user_error';
	
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
			return $this->renderGentlyException($exception, $code, $values);
		}
		return parent::processUserException($exception, $values);
	}
	
	/**
	 * @param Exception $exception
	 * @param array $values
	 * @return HTMLHTTPResponse
	 */
	public function processException(Exception $exception, $values = []) {
		if( !DEV_VERSION ) {
			$code = $exception->getCode();
			if( $code < 100 ) {
				$code = HTTP_INTERNAL_SERVER_ERROR;
			}
			return $this->renderGentlyException($exception, $code, $values);
		}
		return parent::processException($exception, $values);
	}
	
	public function renderGentlyException(Exception $exception, $code, $values = []) {
		$layout = $this->defaultExceptionLayout . '_' . $code;
		if( !HTMLRendering::getCurrent()->existsLayoutPath($layout) ) {
			$layout = $this->defaultExceptionLayout;
		}
		$values['titleRoute'] = $layout;
		$values['Content'] = '';
		$values['exception'] = $exception;
		$response = $this->renderHTML($layout, $values);
		$response->setCode($code);
		return $response;
	}
	
}

