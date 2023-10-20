<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\Locale;

use Orpheus\Initernationalization\TranslationService;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\InputRequest;

class LocaleRouting {
	
	private InputRequest $request;
	
	/**
	 * LocaleService constructor
	 */
	public function __construct(InputRequest $request) {
		$this->request = $request;
	}
	
	public function getDomainExtension(): ?string {
		return $this->request instanceof HttpRequest ? pathinfo($this->request->getDomain(), PATHINFO_EXTENSION) : null;
	}
	
	public function getDomain(): ?string {
		return $this->request instanceof HttpRequest ? $this->request->getDomain() : null;
	}
	
	/**
	 * @return array [string $locale, HttpRequest $request] if containing a request, else an array of null values
	 */
	public function extractLocaleFromPath(): array {
		if( $this->request instanceof HttpRequest ) {
			// TODO Maybe if locale is invalid, this is not a locale, test & fix it
			if( $this->matchPath('#^/(\w{2}(?:\-\w{2,3})?)(/.*)?$#i', $matches) ) {
				// We found a locale and extract the left path
				[, $locale, $leftPath] = $matches + [2 => null];
				
				return [strtr($locale, '-', '_'), $this->request->cloneWithPath($leftPath ?: '/')];
			}
		}
		return [null, null];
	}
	
	public function matchPath(string $regex, ?array &$matches): string {
		return $this->request->matchPath($regex, $matches);
	}
	
	public function getCookieLocale(): ?string {
		return $this->request instanceof HttpRequest ? ($this->request->getCookies()['locale'] ?? null) : null;
	}
	
	public function getPreferredLocale(): ?string {
		$appLocales = TranslationService::formatAssociatedLocales(TranslationService::guessAvailableLocales());
		foreach( $this->getExpectedLocales() as $locale ) {
			if( isset($appLocales[$locale]) ) {
				// Return first matching locale
				return $locale;
			}
		}
		// No matching locale between browser Accept-Language and the application
		
		return null;
	}
	
	public function getExpectedLocales(): array {
		if( $this->request instanceof HttpRequest ) {
			return self::getPreferredHttpLocales($this->request);
		}
		
		return [];
	}
	
	public static function getPreferredHttpLocales(HttpRequest $request): array {
		//Example: en-US,en;q=0.9,fr-FR;q=0.8,fr;q=0.7
		$acceptLanguageString = $request->getHeaders()['Accept-Language'] ?? null;
		$locales = [];
		if( $acceptLanguageString ) {
			$httpLocales = explode(',', $acceptLanguageString);
			foreach( $httpLocales as $httpLocale ) {
				$locales[] = static::getLocaleFromHttpFormat($httpLocale);
			}
		}
		return $locales;
	}
	
	public static function getLocaleFromHttpFormat($httpLocale): string {
		if( strlen($httpLocale) > 7 ) {
			[$httpLocale,] = explode(';', $httpLocale);
		}
		if( strlen($httpLocale) > 3 ) {
			$httpLocale = strtr($httpLocale, '-', '_');
		}
		
		return $httpLocale;
	}
	
	public function getRequest(): InputRequest {
		return $this->request;
	}
	
}
