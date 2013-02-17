<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/pages/abstract/Page_selectGroup.class.php';

/**
 * Abstract class that implements selection of groups
 * for a student. Extend this class if your page must
 * show a list of groups before doing something else.
 */
abstract class Page_selectStudentGroup extends Page_selectGroup {
    /**
     * A user must be at least a student to view this page
     */
    public static function getAccessLevel()
    {
        return User::STUDENT;
    }

	public function __construct()
	{
		parent::__construct();
	}
    /**
     * This function must be implemented in a subclass
     * Returns an SQL query for getting the groups
     *
     * @return string
     */
    protected function getQuery()
    {
        return "SELECT `gid`,`module`.`name`
                FROM `group_student` INNER JOIN `group` 
                ON `group`.`id` = `group_student`.`gid`
                INNER JOIN `moduleoffering`
                ON `group`.`moid` = `moduleoffering`.`id`
                INNER JOIN `module`
                ON `moduleoffering`.`mid` = `module`.`id`
                WHERE `sid` = ?";
    }
}