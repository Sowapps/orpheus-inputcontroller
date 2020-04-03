<?php
/**
 * Loader for Orpheus Input Controller library
 */

use Orpheus\Core\RequestHandler;
use Orpheus\Core\Route;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\InputController\InputRequest;

if( !defined('ORPHEUSPATH') ) {
	// Do not load in a non-orpheus environment
	return;
}

/**
 * Generate URL to a route
 *
 * @param string $routeName
 * @param array $values
 * @return string
 * @throws Exception
 */
function u($routeName, $values = []) {
	/* @var $route HTTPRoute */
	$route = HTTPRoute::getRoute($routeName);
	if( !$route ) {
		throw new Exception('Unable to find route ' . $routeName);
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
function exists_route($routeName) {
	return !!HTTPRoute::getRoute($routeName);
}

/**
 * Test if the $route is the one of the current request
 *
 * @param string
 * @return boolean
 */
function is_current_route($route) {
	return get_current_route() === $route;
}

/**
 * Get the route name of the current request
 *
 * @return string
 */
function get_current_route() {
	$request = InputRequest::getMainRequest();
	return $request->getRoute()->getName();
}

/**
 * Get the link of the current request
 *
 * @return string
 */
function get_current_link() {
	$request = HTTPRequest::getMainRequest();
	return $request->getRoute()->getLink((array) $request->getPathValues());
}

// Polyfill for some FPM systems
if( !function_exists('getallheaders') ) {
	function getallheaders() {
		$headers = [];
		foreach( $_SERVER as $name => $value ) {
			if( substr($name, 0, 5) === 'HTTP_' ) {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}

RequestHandler::suggestHandler(RequestHandler::TYPE_HTTP, 'Orpheus\InputController\HTTPController\HTTPRequest');
RequestHandler::suggestHandler(RequestHandler::TYPE_CONSOLE, 'Orpheus\InputController\CLIController\CLIRequest');

Route::suggestResolver('Orpheus\InputController\HTTPController\HTTPRoute');
