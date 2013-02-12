<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * User access level management
 */
abstract class User {
	const ADMIN = 3;
	const LECTURER = 2;
	const STUDENT = 1;
	const ANONYMOUS = 0;
	/**
	 * Access level of current user
	 */
	private static $currentUser = self::ANONYMOUS;
	/**
	 * Initialises self, by retrieving the id of the current user
	 * from the session (which is set by the Auth class) and fetching
	 * the corresponding access level type from the database
	 *
	 * @return void
	 */
	public static function init()
	{
		if (! empty($_SESSION['uid'])) { // Check if user is logged in
			self::$currentUser = $_SESSION['accesslevel'];
		}
	}
	/**
	 * Returns the access level of the current user
	 *
	 * @return int One of the constants defined
	 *             at the top of this file
	 */
	public static function getAccessLevel()
	{
		return self::$currentUser;
	}
	/**
	 * Returns true if the current user is an admin
	 *
	 * @return bool
	 */	
	public static function isAdmin()
	{
		return self::$currentUser === self::ADMIN;
	}
	/**
	 * Returns true if the current user is a lecturer
	 *
	 * @return bool
	 */
	public static function isLecturer()
	{
		return self::$currentUser === self::LECTURER;
	}
	/**
	 * Returns true if the current user is a student
	 *
	 * @return bool
	 */
	public static function isStudent()
	{
		return self::$currentUser === self::STUDENT;
	}
}

?>