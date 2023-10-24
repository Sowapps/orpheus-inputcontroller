<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Authentication;

use Orpheus\InputController\HttpController\HttpRequest;

class HeaderAuthentication extends AbstractAuthentication {
	
	const HEADER_AUTHORIZATION = 'Authorization';
	const HEADER_ALT_AUTHORIZATION = 'X-Auth';
	
	public function authenticate(): void {
		$request = $this->request;
		$token = null;
		if( $request instanceof HttpRequest ) {
			$headers = $request->getHeaders();
			$authHeader = $headers[self::HEADER_ALT_AUTHORIZATION] ?? $headers[self::HEADER_AUTHORIZATION] ?? null;
			if( $authHeader ) {
				[, $token] = explodeList(' ', $authHeader, 2);
			}
			unset($headers, $authHeader);
		}
		if( $token ) {
			$this->authenticateByToken($token);
		}
	}
	
	public function revoke(): void {
		// Do nothing, we can not revoke header authentication
	}
}
