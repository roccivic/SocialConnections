<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * This page is used by admins to check attendance
 */
class Page_adminCheckAttendance extends Page {
	public static function getAccessLevel()
	{
		return User::ADMIN;
	}

	public function __construct()
	{
		parent::__construct();

		$this->addHtml('<h3>' . __('Check Attendance') . '</h3>');

		foreach ($this->getTerms() as $term) {
			$this->printListHeader($term['year'], $term['term']);
			$total = 0;
			$present = 0;
			$attendanceData = $this->getAttendanceData(
				$term['year'],
				$term['term']
			);
			foreach ($attendanceData as $key => $value) {
				$percentage = round(($value['present'] / $value['total'])*100);
				$this->printListItem($key, $percentage);
				$total += $value['total'];
				$present += $value['present'];
			}
			$this->printListFooter();
			if ($total > 0) {
				$html = '<ul data-role="listview" data-divider-theme="b" ';
		        $html .= 'data-inset="true">';
			    $html .= sprintf(
		        	'<li>%s<span class="ui-li-count">%d %%</span></li>',
		        	__('Average Attendance'),
		        	round(($present / $total)*100)
			    );
		        $html .= '</ul>';
        	} else {
        		$html =  '<div class="notification warning">' . __('Attendance data empty') . '</div>';
        	}
	        $this->addHtml($html);
	    }
	}

	/**
	 * Prints the header for the list
	 *
	 * @return void
	 */
	private function printListHeader($year, $term)
	{
        $html  = '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= sprintf(
        	__('Year %d, Semester %d'),
        	$year,
        	$term
        );
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list
	 *
	 * @return void
	 */
	private function printListItem($name, $percentage)
	{
        $this->addHtml(
	        sprintf(
	        	'<li>%s<span class="ui-li-count">%d %%</span></li>',
	        	$name,
	        	$percentage
	        )
        );
	}
	/**
	 * Prints the footer for the list
	 *
	 * @return void
	 */
	private function printListFooter()
	{
        $this->addHtml('</ul>');
	}

	private function getAttendanceData($year, $term)
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			'SELECT `department`.`name` , COUNT(*) , SUM(`present`)
			FROM `student_attendance`
			INNER JOIN `attendance` ON `student_attendance`.`aid` = `attendance`.`id`
			INNER JOIN `group` ON `group`.`id` = `attendance`.`gid`
			INNER JOIN `moduleoffering` ON `moduleoffering`.`id` = `group`.`moid`
			INNER JOIN `module` ON `module`.`id` = `moduleoffering`.`mid`
			INNER JOIN `department` ON `department`.`id` = `module`.`did`
			WHERE `moduleoffering`.`year` = ?
			AND `moduleoffering`.`term` = ?
			GROUP BY `department`.`id`'
		);
		$stmt->bind_param('ii', $year, $term);
		$stmt->execute();
		$stmt->bind_result($name, $total, $present);
		while ($stmt->fetch()) {
			$arr[$name] = array(
				'total' => $total,
				'present' => $present
			);
		}
		$stmt->close();

		return $arr;
	}

	private function getTerms()
	{
		$arr = array();
		$db = Db::getLink();
		$stmt = $db->prepare(
			'SELECT `year`, `term`
			FROM `moduleoffering`
			GROUP BY `year`,`term`
			ORDER BY `year` DESC, `term` DESC;'
		);
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

?>