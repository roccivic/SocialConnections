<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * This class stores a unique link to the current database connection
 * A singleton of some sort...
 */
abstract class Db {
	/**
	 * A reference to the current database connection
	 */
	private static $db;
	/**
	 * Returns a unique reference to
	 * the current database connection
	 *
	 * @return mysqli object
	 */
	public static function getLink()
	{
		if (empty(self::$db)) {
			self::$db = new mysqli(
				CONFIG::DB_HOST,
				CONFIG::DB_USER,
				CONFIG::DB_PWD,
				CONFIG::DB_DBNAME
			);
		}
		return self::$db;
	}
}

?>