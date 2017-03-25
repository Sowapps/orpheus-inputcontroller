<?php
/**
 * LocalFileHTTPResponse
 */

namespace Orpheus\InputController\HTTPController;

use Orpheus\Exception\UserException;
use Orpheus\Exception\NotFoundException;

/**
 * The LocalFileHTTPResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class LocalFileHTTPResponse extends HTTPResponse {
	
	/**
	 * The path to the local file
	 * 
	 * @var string
	 */
	protected $localFilePath;
	
// 	protected $cacheMaxAge = 600;
	
	/**
	 * Registered mimetype for extension
	 * 
	 * @var array
	 */
	protected static $extensionMimeTypes = array(
		'css' => 'text/css',
		'js' => 'application/javascript'
	);
	
	/**
	 * Default mimetype
	 * 
	 * @var string
	 */
	const DEFAULT_MIMETYPE = 'text/plain';
	
	/**
	 * Constructor
	 *
	 * @param string $filePath
	 */
	public function __construct($filePath) {
		// If is directory or not readable (imply not found)
		if( !is_file($filePath) || !is_readable($filePath) ) {
			throw new NotFoundException('notFoundLocalFile');
		}
		$this->localFilePath = $filePath;
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Orpheus\InputController\HTTPController\HTTPResponse::run()
	 */
	public function run() {
// 		debug('headers_sent() => '.b(headers_sent()));
// 		debug('$this->localFilePath => '.$this->localFilePath);
// 		debug('getMimeType($this->localFilePath) => '.static::getMimetypeFromLocalFilePath($this->localFilePath));
		// Send headers
		if( !headers_sent() ) {
			header('Content-Type: '.static::getMimetypeFromLocalFilePath($this->localFilePath));
// 			header('Content-Disposition: attachment; filename="'.$this->getFileName().'"');
// 			header('Content-Disposition: inline; filename="'.$this->getFileName().'"');
			header('Content-length: '.filesize($this->localFilePath));
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($this->localFilePath)).' GMT');
			// header('Cache-Control: public, max-age=3600, must-revalidate');
// 			header('Cache-Control: private, max-age='.$this->getCacheMaxAge());
			header('Pragma: public');
		}
		// Render file
		readfile($this->localFilePath);
		return;
	}

	/**
	 * Generate HTMLResponse from Exception
	 *
	 * @param Exception $exception
	 * @param string $action
	 * @return \Orpheus\InputController\HTTPController\HTMLHTTPResponse
	 */
	public static function generateFromException(\Exception $exception, $action='Handling the request') {
		HTMLHTTPResponse::generateFromException($exception, $action);
	}

	/**
	 * Generate HTMLResponse from UserException
	 *
	 * @param UserException $exception
	 * @param array $values
	 * @return \Orpheus\InputController\HTTPController\HTMLHTTPResponse
	 */
	public static function generateFromUserException(UserException $exception, $values=array()) {
		HTMLHTTPResponse::generateFromUserException($exception, $values);
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
	 * Set extension's mimetype
	 * 
	 * @param string $extension
	 * @param string $mimetype
	 */
	protected static function setExtensionMimetype($extension, $mimetype) {
		static::$extensionMimeTypes[$extension] = $mimetype;
	}
	
}
