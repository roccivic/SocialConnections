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
	public function display2($access_token, $gid, $config) 
	{	
		$_SESSION['gid'] = NULL;
		if(isset($_FILES['dropboxFile']))
		{ 
			$tmp_name = $_FILES['dropboxFile']['tmp_name'];
			$name = $_FILES['dropboxFile']['name'];
			if($this->upload($tmp_name, $name, $config) && $this->uploadDropbox($gid, $name, $config, $access_token))
			{
				$this->addNotification(
							'notice',
							__('The file was uploaded successfully.')
						);
			}
			else
			{
				$this->addNotification(
							'error',
							__('An error occured while processing your request.')
						);
			}
			$this->uploadForm($gid);
			
		}
		else 
		{
			$this->uploadForm($gid);
		}
	}
	/**
	 * Displays a form for uploading a file
	 *
	 * @return string
	 */
	private function uploadForm($gid)
	{
		$html = '';
		$html .= '<form method="POST" enctype="multipart/form-data" action="">';
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		$html .= '<label for="file">File</label>';
		$html .= '<input type="file" data-clear-btn="false" name="dropboxFile" id="dropboxFile" value="" />';
		$html .= '<input data-theme="b" type="submit" value="' . __('Upload') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}

	/**
	 * Uploads a file to dropbox
	 *
	 * @return bool
	 */
	private function uploadDropbox($gid, $name, $config, $access_token)
	{
		$success = true;
		try 
		{
			$session = new DropboxSession(
				$config["dropbox"]["app_key"],
				$config["dropbox"]["app_secret"],
				$config["dropbox"]["access_type"],
				$access_token
		    );
		  	$client = new DropboxClient($session);
		  	$src = $config["app"]["datadir"] . $name;
    		$dest = "/";
		  	if ($response = $client->putFile($src, $dest)) {

    		}
		   	else
		   	{
		   		$success = false;
		   	}
	   	}
	   	catch (Exception $e) 
	   	{
		   $success = false;
		}
		return $success;
	}
	/**
	 * Uploads a file to the server
	 *
	 * @return bool
	 */
	private function upload($tmp_name, $name, $config)
	{
		$success = move_uploaded_file($tmp_name,  $config["app"]["datadir"].$name);
		return $success;
	}



}
?>