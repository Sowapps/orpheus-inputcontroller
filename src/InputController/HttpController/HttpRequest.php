<?php
/**
 * @author Florent Hazard <contact@sowapps.com>
 */

namespace Orpheus\InputController\HttpController;

use Exception;
use Orpheus\Config\IniConfig;
use Orpheus\InputController\ControllerRoute;
use Orpheus\InputController\Exception\ForceResponseException;
use Orpheus\InputController\InputRequest;
use Orpheus\Service\ApplicationKernel;
use Throwable;

/**
 * @method static HttpRequest getMainRequest()
 */
class HttpRequest extends InputRequest {
	
	protected static ?HttpController $defaultController = null;
	
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
	 * Constructor
	 */
	public function __construct(string $method, string $path, array $parameters, ?array $input = null) {
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
	 */
	public function getUrl(): string {
		return $this->scheme . '://' . $this->domain . $this->path . ($this->parameters ? '?' . http_build_query($this->parameters) : '');
	}
	
	/**
	 * Generate new URL for this request
	 *
	 * @param array $values Force parameters
	 */
	public function formatUrl(array $values = []): string {
		$values += $this->pathValues;
		return $this->route->formatUrl($values, $this->getParameters());
	}
	
	/**
	 * Get all available routes
	 *
	 * @return HttpRoute[]
	 * @see InputRequest::getRoutes()
	 */
	public function getRoutes(): array {
		// Flatten [method=>routes] array to [routes]
		return [...HttpRoute::getRoutes()];
	}
	
	public function redirect(ControllerRoute $route): RedirectHttpResponse {
		return new RedirectHttpResponse(u($route->getName()));
	}
	
	/**
	 * Get the method
	 */
	public function getMethod(): string {
		return $this->method;
	}
	
	/**
	 * Set the method
	 */
	protected function setMethod(string $method): HttpRequest {
		$this->method = $method;
		
		return $this;
	}
	
	/**
	 * Test if this is a GET request
	 */
	public function isGet(): bool {
		return $this->method === HttpRoute::METHOD_GET;
	}
	
	/**
	 * Test if this is a POST request
	 */
	public function isPost(): bool {
		return $this->method === HttpRoute::METHOD_POST;
	}
	
	/**
	 * Test if this is a PUT request
	 */
	public function isPut(): bool {
		return $this->method === HttpRoute::METHOD_PUT;
	}
	
	/**
	 * Test if this is a DELETE request
	 */
	public function isDelete(): bool {
		return $this->method === HttpRoute::METHOD_DELETE;
	}
	
	/**
	 * Get the scheme
	 */
	public function getScheme(): string {
		return $this->scheme;
	}
	
	/**
	 * Set the scheme
	 */
	protected function setScheme(string $scheme): HttpRequest {
		$this->scheme = $scheme;
		
		return $this;
	}
	
	/**
	 * Get the host domain
	 */
	public function getDomain(): string {
		return $this->domain;
	}
	
	/**
	 * Set the host domain
	 *
	 * @return $this
	 */
	protected function setDomain(string $domain): HttpRequest {
		$this->domain = $domain;
		
		return $this;
	}
	
	/**
	 * Test incoming request is over php max post size
	 *
	 * @throws Exception
	 */
	public function isPostSiteOverLimit(): bool {
		return $this->getHeaderContentLength() > $this->getConfigPostMaxSize();
	}
	
	/**
	 * Get the header Content-Length
	 */
	public function getHeaderContentLength(): ?int {
		return isset($this->headers['Content-Length']) ? (int) $this->headers['Content-Length'] : null;
	}
	
	/**
	 * Get php config for max post size
	 *
	 * @throws Exception
	 */
	public function getConfigPostMaxSize(): int {
		return parseHumanSize(ini_get('post_max_size'));
	}
	
	/**
	 * Get the headers
	 */
	public function getHeaders(): array {
		return $this->headers;
	}
	
	/**
	 * Set the headers
	 */
	protected function setHeaders(array $headers): HttpRequest {
		$this->headers = $headers;
		
		return $this;
	}
	
	/**
	 * Get the input type
	 */
	public function getInputType(): string {
		return $this->inputType;
	}
	
	/**
	 * Set the input type
	 */
	protected function setInputType(string $inputType): HttpRequest {
		$this->inputType = $inputType;
		
		return $this;
	}
	
	/**
	 * Get the cookies
	 */
	public function getCookies(): array {
		return $this->cookies;
	}
	
	/**
	 * Set the cookies
	 */
	protected function setCookies(array $cookies): HttpRequest {
		$this->cookies = $cookies;
		
		return $this;
	}
	
	/**
	 * Get the uploaded files
	 */
	public function getFiles(): array {
		return $this->files;
	}
	
	/**
	 * Set the uploaded files
	 */
	protected function setFiles(array $files): HttpRequest {
		$this->files = $files;
		
		return $this;
	}
	
	/**
	 * Get all input data
	 */
	public function getAllData(): array {
		return $this->getInput();
	}
	
	/**
	 * Get the data by key with array as default
	 */
	public function getArrayData(string $key): mixed {
		return $this->getInputValue($key, []);
	}
	
	/**
	 * Test if data $key is an array
	 */
	public function hasArrayData(string $key): bool {
		return is_array($this->getData($key));
	}
	
	/**
	 * Get a data by $key, assuming $default
	 */
	public function getData(string $key, mixed $default = null): mixed {
		return $this->getInputValue($key, $default);
	}
	
	/**
	 * Test if data contains the $key
	 */
	public function hasData(?string $key = null): bool {
		return $key ? $this->hasInputValue($key) : $this->hasInput();
	}
	
	/**
	 * Test if path contains a value and return it as parameter
	 *
	 * @param string|null $path The path to get the value
	 * @param string|null $key The value as output parameter
	 */
	public function hasDataKey(?string $path = null, ?string &$key = null): bool {
		$array = $this->getData($path);
		if( !$array || !is_array($array) ) {
			return false;
		}
		$key = key($array);
		
		return true;
	}
	
	/**
	 * Get path values
	 */
	public function getPathValues(): array {
		return $this->pathValues;
	}
	
	/**
	 * Get path value by $key, assuming $default
	 *
	 * @return string|null The path value for $key
	 */
	public function getPathValue(string $key, mixed $default = null): ?string {
		return $this->pathValues[$key] ?? $default;
	}
	
	/**
	 * Check request has path value $key
	 *
	 * @return boolean True if it has the $key value in path
	 */
	public function hasPathValue(string $key): bool {
		return isset($this->pathValues[$key]);
	}
	
	/**
	 * Set the content (input & input type)
	 */
	protected function setContent(array|string|null $content, string $contentType): HttpRequest {
		return $this->setInput($content)->setInputType($contentType);
	}
	
	/**
	 * Handle the current request as a HttpRequest one
	 * This method ends the script
	 */
	public static function handleCurrentRequest(): void {
		try {
			$responseException = null;
			// Process request & controller
			try {
				HttpRoute::initialize();
				$request = static::generateFromEnvironment();
				static::$mainRequest = ApplicationKernel::get()->configureMainRequest($request);
				$response = static::$mainRequest->process();
			} catch( ForceResponseException $exception ) {
				$response = $exception->getResponse();
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
			$input = [];
			parse_str(file_get_contents("php://input"), $input);
		} elseif( isset($_POST) ) {
			$input = $_POST;
		}
		$path = HttpRoute::parsePath($_SERVER['REQUEST_URI']);
		$request = new static($method, $path, $_GET);
		$request->setContent($input, $inputType)
			->setScheme(!empty($_SERVER['HTTPS']) ? 'https' : 'http')
			->setDomain($_SERVER['HTTP_HOST'])
			->setHeaders(getallheaders())
			->setCookies($_COOKIE)
			->setFiles($_FILES);
		
		return $request;
	}
	
	public static function getDefaultController(): HttpController {
		if( !static::$defaultController ) {
			$class = IniConfig::get('default_http_controller', 'Orpheus\Controller\EmptyDefaultHttpController');
			static::$defaultController = new $class(null, []);
			if( self::$mainRequest ) {
				static::$defaultController->prepare(self::$mainRequest);
			} // Main request could have failed to generate, so we are displaying the exception.
		}
		
		return static::$defaultController;
	}
	
	public static function showFallbackError(Throwable $exception, ?Throwable $responseException): void {
		if( DEBUG_ENABLED ) {
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
	 */
	public static function getRouteClass(): string {
		return HttpRoute::class;
	}
	
	/**
	 * Get the main http request or null if not an HTTP request
	 */
	public static function getMainHttpRequest(): ?HttpRequest {
		return static::$mainRequest instanceof HttpRequest ? static::$mainRequest : null;
	}
	
}
