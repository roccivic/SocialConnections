<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/pages/abstract/Page_selectGroup.class.php';

/**
 * Abstract class that implements selection of groups
 * for a lecturer. Extend this class if your page must
 * show a list of groups before doing something else.
 */
abstract class Page_selectLecturerGroup extends Page_selectGroup {
	/**
     * A user must be at least a student to view this page
     */
    public static function getAccessLevel()
    {
        return User::LECTURER;
    }
	public function __construct($haveCreateBtn = null)
	{
		parent::__construct($haveCreateBtn);
	}
	/**
	 * This function must be implemented in a subclass
	 * Returns an SQL query for getting the groups
	 *
	 * @return string
	 */
	protected function getQuery()
	{
        return "SELECT `group`.`id`, `group`.`name`
				FROM `group` INNER JOIN `moduleoffering_lecturer`
				ON `group`.`moid` = `moduleoffering_lecturer`.`moid`
				WHERE `lid` = ?";
	}
}