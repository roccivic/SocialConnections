<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * User access level management
 */
abstract class User {
	const SUPER = 4;
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
			$db = Db::getLink();
			$stmt = $db->prepare("SELECT type FROM user WHERE uid = ?;");
			$stmt->bind_param('s', $_SESSION['uid']);
			$stmt->execute();
			$stmt->bind_result($type);
			$stmt->fetch();
			$stmt->close();
			self::$currentUser = $type;
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
	 * Returns true if the current user is a super user
	 *
	 * @return bool
	 */
	public static function isSuper()
	{
		return self::$currentUser === self::SUPER;
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