<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Authentication;

use Orpheus\EntityDescriptor\User\AbstractUser;

class HttpAuthentication extends AbstractAuthentication {
	
	protected function getUserNameField(): string {
		return 'name';
	}
	
	public function authenticate(): void {
		if( !isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ) {
			return;
		}
		/** @var AbstractUser $user */
		$user = AbstractUser::requestSelect()
			->where($this->getUserNameField(), $_SERVER['PHP_AUTH_USER'])
			->asObject()->run();
		if( empty($user) ) {
			AbstractUser::throwNotFound();
		}
		if( $user->password !== hashString($_SERVER['PHP_AUTH_PW']) ) {
			AbstractUser::throwException("wrongPassword");
		}
	}
	
	public function revoke(): void {
		// Do nothing, we can not revoke header authentication
	}
}
