<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

/**
 * The RedirectHttpResponse class
 */
class RedirectHttpResponse extends HttpResponse {
	
	/**
	 * The destination URI to redirect client
	 *
	 * @var string
	 */
	protected string $destinationUri;
	
	/**
	 * Constructor
	 *
	 * @param string $destination
	 * @param bool $permanent
	 */
	public function __construct(string $destination, $permanent = false) {
		parent::__construct();
		if( $permanent ) {
			$this->setPermanent();
		} else {
			$this->setTemporarily();
		}
		if( exists_route($destination) ) {
			$destination = u($destination);
		}
		$this->setDestinationUri($destination);
	}
	
	/**
	 * Set this redirection permanent
	 */
	public function setPermanent() {
		$this->setCode(HTTP_MOVED_PERMANENTLY);
	}
	
	/**
	 * Set this redirection temporarily
	 */
	public function setTemporarily() {
		$this->setCode(HTTP_MOVED_TEMPORARILY);
	}
	
	/**
	 * @return bool
	 */
	public function run(): bool {
		header('Location: ' . $this->destinationUri);
		
		return true;
	}
	
	/**
	 * Get the destination URI
	 *
	 * @return string
	 */
	public function getDestinationUri(): string {
		return $this->destinationUri;
	}
	
	/**
	 * Set the destination URI
	 *
	 * @param string $destinationUri
	 * @return RedirectHttpResponse
	 */
	public function setDestinationUri(string $destinationUri): RedirectHttpResponse {
		$this->destinationUri = $destinationUri;
		
		return $this;
	}
	
}
