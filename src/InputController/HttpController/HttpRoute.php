<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Exception;
use Orpheus;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\InputRequest;
use Orpheus\Service\ApplicationKernel;
use RuntimeException;

class HttpRoute extends ControllerRoute {
	
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	
	/**
	 * Registered regex for a type
	 *
	 * @var array
	 */
	protected static array $typesRegex = [];
	
	/**
	 * Registered routes
	 *
	 * @var array
	 */
	protected static array $routes = [];
	
	/**
	 * Registered response class for output option
	 *
	 * @var array
	 */
	protected static array $outputResponses = [];
	
	/**
	 * All known methods
	 *
	 * @var array
	 */
	protected static array $knownMethods = [
		self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE,
	];
	
	/**
	 * The methods allowed to reach this route
	 *
	 * @var array
	 */
	protected array $methods;
	
	/**
	 * Path with converted regex
	 *
	 * @var string|null
	 */
	protected ?string $pathRegex = null;
	
	/**
	 * Variables in path
	 *
	 * @var string[]
	 */
	protected array $pathVariables = [];
	
	/**
	 * Constructor
	 */
	protected function __construct(string $name, string $path, string $controller, array $methods, ?array $restrictTo, string $defaultResponse, array $options) {
		parent::__construct($name, $path, $controller, $restrictTo, $defaultResponse, $options);
		$this->methods = $methods;
		$this->generatePathRegex();
	}
	
	/**
	 * Generate all regex of the path from extracted variables
	 */
	protected function generatePathRegex(): void {
		if( $this->pathRegex ) {
			return;
		}
		$variables = [];
		$this->pathRegex = preg_replace_callback(
			'#\{([^\}]+)\}#sm',
			function ($matches) use (&$variables) {
				$regex = $var = null;
				static::extractVariable(str_replace('\.', '.', $matches[1]), $var, $regex);
				$variables[] = $var;
				return '(' . $regex . ')';
			},
			str_replace('.', '\.', $this->path)
		);
		$this->pathVariables = $variables;
	}
	
	/**
	 * Extract variable from configuration string
	 */
	protected static function extractVariable(string $value, ?string &$variable = null, ?string &$regex = null): void {
		[$p1, $p2] = explodeList(':', $value, 2);
		// Optional only if there is a default value
		if( $p2 ) {
			// {regex|type:variable}
			$variable = $p2;
			$regex = $p1;
			if( ctype_alpha($regex) && isset(static::$typesRegex[$regex]) ) {
				$regex = static::$typesRegex[$regex];
			}
		} else {
			// {variable}, regex=[^\/]+
			$variable = $p1;
			$regex = '[^\/]+';
		}
	}
	
	/**
	 * Format the current route to get a URL from path
	 *
	 * @param string[] $values
	 */
	public function formatUrl(array $values = [], array $parameters = []): string {
		$path = preg_replace_callback(
			'#\{([^\}]+)\}#sm',
			function ($matches) use ($values) {
				$var = $regex = null;
				static::extractVariable($matches[1], $var, $regex);
				if( !isset($values[$var]) ) {
					throw new RuntimeException(sprintf('The variable "%s" is missing to generate URL for route "%s"', $var, $this->name));
				}
				$value = $values[$var];
				if( !preg_match('#^' . $regex . '$#', $value) ) {
					throw new RuntimeException(sprintf('The given value "%s" of variable "%s" is not matching the regex requirements to generate URL for route "%s"', $value, $var, $this->name));
				}
				return $value;
			},
			$this->path
		);
		// Format path to ensure it start with slash
		$path = $path && $path[0] === '/' ? $path : '/' . $path;
		ApplicationKernel::get()->formatRoutePath($this,$path, $values);
		return WEB_ROOT . $path . ($parameters ? '?' . http_build_query($parameters) : '');
	}
	
	/**
	 * Get route as string
	 */
	public function __toString(): string {
		return sprintf('%s([%s], %s)', $this->name, implode(',', $this->methods), $this->path);
	}
	
