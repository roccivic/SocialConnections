<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/Page.class.php';

/**
 *
 * Access level restrictions are implemented here
 */
abstract class PageFactory {
	/**
	 * Instantiates the appropriate page sub class given a page name
	 *
	 * @return new Page
	 */
	public static function getInstance($name)
	{
		if (User::getAccessLevel() === User::ANONYMOUS) {
			// Anonymous users are always and only shown the login form
			include_once 'classes/pages/Page_login.class.php';
			$retval = new Page_Login();
		} else if (file_exists('classes/pages/Page_' . $name . '.class.php')) {
			// The requested page exists
			include_once 'classes/pages/Page_' . $name . '.class.php';
			$className = 'Page_' . $name;
			$retval = new $className();
			// Check access level of current user
			if (User::getAccessLevel() < $retval->getAccessLevel()) {
				include_once 'classes/pages/Page_authError.class.php';
				$retval = new Page_authError();
			}
			$retval = new $className();
			if (Config::DISPLAY_ERRORS) {
				foreach ($retval->getRequiredParams() as $param) {
					if (! isset($_REQUEST[$param])) {
						$retval->addNotification(
							'warning',
							sprintf(
								__('Warning: Missing required parameter "%s"'),
								$param
							)
						);
					}
				}
			}
		} else {
			include_once 'classes/pages/Page_main.class.php';
			$retval = new Page_Main();
			if (! empty($name)) {
				$retval->addNotification(
					'warning',
					__('The requested page does not exist')
				);
			}
		}
		return $retval;
	}
}

?>