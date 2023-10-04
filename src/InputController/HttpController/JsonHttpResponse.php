<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Exception;
use JsonSerializable;
use Orpheus\Exception\UserException;
use Orpheus\Exception\UserReportsException;
use stdClass;
use Throwable;

class JsonHttpResponse extends HttpResponse {
	
	/**
	 * The data of the JSON response
	 *
	 * @var array|JsonSerializable|stdClass|null
	 */
	protected array|JsonSerializable|stdClass|null $data;
	
	/**
	 * Constructor
	 */
	public function __construct(array|JsonSerializable|stdClass|null $data = null, bool $download = false, ?string $fileName = null) {
		parent::__construct(null, 'application/json', $download, $fileName);
		$this->setData($data);
	}
	
	/**
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
		throw match (json_last_error()) {
			JSON_ERROR_NONE => new Exception('JSON Encoding error - No errors'),
			JSON_ERROR_DEPTH => new Exception('JSON Encoding error - Maximum stack depth exceeded'),
			JSON_ERROR_STATE_MISMATCH => new Exception('JSON Encoding error - Underflow or the modes mismatch'),
			JSON_ERROR_CTRL_CHAR => new Exception('JSON Encoding error - Unexpected control character found'),
			JSON_ERROR_SYNTAX => new Exception('JSON Encoding error - Syntax error, malformed JSON'),
			JSON_ERROR_UTF8 => new Exception('JSON Encoding error - Malformed UTF-8 characters, possibly incorrectly encoded'),
			default => new Exception('JSON Encoding error - Unknown error'),
		};
	}
	
	/**
	 * Get the data
	 */
	public function getData(): array|stdClass|JsonSerializable|null {
		return $this->data;
	}
	
	/**
	 * Set the data
	 */
	public function setData(array|JsonSerializable|stdClass|null $data): JsonHttpResponse {
		$this->data = $data;
		
		return $this;
	}
	
	/**
	 * Get a response with the given $data
	 *
	 * @see JsonHttpResponse::render()
	 */
	public static function returnData(mixed $data): JsonHttpResponse {
		// Return success with data
		$response = new static();
		$response->data = $data;
		
		return $response;
	}
	
	/**
	 * Render the given data
	 *
	 * @param mixed|null $other
	 * @see JsonHttpResponse::returnData()
	 *
	 * We recommend to use returnData() to return data, that is more restful and to use this method only for errors
	 */
	public static function render(string $textCode, mixed $other = null, string $domain = 'global', ?string $description = null): JsonHttpResponse {
		$response = new static();
		$response->collect($textCode, $other, $domain, $description);
		
		return $response;
	}
	
	public function collect(string $textCode, $other = null, string $domain = 'global', ?string $description = null): void {
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
		$response = static::render('exception', $other, 'global', t('fatalErrorOccurred'));
		$response->setCode($code);
		
		return $response;
	}
	
	/**
	 * Generate HtmlResponse from UserException
	 *
	 * @return JsonHttpResponse
	 */
	public static function generateFromUserException(UserException $exception, array $values = []): HttpResponse {
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