	/**
	 * Test current route is matching request
	 *
	 * {@inheritDoc}
	 * @param HttpRequest $request
	 * @param boolean $alternative
	 * @see ControllerRoute::isMatchingRequest()
	 */
	public function isMatchingRequest(InputRequest $request, array &$values = []): bool {
		// Method match && Path match (variables included)
		if( !in_array($request->getMethod(), $this->methods) ) {
			return false;
		}
		$regex = $this->pathRegex;
		$matches = [];
		if( preg_match('#^' . $regex . '$#i', urldecode($request->getPath()), $matches) ) {
			unset($matches[0]);
			$values = array_combine($this->pathVariables, $matches);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Register route by $name from config
	 *
	 * @throws Exception
	 */
	public static function registerConfig(string $name, array $config): void {
		if( empty($config['path']) ) {
			throw new Exception('Missing a valid `path` in configuration of route "' . $name . '"');
		}
		if( empty($config['response']) ) {
			$config['response'] = !empty($config['output']) ? static::getOutputResponse($config['output']) : 'Orpheus\InputController\HttpController\HtmlHttpResponse';
		}
		if( empty($config['controller']) ) {
			if( !empty($config['redirect']) ) {
				$config['controller'] = 'Orpheus\\Controller\\RedirectController';
			} elseif( !empty($config['render']) ) {
				$config['controller'] = 'Orpheus\\Controller\\StaticPageController';
			} else {
				throw new Exception('Missing a valid `controller` in configuration of route "' . $name . '"');
			}
		}
		if( !isset($config['restrictTo']) ) {
			$config['restrictTo'] = null;
		}
		$options = $config;
		unset($options['path'], $options['controller'], $options['method'], $options['restrictTo']);
		$methods = $config['method'] ?? null;
		if( is_string($methods) ) {
			$methods = [$methods];
		}
		$path = static::parsePath($config['path']);
		static::register($name, $path, $config['controller'], $methods, $config['restrictTo'], $config['response'], $options);
	}
	
	/**
	 * Normalize path by removing any trailing slash
	 */
	public static function parsePath(string $path): string {
		// Remove query string
		$path = parse_url($path, PHP_URL_PATH);
		// Remove trailing slash
		return strlen($path) > 1 ? rtrim($path, '/') : $path;
	}
	
	/**
	 * Get the output response
	 */
	public static function getOutputResponse(string $output): mixed {
		return static::$outputResponses[$output];
	}
	
	/**
	 * Register route by $name
	 *
	 * @throws Exception
	 */
	public static function register(string $name, string $path, string $controller, ?array $methods, ?array $restrictTo, string $defaultResponse, array $options = []): void {
		if( $methods ) {
			// Check methods are valid
			$diff = array_diff($methods, self::getKnownMethods());
			if( $diff ) {
				throw new Exception(sprintf('Invalid routes configuration, unknown methods "%s" for route "%s"', implode(',', $diff), $name));
			}
		} else {
			$methods = self::getKnownMethods();
		}
		static::$routes[$name] = new static($name, $path, $controller, $methods, $restrictTo, $defaultResponse, $options);
	}
	
	/**
	 * Set the output response
	 */
	public static function setOutputResponse(string $output, string $responseClass): void {
		static::$outputResponses[$output] = $responseClass;
	}
	
	/**
	 * Set the regex of a type, used to parse paths
	 */
	public static function setTypeRegex(string $type, string $regex): void {
		static::$typesRegex[$type] = $regex;
	}
	
	/**
	 * Get the route object for the $route name
	 */
	public static function getRoute(string $name): ?HttpRoute {
		/** @var HttpRoute[] $routes */
		$routes = static::getRoutes();
		return $routes[$name] ?? null;
	}
	
	/**
	 * Get the route object for the $route name
	 */
	public static function getDefaultRoute(): HttpRoute {
		// If specific default route is available
		// Else the app global default route
		// Else we build one because it may be required (Links to self request on template of error's page)
		return static::getRoute('default') ?? static::getRoute(DEFAULT_ROUTE) ?? new HttpRoute('default', '/', '', [], null, '', []);
	}
	
	/**
	 * Get the known HTTP methods
	 *
	 * @return string[]
	 */
	public static function getKnownMethods(): array {
		return static::$knownMethods;
	}
	
}

//http://fr.php.net/manual/fr/regexp.reference.escape.php
//http://fr.php.net/manual/fr/regexp.reference.character-classes.php
// Case Insensitive
HttpRoute::setTypeRegex('int', '\d+');
HttpRoute::setTypeRegex('id', '[1-9]\d*');
HttpRoute::setTypeRegex('slug', '[a-z0-9\-_]+');

HttpRoute::setOutputResponse('html', 'Orpheus\InputController\HttpController\HtmlHttpResponse');
HttpRoute::setOutputResponse('json', 'Orpheus\InputController\HttpController\JsonHttpResponse');

