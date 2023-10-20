<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Orpheus\Authentication;

use Orpheus\InputController\InputRequest;

interface AuthenticationManager {
	
	function getAuthentication(InputRequest $request): ?AbstractAuthentication;
	
}
