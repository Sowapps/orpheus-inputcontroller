<?php

namespace Orpheus\InputController\HttpController;

use RuntimeException;

/**
 * The FileHttpResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
class FileHttpResponse extends HttpResponse {
	
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
	protected static array $extensionMimeTypes = [
		'css' => 'text/css',
		'js'  => 'application/javascript',
		'pdf' => 'application/pdf',
	];
	
	/**
	 * The resource of file
	 *
	 * @var resource|null
	 */
	protected $resource;
	
	/**
	 * Constructor
	 *
	 * @param resource $resource
	 * @param string $fileName
	 * @param bool $download
	 * @param int $cacheMaxAge
	 */
	public function __construct($resource, string $fileName, bool $download = true, int $cacheMaxAge = 0) {
		if( $resource && !is_resource($resource) ) {
			throw new RuntimeException('fileNotResource');
		}
		parent::__construct(null, null, $download, $fileName);
		$this->resource = $resource;
		$this->setCacheMaxAge($cacheMaxAge);
	}
	
	public function getSize(): int {
		return fstat($this->resource)['size'];
	}
	
	public function process() {
		$this->setContentType($this->getMimeType());
		$this->setContentLength($this->getSize());
		
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
		fpassthru($this->resource);
	}
	
	/**
	 * Get mimetype
	 *
	 * @return string
	 */
	public function getMimeType(): string {
		return static::getMimetypeFromExtension(pathinfo($this->fileName, PATHINFO_EXTENSION));
	}
	
	/**
	 * Get mimetype from extension
	 *
	 * @param string $extension
	 * @return string
	 */
	protected static function getMimetypeFromExtension($extension): string {
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
