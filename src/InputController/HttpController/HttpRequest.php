<?php
/**
 * @author Florent Hazard <contact@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Exception;
use Orpheus\Config\IniConfig;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\InputRequest;
use Throwable;

/**
 * The HttpRequest class
 */
class HttpRequest extends InputRequest {
	
	/** @var HttpController */
	protected static $defaultController;
	
	/**
	 * The used method for this request
	 *
	 * @var string
	 */
	protected string $method;
	
	/**
	 * The scheme used to access this route
	 *
	 * @var string
	 */
	protected string $scheme;
	
	/**
	 * The request host domain name
	 *
	 * @var string
	 */
	protected string $domain;
	
	/**
	 * The headers sent to this route
	 *
	 * @var array
	 */
	protected array $headers = [];
	
	/**
	 * The cookies sent to this route
	 *
	 * @var array
	 */
	protected array $cookies = [];
	
	/**
	 * The uploaded files sent to this route
	 *
	 * @var array
	 */
	protected array $files = [];
	
	/**
	 * The input content type
	 *
	 * @var string
	 * @see https://en.wikipedia.org/wiki/Media_type
	 */
	protected string $inputType;
	
	/**
	 * The values in path
	 *
	 * @var array
	 */
	protected array $pathValues;
	
	/**
	 * Constructor
	 *
	 * @param string $method
	 * @param string $path
	 * @param array $parameters
	 * @param array $input
	 */
	public function __construct($method, $path, $parameters = null, $input = null) {
		parent::__construct($path, $parameters, $input);
		$this->setMethod($method);
	}
	
	/**
	 * Get this request as string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->method . '("' . $this->path . '")';
	}
	
	/**
	 * Get the URL used for this request
	 *
	 * @return string
	 */
	public function getURL(): string {
		return $this->scheme . '://' . $this->domain . $this->path . ($this->parameters ? '?' . http_build_query($this->parameters) : '');
	}
	
	/**
	 * Find a matching route according to the request
	 *
	 * @param boolean $alternative Is this looking for an alternative route ?
	 * @return HttpRoute|null
	 */
	public function findFirstMatchingRoute($alternative = false): ?ControllerRoute {
		/* @var HttpRoute $route */
		foreach( $this->getRoutes() as $methodRoutes ) {
			if( !isset($methodRoutes[$this->method]) ) {
				continue;
			}
			$route = $methodRoutes[$this->method];
			$values = [];
			if( $route->isMatchingRequest($this, $values, $alternative) ) {
				$this->pathValues = $values;
				
				return $route;
			}
		}
		
		return null;
	}
	
	/**
	 * Get all available routes
	 *
	 * @return HttpRoute[]
	 * @see InputRequest::getRoutes()
	 */
	public function getRoutes(): array {
		return HttpRoute::getRoutes();
	}
	
	/**
	 * {@inheritDoc}
	 * @param ControllerRoute $route
	 * @return RedirectHttpResponse
	 * @see InputRequest::redirect()
	 */
	public function redirect(ControllerRoute $route): RedirectHttpResponse {
		return new RedirectHttpResponse(u($route->getName()));
	}
	
	/**
	 * Get the method
	 *
	 * @return string
	 */
	public function getMethod(): string {
		return $this->method;
	}
	
	/**
	 * Set the method
	 *
	 * @param string $method
	 * @return HttpRequest
	 */
	protected function setMethod(string $method): HttpRequest {
		$this->method = $method;
		
		return $this;
	}
	
	/**
	 * Test if this is a GET request
	 *
	 * @return boolean
	 */
	public function isGET(): bool {
		return $this->method === HttpRoute::METHOD_GET;
	}
	
	/**
	 * Test if this is a POST request
	 *
	 * @return boolean
	 */
	public function isPOST(): bool {
		return $this->method === HttpRoute::METHOD_POST;
	}
	
	/**
	 * Test if this is a PUT request
	 *
	 * @return boolean
	 */
	public function isPUT(): bool {
		return $this->method === HttpRoute::METHOD_PUT;
	}
	
	/**
	 * Test if this is a DELETE request
	 *
	 * @return boolean
	 */
	public function isDELETE(): bool {
		return $this->method === HttpRoute::METHOD_DELETE;
	}
	
