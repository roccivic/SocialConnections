<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/pages/abstract/Page_selectStudentGroup.class.php';

/**
 * This page is used by students to view their attendance
 */
class Page_makeExcuse extends Page_selectStudentGroup {
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Shows a page after a group has been selected
	 *
	 * @return void
	 */
	protected function display($gid) {
		$aid = ! empty($_REQUEST['aid']) ? intval($_REQUEST['aid']) : 0;
		$excuse = ! empty($_REQUEST['excuse']) ? $_REQUEST['excuse'] : '';
		if ($aid > 0 && ! empty($_REQUEST['process'])) {

			$db = Db::getLink();
			$stmt = $db->prepare(
				"SELECT count(*) FROM `student_attendance`
				WHERE sid = ?
				AND aid = ?;"
			);
			$stmt->bind_param('ii', $_SESSION['uid'], $aid);
			$stmt->execute();
			$stmt->bind_result($valid);
			$stmt->fetch();
			$stmt->close();

			if ($valid) {
				if ($this->validate($excuse, $aid)) {
					if ($this->save($aid, $excuse)) {
						$this->addNotification(
							'notice',
							__('Your excuse was succeessfully saved.')
						);
						$this->groupSelector();
					} else {
						$this->addNotification(
							'error',
							__('An error occured while processing your request.')
						);
						$this->printExcuseForm($gid, $aid, $excuse);
					}
				} else {
					$this->addNotification(
						'error',
						__('The values in the form were invalid. Please try again.')
					);
					$this->printExcuseForm($gid, $aid, $excuse);
				}
			} else {
				$this->addNotification(
					'error',
					__('An invalid class was selected.')
				);
				$this->groupSelector();
			}
		} else if ($aid > 0) {
			$this->printExcuseForm($gid, $aid, '');
		} else {
			$classes = $this->getMissedClasses($gid);
			if (count($classes)) {
				$this->printExcuseListHeader();
				foreach ($classes as $aid => $timestamp) {
					$this->printExcuseListItem($gid, $aid, $timestamp);
				}
				$this->printExcuseListFooter();
			} else {
				$this->addNotification(
					'notice',
					__('You have no classes in this module to excuse yourself for')
				);
			}
		}
	}

	private function getMissedClasses($gid) {
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `aid`, `timestamp` FROM `student_attendance`
			INNER JOIN `attendance`
			ON `attendance`.`id` = `student_attendance`.`aid`
			WHERE `sid` = ?
			AND `gid` = ?
			AND `present` = 0
			AND `excuse` IS NULL
			AND `timestamp` > CURRENT_TIMESTAMP - INTERVAL 2 WEEK;"
		);
		$stmt->bind_param('ii', $_SESSION['uid'], $gid);
		$stmt->execute();
		$stmt->bind_result($aid, $timestamp);
		while ($stmt->fetch()) {
			$arr[$aid] = $timestamp;
		}
		return $arr;
	}
	/**
	 * Prints the header for the list of groups
	 *
	 * @return void
	 */
	private function printExcuseListHeader()
	{
		$html  = '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= __('Missed Classes');
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of groups
	 *
	 * @return void
	 */
	private function printExcuseListItem($gid, $aid, $timestamp)
	{
        $this->addHtml(
	        sprintf(
	        	'<li><a href="?action=makeExcuse&gid=%d&aid=%d">%s</a>%s</li>',
	        	$gid,
	        	$aid,
	        	date("l, j F", strtotime($timestamp)),
	        	'<span class="ui-li-count">' . date("ga", strtotime($timestamp)) . '</span>'
	        )
        );
	}
	/**
	 * Prints the footer for the list of groups
	 *
	 * @return void
	 */
	private function printExcuseListFooter()
	{
        $this->addHtml('</ul>');
	}

	private function printExcuseForm($gid, $aid, $excuse)
	{
		$html  = '<h3>';
		$html .= sprintf(
			__('Group `%s`'),
			$this->getGroupName($gid)
		);
		$html .= '</h3>';
		$html .= '<h4>' . date("l, j F", $this->getAttendanceTime($aid)) . '</h4>';
		$html .= '<form action="?action=makeExcuse" method="post">';
		$html .= '<input type="hidden" name="gid" value="' . $gid . '" />';
		$html .= '<input type="hidden" name="aid" value="' . $aid . '" />';
		$html .= '<input type="hidden" name="process" value="1" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="excuse">' . __('Excuse') . '</label>';
		$html .= '<textarea id="excuse" name="excuse">';
		$html .= htmlspecialchars($excuse);
		$html .= '</textarea>';
		$html .= '</div>';
		$html .= '<input type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}

	private function getAttendanceTime($aid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `timestamp` FROM `attendance`
			WHERE `id` = ?;"
		);
		$stmt->bind_param('i', $aid);
		$stmt->execute();
		$stmt->bind_result($timestamp);
		$stmt->fetch();
		$stmt->close();
		return strtotime($timestamp);
	}

	private function getGroupName($gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `module`.`name` FROM `group`
			INNER JOIN `moduleoffering`
			ON `moduleoffering`.`id` = `group`.`moid`
			INNER JOIN `module`
			ON `module`.`id` = `moduleoffering`.`mid`
			WHERE `group`.`id` = ?;"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
	}

	private function validate($excuse, $aid)
	{
		if (strlen($excuse) > 640) {
			$this->addNotification(
				'warning',
				__('The excuse must be less than 640 characters in length')
			);
			return false;
		} else if (strlen($excuse) < 5) {
			$this->addNotification(
				'warning',
				__('The excuse must be at least 5 characters in length')
			);
			return false;
		}
		return true;
	}

	private function save($aid, $excuse)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			'UPDATE `student_attendance`
			SET `excuse` = ?, `excuse_viewed` = 0
			WHERE `sid` = ?
			AND aid = ?'
		);
		$stmt->bind_param('sii', $excuse, $_SESSION['uid'], $aid);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}
}