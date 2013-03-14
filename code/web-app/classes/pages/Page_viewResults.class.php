<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectStudentGroup.class.php';
/**
 * This page is used by students to View their
 * results
 */
class Page_viewResults extends Page_selectStudentGroup
{
	public static function getAccessLevel()
	{
		return User::STUDENT;
	}

	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Called from the Page_selectGroup superclass
	 * when the user has selected a group
	 *
	 * @return void
	 */
	public function display($gid)
	{
		$gid = intval($gid);
		$gname = $this->getGroupName($gid);
		if(!empty($gname)) {
			$this->displayResults($gid);
		}
		else {
			$this->addNotification(
						'error',
						__('The selected group does not exist')
					);
			$this->groupSelector();
		}
	}
	/**
	 * Returns an array of assessments name and result
	 *
	 * @return array
	 */
	private function getResults($gid)
	{
		$arr = array();
		$sid = $_SESSION['uid'];
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `assessment`.`name` , `results`.`grade`, `assessment`.`isDraft`
			FROM `results`
			LEFT JOIN `assessment` ON `assessment`.`id` = `results`.`aid`
			WHERE `results`.`sid` = ? AND `assessment`.`moid` IN
			(SELECT `group`.`moid` FROM `group` WHERE `group`.`id` = ?);"
		);
		$stmt->bind_param('ii', $sid, $gid);
		$stmt->execute();
		$stmt->bind_result($aname, $grade, $isDraft);
		while ($stmt->fetch()) {
			if($isDraft) {
				$arr[$aname] = 'Not published';
			}
			else {
				$arr[$aname] = $grade . '%';
			}
		}
		return $arr;
	}
	/**
	 * displays Results
	 * @return void
	 */
	public function displayResults($gid)
	{
		$html  = '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">`';
        $html .= $this->getGroupName($gid);
        $html .= '`</li>';
        $details = $this->getResults($gid);
        if(!empty($details)) {
	     	 foreach ($details as $key => $value) {
	    		$html .= '<li>';
		        $html .= $key;
		        $html .= '<span class="ui-li-count">';
		        $html .= $value;
		        $html .= '</span>';
		        $html .= '</li>';
	    	}
		 	$html .= '</ul>';
		 	$ids = $this->getResultsIDs($gid);
		 	$this->deleteResultsNotifications($ids);
		}
		else {
			$this->addNotification(
						'notice',
						__('There are no results yet')
					);
		}
        $this->addHtml($html);
	}
	/**
	 * Returns an array of results ids
	 *
	 * @return array
	 */
	private function getResultsIDs($gid)
	{
		$arr = array();
		$sid = $_SESSION['uid'];
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `results`.`id`, `assessment`.`isDraft`
			FROM `results`
			LEFT JOIN `assessment` ON `assessment`.`id` = `results`.`aid`
			WHERE `results`.`sid` = ? AND `assessment`.`moid` IN
			(SELECT `group`.`moid` FROM `group` WHERE `group`.`id` = ?);"
		);
		$stmt->bind_param('ii', $sid, $gid);
		$stmt->execute();
		$stmt->bind_result($id, $isDraft);
		while ($stmt->fetch()) {
			if($isDraft) {
				
			}
			else {
				$arr[$id] = $id;
			}
		}
		return $arr;
	}
	/**
	 * Deletes results notifications from db
	 *
	 * @return void
	 */
	private function deleteResultsNotifications($results)
	{
		$uid = $_SESSION['uid'];
		$db = Db::getLink();
		foreach($results as $key => $value)
		{
			$stmt = $db->prepare(
				"DELETE FROM `results_notifications` WHERE `rid` = ? AND `sid` = ?"
			);
			$stmt->bind_param('ii', $key, $uid);
			$stmt->execute();
			$stmt->fetch();
			$stmt->close();
		}
	}
	

}
?>