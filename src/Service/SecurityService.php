<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Service;

use Orpheus\Authentication\AbstractAuthentication;
use Orpheus\Authentication\AuthenticationManager;
use Orpheus\Authentication\OrpheusAuthenticationManager;
use Orpheus\Authentication\SessionAuthentication;
use Orpheus\Config\Config;
use Orpheus\EntityDescriptor\User\AbstractUser;
use Orpheus\Exception\ForbiddenException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\RedirectHttpResponse;
use Orpheus\InputController\InputRequest;
use RuntimeException;

class SecurityService {
	
	private string $authenticationManagerClass = OrpheusAuthenticationManager::class;
	private AuthenticationManager $authenticationManager;
	private ?AbstractAuthentication $authentication = null;
	private ?AbstractUser $authenticatedUser = null;
	private ?AbstractUser $activeUser = null;
	
	private static SecurityService $instance;
	
	public function loadUserAuthentication(InputRequest $request): void {
		if( $this->isAuthenticated() ) {
			// Already authenticated - silent reject
			return;
		}
		$managerClass = $this->authenticationManagerClass;
		/** @var AuthenticationManager $manager */
		$this->authenticationManager = $manager = new $managerClass();
		$authentication = $manager->getAuthentication($request);
		if( $authentication ) {
			$this->authenticate($authentication);
		}
	}
	
	public function isAuthenticated(): bool {
		return !!$this->authenticatedUser;
	}
	
	public function setPersistentAuthentication(AbstractUser $user): void {
		if( $this->isAuthenticated() ) {
			throw new RuntimeException('Already authenticated');
		}
		$authentication = new SessionAuthentication();
		$authentication->create($user);
		$authentication->authenticate();
		$this->authenticate($authentication);
	}
	
	public function authenticate(AbstractAuthentication $authentication): void {
		if( $this->isAuthenticated() ) {
			throw new RuntimeException(sprintf('Already authenticated with "%s"', $this->authenticatedUser));
		}
		if( !$authentication->isAuthenticated() ) {
			throw new RuntimeException('Invalid authentication');
		}
		$this->authenticatedUser = $authentication->getAuthenticatedUser();
		$this->activeUser = $authentication->getImpersonatedUser() ?? $this->authenticatedUser;
		$this->authentication = $authentication;
		$this->authenticatedUser->onAuthenticated();
	}
	
	public function invalidateAuthentication(): void {
		if( $this->authentication ) {
			$this->authentication->revoke();
			$this->authenticatedUser = $this->activeUser = $this->authentication = null;
		}
	}
	
	public function setActiveUser(AbstractUser $user): void {
		if( !$this->isAuthenticated() ) {
			throw new RuntimeException('Authentication required');
		}
		if( !$this->activeUser->equals($user) ) {
			$this->activeUser = $user;
			// Store impersonation using current authentication
			$this->authentication->impersonate($user);
		} else {
			// Store impersonation using current authentication
			$this->authentication->terminateImpersonation();
		}
	}
	
	public function getForbiddenHttpResponse(ForbiddenException $exception): ?HttpResponse {
		$authenticated = $this->isAuthenticated();
		// User is logged but accessing a forbidden route OR User is not logged and try to access a route with required authentication
		$route = $authenticated ? $this->getAuthenticationForbiddenTargetRoute() : $this->getAuthenticationExpirationTargetRoute();
		if( $route ) {
			$navigationKey = null;
			if( $this->isAuthenticationLoginRecoveringNavigation() ) {
				$navigationKey = $this->generateNavigationKey(HttpRequest::getMainHttpRequest());
			}
			
			return new RedirectHttpResponse(u($route) . ($navigationKey ? '?rnk=' . $navigationKey : ''));
		} elseif( !$authenticated ) {
			throw new RuntimeException(
				'No route to redirect non-authenticated user, please configure "authentication.expiration.target_route" or provide constant "DEFAULT_ROUTE"',
				0, $exception);
		}
		
		return null;
	}
	
	public function consumeNavigationKey(string $key) {
		$_SESSION['ORPHEUS']['AUTHENTICATION_NAVIGATION_STORE'] ??= [];
		$store = &$_SESSION['ORPHEUS']['AUTHENTICATION_NAVIGATION_STORE'];
		$url = $store[$key] ?? null;
		if( $url ) {
			unset($store[$key]);
		}
		
		return $url;
	}
	
	protected function generateNavigationKey(?HttpRequest $request): string {
		$method = $this->getAuthenticationLoginRecoverMethod();
		switch( $method ) {
			case 'session':
				$_SESSION['ORPHEUS']['AUTHENTICATION_NAVIGATION_STORE'] ??= [];
				$store = &$_SESSION['ORPHEUS']['AUTHENTICATION_NAVIGATION_STORE'];
				do {
					$key = generateRandomString(8);
				} while( isset($store[$key]) );
				$store[$key] = $request->getUrl();
				
				return $key;
			default:
				throw new RuntimeException(sprintf('Unsupported login recover method "%s", please configure "authentication.login.recover_method"', $method));
		}
	}
	
	public function getAuthenticationExpirationTargetRoute(): ?string {
		return Config::get('authentication.expiration.target_route') ?? (defined(DEFAULT_ROUTE) ? DEFAULT_ROUTE : null);
	}
	
	public function getAuthenticationForbiddenTargetRoute(): ?string {
		return Config::get('authentication.forbidden.target_route') ?? null;
	}
	
	public function getAuthenticationLoginRecoverMethod(): string {
		return Config::get('authentication.login.recover_method') ?? 'session';
	}
	
	public function isAuthenticationLoginRecoveringNavigation(): bool {
		return Config::get('authentication.login.recover_navigation') ?? false;
	}
	
	public function getAuthentication(): ?AbstractAuthentication {
		return $this->authentication;
	}
	
	public function getAuthenticatedUser(): ?AbstractUser {
		return $this->authenticatedUser;
	}
	
	public function getActiveUser(): ?AbstractUser {
		return $this->activeUser;
	}
	
	public function getAuthenticationManager(): AuthenticationManager {
		return $this->authenticationManager;
	}
	
	public static function get(): SecurityService {
		return static::$instance ??= new SecurityService();
	}
	
}
