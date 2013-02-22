<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * This page is used by admins to view/edit the
 * attendance threshold for modules
 */
class Page_attendanceThreshold extends Page {
	public static function getAccessLevel()
	{
		return User::ADMIN;
	}

	public function __construct()
	{
		parent::__construct();
		if (! empty($_REQUEST['process'])) {
			$overall = -1;
			if (! empty($_REQUEST['overall'])) {
				$overall = intval($_REQUEST['overall']);
			}
			$labs = -1;
			if (! empty($_REQUEST['labs'])) {
				$labs = intval($_REQUEST['labs']);
			}
			if ($this->validateForm($overall, $labs)) {
				if ($this->save($overall, $labs)) {
					$this->addNotification(
						'notice',
						__('The threshold values were successfully updated.')
					);
				} else {
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
				}
			} else {
				$this->addNotification(
					'error',
					__('The values in the form were invalid. Please try again.')
				);
			}
		}
		$this->displayForm();
	}
	/**
	 * Displays the form for updating the attendance threshold levels
	 *
	 * @return @void
	 */
	private function displayForm()
	{
		$levels = $this->getLevels();

		$html  = '<h3>' . __('Poor Attendance Threshold') . '</h3>';
		$html .= '<p>';
		$html .= __(
			'Here you can define below what percencentage a '
			. 'student\'s attendance will be considered to be poor.'
		);
		$html .= '</p>';
		$html .= '<form method="post" action="">';
		$html .= '<input name="process" value="1" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="overall">' . __('Overall (%)') . ': </label>';
		$html .= '<input type="text" name="overall" id="overall" ';
		$html .= 'value="' . $levels['overall'] . '" />';
		$html .= '</div>';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="labs">' . __('Labs (%)') . ': </label>';
		$html .= '<input type="text" name="labs" id="labs" ';
		$html .= 'value="' . $levels['labs'] . '" />';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Save') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Validates the form for updating the attendance threshold levels
	 *
	 * @return bool
	 */
	private function validateForm($overall, $labs)
	{
		$retval = true;
		if ($overall <= 0 || $overall > 100) {
			$retval = false;
		} else if ($labs <= 0 || $labs > 100) {
			$retval = false;
		}
		return $retval;
	}
	/**
	 * Saves the attendance threshold levels to the db
	 *
	 * @return bool Success
	 */
	private function save($overall, $labs)
	{
		return Db::getLink()->query(
			"UPDATE threshold SET overall = $overall, labs = $labs WHERE id = 0;"
		);
	}
	/**
	 * Retrieves the attendance threshold levels from the db
	 *
	 * @return array
	 */
	private function getLevels()
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT overall, labs FROM threshold WHERE id = 0;"
		);
		$stmt->execute();
		$stmt->bind_result($overall, $labs);
		$stmt->fetch();
		$stmt->close();
		return array(
			'overall' => $overall,
			'labs' => $labs
		);
	}
}