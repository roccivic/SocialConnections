<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/PageSelector.class.php';

/**
 * This page is used by lecturers to view the
 * excuses of students for missing classes
 */
class Page_viewExcuses extends Page {
	/**
     * A user must be at least a lecturer to view this page
     */
    public static function getAccessLevel()
    {
        return User::LECTURER;
    }
	public function __construct()
	{
		parent::__construct();

		$numExcuses = $this->countExcuses();
		if ($numExcuses > 0) {
			$ps = new PageSelector();
			$this->addHtml(
				$ps->getPageSelector(
					$numExcuses,
					'?action=viewExcuses'
				)
			);
			$excuses = $this->getExcuses($ps->getPos(), $ps->getLimit());
			$this->addHtml('<ul data-role="listview">');
			foreach ($excuses as $day => $dayExcuses) {
				$this->addHtml('<li data-role="list-divider">' . $day . '</li>');
				foreach ($dayExcuses as $excuse) {
					$theme = "c";
					if (! $excuse['excuse_viewed']) {
						$theme = "a";
					}
					$this->addHtml(
					    '<li data-theme="' . $theme .  '">
					        <h2>' . $excuse['name'] . '</h2>
					        <p style="white-space:normal;"><strong>' . $excuse['group'] . '</strong></p>
					        <p style="white-space:normal;">' . $excuse['excuse'] . '</p>
					        <p class="ui-li-aside"><strong>' . date("g A", strtotime($excuse['timestamp'])) . '</strong></p>
					    </li>'
					);
				}
			}
			$this->addHtml('</ul>');
		} else {
			$this->addNotification(
				'notice',
				__('There are no excuses to be viewed at the moment')
			);
		}
	}

	private function countExcuses()
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			'SELECT COUNT(*)
			FROM `student_attendance`
			INNER JOIN `attendance`
			ON `attendance`.`id` = `student_attendance`.`aid`
			INNER JOIN `group`
			ON `attendance`.`gid` = `group`.`id`
			INNER JOIN `moduleoffering`
			ON `moduleoffering`.`id` = `group`.`moid`
			INNER JOIN `moduleoffering_lecturer`
			ON `moduleoffering_lecturer`.`moid` = `moduleoffering`.`id`
			INNER JOIN `student`
			ON `student`.`id` = `student_attendance`.`sid`
			WHERE `moduleoffering_lecturer`.`lid` = ?
			AND `excuse` IS NOT NULL;'
		);
		$stmt->bind_param('i', $_SESSION['uid']);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();
		$stmt->close();
		return $count;
	}

	private function getExcuses($pos, $limit)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			'SELECT `excuse`,`excuse_viewed`,`fname`,`lname`,`name`,`timestamp`
			FROM `student_attendance`
			INNER JOIN `attendance`
			ON `attendance`.`id` = `student_attendance`.`aid`
			INNER JOIN `group`
			ON `attendance`.`gid` = `group`.`id`
			INNER JOIN `moduleoffering`
			ON `moduleoffering`.`id` = `group`.`moid`
			INNER JOIN `moduleoffering_lecturer`
			ON `moduleoffering_lecturer`.`moid` = `moduleoffering`.`id`
			INNER JOIN `student`
			ON `student`.`id` = `student_attendance`.`sid`
			WHERE `moduleoffering_lecturer`.`lid` = ?
			AND `excuse` IS NOT NULL
			ORDER BY `timestamp` DESC
			LIMIT ?,?;'
		);
		$stmt->bind_param('iii', $_SESSION['uid'], $pos, $limit);
		$stmt->execute();
		$stmt->bind_result($excuse, $excuse_viewed, $fname, $lname, $group, $timestamp);
		while ($stmt->fetch()) { 
			$arr[date("j F Y", strtotime($timestamp))][] = array(
				'excuse' => $excuse,
				'name' => $fname . ' ' . $lname,
				'group' => $group,
				'timestamp' => $timestamp,
				'excuse_viewed' => $excuse_viewed
			);
		}
		$stmt->close();

		// Mark all excuses viewed
		$stmt = $db->prepare(
			'UPDATE `student_attendance`
			INNER JOIN `attendance`
			ON `attendance`.`id` = `student_attendance`.`aid`
			INNER JOIN `group`
			ON `attendance`.`gid` = `group`.`id`
			INNER JOIN `moduleoffering`
			ON `moduleoffering`.`id` = `group`.`moid`
			INNER JOIN `moduleoffering_lecturer`
			ON `moduleoffering_lecturer`.`moid` = `moduleoffering`.`id`
			SET `excuse_viewed` = 1
			WHERE `moduleoffering_lecturer`.`lid` = ?;'
		);
		$stmt->bind_param('i', $_SESSION['uid']);
		$stmt->execute();
		$stmt->close();

		return $arr;
	}
}