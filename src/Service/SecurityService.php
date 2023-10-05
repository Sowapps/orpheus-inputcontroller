<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Service;

use Orpheus\Config\Config;
use Orpheus\EntityDescriptor\User\AbstractUser;
use Orpheus\Exception\ForbiddenException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\RedirectHttpResponse;
use RuntimeException;

class SecurityService {
	
	private static SecurityService $instance;
	
	public function getForbiddenHttpResponse(ForbiddenException $exception): ?HttpResponse {
		$authenticated = AbstractUser::isLogged();
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
	
	public static function get(): SecurityService {
		return static::$instance ??= new SecurityService();
	}
	
}