	/**
	 * Get the scheme
	 *
	 * @return string
	 */
	public function getScheme(): string {
		return $this->scheme;
	}
	
	/**
	 * Set the scheme
	 *
	 * @param string $scheme
	 * @return HttpRequest
	 */
	protected function setScheme(string $scheme): HttpRequest {
		$this->scheme = $scheme;
		
		return $this;
	}
	
	/**
	 * Get the host domain
	 *
	 * @return string
	 */
	public function getDomain(): string {
		return $this->domain;
	}
	
	/**
	 * Set the host domain
	 *
	 * @param string $domain
	 * @return $this
	 */
	protected function setDomain($domain): HttpRequest {
		$this->domain = $domain;
		
		return $this;
	}
	
	/**
	 * Test incoming request is over php max post size
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isPostSiteOverLimit(): bool {
		return $this->getHeaderContentLength() > $this->getConfigPostMaxSize();
	}
	
	/**
	 * Get the header Content-Length
	 *
	 * @return int
	 */
	public function getHeaderContentLength(): ?int {
		return isset($this->headers['Content-Length']) ? (int) $this->headers['Content-Length'] : null;
	}
	
	/**
	 * Get php config for max post size
	 *
	 * @return int
	 * @throws Exception
	 */
	public function getConfigPostMaxSize(): int {
		return parseHumanSize(ini_get('post_max_size'));
	}
	
	/**
	 * Get the headers
	 *
	 * @return array
	 */
	public function getHeaders(): array {
		return $this->headers;
	}
	
	/**
	 * Set the headers
	 *
	 * @param array $headers
	 * @return HttpRequest
	 */
	protected function setHeaders($headers): HttpRequest {
		$this->headers = $headers;
		
		return $this;
	}
	
	/**
	 * Get the input type
	 *
	 * @return string
	 */
	public function getInputType(): string {
		return $this->inputType;
	}
	
	/**
	 * Set the input type
	 *
	 * @param string $inputType
	 * @return HttpRequest
	 */
	protected function setInputType($inputType): HttpRequest {
		$this->inputType = $inputType;;
		
		return $this;
	}
	
	/**
	 * Get the cookies
	 *
	 * @return array
	 */
	public function getCookies(): array {
		return $this->cookies;
	}
	
	/**
	 * Set the cookies
	 *
	 * @param array $cookies
	 * @return HttpRequest
	 */
	protected function setCookies($cookies): HttpRequest {
		$this->cookies = $cookies;
		
		return $this;
	}
	
	/**
	 * Get the uploaded files
	 *
	 * @return array
	 */
	public function getFiles(): array {
		return $this->files;
	}
	
	/**
	 * Set the uploaded files
	 *
	 * @param array $files
	 * @return HttpRequest
	 */
	protected function setFiles($files): HttpRequest {
		$this->files = $files;
		
		return $this;
	}
	
	/**
	 * Get all input data
	 *
	 * @return array
	 */
	public function getAllData(): array {
		return $this->getInput();
	}
	
	/**
	 * Get the data by key with array as default
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getArrayData($key) {
		return $this->getInputValue($key, []);
	}
	
	/**
	 * Test if data $key is an array
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasArrayData($key = null): bool {
		return is_array($this->getData($key));
	}
	
	/**
	 * Get a data by $key, assuming $default
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getData($key, $default = null) {
		return $this->getInputValue($key, $default);
	}
	
	/**
	 * Test if data contains the $key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasData($key = null): bool {
		return $key ? $this->hasInputValue($key) : $this->hasInput();
	}
	
	/**
	 * Test if path contains a value and return it as parameter
	 *
	 * @param string $path The path to get the value
	 * @param string $value The value as output parameter
	 * @return boolean
	 */
	public function hasDataKey($path = null, &$value = null): bool {
		$v = $this->getData($path);
		if( !$v || !is_array($v) ) {
			return false;
		}
		$value = key($v);
		
		return true;
	}
	
	/**
	 * Get path values
	 *
	 * @return array
	 */
	public function getPathValues(): array {
		return $this->pathValues;
	}
	
