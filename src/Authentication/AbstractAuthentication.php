<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Authentication;

use Orpheus\EntityDescriptor\User\AbstractUser;
use Orpheus\InputController\InputRequest;

abstract class AbstractAuthentication {
	
	protected ?InputRequest $request;
	protected ?AbstractUser $authenticatedUser = null;
	protected ?AbstractUser $impersonatedUser = null;
	
	/**
	 * AbstractAuthentication constructor
	 */
	public function __construct(?InputRequest $request = null) {
		$this->request = $request;
	}
	
	abstract function authenticate(): void;
	
	abstract function revoke(): void;
	
	public function impersonate(AbstractUser $user): void {
	}
	
	public function terminateImpersonation(): void {
	}
	
	public function authenticateByToken(string $token): void {
		/** @var AbstractUser $userClass */
		$userClass = AbstractUser::getUserClass();
		$this->authenticatedUser = $userClass::getByAuthenticationToken($token);
	}
	
	public function impersonateByToken(string $token): void {
		/** @var AbstractUser $userClass */
		$userClass = AbstractUser::getUserClass();
		$this->impersonatedUser = $userClass::getByAuthenticationToken($token);
	}
	
	public function isAuthenticated(): bool {
		return !!$this->authenticatedUser;
	}
	
	public function getAuthenticatedUser(): ?AbstractUser {
		return $this->authenticatedUser;
	}
	
	public function getAuthenticationToken(): ?string {
		return $this->authenticatedUser?->getAuthenticationToken();
	}
	
	public function getRequest(): InputRequest {
		return $this->request;
	}
	
	public function getImpersonatedUser(): ?AbstractUser {
		return $this->impersonatedUser;
	}
	
}
