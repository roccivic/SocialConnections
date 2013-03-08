<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_dropboxAuth.class.php';
/**
 * This page is used by lecturers to tweet
 */
class Page_postNotes extends Page_dropboxAuth {
	public static function getAccessLevel()
	{
		return User::LECTURER;
	}
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * Called from the Page_twitterAuth superclass
	 * when access to twitter is granted
	 *
	 * @return void
	 */
	public function display2($access_token, $gid) 
	{	
		$_SESSION['gid'] = NULL;
		$config = array();
		$config["dropbox"]["app_key"] = CONFIG::DROPBOX_APP_KEY;
		$config["dropbox"]["app_secret"] = CONFIG::DROPBOX_APP_SECRET;
		$config["dropbox"]["access_type"] = CONFIG::DROPBOX_ACCESS_TYPE;
		$config["app"]["root"] = ((!empty($_SERVER["HTTPS"])) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . "/socialconnections/?action=postNotes";
		$session = new DropboxSession(
	    $config["dropbox"]["app_key"],
	    $config["dropbox"]["app_secret"],
	    $config["dropbox"]["access_type"]
	    );
		$this->uploadForm($gid);
		
	}
	/**
	 * Displays a form for uploading a file
	 *
	 * @return string
	 */
	private function uploadForm($gid)
	{
		$html = '';
		$html .= '<form method="POST" action="">';
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		$html .= '<label for="file">File</label>';
		$html .= '<input data-clear-btn="false" name="file" id="file" value="" type="file">';
		$html .= '<input data-theme="b" type="submit" value="' . __('Upload') . '" />';
		$this->addHtml($html);
	}

}
?>