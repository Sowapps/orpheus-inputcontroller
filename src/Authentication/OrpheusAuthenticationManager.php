<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Authentication;

use Orpheus\InputController\InputRequest;

class OrpheusAuthenticationManager implements AuthenticationManager {
	private array $authentications = [
		SessionAuthentication::class,
	];
	
	function getAuthentication(InputRequest $request): ?AbstractAuthentication {
		foreach( $this->authentications as $authenticationClass ) {
			/** @var AbstractAuthentication $authentication */
			$authentication = new $authenticationClass($request);
			$authentication->process();
			if( $authentication->isAuthenticated() ) {
				return $authentication;
			}
		}
		
		return null;
	}
}
