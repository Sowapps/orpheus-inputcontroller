<?php
/**
 * HTTPRequest
 */

namespace Orpheus\InputController\HTTPController;

use Exception;
use Orpheus\Config\IniConfig;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\InputRequest;
use stdClass;
use Throwable;

/**
 * The HTTPRequest class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
class HTTPRequest extends InputRequest {
	
	/** @var HTTPController */
	protected static $defaultController;
	
	/**
	 * The used method for this request
	 *
	 * @var string
	 */
	protected $method;
	
	/**
	 * The scheme used to access this route
	 *
	 * @var string
	 */
	protected $scheme;
	
	/**
	 * The request host domain name
	 *
	 * @var string
	 */
	protected $domain;
	
	/**
	 * The headers sent to this route
	 *
	 * @var array
	 */
	protected $headers;
	
	/**
	 * The cookies sent to this route
	 *
	 * @var array
	 */
	protected $cookies;
	
	/**
	 * The uploaded files sent to this route
	 *
	 * @var array
	 */
	protected $files;
	
	/**
	 * The input content type
	 *
	 * @var string
	 * @see https://en.wikipedia.org/wiki/Media_type
	 */
	protected $inputType;
	
	/**
	 * The values in path
	 *
	 * @var \stdClass
	 */
	protected $pathValues;
	
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
	public function getURL() {
		return $this->scheme . '://' . $this->domain . $this->path . ($this->parameters ? '?' . http_build_query($this->parameters) : '');
	}
	
	/**
	 * Find a matching route according to the request
	 *
	 * @param boolean $alternative Is this looking for an alternative route ?
	 * @return Route
	 */
	public function findFirstMatchingRoute($alternative = false) {
		/* @var ControllerRoute $route */
		foreach( $this->getRoutes() as $methodRoutes ) {
			if( !isset($methodRoutes[$this->method]) ) {
				continue;
			}
			/* @var $route HTTPRoute */
			$route = $methodRoutes[$this->method];
			$values = null;
			if( $route->isMatchingRequest($this, $values, $alternative) ) {
				$this->pathValues = (object) $values;
				return $route;
			}
		}
		return null;
	}
	
	/**
	 * Get all available routes
	 *
	 * @return HTTPRoute[]
	 * @see InputRequest::getRoutes()
	 */
	public function getRoutes() {
		return HTTPRoute::getRoutes();
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @param ControllerRoute $route
	 * @return RedirectHTTPResponse
	 * @see InputRequest::redirect()
	 */
	public function redirect(ControllerRoute $route) {
		return new RedirectHTTPResponse(u($route->getName()));
	}
	
	/**
	 * Get the method
	 *
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}
	
	/**
	 * Set the method
	 *
	 * @param string $method
	 * @return HTTPRequest
	 */
	protected function setMethod($method) {
		$this->method = $method;
		return $this;
	}
	
	/**
	 * Test if this is a GET request
	 *
	 * @return boolean
	 */
	public function isGET() {
		return $this->method === HTTPRoute::METHOD_GET;
	}
	
	/**
	 * Test if this is a POST request
	 *
	 * @return boolean
	 */
	public function isPOST() {
		return $this->method === HTTPRoute::METHOD_POST;
	}
	
	/**
	 * Test if this is a PUT request
	 *
	 * @return boolean
	 */
	public function isPUT() {
		return $this->method === HTTPRoute::METHOD_PUT;
	}
	
	/**
	 * Test if this is a DELETE request
	 *
	 * @return boolean
	 */
	public function isDELETE() {
		return $this->method === HTTPRoute::METHOD_DELETE;
	}
	
	/**
	 * Get the scheme
	 *
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}
	
	/**
	 * Set the scheme
	 *
	 * @param string $scheme
	 * @return HTTPRequest
	 */
	protected function setScheme($scheme) {
		$this->scheme = $scheme;
		return $this;
	}
	
	/**
	 * Get the host domain
	 *
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}
	
	/**
	 * Set the host domain
	 *
	 * @param string $domain
	 * @return $this
	 */
	protected function setDomain($domain) {
		$this->domain = $domain;
		return $this;
	}
	
	/**
	 * Test incoming request is over php max post size
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isPostSiteOverLimit() {
		return $this->getHeaderContentLength() > $this->getConfigPostMaxSize();
	}
	
	/**
	 * Get the header Content-Length
	 *
	 * @return int
	 */
	public function getHeaderContentLength() {
		return isset($this->headers['Content-Length']) ? (int) $this->headers['Content-Length'] : null;
	}
	
	/**
	 * Get php config for max post size
	 *
	 * @return int
	 * @throws Exception
	 */
	public function getConfigPostMaxSize() {
		return parseHumanSize(ini_get('post_max_size'));
	}
	
	/**
	 * Get the headers
	 *
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}
	
	/**
	 * Set the headers
	 *
	 * @param array $headers
	 * @return HTTPRequest
	 */
	protected function setHeaders($headers) {
		$this->headers = $headers;
		return $this;
	}
	
	/**
	 * Get the input type
	 *
	 * @return string
	 */
	public function getInputType() {
		return $this->inputType;
	}
	
	/**
	 * Set the input type
	 *
	 * @param string $inputType
	 * @return HTTPRequest
	 */
	protected function setInputType($inputType) {
		$this->inputType = $inputType;;
		return $this;
	}
	
	/**
	 * Get the cookies
	 *
	 * @return array
	 */
	public function getCookies() {
		return $this->cookies;
	}
	
	/**
	 * Set the cookies
	 *
	 * @param array $cookies
	 * @return HTTPRequest
	 */
	protected function setCookies($cookies) {
		$this->cookies = $cookies;
		return $this;
	}
	
	/**
	 * Get the uploaded files
	 *
	 * @return array
	 */
	public function getFiles() {
		return $this->files;
	}
	
	/**
	 * Set the uploaded files
	 *
	 * @param array $files
	 * @return HTTPRequest
	 */
	protected function setFiles($files) {
		$this->files = $files;
		return $this;
	}
	
	/**
	 * Get all input data
	 *
	 * @return array
	 */
	public function getAllData() {
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
	public function hasArrayData($key = null) {
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
	public function hasData($key = null) {
		return $key ? $this->hasInputValue($key) : $this->hasInput();
	}
	
	/**
	 * Test if path contains a value and return it as parameter
	 *
	 * @param string $path The path to get the value
	 * @param string $value The value as ouput parameter
	 * @return boolean
	 */
	public function hasDataKey($path = null, &$value = null) {
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
	 * @return stdClass
	 */
	public function getPathValues() {
		return $this->pathValues;
	}
	
	/**
	 * Get path value by $key, assuming $default
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return string The path value for $key
	 */
	public function getPathValue($key, $default = null) {
		return $this->hasPathValue($key) ? $this->pathValues->$key : $default;
	}
	
	/**
	 * Check request has path value $key
	 *
	 * @param string $key
	 * @return boolean True if it has the $key value in path
	 */
	public function hasPathValue($key) {
		return isset($this->pathValues->$key);
	}
	
	/**
	 * Handle the current request as a HTTPRequest one
	 * This method ends the script
	 */
	public static function handleCurrentRequest() {
		
		try {
			$responseException = null;
			// Process request & controller
			try {
				HTTPRoute::initialize();
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
	 * Generate HTTPRequest from environment
	 *
	 * @return HTTPRequest
	 */
	public static function generateFromEnvironment() {
		
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
			if( $method === HTTPRoute::METHOD_PUT || $method === HTTPRoute::METHOD_POST ) {
				$input = json_decode(file_get_contents('php://input'), true);
			}
			if( !$input ) {
				$input = [];
			}
		} elseif( $method === HTTPRoute::METHOD_PUT ) {
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
	 * Set the content (input & input type)
	 *
	 * @param string $content
	 * @param string $contentType
	 * @return HTTPRequest
	 */
	protected function setContent($content, $contentType) {
		return $this->setInput($content)->setInputType($contentType);
	}
	
	/**
	 * @return HTTPController
	 */
	public static function getDefaultController() {
		if( !static::$defaultController ) {
			$class = IniConfig::get('default_http_controller', 'Orpheus\Controller\EmptyDefaultHttpController');
			static::$defaultController = new $class(null, []);
		}
		return static::$defaultController;
	}
	
	public static function showFallbackError(Throwable $exception, ?Throwable $responseException) {
		if( DEV_VERSION ) {
			convertExceptionAsHTMLPage($exception, 500, null);
		}
		echo <<<EOF
A fatal error occurred displaying an error.<br />
Message: {$exception->getMessage()}<br />
Code: {$exception->getCode()}<br />
EOF;
	}
	
	/**
	 * Get the name of the route class associated to a HTTPRequest
	 *
	 * @return string
	 */
	public static function getRouteClass() {
		return '\Orpheus\InputController\HTTPController\HTTPRoute';
	}
	
	/**
	 * Get the main http request or null if not a HTTP request
	 *
	 * @return HTTPRequest
	 */
	public static function getMainHTTPRequest() {
		return static::$mainRequest instanceof HTTPRequest ? static::$mainRequest : null;
	}
}
