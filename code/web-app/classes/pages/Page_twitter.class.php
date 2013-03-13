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
		$tweet = '';
		if(!empty($_REQUEST['userTweet']))
		{
			$tweet = $_REQUEST['userTweet'];
		}
		$gName = $this->getGroupName($gid);
		$id = '';
		if(!empty($_REQUEST['id']))
		{
			$id = $_REQUEST['id'];
		}
		$username = '';
		if(!empty($_REQUEST['username']))
		{
			$username = $_REQUEST['username'];
		}
		if(!empty($gName))
		{
			if(!empty($_REQUEST['tweeting']))
			{
				if(strlen($tweet) > 0 && $this->tweet($gid, $connection, $tweet))
				{
					$this->addNotification(
						'notice',
						__('You have tweeted successfully!')
					);
					$this->displayMenu($gid);
				}
				else
				{
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
					$this->tweetForm($gid);
				}
			}
			else if(!empty($_REQUEST['replyTweet']))
			{
				if(strlen($tweet) > 0 && $this->retweet($gid, $connection, $id, $tweet,$username))
				{
					$this->addNotification(
						'notice',
						__('You have replied successfully!')
					);	
				}
				else
				{
					$this->addNotification(
						'error',
						__('An error occured while processing your request.')
					);
				}
			}
			else if(!empty($_REQUEST['viewReplies']))
			{	
				$this->displayTweetReplies($gid, $connection, $id);
			}
			else if(!empty($_REQUEST['viewTweets']))
			{
				$this->displayTweets($gid, $connection);
			}
			else if(!empty($_REQUEST['tweetForm']))
			{
				$this->tweetForm($gid);
			}
			else{
				$this->displayMenu($gid);
			}
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
	 * Displays menu to user
	 *
	 * @return void
	 */
	private function displayMenu($gid)
	{
		$html = '';
		$html .= '<a href="?action=twitter&tweetForm=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('Tweet').'</a>';
		$html .= '<a href="?action=twitter&viewTweets=1&gid='.$gid.'" data-role="button" data-theme="b">'.__('View Tweets').'</a>';
		$this->addHtml($html);
	}
	/**
	 * display tweeting form
	 *
	 * @return void
	 */
	private function tweetForm($gid)
	{
		$html = '<form method="post" action="">';
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		$html .= '<input name="tweeting" value="1" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="userTweet">' . __('Enter Text') . ': </label>';
		$html .= '<input type="text" name="userTweet" id="userTweet" ';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Tweet') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
	/**
	 * Tweet
	 *
	 * @return void
	 */
	private function tweet($gid, $connection, $tweet)
	{
		$hashtag = ' #aStrInGthAtNoOneIsSupPosEdtOuSe' . $gid;
		$tweet .= $hashtag;
		$success = $connection->post('statuses/update', array('status' => $tweet));
		return $success;
	}
	/**
	 * Display tweets for group
	 *
	 * @return void
	 */
	private function displayTweets($gid, $connection)
	{
		$this->printListHeaderTweets();
		$tweets = $this->getTweets($gid, $connection);
		$count = 0;
		foreach($tweets as $key)
		{
			$username = $connection->get('users/show', array('id' => $key->user->id));
			if (strpos($key->text,' #aStrInGthAtNoOneIsSupPosEdtOuSe'.$gid) !== false) 
			{
				$count++;
				$this->printListItemTweets($gid, $key->id, $key->text, $key->user->name, $username->screen_name);
			}
		}
		if($count == 0)
		{
			$this->addNotification('notice',__('No tweets to display'));
		}
		$this->printListFooterTweets();
	}
	/**
	 * get tweets for group
	 *
	 * @return void
	 */
	private function getTweets($gid, $connection)
	{
		$tweets = $connection->get('statuses/home_timeline');
		return $tweets;
	}
	/**
	 * Prints the header for the list of groups
	 *
	 * @return void
	 */
	private function printListHeaderTweets()
	{
		$html='';
		$html .= '<ul data-role="listview" data-divider-theme="b" ';
        $html .= 'data-inset="true">';
        $html .= '<li data-role="list-divider" role="heading">';
        $html .= 'Tweets';
        $html .= '</li>';
        $this->addHtml($html);
	}
	/**
	 * Prints a single item for the list of groups
	 *
	 * @return void
	 */
	private function printListItemTweets($gid, $id, $text, $author, $username)
	{
		$this->addHtml(
	        sprintf(
	        	'<li><a href="?action=%s&viewReplies=1&gid=%d&id=%s&username=%s">%s
	        	 <span class="ui-li-count">
                  %s
                 </span></a></li>',
	        	urlencode(htmlspecialchars($_REQUEST['action'])),
	        	$gid,
	        	$id,
	        	$username, 
	        	$text,
	        	$author
	        )
        );
	}
	/**
	 * Prints the footer for the list of groups
	 *
	 * @return void
	 */
	private function printListFooterTweets()
	{
        $this->addHtml('</ul>');
	}
	/**
	 * Retweet
	 *
	 * @return void
	 */
	private function retweet($gid, $connection, $id, $tweet, $username)
	{
		$tweet = '@'.$username. ' ' . $tweet;
		$success = $connection->post('statuses/update', array('status' => $tweet, 'in_reply_to_status_id' => $id));
		return $success;
	}
	/**
	 * Display tweets for group
	 *
	 * @return void
	 */
	private function displayTweetReplies($gid, $connection, $id)
	{
		$this->printListHeaderTweets();
		$tweets = $this->getTweets($gid, $connection);
		$count = 0;
		foreach($tweets as $key)
		{
			if (intval($key->in_reply_to_status_id) == intval($id)) 
			{
				$count++;
				$username = $connection->get('users/show', array('id' => $key->user->id));
				$this->printListItemTweets($gid, $key->id, $key->text, $key->user->name, $username->screen_name);
			}
		}
		$this->printListFooterTweets();
		if($count == 0)
		{
			$this->addNotification('notice',__('No tweets to display'));
		}
		$html = '<form method="post" action="">';
		$html .= '<input name="gid" value="'.$gid.'" type="hidden" />';
		$html .= '<input name="id" value="'.$id.'" type="hidden" />';
		$html .= '<input name="replyTweet" value="1" type="hidden" />';
		$html .= '<div data-role="fieldcontain">';
		$html .= '<label for="userTweet">' . __('Enter Text') . ': </label>';
		$html .= '<input type="text" name="userTweet" id="userTweet" ';
		$html .= '</div>';
		$html .= '<input data-theme="b" type="submit" value="' . __('Reply') . '" />';
		$html .= '</form>';
		$this->addHtml($html);
	}
}
?>