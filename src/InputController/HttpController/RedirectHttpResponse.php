<?php

namespace Orpheus\InputController\HttpController;

use Exception;

/**
 * The RedirectHttpResponse class
 *
 * @author Florent Hazard <contact@sowapps.com>
 */
class RedirectHttpResponse extends HttpResponse {
	
	/**
	 * The destination URI to redirect client
	 *
	 * @var string
	 */
	protected $destinationUri;
	
	/**
	 * Constructor
	 *
	 * @param string $destination
	 * @param bool $permanent
	 * @throws Exception
	 */
	public function __construct($destination, $permanent = false) {
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
	 *
	 * {@inheritDoc}
	 * @see HttpResponse::run()
	 */
	public function run() {
		header('Location: ' . $this->destinationUri);
	}
	
	/**
	 * Get the destination URI
	 *
	 * @return string
	 */
	public function getDestinationUri() {
		return $this->destinationUri;
	}
	
	/**
	 * Set the destination URI
	 *
	 * @param string $destinationUri
	 * @return RedirectHttpResponse
	 */
	public function setDestinationUri($destinationUri) {
		$this->destinationUri = $destinationUri;
		return $this;
	}
	
}
