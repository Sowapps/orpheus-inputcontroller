<?php
/**
 * CLIController
 */

namespace Orpheus\InputController\CLIController;

use Orpheus\InputController\Controller;
use Orpheus\Exception\UserException;

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
	public function printLine($text=null) {
		if( $text ) {
			echo $text."\n";
		}
	}
	
	/**
	 * Request a input line to user
	 */
	public function requestInputLine($text=null) {
		if( $text ) {
			echo $text."\n";
		}
		return trim(fgets(STDIN));
	}
	
	/**
	 * Run this controller
	 * 
	 * @param CLIRequest $request
	 * @return CLIResponse
	 */
	public abstract function run(CLIRequest $request);

	/**
	 * Prepare controller for request before running
	 * 
	 * @param CLIRequest $request
	 */
	public function prepare(CLIRequest $request) {
	}

	/**
	 * Before running controller
	 * 
	 * @param CLIRequest $request
	 */
	public function preRun(CLIRequest $request) {
	}
	
	/**
	 * After running the controller
	 * 
	 * @param CLIRequest $request
	 * @param CLIResponse $response
	 */
	public function postRun(CLIRequest $request, CLIResponse $response) {
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Orpheus\InputController\Controller::processUserException()
	 * @param UserException $exception
	 * @param array $values
	 */
	public function processUserException(UserException $exception, $values=array()) {
		return $this->getRoute()->processUserException($exception, $values);
	}
}
