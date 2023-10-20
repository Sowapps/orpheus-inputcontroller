<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Authentication;

use Orpheus\EntityDescriptor\User\AbstractUser;
use Orpheus\InputController\InputRequest;

abstract class AbstractAuthentication {
	
	protected InputRequest $request;
	protected ?AbstractUser $user = null;
	
	/**
	 * AbstractAuthentication constructor
	 */
	public function __construct(InputRequest $request) {
		$this->request = $request;
	}
	
	abstract function process(): void;
	
	public function authenticateByToken(string $token): void {
		/** @var AbstractUser $userClass */
		$userClass = AbstractUser::getUserClass();
		$this->user = $userClass::getByAuthenticationToken($token);
	}
	
	public function isAuthenticated(): bool {
		return !!$this->user;
	}
	
	public function getAuthenticatedUser(): ?AbstractUser {
		return $this->user;
	}
	
	public function getAuthenticationToken(): ?string {
		return $this->user?->getAuthenticationToken();
	}
	
	public function getRequest(): InputRequest {
		return $this->request;
	}
	
}
