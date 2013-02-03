<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

/**
 * Internationalisation class
 *
 * Defines available languages and sets gettext up
 */
class Lang {
	/**
	 * Code of currently selected language
	 */
	private static $current = 'en';
	/**
	 * Returns a list of languages available on the system
	 *
	 * @return array()
	 */
	public static function getLanguages()
	{
		return array(
			'en' => __('English'),
			'it' => __('Italiano')
		);
	}
	/**
	 * Returns a list of available languages as OPTION tags
	 * which can be used in an HTML form
	 *
	 * @return string
	 */
	public static function getLanguageOptions()
	{
		$options = '';
		foreach (self::getLanguages() as $index => $name) {
			$options .= '<option value="' . $index . '"';
			if ($index === self::$current) {
				$options .= ' selected="selected"';
			}
			$options .= '>';
			$options .= $name;
			$options .= '</option>';
		}
		return $options;
	}
	/**
	 * Selects the current language from a request
	 * or the session and initialises gettext
	 *
	 * @return void
	 */
	public static function setLang()
	{
		if (! empty($_REQUEST['lang'])) {
			foreach (self::getLanguages() as $index => $name) {
				if ($_REQUEST['lang'] === $index) {
					self::$current = $_REQUEST['lang'];
					$_SESSION['lang'] = self::$current;
					break;
				}
			}
		} else {
			if (! empty($_SESSION['lang'])) {
				foreach (self::getLanguages() as $index => $name) {
					if ($_SESSION['lang'] === $index) {
						self::$current = $_SESSION['lang'];
						break;
					}
				}
			}
		}
		// Set locale
		_setlocale(LC_MESSAGES, self::$current);
		_bindtextdomain('socialconnections', 'locale');
		_bind_textdomain_codeset('socialconnections', 'UTF-8');
		_textdomain('socialconnections');
	}
}

?>