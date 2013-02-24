<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * Displays the login form and processes authentication requests
 */
class Page_Login extends Page {
	public function __construct()
	{
		parent::__construct();
		$this->setType('nomenu');

		if (isset($_POST['processform'])) {
			// The form was submitted, process the request
			if (! Auth::speedLimitOk()) { // Check for limit violation

				$this->addNotification(
					'error',
					sprintf(
						__(
							'Error: You have exceeded your maximum number of login attempts. '
							. 'Please try again in %s seconds.'
						),
						Auth::speedLimitExpiry()
					)
				);
				$this->addHtml(
					$this->loginForm()
				);
			} else if (Auth::login($_POST['username'], $_POST['password'])) {
				$this->redirect();
			} else {
				$this->addNotification(
					'error',
					__('Error: Invalid username or password')
				);
				$this->addHtml(
					$this->loginForm()
				);
			}
		} else {
			// The form was not submitted
			if ($this->isMobile()) {
				// If the user is using the android app, we
				// just tell him to re-login inside the app
				$this->addNotification(
					'error',
					__('Your session has expired, please log in again')
				);
			} else {
				$this->addHtml(
					$this->loginForm()
				);
			}
		}
	}
	/**
	 * Generates the HTML code for the login form
	 *
	 * @return string HTML
	 */
	private function loginForm() {
		$form = '<h2>' . __('Login Form') . '</h2>';
		$form .= '<form data-ajax="false" method="post" action="?action=login">';
		$form .= '<div data-role="fieldcontain">';
		$form .= '<label for="username">' . __('Username') . ': </label>';
		$form .= '<input type="text" name="username" id="username" value="';
		if (isset($_POST['processform']) && isset($_POST['username'])) {
			// If the form was previously submitted, put the username
			// back into the username field of the form
			$form .= htmlspecialchars($_POST['username']);
		}
		$form .= '" />';
		$form .= '</div>';
		$form .= '<div data-role="fieldcontain">';
		$form .= '<label for="password">' . __('Password') . ': </label>';
		$form .= '<input type="password" name="password" id="password" value="" />';
		$form .= '</div>';
		$form .= '<div data-role="fieldcontain">';
		$form .= '<label for="lang">' . __('Language') . ': </label>';
		$form .= '<select name="lang" id="lang">';
		$form .= Lang::getLanguageOptions();
		$form .= '</select>';
		$form .= '</div>';
		$form .= '<input data-theme="b" type="submit" value="' . __('Go') . '" />';
		$form .= '<input type="hidden" name="processform" value="1" />';
		$form .= '</form>';
		return $form;
	}
}

?>