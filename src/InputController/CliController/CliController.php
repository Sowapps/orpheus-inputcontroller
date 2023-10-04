<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\CliController;

use Orpheus\Exception\UserException;
use Orpheus\InputController\AbstractController;
use Throwable;

abstract class CliController extends AbstractController {
	
	/**
	 * Print text
	 */
	public function print(?string $text = null): void {
		echo $text ?: '';
	}
	/**
	 * Print text line
	 */
	public function printLine(?string $text = null): void {
		$this->print(($text ?: '') . "\n");
	}
	
	/**
	 * Print Error Line
	 */
	function printError($text): void {
		fwrite(STDERR, $text . PHP_EOL);
	}
	
	/**
	 * Request a input line to user
	 */
	public function requestInputLine(?string $text = null, bool $return = true): string {
		if( $text ) {
			echo $text . ($return ? "\n" : ' ');
		}
		
		return trim(fgets(STDIN));
	}
	
	public function processUserException(UserException $exception, array $values = []): CliResponse {
		return CliResponse::generateFromUserException($exception, $values);
	}
	
	public function processException(Throwable $exception, array $values = []): CliResponse {
		return CliResponse::generateFromException($exception, $values);
	}
	
}
