<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';

class Page_facialRec extends Page_selectLecturerGroup {
	private $session;
	public function __construct()
	{
		$this->session = ! empty($_REQUEST['session']) ? $_REQUEST['session'] : '';
		parent::__construct();
	}

	public function display($gid)
	{
		$folder = 'face_cache/' . $this->session;
		$name = $this->getGroupName($gid);
		$date = ! empty($_REQUEST['date']) ? $_REQUEST['date'] : date("Y-m-d");
		$time = ! empty($_REQUEST['time']) ? $_REQUEST['time'] : date("H:i");

		if (! strlen($name)) {
			$this->addNotification(
				'error',
				__('Invalid group selected')
			);
		} else if (empty($this->session) || ! is_readable($folder)) {
			$this->addNotification(
				'error',
				__('Invalid attendance session, could not find images')
			);
		} else {
			$this->printForm($folder, $gid, $date, $time, $name);
		}
	}

	protected function getArgs()
	{
		return '&session=' . $this->session;
	}

	/**
	 * Displays the form for taking attendance
	 *
	 * @return void
	 */
	private function printForm($folder, $gid, $date, $time, $name) {
		$this->addHtml(
			'<h3>'
			. sprintf(
				 __('Group `%s`'),
				 $name
			)
			. '</h3>'
		);

		$students = $this->getStudentsInGroup($gid);
		if (! count($students)) {
			$this->addNotification(
				'error',
				__('There are no students assigned to this group')
			);
		} else {
			$files = scandir($folder);
			array_shift($files); // .
			array_shift($files); // ..

			// AJAX data
			$html  = '<div id="recognise"></div>';
			$html .= '<div style="display:none;">';
			$html .= '<div id="gid">' . $gid . '</div>';
			$html .= '<div id="session">' . $this->session . '</div>';
			$html .= '<select class="students" name="student[]">';
			$html .= '<option value="0">';
			$html .= __('-- Ignore --');
			$html .= '<option>';
			foreach ($students as $id => $student) {
				$html .= '<option value="' . $id . '">';
				$html .= $student;
				$html .= '<option>';
			}
			$html .= '</select>';
			$html .= '</div>';

			// Actual form
			$html .= '<form action="" method="post">';
			$html .= '<input type="hidden" name="process" value="1" />';
			$html .= '<div data-role="fieldcontain">';
			$html .= '<label for="date">' . __('Date:') . '</label>';
			$html .= '<input type="date" data-role="datebox" ';
			$html .= 'data-options=\'{"mode":"calbox", "useNewStyle":true}\' id="date" ';
			$html .= 'name="date" value="' . htmlspecialchars(
				date("Y-m-d", strtotime($date))
			) . '" />';
			$html .= '</div>';
			$html .= '<div data-role="fieldcontain">';
			$html .= '<label for="time">' . __('Time:') . '</label>';
			$html .= '<input type="time" data-role="datebox" ';
			$html .= 'data-options=\'{"mode":"timebox", "useNewStyle":true}\' id="time" ';
			$html .= 'name="time" value="' . htmlspecialchars(
				date("H:i", strtotime($time))
			) . '" />';
			$html .= '</div>';

			$html .= '<div data-role="fieldcontain">';
			$html .= '<fieldset data-role="controlgroup" data-type="horizontal">';
			$html .= '<legend>' . __('Type') . ':</legend>';
			$html .= '<input type="radio" name="isLecture"';
			$html .= ' id="radio-1" value="1" checked="checked" />';
			$html .= '<label for="radio-1">' . __('Lecture') . '</label>';
			$html .= '<input type="radio" name="isLecture"';
			$html .= ' id="radio-2" value="0" />';
			$html .= '<label for="radio-2">' . __('Lab') . '</label>';
			$html .= '</fieldset>';
			$html .= '</div>';

			$html .= '<div data-role="fieldcontain">';
			$html .= '<fieldset data-role="controlgroup" data-type="horizontal">';
			$html .= '<legend>' . __('Students') . ':</legend>';
			$html .= '<ul data-inset="true" id="studentlist" data-role="listview" data-divider-theme="b">';
			foreach ($students as $student) {
				$html .= '<li>';

				$file = array_shift($files);
				$path = $folder . '/' . $file;
				if ($file && is_readable($path)) {
					$html .= '<img src="' . $path . '" alt="" />';
				} else {
					$html .= '<img class="avatar" src="' . Config::URL . 'images/avatar.png" alt="" />';
				}
				$html .= '<h3>' . __('Processing...') . '</h3>';
				$html .= '</li>';
			}
			$html .= '</ul>';
			$html .= '</fieldset>';
			$html .= '</div>';

			$html .= '<input type="submit" data-theme="b" value="' . __('Save') . '" />';
			$html .= '</form>';
			$this->addHtml($html);
		}
	}
}

?>