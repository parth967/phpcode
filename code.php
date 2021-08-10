<?php 

class Tagging
{
	CONST DB_TABLE 					= 'user_feed_item_tag';
	CONST DB_COL_ID 				= 'id';
	CONST DB_COL_USER_ID			= 'user_id';
	CONST DB_COL_FEED_ID			= 'feed_id';
	CONST DB_COL_WORKING 			= 'working';
	CONST DB_COL_SPOKEN_LANGUAGE 	= 'spoken_language';
	CONST DB_COL_SUBTITLES 			= 'subtitles';
	CONST DB_COL_GENRE 				= 'genre';
	CONST DB_COL_AUDIO_QUALITY 		= 'audio_quality';
	CONST DB_COL_VIDEO_QUALITY 		= 'video_quality';
	CONST DB_COL_NOT_WORKING_REASON = 'not_working_reason';
	
	public static function getTagsByFeedId($feed_id)
	{
		$feed_id = Sql::safeString((float) $feed_id);
		global $resultR, $result, $connection;
		$query = "
			 SELECT 
			 	AVG(video_quality) as video_quality, 
			 	AVG(audio_quality) as audio_quality,
				AVG(working) as working,
			 	GROUP_CONCAT(spoken_language) as spoken_language,
			 	GROUP_CONCAT(subtitles) as subtitles,
			 	GROUP_CONCAT(genre) as genre
			 FROM " . Tagging::DB_TABLE . "
			 WHERE " .  Tagging::DB_COL_FEED_ID . " = $feed_id
			 GROUP BY feed_id";
			
		Sql::dbQueryR($query);
		return (mysql_num_rows($resultR) == 0 ? false : $resultR);
	}
	
	public static function getTagsByUserId($user_id, $feed_id)
	{
		global $resultR, $result, $connection;
		
		$user_id = Sql::safeString((float) $user_id);
		$feed_id = Sql::safeString((float) $feed_id);
		
		$query = "SELECT * FROM " . Tagging::DB_TABLE . " where " . Tagging::DB_COL_FEED_ID . " = $feed_id AND " . Tagging::DB_COL_USER_ID . " = $user_id";
		Sql::dbQueryR($query);
		return (mysql_num_rows($resultR) == 0 ? false : $resultR);
	}
	
	public static function updateUserFeedItemTag($user_id, $feed_id, $working = 1, $spoken_languages = NULL, $subtitles = NULL, $genre = NULL, $audio_quality = NULL, $video_quality = NULL, $not_working_reason = NULL)
	{
		global $resultR, $result, $connection;
		$query = "";
		
		$user_id = Sql::safeString((float) $user_id);
		$feed_id = Sql::safeString((float) $feed_id);
		$working = Sql::safeString((int) $working);
		if(Tagging::getTagsByUserId($user_id, $feed_id))
		{
			$query = "UPDATE ".Tagging::DB_TABLE." SET ".
				Tagging::DB_COL_USER_ID 		. " = $user_id, " . 
				Tagging::DB_COL_FEED_ID 		. " = $feed_id, " .
				Tagging::DB_COL_WORKING 		. " = $working, " .
				Tagging::DB_COL_SPOKEN_LANGUAGE . " = $spoken_languages, " . 
				Tagging::DB_COL_SUBTITLES 		. " = $subtitles, " .
				Tagging::DB_COL_GENRE 			. " = $genre, " .
				Tagging::DB_COL_AUDIO_QUALITY 	. " = $audio_quality, " .
				Tagging::DB_COL_VIDEO_QUALITY 	. " = $video_quality, " .
				Tagging::DB_COL_NOT_WORKING_REASON		. " = '$not_working_reason' " .
				"WHERE " . 
				Tagging::DB_COL_USER_ID 		. " = $user_id AND " . 
				Tagging::DB_COL_FEED_ID 		. " = $feed_id ";	
		}
		else
		{
			$query = "INSERT INTO " . Tagging::DB_TABLE . "(
				".Tagging::DB_COL_USER_ID.",
				".Tagging::DB_COL_FEED_ID.",
				".Tagging::DB_COL_WORKING.",
				".Tagging::DB_COL_SPOKEN_LANGUAGE.",
				".Tagging::DB_COL_SUBTITLES.",
				".Tagging::DB_COL_GENRE.",
				".Tagging::DB_COL_AUDIO_QUALITY.",
				".Tagging::DB_COL_VIDEO_QUALITY.",
				".Tagging::DB_COL_NOT_WORKING_REASON."
				) VALUES($user_id,$feed_id,$working,$spoken_languages,$subtitles,$genre,$audio_quality,$video_quality,'$not_working_reason')";
		}
		return (addUserVotebyFeed($feed_id,$working));
	}
	
	public static function addUserVotebyFeed($feed_id,$working){
		//add a vote to the votes table
		$username = $_SESSION['USER']['userLogin'];
		$feed_id = Sql::safeString((float) $feed_id);
		$working = Sql::safeString((int) $working);
        $vote_query = "";
        if (Tagging::feedItemVotesExists($feed_id, $username, 'search') == false)
        {
            $vote_query = "INSERT INTO feed_items_votes (feedItemId, user, vote, dateLastVote, voteSource) VALUES ('$feed_id', '$username', '$working', NOW(), 'search')";
            Sql::dbQuery($vote_query);
        }
        else
        {
            $vote_query = "UPDATE feed_items_votes SET vote='$working' WHERE user='$username' and feedItemId='$feed_id'";
            Sql::dbQuery($vote_query);
        }

		Sql::dbQuery($query);
		return (mysql_affected_rows($connection) == 0 ? false : true);
	}
	
	public static function getMemberIdByUsername($username) 
	{
		if (isset($_SESSION['USER']['member_id']))
		{
			return $_SESSION['USER']['member_id'];
		}
		else
		{
			return NULL;
		}
	}

    public static function feedItemVotesExists ($feedItemId, $user, $voteSource)
    {
        global $resultR;

		$feed_id = Sql::safeString((float) $feedItemId);
		$user = Sql::safeString($user);
		$voteSource = Sql::safeString($voteSource);
		
        $q = "SELECT * FROM " . "feed_items_votes" . " where " . "feedItemId" . " = $feedItemId AND " . "user" . " = '$user' AND " . "voteSource" . "= '$voteSource'";
        Sql::dbQuery($q);
        return (mysql_num_rows($resultR) == 0 ? false : true);
    }
	
}

?>
