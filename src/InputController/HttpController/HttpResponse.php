<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use DateTime;
use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\OutputResponse;
use Throwable;

class HttpResponse extends OutputResponse {
	
	/**
	 * @var string|null The content type to send to client
	 */
	protected ?string $contentType = null;
	
	/**
	 * @var int|null The content length to send to client
	 */
	protected ?int $contentLength = null;
	
	/**
	 * The HTML body of the response
	 *
	 * @var string|null
	 */
	protected ?string $body = null;
	
	/**
	 * The HTTP response code
	 *
	 * @var int|null
	 */
	protected ?int $code = null;
	
	/**
	 * The file name shared to the client
	 *
	 * @var string|null
	 */
	protected ?string $fileName = null;
	
	/**
	 * Is the file downloaded ? Or displayed ?
	 *
	 * @var bool
	 */
	protected bool $download = false;
	
	/**
	 * Client cache max age in seconds
	 *
	 * @var int|null
	 */
	protected ?int $cacheMaxAge = null;
	
	/**
	 * Client cache max age in seconds
	 *
	 * @var DateTime|null
	 */
	protected ?DateTime $lastModifiedDate = null;
	
	/**
	 * Constructor
	 */
	public function __construct(?string $body = null, ?string $contentType = null, bool $download = false, ?string $fileName = null) {
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
	public function process(): void {
		$this->sendHeaders();
		$this->run();
	}
	
	protected function sendHeaders(): void {
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
			header('Content-Length: ' . $this->getContentLength());
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
	
	public function getContentType(): ?string {
		return $this->contentType;
	}
	
	public function setContentType(string $contentType): void {
		$this->contentType = $contentType;
	}
	
	public function getContentLength(): int {
		return $this->contentLength;
	}
	
	public function setContentLength(int $contentLength): void {
		$this->contentLength = $contentLength;
	}
	
	/**
	 * Get content disposition header value
	 */
	protected function getContentDisposition(): string {
		return $this->isDownload() ? 'attachment' : 'inline';
	}
	
	public function isDownload(): bool {
		return $this->download;
	}
	
	public function setDownload(bool $download): void {
		$this->download = $download;
	}
	
	public function getFileName(): string {
		return $this->fileName;
	}
	
	public function setFileName(string $fileName): void {
		$this->fileName = $fileName;
	}
	
	public function getLastModifiedDate(): DateTime {
		return $this->lastModifiedDate;
	}
	
	/**
	 * @param DateTime|int $lastModifiedDate DateTime or timestamp
	 * @return $this
	 */
	public function setLastModifiedDate(DateTime|int $lastModifiedDate): HttpResponse {
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
	
	public function getCacheMaxAge(): int {
		return $this->cacheMaxAge;
	}
	
	public function setCacheMaxAge(int $cacheMaxAge): void {
		$this->cacheMaxAge = $cacheMaxAge;
	}
	
	/**
	 * Process response to client
	 */
	public function run(): bool {
		if( $this->body !== null ) {
			// if already generated we display the body
			echo $this->getBody();
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get the body
	 */
	public function getBody(): string {
		return $this->body;
	}
	
	/**
	 * Set the body
	 */
	public function setBody(?string $body): HttpResponse {
		$this->body = $body;
		
		return $this;
	}
	
	/**
	 * Get the code
	 */
	public function getCode(): ?int {
		return $this->code;
	}
	
	/**
	 * Set the code
	 */
	public function setCode(int $code): HttpResponse {
		$this->code = $code;
		
		return $this;
	}
	
	/**
	 * Generate HtmlResponse from Exception
	 *
	 * @param Exception $exception
	 */
	public static function generateFromException(Throwable $exception, array $values = []): HttpResponse {
		return HtmlHttpResponse::generateFromException($exception, $values);
	}
	
	/**
	 * Generate HtmlResponse from UserException
	 */
	public static function generateFromUserException(UserException $exception, array $values = []): HttpResponse {
		return HtmlHttpResponse::generateFromUserException($exception, $values);
	}
	
}
