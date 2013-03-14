<?php
if (! defined('SOCIALCONNECTIONS')) {
	die();
}
require_once 'classes/pages/abstract/Page_selectLecturerGroup.class.php';
require_once 'classes/pages/abstract/Page_selectStudentGroup.class.php';
require_once('libs/twitter/twitteroauth.php');
/**
 * Abstract class that implements connecting to twitter
 */
if(USER::isLecturer())
{
	abstract class Page_twitterAuth extends Page_selectLecturerGroup {
		public function __construct()
		{
			parent::__construct();
			
		}
		public function display($gid) 
		{	
			$gid = intval($gid);
			$_SESSION['gid'] = $gid;
			if(!empty($_REQUEST['callback'])) {
				if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
					 	 $_SESSION['oauth_status'] = 'oldtoken';
					  	 $this->connect();
				}
				$connection = new TwitterOAuth(CONFIG::TWITTER_CONSUMER_KEY,CONFIG::TWITTER_CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
				$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
				$_SESSION['access_tokenTwitter'] = $access_token;
				unset($_SESSION['oauth_token']);
				unset($_SESSION['oauth_token_secret']);
				if (200 == $connection->http_code) {
				 $_SESSION['status'] = 'verified';
				} 
				else 
				{
				 $this->connect();
				}
			}
			if(!empty($_SESSION['access_tokenTwitter']))
			{
				$access_token = $_SESSION['access_tokenTwitter'];
			}
			if(!empty($access_token['oauth_token'])) 
			{
				$_SESSION['access_tokenTwitter'] = $access_token;
				$this->display2($gid);
			}
			else if(empty($_SESSION['access_tokenTwitter']) || empty($_SESSION['access_tokenTwitter']['oauth_token']) || empty($_SESSION['access_tokenTwitter']['oauth_token_secret'])) 
			{

	    		$this->connect();
			}
		}
		/**
		 * This function must be implemented in a subclass
		 * Shows a page after access to twitter has been granted
		 *
		 * @return void
		 */
		protected abstract function display2($gid);
		/**
		 * This function connects to twitter
		 * @return void
		 */
		private function connect() {
			if (CONFIG::TWITTER_CONSUMER_KEY === '' || CONFIG::TWITTER_CONSUMER_SECRET === '') {
	  			$this->addNotification('error',
	  								__('Twitter does not work at the moment. Please, contact administrator.')
				);
	  		}
	  		$connection = new TwitterOAuth(CONFIG::TWITTER_CONSUMER_KEY, CONFIG::TWITTER_CONSUMER_SECRET);
	  		$callbackTwitter = CONFIG::URL . CONFIG::TWITTER_CALLBACK;
			$request_token = $connection->getRequestToken($callbackTwitter);
			$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
			$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
			switch ($connection->http_code) {
			  case 200:
			  $url = $connection->getAuthorizeURL($token);
			    header('Location: ' . $url); 
			    break;
			   
			  	default:  $this->addNotification('error',
	  								__('Could not connect to Twitter. Refresh the page or try again later.')
				);
			}
		}
	}
}
else if(USER::isStudent())
{
	abstract class Page_twitterAuth extends Page_selectStudentGroup {
		public function __construct()
		{
			parent::__construct();
			
		}
		public function display($gid) 
		{	
			$gid = intval($gid);
			$_SESSION['gid'] = $gid;
			if(!empty($_REQUEST['callback'])) {
				if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
					 	 $_SESSION['oauth_status'] = 'oldtoken';
					  	 $this->connect();
				}
				$connection = new TwitterOAuth(CONFIG::TWITTER_CONSUMER_KEY,CONFIG::TWITTER_CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
				$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
				$_SESSION['access_tokenTwitter'] = $access_token;
				unset($_SESSION['oauth_token']);
				unset($_SESSION['oauth_token_secret']);
				if (200 == $connection->http_code) {
				 $_SESSION['status'] = 'verified';
				} 
				else 
				{
				 $this->connect();
				}
			}
			if(!empty($_SESSION['access_tokenTwitter']))
			{
				$access_token = $_SESSION['access_tokenTwitter'];
			}
			if(!empty($access_token['oauth_token'])) 
			{
				$_SESSION['access_tokenTwitter'] = $access_token;
				$this->display2($gid);
			}
			else if(empty($_SESSION['access_tokenTwitter']) || empty($_SESSION['access_tokenTwitter']['oauth_token']) || empty($_SESSION['access_tokenTwitter']['oauth_token_secret'])) 
			{

	    		$this->connect();
			}
		}
		/**
		 * This function must be implemented in a subclass
		 * Shows a page after access to twitter has been granted
		 *
		 * @return void
		 */
		protected abstract function display2($gid);
		/**
		 * This function connects to twitter
		 * @return void
		 */
		private function connect() {
			if (CONFIG::TWITTER_CONSUMER_KEY === '' || CONFIG::TWITTER_CONSUMER_SECRET === '') {
	  			$this->addNotification('error',
	  								__('Twitter does not work at the moment. Please, contact administrator.')
				);
	  		}
	  		$connection = new TwitterOAuth(CONFIG::TWITTER_CONSUMER_KEY, CONFIG::TWITTER_CONSUMER_SECRET);
	  		$callbackTwitter = CONFIG::URL . CONFIG::TWITTER_CALLBACK;
			$request_token = $connection->getRequestToken($callbackTwitter);
			$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
			$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
			switch ($connection->http_code) {
			  case 200:
			  $url = $connection->getAuthorizeURL($token);
			    header('Location: ' . $url); 
			    break;
			   
			  	default:  $this->addNotification('error',
	  								__('Could not connect to Twitter. Refresh the page or try again later.')
				);
			}
		}
	}
}

?>