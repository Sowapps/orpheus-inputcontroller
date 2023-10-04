<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use RuntimeException;

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
	 * @param resource|null $resource resource to get read while rendering, pass null and override run() to get it by your own way
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
	
	public function process(): void {
		$this->setContentType($this->getMimeType());
		$this->setContentLength($this->getSize());
		
		// Close session to prevent session-blocking
		if( session_status() === PHP_SESSION_ACTIVE ) {
			session_write_close();
		}
		// Clean any previous output
		while( ob_get_level() ) {
			ob_end_clean();
		}
		
		parent::process();
	}
	
	public function run(): bool {
		fpassthru($this->resource);
		
		return true;
	}
	
	/**
	 * Get mimetype
	 */
	public function getMimeType(): string {
		return static::getMimetypeFromExtension(pathinfo($this->fileName, PATHINFO_EXTENSION));
	}
	
	/**
	 * Get mimetype from extension
	 */
	protected static function getMimetypeFromExtension(string $extension): string {
		return static::$extensionMimeTypes[$extension] ?? self::DEFAULT_MIMETYPE;
	}
	
	/**
	 * Set extension's mimetype
	 */
	protected static function setExtensionMimetype(string $extension, string $mimetype): void {
		static::$extensionMimeTypes[$extension] = $mimetype;
	}
	
}
