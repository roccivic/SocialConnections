<?php

if (! defined('SOCIALCONNECTIONS')) {
	die();
}

require_once 'classes/TidyHtml.class.php';
require_once 'classes/Menu.class.php';

/**
 * Base class for creating and displaying HTML pages
 * Must be inherited from to generate concrete pages
 */
abstract class Page {
	/**
	 * A db link, here just for convenience so that subclasses
	 * don't have to retrive the link themselves
	 */
	protected $db;
	/**
	 * Output buffer
	 */
	private $html;
	/**
	 * Type of page, can be one of: 'normal' or 'nomenu'
	 */
	private $type;
	/**
	 * Whether the page request is coming from
	 * the android app or an arbitrary web browser
	 */
	private $isMobile;
	/**
	 * A list of notification to include on the page
	 * when it displayed. See also: addNotification()
	 */
	private $notifications;

	/**
	 * Override this method to specify what level of access
	 * is required to view a particular page
	 *
	 * @return int See the User class for details
	 */
	public function getAccessLevel()
	{
		return User::ANONYMOUS;
	}
	/**
	 * Override this method to specify what parameters
	 * must be passed to the page, so that it is correctly displayed.
	 *
	 * E.g.: array('groupid', 'userid')
	 *
	 * @return array()
	 */
	public function getRequiredParams()
	{
		return array();
	}
	/**
	 * Instanciates the class
	 *
	 * @return new Page
	 */
	public function __construct()
	{
		$this->html = '';
		$this->type = 'normal';
		$this->isMobile = false;
		$this->notifications = array();
		$this->db = Db::getLink();

		if ($this->db->connect_error) {
			$this->addNotification(
				'error',
				__('Unable to connect to database')
			);
		}

		if (! empty($_REQUEST['mobile'])) {
			$_SESSION['mobile'] = true;
		}
		if (! empty($_SESSION['mobile'])) {
			$this->isMobile = true;
			$this->type = 'nomenu';
		}
	}
	/**
	 * Used to check whether the page request is coming
	 * from the android app or an arbitrary web browser
	 *
	 * @return bool
	 */
	public function isMobile()
	{
		return $this->isMobile;
	}
	/**
	 * Sets the page type
	 *
	 * @return void
	 */
	protected function setType($type)
	{
		if ($type === 'normal' || $type === 'nomenu') {
			$this->type = $type;
		}
	}
	/**
	 * Adds a notification to the top of the page
	 *
	 * @return void
	 */
	public function addNotification($type, $html)
	{
		$this->notifications[] = array(
			'type' => $type,
			'html' => $html
		);
	}
	/**
	 * Adds an html string to the main content of the page
	 *
	 * @return void
	 */
	protected function addHtml($string)
	{
		$this->html .= $string;
	}
	/**
	 * Adds an text string to the main content of the page
	 * The string will be sanitised to avoid XSS
	 *
	 * @return void
	 */
	protected function addText($string)
	{
		$this->html .= htmlspecialchars($string);
	}
	/**
	 * Sends HTTP headers, if not already done
	 *
	 * @return void
	 */
	public function sendHttpHeaders()
	{
		if (! headers_sent()) {
			// XSS protection, see: http://www.w3.org/TR/CSP/
			header("Content-Security-Policy: default-src 'self'");
		}
	}
	/**
	 * Generates the page and sends it to the browser
	 *
	 * @return void
	 */
	public function displayPage()
	{
		$page  = $this->getHeader();
		$page .= '<div class="content-primary">';
    	foreach ($this->notifications as $notification) {
			$page .= '<div class="notification ' . $notification['type'] . '">';
			$page .= htmlspecialchars($notification['html']);
			$page .= '</div>';
    	}
		$page .= $this->html;
		if ($this->isMobile()
			&& ! empty($_SERVER['HTTP_REFERER'])
			&& ! empty($_REQUEST['action'])
			&& $_REQUEST['action'] !== 'login'
			&& $_REQUEST['action'] !== 'logout'			
		) {
			$page .= '<a data-theme="e" data-direction="reverse" data-icon="back" data-role="button" href="' . $_SERVER['HTTP_REFERER'] . '">';
			$page .= __('Back');
			$page .= '</a>';
		}

		$page .= '</div>';
		if (! $this->isMobile()) {
			$page .= '<div class="content-secondary">';
			$page .= $this->getMenu();
			$page .= '</div>';
		}
		$page .= $this->getFooter();

		echo TidyHtml::process($page);
	}
	/**
	 * Sends an HTTP Redirect to the browser
	 * Used in login form, other than that, avoid this at all costs
	 *
	 * @return void
	 */
	protected function redirect($page = '')
	{
		$msg = '';
		if (! empty($page)) {
			$page = '?action=' . $page;
		}
		if (headers_sent()) {
			$msg = "Error: attempted to redirect after output had already started.";
		} else {
			header('Location: ' . Config::URL . $page);
		}
		die($msg);
	}
	/**
	 * Generates the appropriate menu for the page
	 *
	 * @return string HTML
	 */
	private function getMenu()
	{
		$html = '';
		$menu = new Menu();
		if (User::isStudent()) {
			$html .= $menu->getStudentMenu();
		} else if (User::isLecturer()) {
			$html .= $menu->getLecturerMenu();
		} else if (User::isAdmin()) {
			$html .= $menu->getAdminMenu();
		} else if (User::isSuper()) {
			$html .= $menu->getSuperMenu();
		} else {
			$this->type = 'nomenu';
		}
		return $html;
	}
	/**
	 * Generates the HTML header for the page
	 *
	 * @return string HTML
	 */
	private function getHeader()
	{
		$title = htmlspecialchars(__('Social Connections Web App'));

		$html  = '<!DOCTYPE html>';
		$html .= '<html>';
		$html .= '<head>';
		$html .= '<title>' . $title . '</title>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0" />';
		$html .= '<link rel="stylesheet" type="text/css" href="' . Config::URL . 'css/jquery.mobile-1.2.0.min.css" />';
		$html .= '<link rel="stylesheet" type="text/css" href="' . Config::URL . 'css/style.css" />';
		$html .= '<link href="' . Config::URL . 'images/favicon.png" rel="shortcut icon" />';
		$html .= '<script src="' . Config::URL . 'scripts/jquery-1.8.2.min.js" type="text/javascript"></script>';
		$html .= '<script type="text/javascript">';
		$html .= '$(document).live("mobileinit", function(){$.mobile.ajaxEnabled = false;});';
		$html .= '</script>';
		$html .= '<script src="' . Config::URL . 'scripts/jquery.mobile-1.2.0.min.js" type="text/javascript"></script>';
		$html .= '</head>';
		$html .= '<body>';
		$html .= sprintf(
			'<div data-role="page" class="type-%s">',
			$this->type
		);
		$html .= '<div data-role="content">';

		if (! $this->isMobile()) {
			$html .= '<div data-role="header" data-theme="a">';
	        $html .= '<h1>' . $title . '</h1>';
	        $html .= '<a href="' . Config::URL . '" data-icon="home" data-iconpos="notext" data-direction="reverse">Home</a>';
	        if (User::getAccessLevel() > User::ANONYMOUS) {
	        	$html .= '<a href="' . Config::URL . '?action=logout">';
	        	$html .= __('Log out');
	        	$html .= '</a>';
	    	}
	        $html .= '</div>';
    	}

		return $html;
	}
	/**
	 * Generates the HTML footer for the page
	 *
	 * @return string HTML
	 */
	private function getFooter()
	{
		$html  = '<div data-role="footer" class="footer-docs" data-theme="c">';
		$html .= '<p style="float: left">';
		$html .= 'v' . Config::VERSION;
		$html .= '</p>';
		$html .= '<p style="float: right">';
		$html .= '&copy; 2013 ';
		$html .= sprintf(
			'<a href="%s">%s</a>',
			Config::URL,
			Config::URL
		);
		$html .= '</p>';
		$html .= '<div style="clear: both;"></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</body>';
		$html .= '</html>';
		return $html;
	}
}

?>