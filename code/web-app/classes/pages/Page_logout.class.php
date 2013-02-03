<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * This page destroys the user's session
 * and redirects him back to the login page
 */
class Page_Logout extends Page {
	public function __construct()
	{
		parent::__construct();
		Auth::logout();
		$this->redirect();
	}
}

?>