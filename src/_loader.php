<?php
/**
 * Loader for Orpheus Input Controller library
 */

use Orpheus\Core\RequestHandler;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;

/**
 * Generate URL to a route
 *
 */
function u(string $routeName, array $values = []): string {
	/* @var HttpRoute $route */
	$route = HttpRoute::getRoute($routeName);
	if( !$route ) {
		throw new RuntimeException('Unable to find route ' . $routeName);
	}
	
	return $route->formatUrl($values);
}

/**
 * Display URL to a route
 *
 * @deprecated Use u() with echo
 */
function _u(string $routeName, array $values = []): void {
	echo u($routeName, $values);
}

/**
 * Test if a route exists
 *
 */
function exists_route(string $routeName): bool {
	return !!HttpRoute::getRoute($routeName);
}

/**
 * Get the link of the current request
 *
 */
function get_current_link(): string {
	$request = HttpRequest::getMainRequest();
	if( $request && $request->getRoute() ) {
		return $request->getRoute()->getLink($request->getPathValues());
	}
	
	return sprintf('%s://%s%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
}

// Polyfill for some FPM systems
if( !function_exists('getallheaders') ) {
	function getallheaders(): array {
		$headers = [];
		foreach( $_SERVER as $name => $value ) {
			if( str_starts_with($name, 'HTTP_') ) {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		
		return $headers;
	}
}

RequestHandler::suggestHandler(RequestHandler::TYPE_HTTP, 'Orpheus\InputController\HttpController\HttpRequest');
RequestHandler::suggestHandler(RequestHandler::TYPE_CONSOLE, 'Orpheus\InputController\CliController\CliRequest');
