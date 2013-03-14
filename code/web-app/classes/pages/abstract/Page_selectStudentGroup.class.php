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

	public function __construct($disableAjax = null)
	{
		parent::__construct(null, $disableAjax);
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
                WHERE `sid` = ?
                AND `moduleoffering`.`year` = ?
                AND `moduleoffering`.`term` = ?";
    }
    /**
     * Retrieves a list of terms that
     * the user is registered for
     *
     * @return @array
     */
    protected function getTerms($sid)
    {
        $arr = array();
        $db = Db::getLink();
        $stmt = $db->prepare(
            'SELECT `year`, `term`
            FROM `moduleoffering`
            INNER JOIN `group`
            ON `group`.`moid` = `moduleoffering`.`id`
            INNER JOIN `group_student`
            ON `group_student`.`gid` = `group`.`id`
            INNER JOIN `student`
            ON `student`.`id` = `group_student`.`sid`
            WHERE `student`.`id` = ?
            GROUP BY `year`,`term`
            ORDER BY `year` DESC, `term` DESC;'
        );
        $stmt->bind_param('i', $sid);
        $stmt->execute();
        $stmt->bind_result($year, $term);
        while ($stmt->fetch()) {
            $arr[] = array(
                'year' => $year,
                'term' => $term
            );
        }
        $stmt->close();
        return $arr;
    }
}