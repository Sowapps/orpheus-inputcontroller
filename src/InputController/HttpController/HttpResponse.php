<?php

namespace Orpheus\InputController\HttpController;


use DateTime;
use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\OutputResponse;
use Throwable;

/**
 * The HttpResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
class HttpResponse extends OutputResponse {
	
	/**
	 * @var string The content type to send to client
	 */
	protected $contentType;
	
	/**
	 * @var int The content length to send to client
	 */
	protected $contentLength;
	
	/**
	 * The HTML body of the response
	 *
	 * @var string
	 */
	protected $body;
	
	/**
	 * The HTTP response code
	 *
	 * @var int
	 */
	protected $code;
	
	/**
	 * The file name shared to the client
	 *
	 * @var string
	 */
	protected $fileName;
	
	/**
	 * Is the file downloaded ? Or displayed ?
	 *
	 * @var bool
	 */
	protected $download;
	
	/**
	 * Client cache max age in seconds
	 *
	 * @var int
	 */
	protected $cacheMaxAge;
	
	/**
	 * Client cache max age in seconds
	 *
	 * @var DateTime
	 */
	protected $lastModifiedDate;
	
	/**
	 * Constructor
	 *
	 * @param string $body
	 * @param string $contentType
	 */
	public function __construct($body = null, $contentType = null, $download = false, $fileName = null) {
		if( $body ) {
			$this->setBody($body);
		}
		if( $contentType ) {
			$this->setContentType($contentType);
		}
		if( $fileName ) {
			$this->setFileName($fileName);
		}
		$this->setDownload($download);
	}
	
	/**
	 * Process the response
	 */
	public function process() {
		$this->sendHeaders();
		$this->run();
	}
	
	protected function sendHeaders() {
		if( headers_sent() ) {
			return;
		}
		if( $this->code ) {
			http_response_code($this->code);
		}
		if( $this->contentType ) {
			header('Content-Type: ' . $this->getContentType());
		}
		if( $this->contentLength ) {
			header('Content-Type: ' . $this->getContentLength());
		}
		if( $this->fileName || $this->download ) {
			header('Content-Disposition: ' . $this->getContentDisposition() . ($this->getFileName() ? '; filename="' . $this->getFileName() . '"' : ''));
		}
		if( $this->lastModifiedDate ) {
			header('Last-Modified: ' . $this->getLastModifiedDate()->format($this->getHttpDateFormat()));
		}
		if( $this->cacheMaxAge ) {
			header('Cache-Control: private, max-age=' . $this->getCacheMaxAge() . ', must-revalidate');
		}
		header('Pragma: public');
	}
	
	/**
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}
	
	/**
	 * @param string $contentType
	 */
	public function setContentType(string $contentType) {
		$this->contentType = $contentType;
	}
	
	/**
	 * @return int
	 */
	public function getContentLength(): int {
		return $this->contentLength;
	}
	
	/**
	 * @param int $contentLength
	 */
	public function setContentLength(int $contentLength): void {
		$this->contentLength = $contentLength;
	}
	
	/**
	 * Get content disposition header value
	 *
	 * @return string
	 */
	protected function getContentDisposition() {
		return $this->isDownload() ? 'attachment' : 'inline';
	}
	
	/**
	 * @return bool
	 */
	public function isDownload(): bool {
		return $this->download;
	}
	
	/**
	 * @param bool $download
	 */
	public function setDownload(bool $download): void {
		$this->download = $download;
	}
	
	/**
	 * @return string
	 */
	public function getFileName(): string {
		return $this->fileName;
	}
	
	/**
	 * @param string $fileName
	 */
	public function setFileName(string $fileName): void {
		$this->fileName = $fileName;
	}
	
	/**
	 * @return DateTime
	 */
	public function getLastModifiedDate(): DateTime {
		return $this->lastModifiedDate;
	}
	
	/**
	 * @param DateTime|int $lastModifiedDate
	 * @return $this
	 */
	public function setLastModifiedDate($lastModifiedDate): HttpResponse {
		if( is_numeric($lastModifiedDate) ) {
			$lastModifiedDate = DateTime::createFromFormat('U', $lastModifiedDate);
		}
		$this->lastModifiedDate = $lastModifiedDate;
		
		return $this;
	}
	
	public function getHttpDateFormat(): string {
		// DATE_RFC7231 is 7.0.19+ & 7.1.5+
		// See https://www.php.net/manual/fr/class.datetimeinterface.php#datetime.constants.types
		return defined('DATE_RFC7231') ? DATE_RFC7231 : DATE_RFC2822;
	}
	
	/**
	 * @return int
	 */
	public function getCacheMaxAge(): int {
		return $this->cacheMaxAge;
	}
	
	/**
	 * @param int $cacheMaxAge
	 */
	public function setCacheMaxAge(int $cacheMaxAge): void {
		$this->cacheMaxAge = $cacheMaxAge;
	}
	
	/**
	 * Process response to client
	 *
	 * @return bool
	 */
	public function run() {
		if( $this->body !== null ) {
			// if already generated we display the body
			echo $this->getBody();
			return true;
		}
		return false;
	}
	
	/**
	 * Get the body
	 *
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}
	
	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return HtmlHttpResponse
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}
	
	/**
	 * Collect response data from parameters
	 *
	 * @param string $layout
	 * @param array $values
	 * @return NULL
	 */
	public function collectFrom($layout, $values = []) {
		return null;
	}
	
	/**
	 * Get the code
	 *
	 * @return int
	 */
	public function getCode() {
		return $this->code;
	}
	
	/**
	 * Set the code
	 *
	 * @param int
	 * @return HttpResponse
	 */
	public function setCode($code) {
		$this->code = (int) $code;
		
		return $this;
	}
	
	/**
	 * Generate HTMLResponse from Exception
	 *
	 * @param Exception $exception
	 * @param array $values
	 * @return HttpResponse
	 */
	public static function generateFromException(Throwable $exception, array $values = []): HttpResponse {
		return HtmlHttpResponse::generateFromException($exception, $values);
	}
	
	/**
	 * Generate HTMLResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return HttpResponse
	 */
	public static function generateFromUserException(UserException $exception, array $values = []): HttpResponse {
		return HtmlHttpResponse::generateFromUserException($exception, $values);
	}
	
}
