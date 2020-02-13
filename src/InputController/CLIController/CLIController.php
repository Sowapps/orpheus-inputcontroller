<?php
/**
 * CLIController
 */

namespace Orpheus\InputController\CLIController;

use Orpheus\Exception\UserException;
use Orpheus\InputController\Controller;

/**
 * The CLIController class
 *
 * @author Florent Hazard <contact@sowapps.com>
 *
 */
abstract class CLIController extends Controller {
	
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
	public function requestInputLine($text = null, $return = true) {
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
		return $this->getRoute()->processUserException($exception, $values);
	}
	
	/**
	 * Get the CLI request
	 *
	 * @return CLIRequest
	 */
	public function getRequest() {
		return $this->request;
	}
}
