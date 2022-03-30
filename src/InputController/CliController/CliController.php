<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\InputController\CliController;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\Controller;
use Throwable;

abstract class CliController extends Controller {
	
	/**
	 * Print text line
	 */
	public function printLine(?string $text = null) {
		echo ($text ?: '') . "\n";
	}
	
	/**
	 * Print Error Line
	 *
	 * @param $text
	 */
	function printError($text) {
		fwrite(STDERR, $text . PHP_EOL);
	}
	
	/**
	 * Request a input line to user
	 *
	 * @param string|null $text
	 * @param bool $return
	 * @return string
	 */
	public function requestInputLine(?string $text = null, bool $return = true): string {
		if( $text ) {
			echo $text . ($return ? "\n" : ' ');
		}
		
		return trim(fgets(STDIN));
	}
	
	/**
	 * @param UserException $exception
	 * @param $values
	 * @return CliResponse
	 */
	public function processUserException(UserException $exception, array $values = []): CliResponse {
		return CliResponse::generateFromUserException($exception, $values);
	}
	
	/**
	 * @param Exception $exception
	 * @param array $values
	 * @return CliResponse
	 */
	public function processException(Throwable $exception, array $values = []): CliResponse {
		return CliResponse::generateFromException($exception, $values);
	}
	
}
