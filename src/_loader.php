<?php
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Core\Route;

/**
 * InputController Library
 * 
 * InputController library to bring MVC features
 * 
 */

// define('HOOK_ROUTEMODULE', 'routeModule');
// Hook::create(HOOK_ROUTEMODULE);


function u($routeName, $values=array()) {
// 	$routes	= HTTPRoute::getRoutes();
// 	$routes	= HTTPRoute::getRoutes();
// 	debug("u($routeName)");
	$route	= HTTPRoute::getRoute($routeName);
// 	debug("u($routeName) - Got route");
	if( !$route ) {
// 		debug("u($routeName) - Route not found");
		throw new Exception('Unable to find route '.$routeName);
	}
// 	if( !isset($routes[$routeName]) ) {
// 		throw new Exception('Unable to find route '.$routeName);
// 	}
// 	if( !isset($routes[$routeName][HTTPRoute::METHOD_GET]) ) {
// 		throw new Exception('Unable to find route '.$routeName.' for GET method');
// 	}
	/* @var $route HTTPRoute */
// 	$route	= $routes[$routeName][HTTPRoute::METHOD_GET];
	return $route->formatURL($values);
}

function exists_route($routeName) {
	return !!HTTPRoute::getRoute($routeName);
}

/**
 * @param string
 * @return boolean
 */
function is_current_route($route) {
	return get_current_route() === $route;
}

/**
 * @return string
 */
function get_current_route() {
	$request	= HTTPRequest::getMainRequest();
	return $request->getRoute()->getName();
}

function _u($route, $values=array()) {
	echo u($route, $values);
}

Route::suggestResolver('Orpheus\InputController\HTTPController\HTTPRoute');
