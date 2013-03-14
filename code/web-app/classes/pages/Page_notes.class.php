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
			if($this->isStudentInGroup($_SESSION['uid'], $gid))
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
			$html = $this->printListHeaderNotes($gid);
			foreach($notes as $key => $value) {
				$array = explode("/", $value);
				end($array);
				$fname = current($array);
				$html .= $this->printListItemNotes($value, $fname);
			}
			$html .= $this->printListFooterNotes();
			$this->deleteNotesNotifications($notes);
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
	private function printListHeaderNotes($gid)
	{
		$html = '';
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
	private function printListItemNotes($url, $name)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="%s">%s</a></li>',
	        	$url,
	        	$name
	        )
        );
	}
	/**
	 * Prints the footer for the list of assessments
	 *
	 * @return void
	 */
	private function printListFooterNotes()
	{
        $this->addHtml('</ul>');
	}
	/**
	 * Deletes notifications from db
	 *
	 * @return void
	 */
	private function deleteNotesNotifications($notes)
	{
		$uid = $_SESSION['uid'];
		$db = Db::getLink();
		foreach($notes as $key => $value)
		{
			$stmt = $db->prepare(
				"DELETE FROM `notes_notifications` WHERE `nid` = ? AND `sid` = ?"
			);
			$stmt->bind_param('ii', $key, $uid);
			$stmt->execute();
			$stmt->fetch();
			$stmt->close();
		}
	}
}