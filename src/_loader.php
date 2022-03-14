<?php
/**
 * Loader for Orpheus Input Controller library
 */

use Orpheus\Core\RequestHandler;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\InputController\InputRequest;

if( !defined('ORPHEUS_PATH') ) {
	// Do not load in a non-orpheus environment
	return;
}

/**
 * Generate URL to a route
 *
 * @param string $routeName
 * @param array $values
 * @return string
 */
function u(string $routeName, array $values = []): string {
	/* @var HttpRoute $route */
	$route = HttpRoute::getRoute($routeName);
	if( !$route ) {
		throw new RuntimeException('Unable to find route ' . $routeName);
	}
	
	return $route->formatURL($values);
}

/**
 * Display URL to a route
 *
 * @param string $route
 * @param array $values
 * @throws Exception
 */
function _u($route, $values = []) {
	echo u($route, $values);
}

/**
 * Test if a route exists
 *
 * @param string $routeName
 * @return boolean
 */
function exists_route(string $routeName): bool {
	return !!HttpRoute::getRoute($routeName);
}

/**
 * Test if the $route is the one of the current request
 *
 * @param string
 * @return boolean
 * @deprecated Not used, there are other ways to do
 */
function is_current_route(string $route): bool {
	return get_current_route() === $route;
}

/**
 * Get the route name of the current request
 *
 * @return string
 * @deprecated Not used, there are other ways to do
 */
function get_current_route(): string {
	$request = InputRequest::getMainRequest();
	
	return $request->getRoute()->getName();
}

/**
 * Get the link of the current request
 *
 * @return string
 */
function get_current_link(): string {
	$request = HttpRequest::getMainRequest();
	if( $request && $request->getRoute() ) {
		return $request->getRoute()->getLink((array) $request->getPathValues());
	}
	
	return sprintf('%s://%s%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
}

// Polyfill for some FPM systems
if( !function_exists('getallheaders') ) {
	function getallheaders(): array {
		$headers = [];
		foreach( $_SERVER as $name => $value ) {
			if( substr($name, 0, 5) === 'HTTP_' ) {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		
		return $headers;
	}
}

RequestHandler::suggestHandler(RequestHandler::TYPE_HTTP, 'Orpheus\InputController\HttpController\HttpRequest');
RequestHandler::suggestHandler(RequestHandler::TYPE_CONSOLE, 'Orpheus\InputController\CliController\CliRequest');

//Route::suggestResolver('Orpheus\InputController\HttpController\HttpRoute');
