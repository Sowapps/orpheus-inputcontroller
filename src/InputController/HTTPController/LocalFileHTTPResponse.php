<?php
/**
 * LocalFileHTTPResponse
 */

namespace Orpheus\InputController\HTTPController;

use Exception;
use Orpheus\Exception\NotFoundException;
use Orpheus\Exception\UserException;

/**
 * The LocalFileHTTPResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class LocalFileHTTPResponse extends HTTPResponse {
	
	/**
	 * Default mimetype
	 *
	 * @var string
	 */
	const DEFAULT_MIMETYPE = 'text/plain';
	
	/**
	 * Registered mimetype for extension
	 *
	 * @var array
	 */
	protected static $extensionMimeTypes = [
		'css' => 'text/css',
		'js'  => 'application/javascript',
		'pdf' => 'application/pdf',
	];
	
	/**
	 * The path to the local file
	 *
	 * @var string
	 */
	protected $localFilePath;
	
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
	 * Constructor
	 *
	 * @param string $filePath
	 */
	public function __construct($filePath, $fileName = null, $download = true, $cacheMaxAge = 0) {
		// If is directory or not readable (imply not found)
		if( !is_file($filePath) || !is_readable($filePath) ) {
			throw new NotFoundException('notFoundLocalFile');
		}
		$this->localFilePath = $filePath;
		$this->fileName = $fileName ?: basename($filePath);
		$this->download = $download;
		$this->cacheMaxAge = $cacheMaxAge;
	}
	
	/**
	 * @return bool
	 */
	public function run() {
		// Close to unlock session
		if( session_status() === PHP_SESSION_ACTIVE ) {
			session_write_close();
		}
		// Clean all output buffers to not send it
		while( ob_get_level() ) {
			ob_end_clean();
		}
		
		// Send headers
		if( !headers_sent() ) {
			header('Content-Type: ' . static::getMimetypeFromLocalFilePath($this->localFilePath));
			header('Content-length: ' . filesize($this->localFilePath));
			header('Content-Disposition: ' . $this->getContentDisposition() . '; filename="' . $this->fileName . '"');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($this->localFilePath)) . ' GMT');
			if( $this->cacheMaxAge ) {
				header('Cache-Control: private, max-age=' . $this->cacheMaxAge . ', must-revalidate');
			}
			header('Pragma: public');
		}
		// Render file
		readfile($this->localFilePath);
		return true;
	}
	
	/**
	 * Get mimetype from local file path
	 *
	 * @param string $filePath
	 * @return string
	 */
	protected static function getMimetypeFromLocalFilePath($filePath) {
		$mimetype = getMimeType($filePath);
		if( $mimetype !== 'text/plain' ) {
			return $mimetype;
		}
		return static::getMimetypeFromExtension(pathinfo($filePath, PATHINFO_EXTENSION));
	}
	
	/**
	 * Get mimetype from extension
	 *
	 * @param string $extension
	 * @return string
	 */
	protected static function getMimetypeFromExtension($extension) {
		return isset(static::$extensionMimeTypes[$extension]) ? static::$extensionMimeTypes[$extension] : self::DEFAULT_MIMETYPE;
	}
	
	/**
	 * Get content disposition header value
	 *
	 * @return string
	 */
	protected function getContentDisposition() {
		return $this->download ? 'attachment' : 'inline';
	}
	
	/**
	 * Generate HTMLResponse from Exception
	 *
	 * @param Exception $exception
	 * @param string $action
	 * @return void
	 */
	public static function generateFromException(\Exception $exception, $action = 'Handling the request') {
		return HTMLHTTPResponse::generateFromException($exception, $action);
	}
	
	/**
	 * Generate HTMLResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return HTMLHTTPResponse
	 */
	public static function generateFromUserException(UserException $exception, $values = []) {
		return HTMLHTTPResponse::generateFromUserException($exception, $values);
	}
	
	/**
	 * Set extension's mimetype
	 *
	 * @param string $extension
	 * @param string $mimetype
	 */
	protected static function setExtensionMimetype($extension, $mimetype) {
		static::$extensionMimeTypes[$extension] = $mimetype;
	}
	
	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->fileName;
	}
	
	/**
	 * @param string $fileName
	 * @return static
	 */
	public function setFileName(string $fileName) {
		$this->fileName = $fileName;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isDownload() {
		return $this->download;
	}
	
	/**
	 * @param bool $download
	 * @return static
	 */
	public function setDownload(bool $download) {
		$this->download = $download;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getCacheMaxAge() {
		return $this->cacheMaxAge;
	}
	
	/**
	 * @param int $cacheMaxAge
	 * @return static
	 */
	public function setCacheMaxAge(int $cacheMaxAge) {
		$this->cacheMaxAge = $cacheMaxAge;
		return $this;
	}
	
}
