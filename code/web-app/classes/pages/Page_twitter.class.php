<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_twitterAuth.class.php';
/**
 * This page is used by lecturers to tweet
 */
class Page_twitter extends Page_twitterAuth {
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
	public function display2($gid) 
	{	
		$_SESSION['gid'] = NULL;
		$access_token = $_SESSION['access_tokenTwitter'];
		$connection = new TwitterOAuth(CONFIG::TWITTER_CONSUMER_KEY, CONFIG::TWITTER_CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		$user = $connection->get('account/verify_credentials');
		$gid = intval($gid);
		$gName = $this->getGroupName($gid);
		if(!empty($gName))
		{
			
		}
		else
		{
			$this->addNotification(
						'error',
						__('Invalid group selected.')
					);
			$this->groupSelector();
		}
		
	}
	/**
	 * Returns group name
	 *
	 * @return string
	 */
	private function getGroupName($gid)
	{
		$db = Db::getLink();
		$stmt = $db->prepare(
			"SELECT `name` FROM `group` WHERE `id`=?"
		);
		$stmt->bind_param('i', $gid);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name;
	}
}
?>