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
		$uid = $_SESSION['uid'];
		if($this->isLecturerInGroup($uid, $gid))
		{
			if(isset($_FILES['dropboxFile']))
			{ 
				$tmp_name = $_FILES['dropboxFile']['tmp_name'];
				$name = $_FILES['dropboxFile']['name'];
				if($this->upload($tmp_name, $name, $config) && $this->uploadDropbox($gid, $name, $config, $access_token) && $this->rmvFile($config, $name) && $this->getLink($gid, $config, $access_token, $name))
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
		else
		{
			$this->addNotification(
								'error',
								__('You are not a part of this group.')
							);
			$this->groupSelector();
		}
	}
	/**
	 * Displays a form for uploading a file
	 *
	 * @return string
	 */
	private function uploadForm($gid)
	{
		$html  = '<form data-ajax="false" method="POST" enctype="multipart/form-data" action="?action=postNotes&gid=' . $gid . '">';
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
		  	if ($response = $client->putFile($src, $dest)) {}
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
	/**
	 * deletes uploaded file from the server
	 *
	 * @return bool
	 */
	private function rmvFile($config, $name)
	{
		$success = unlink($config["app"]["datadir"].$name);
		return $success;
	}
	/**
	 * Gets the link of a file for download
	 *
	 * @return bool
	 */
	private function getLink($gid, $config, $access_token, $name)
	{
		$response = true;
		try 
		{
			$session = new DropboxSession(
				$config["dropbox"]["app_key"],
				$config["dropbox"]["app_secret"],
				$config["dropbox"]["access_type"],
				$access_token
		    );
		  	$client = new DropboxClient($session);
		  	$response = $client->media($name);
		  	
		  	if($response['code'] == 200)
		  	{
		  		$success = $this->saveLink($gid, $response);
		  	}
		  	else {
		  		$success = false;
		  	}
		  	
		}
	   	catch (Exception $e) 
	   	{
	   		$success = false;
	   	}
	   	return $response;
	}
	/**
	 * Saves link to file
	 *
	 * @return bool success
	 */
	private function saveLink($gid, $response) 
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
		"INSERT INTO `notes` (`gid`, `url`) VALUES (?, ?);"
		);
		$stmt->bind_param('is', $gid, $response['body']['url']);
		$success = $stmt->execute();
		$stmt->fetch();
		$stmt->close();
		$success = $this->saveNotification($gid, $response);
		return $success;
	}
	/**
	 * Saves notification to db
	 *
	 * @return bool success
	 */
	private function saveNotification($gid, $response) 
	{
		$db = Db::getLink();
		$arr = array();	
		$stmt = $db->prepare(
		"SELECT `id` FROM `notes` WHERE `gid` = ? AND`url` = ?"
		);
		$stmt->bind_param('is', $gid, $response['body']['url']);
		$success = $stmt->execute();
		$stmt->bind_result($nid);
		$stmt->fetch();
		$stmt->close();
		if($success)
		{
			$stmt = $db->prepare(
			"SELECT `sid` FROM `group_student` WHERE `gid` = ?"
			);
			$stmt->bind_param('s', $gid);
			$success = $stmt->execute();
			$stmt->bind_result($sid);
			while ($stmt->fetch()) 
			{
				$arr['id'] = $sid;
			}
			$stmt->close();
		}
		if($success)
		{
			foreach($arr as $key => $value)
			{
				$stmt = $db->prepare(
			"INSERT INTO `notes_notifications` (`sid`,`nid`) VALUES (?, ?);"
			);
			$stmt->bind_param('ii', $value, $nid);
			$success = $stmt->execute();
			$stmt->fetch();
			$stmt->close();
			}
		}
		return $success;
	}

}
?>