	/**
	 * Get path value by $key, assuming $default
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return string The path value for $key
	 */
	public function getPathValue($key, $default = null): string {
		return $this->pathValues[$key] ?? $default;
	}
	
	/**
	 * Check request has path value $key
	 *
	 * @param string $key
	 * @return boolean True if it has the $key value in path
	 */
	public function hasPathValue($key): bool {
		return isset($this->pathValues[$key]);
	}
	
	/**
	 * Set the content (input & input type)
	 *
	 * @param string $content
	 * @param string $contentType
	 * @return HttpRequest
	 */
	protected function setContent($content, $contentType): HttpRequest {
		return $this->setInput($content)->setInputType($contentType);
	}
	
	/**
	 * Handle the current request as a HttpRequest one
	 * This method ends the script
	 */
	public static function handleCurrentRequest() {
		try {
			$responseException = null;
			// Process request & controller
			try {
				HttpRoute::initialize();
				static::$mainRequest = static::generateFromEnvironment();
				$response = static::$mainRequest->process();
			} catch( Throwable $e ) {
				$response = static::getDefaultController()->processException($e);
			}
			// Process response
			try {
				$response->process();
			} catch( Throwable $e ) {
				// An exception may occur when processing response, we want to process it the same way
				$responseException = $e;
				$response = static::getDefaultController()->processException($e);
				$response->process();
			}
		} catch( Throwable $e ) {
			static::showFallbackError($e, $responseException);
		}
		die();
	}
	
	/**
	 * Generate HttpRequest from environment
	 *
	 * @return HttpRequest
	 */
	public static function generateFromEnvironment(): HttpRequest {
		// Get Content type
		$method = $_SERVER['REQUEST_METHOD'];
		
		if( !empty($_SERVER['CONTENT_TYPE']) ) {
			[$inputType] = explodeList(';', $_SERVER['CONTENT_TYPE'], 2);
			$inputType = trim($inputType);
		} else {
			$inputType = 'application/x-www-form-urlencoded';
		}
		
		// Get input
		$input = null;
		if( $inputType === 'application/json' ) {
			if( $method === HttpRoute::METHOD_PUT || $method === HttpRoute::METHOD_POST ) {
				$input = json_decode(file_get_contents('php://input'), true);
			}
			if( !$input ) {
				$input = [];
			}
		} elseif( $method === HttpRoute::METHOD_PUT ) {
			parse_str(file_get_contents("php://input"), $input);
		} elseif( isset($_POST) ) {
			$input = $_POST;
		}
		$request = new static($method, parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $_GET);
		$request->setContent($input, $inputType)
			->setScheme(!empty($_SERVER['HTTPS']) ? 'https' : 'http')
			->setDomain($_SERVER['HTTP_HOST'])
			->setHeaders(getallheaders())
			->setCookies($_COOKIE)
			->setFiles($_FILES);
		
		return $request;
	}
	
	/**
	 * @return HttpController
	 */
	public static function getDefaultController(): HttpController {
		if( !static::$defaultController ) {
			$class = IniConfig::get('default_http_controller', 'Orpheus\Controller\EmptyDefaultHttpController');
			static::$defaultController = new $class(null, []);
			static::$defaultController->prepare(self::$mainRequest);
		}
		
		return static::$defaultController;
	}
	
	public static function showFallbackError(Throwable $exception, ?Throwable $responseException) {
		if( DEV_VERSION ) {
			die(convertExceptionAsHTMLPage($exception, 500));
		}
		echo <<<EOF
A fatal error occurred displaying an error.<br />
Message: {$exception->getMessage()}<br />
Code: {$exception->getCode()}<br />
EOF;
	}
	
	/**
	 * Get the name of the route class associated to a HttpRequest
	 *
	 * @return string
	 */
	public static function getRouteClass(): string {
		return HttpRoute::class;
	}
	
	/**
	 * Get the main http request or null if not a HTTP request
	 *
	 * @return HttpRequest
	 */
	public static function getMainHttpRequest(): ?HttpRequest {
		return static::$mainRequest instanceof HttpRequest ? static::$mainRequest : null;
	}
	
}
