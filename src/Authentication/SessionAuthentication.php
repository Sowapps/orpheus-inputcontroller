<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Authentication;

use Orpheus\EntityDescriptor\User\AbstractUser;

class SessionAuthentication extends AbstractAuthentication {
	
	public function create(AbstractUser $user): void {
		$_SESSION['ORPHEUS']['AUTHENTICATION_TOKEN'] = $user->getAuthenticationToken();
		$_SESSION['ORPHEUS']['IMPERSONATION_TOKEN'] = null;
	}
	
	public function impersonate(AbstractUser $user): void {
		$userToken = $user->getAuthenticationToken();
		if( $userToken !== $this->getSessionAuthenticationToken() ) {
			$_SESSION['ORPHEUS']['IMPERSONATION_TOKEN'] = $userToken;
		} else {
			$this->terminateImpersonation();
		}
	}
	
	public function terminateImpersonation(): void {
		$_SESSION['ORPHEUS']['IMPERSONATION_TOKEN'] = null;
	}
	
	public function authenticate(): void {
		$token = $this->getSessionAuthenticationToken();
		if( $token ) {
			$this->authenticateByToken($token);
			$token = $_SESSION['ORPHEUS']['IMPERSONATION_TOKEN'] ?? null;
			if( $token ) {
				$this->impersonateByToken($token);
			}
		}
	}
	
	public function getSessionAuthenticationToken(): ?string {
		return $_SESSION['ORPHEUS']['AUTHENTICATION_TOKEN'] ?? null;
	}
	
	public function revoke(): void {
		$_SESSION['ORPHEUS']['AUTHENTICATION_TOKEN'] = $_SESSION['ORPHEUS']['IMPERSONATION_TOKEN'] = null;
	}
}
