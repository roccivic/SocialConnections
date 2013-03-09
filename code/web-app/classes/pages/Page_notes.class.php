<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectStudentGroup.class.php';
/**
 * This page is used by students to download notes
 */
class Page_notes extends Page_selectStudentGroup {
	public static function getAccessLevel()
	{
		return User::STUDENT;
	}

	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Called from the Page_selectStudentGroup superclass
	 * when the user has selected a department
	 *
	 * @return void
	 */
	public function display($gid) 
	{
		$gid = intval($gid);
		$gName = $this->getGroupName($gid);
		if(!empty($gName))
		{
			if($this->isPartOfGroup($gid))
			{
				$this->displayNotes($gid);
			}
			else
		{
			$this->addNotification(
						'error',
						__('You are not a student in that group!')
					);
			$this->groupSelector();
		}
		}
		else
		{
			$this->addNotification(
						'error',
						__('Invalid group selected.')
					);
			$this->groupSelector();
		}
	}
	/**
	 * Returns group name
	 *
	 * @return string
	 */
	private function getGroupName($gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `name` FROM `group` WHERE `id`=?"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
	}
	/**
	 * Checks if student is in Group
	 *
	 * @return string
	 */
	private function isPartOfGroup($gid)
	{
		$uid = $_SESSION['uid'];
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `sid` FROM `group_student` WHERE `gid`= ? AND `sid` = ?"
		);
		$stmt->bind_param('ii', $gid, $uid);
		$stmt->execute();
		$stmt->bind_result($sid);
		$stmt->fetch();
		$stmt->close();
		if(!empty($sid))
		{
			return true;
		}
		return false;
	}
	/**
	 * Returns an array of urls
	 *
	 * @return array
	 */
	private function getUrls($gid)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `id`, `url` FROM `notes` WHERE `gid` = ?"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($id, $url);
		while ($stmt->fetch()) {
			$arr[$id] = $url;
		}
		$stmt->close();
		return $arr;
	}
	/**
	 * Displays a list of notes
	 *
	 * @return array
	 */
	private function displayNotes($gid)
	{
		$notes = $this->getUrls($gid);
		if (count($notes) > 0) {
			$html = $this->printListHeader($gid);
			foreach($notes as $key => $value) {
				$html .= $this->printListItem($value);
			}
			$html .= $this->printListFooter();
			$this->addHtml($html);
		} else {
			$this->addNotification(
				'warning',
				__('The are no notes to display')
			);
		}
	}
	/**
	 * Prints the header for the list of urls
	 *
	 * @return void
	 */
	private function printListHeader($gid)
	{
		$html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Select notes');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of assessments
	 *
	 * @return void
	 */
	private function printListItem($url)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="%s">%s</a></li>',
	        	$url,
	        	$url
	        )
        );
	}
	/**
	 * Prints the footer for the list of assessments
	 *
	 * @return void
	 */
	private function printListFooter()
	{
        $this->addHtml('</ul>');
	}
}