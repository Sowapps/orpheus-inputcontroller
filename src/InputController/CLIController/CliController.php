<?php
/**
 * CLIController
 */

namespace Orpheus\InputController\CLIController;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\InputController\Controller;
use Throwable;

/**
 * The CLIController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 * @method getRequest() CLIRequest
 */
abstract class CliController extends Controller {
	
	/**
	 * Print text line
	 */
	public function printLine($text = null) {
		if( $text ) {
			echo $text . "\n";
		}
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
	 */
	public function requestInputLine($text = null, $return = true): string {
		if( $text ) {
			echo $text . ($return ? "\n" : ' ');
		}
		
		return trim(fgets(STDIN));
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @param UserException $exception
	 * @param array $values
	 * @see Controller::processUserException()
	 */
	public function processUserException(UserException $exception, $values = []) {
		return CliResponse::generateFromUserException($exception);
	}
	
	/**
	 * @param Exception $exception
	 * @param array $values
	 * @return CliResponse
	 */
	public function processException(Throwable $exception, $values = []) {
		return CliResponse::generateFromException($exception);
	}
}
