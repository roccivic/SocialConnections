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
	public function __construct($haveCreateBtn = null, $disableAjax = null)
	{
		parent::__construct($haveCreateBtn, $disableAjax);
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
				FROM `group`
				INNER JOIN `moduleoffering_lecturer`
				ON `group`.`moid` = `moduleoffering_lecturer`.`moid`
				INNER JOIN `moduleoffering`
				ON `moduleoffering_lecturer`.`moid` = `moduleoffering`.`id`
				WHERE `lid` = ?
				AND `moduleoffering`.`year` = ?
				AND `moduleoffering`.`term` = ?";
	}
	/**
	 * Retrieves a list of terms that
	 * the user is registered for
	 *
	 * @return @array
	 */
    protected function getTerms($lid)
    {
        $arr = array();
        $db = Db::getLink();
        $stmt = $db->prepare(
            'SELECT `year`, `term`
            FROM `moduleoffering`
            INNER JOIN `group`
            ON `group`.`moid` = `moduleoffering`.`id`
            INNER JOIN `moduleoffering_lecturer`
            ON `moduleoffering_lecturer`.`moid` = `moduleoffering`.`id`
			INNER JOIN `lecturer`
            ON `moduleoffering_lecturer`.`lid` = `lecturer`.`id`
            WHERE `moduleoffering_lecturer`.`lid` = ?
            GROUP BY `year`,`term`
            ORDER BY `year` DESC, `term` DESC;'
        );
        $stmt->bind_param('i', $lid);
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