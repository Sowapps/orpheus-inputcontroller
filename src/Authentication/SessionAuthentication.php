<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Authentication;

class SessionAuthentication extends AbstractAuthentication {
	
	function process(): void {
		$token = $_SESSION['ORPHEUS']['AUTHENTICATION_TOKEN'] ?? null;
		if( $token ) {
			$this->authenticateByToken($token);
		}
	}
}
