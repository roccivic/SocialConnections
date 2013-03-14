<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * This page if a logged-in user is not allowed
 * to access a certain other page
 */
class Page_authError extends Page {
	public function __construct()
	{
		parent::__construct();
		$this->addNotification(
			'error',
			__('You don\'t have the permissions to view this page.')
		);
	}
}

?>