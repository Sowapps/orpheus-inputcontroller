<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Orpheus\Exception\NotFoundException;

class LocalFileHttpResponse extends FileHttpResponse {
	
	/**
	 * The path to the local file
	 *
	 * @var string
	 */
	protected string $localFilePath;
	
	/**
	 * Constructor
	 *
	 * @param string $filePath
	 * @param string|null $fileName
	 * @param bool $download
	 * @param int $cacheMaxAge
	 */
	public function __construct(string $filePath, ?string $fileName = null, bool $download = true, int $cacheMaxAge = 0) {
		// If is directory or not readable (imply not found)
		if( !is_file($filePath) || !is_readable($filePath) ) {
			throw new NotFoundException('notFoundLocalFile');
		}
		parent::__construct(null, $fileName ?: basename($filePath), $download, $cacheMaxAge);
		$this->localFilePath = $filePath;
	}
	
	public function getMimeType(): string {
		$mimetype = getMimeType($this->localFilePath);
		if( $mimetype !== 'text/plain' ) {
			return $mimetype;
		}
		
		return static::getMimetypeFromExtension(pathinfo($this->localFilePath, PATHINFO_EXTENSION));
	}
	
	public function getSize(): int {
		return filesize($this->localFilePath);
	}
	
	public function run(): bool {
		readfile($this->localFilePath);
		
		return true;
	}
	
	public function process() {
		$this->setLastModifiedDate(filemtime($this->localFilePath));
		
		parent::process();
	}
	
}
