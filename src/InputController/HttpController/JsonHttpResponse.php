<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\Exception\UserReportsException;
use stdClass;
use Throwable;

class JsonHttpResponse extends HttpResponse {
	
	/**
	 * The data of the JSON response
	 *
	 * @var array
	 */
	protected array $data;
	
	/**
	 * Constructor
	 *
	 * @param array|JsonSerializable|stdClass|null $data
	 */
	public function __construct($data = null, bool $download = false, ?string $fileName = null) {
		parent::__construct(null, 'application/json', $download, $fileName);
		$this->setData($data);
	}
	
	/**
	 * @return bool|false|string
	 * @throws Exception
	 */
	public function run(): bool {
		$json = json_encode($this->data);
		if( $json !== false ) {
			// Success
			echo $json;
			
			return true;
		}
		// Error
		switch( json_last_error() ) {
			case JSON_ERROR_NONE:
				throw new Exception('JSON Encoding error - No errors');
			case JSON_ERROR_DEPTH:
				throw new Exception('JSON Encoding error - Maximum stack depth exceeded');
			case JSON_ERROR_STATE_MISMATCH:
				throw new Exception('JSON Encoding error - Underflow or the modes mismatch');
			case JSON_ERROR_CTRL_CHAR:
				throw new Exception('JSON Encoding error - Unexpected control character found');
			case JSON_ERROR_SYNTAX:
				throw new Exception('JSON Encoding error - Syntax error, malformed JSON');
			case JSON_ERROR_UTF8:
				throw new Exception('JSON Encoding error - Malformed UTF-8 characters, possibly incorrectly encoded');
			default:
				throw new Exception('JSON Encoding error - Unknown error');
		}
	}
	
	/**
	 * Get the data
	 *
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * Set the data
	 *
	 * @param array|null $data
	 * @return JsonHttpResponse
	 */
	public function setData(?array $data): JsonHttpResponse {
		$this->data = $data;
		
		return $this;
	}
	
	/**
	 * Get a response with the given $data
	 *
	 * @param mixed $data
	 * @return JsonHttpResponse
	 * @see JsonHttpResponse::render()
	 */
	public static function returnData($data): JsonHttpResponse {
		// Return success with data
		$response = new static();
		$response->data = $data;
		
		return $response;
	}
	
	/**
	 * Render the given data
	 *
	 * @param string $textCode
	 * @param mixed $other
	 * @param string $domain
	 * @param string $description
	 * @return JsonHttpResponse
	 * @see JsonHttpResponse::returnData()
	 *
	 * We recommend to use returnData() to return data, that is more restful and to use this method only for errors
	 */
	public static function render($textCode, $other = null, $domain = 'global', $description = null): JsonHttpResponse {
		$response = new static();
		$response->collect($textCode, $other, $domain, $description);
		
		return $response;
	}
	
	/**
	 * @param string $textCode
	 * @param $other
	 * @param string $domain
	 * @param string|null $description
	 */
	public function collect(string $textCode, $other = null, string $domain = 'global', ?string $description = null) {
		// For errors only
		$this->data = [
			'code'        => $textCode,
			'description' => t($description ?: $textCode, $domain),
			'other'       => $other,
		];
	}
	
	/**
	 * Generate HtmlResponse from Exception
	 *
	 * @param Exception $exception
	 * @param array $values
	 * @return JsonHttpResponse
	 */
	public static function generateFromException(Throwable $exception, array $values = []): HttpResponse {
		$code = $exception->getCode();
		if( $code < 100 ) {
			$code = HTTP_INTERNAL_SERVER_ERROR;
		}
		$other = new stdClass();
		$other->code = $exception->getCode();
		$other->message = $exception->getMessage();
		$other->file = $exception->getFile();
		$other->line = $exception->getLine();
		$other->trace = $exception->getTrace();
		$other->values = $values;
		$response = static::render('exception', $other, 'global', t('fatalErrorOccurred', 'global'));
		$response->setCode($code);
		
		return $response;
	}
	
	/**
	 * Generate HtmlResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return JsonHttpResponse
	 */
	public static function generateFromUserException(UserException $exception, $values = []): HttpResponse {
		$code = $exception->getCode();
		if( !$code ) {
			$code = HTTP_BAD_REQUEST;
		}
		if( $exception instanceof UserReportsException ) {
			/* @var $exception UserReportsException */
			$response = static::render($exception->getMessage(), $exception->getReports(), $exception->getDomain());
		} else {
			$response = static::render($exception->getMessage(), null, $exception->getDomain());
		}
		$response->setCode($code);
		
		return $response;
	}
	
}
