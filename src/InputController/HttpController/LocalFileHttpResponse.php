<?php

namespace Orpheus\InputController\HttpController;

use Orpheus\Exception\NotFoundException;

/**
 * The LocalFileHttpResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
class LocalFileHttpResponse extends HttpResponse {
	
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
	 * Constructor
	 *
	 * @param string $filePath
	 */
	public function __construct($filePath, $fileName = null, $download = true, $cacheMaxAge = 0) {
		// If is directory or not readable (imply not found)
		if( !is_file($filePath) || !is_readable($filePath) ) {
			throw new NotFoundException('notFoundLocalFile');
		}
		parent::__construct(null, null, $download, $fileName ?: basename($filePath));
		$this->localFilePath = $filePath;
		$this->setCacheMaxAge($cacheMaxAge);
	}
	
	public function process() {
		$this->setContentType(static::getMimetypeFromLocalFilePath($this->localFilePath));
		$this->setContentLength(filesize($this->localFilePath));
		$this->setLastModifiedDate(filemtime($this->localFilePath));
		
		// Close to unlock session
		if( session_status() === PHP_SESSION_ACTIVE ) {
			session_write_close();
		}
		// Clean all output buffers to not send it
		while( ob_get_level() ) {
			ob_end_clean();
		}
		
		parent::process();
	}
	
	public function run() {
		readfile($this->localFilePath);
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
