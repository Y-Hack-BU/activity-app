<?PHP
require_once("config.php");
function UpdateFriendsLists($facebook) {
	$user_id = $facebook->getUser();
	try {
		$params = array(
			'method' => 'fql.query',
			'query' => "SELECT name,flid FROM friendlist WHERE owner=me()",
			);

		$result = $facebook->api($params);
		foreach ($result as $r) {
			$name = $r['name'];
			$flid = $r['flid'];
			$query = mysql_query("SELECT id FROM groups WHERE flid = $flid");
			if (!$query) {
				die("Query error");
			}
			if (mysql_num_rows($query) < 1) {
				$query = mysql_query("INSERT INTO groups (fb_uid, flid, name) VALUES ($user_id, $flid, '".$name."');");
				if (!$query) {
					die("Query error");
				}

			}
			$params = array(
				'method' => 'fql.query',
				'query' => "SELECT uid FROM friendlist_member WHERE flid=$flid",
				);
			$result = $facebook->api($params);
			foreach ($result as $uid) {
				$uid = $uid['uid'];
				$query = mysql_query("INSERT INTO `group_mems` (flid,fid) SELECT $flid, $uid FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `group_mems` WHERE flid=$flid AND fid=$uid) LIMIT 1");
				if (!$query) {
					die("Query error");
				}
			}
		}
	} catch(FacebookApiException $e) {
		print($e->getMessage());
	}   
}

function RegisterIfNotExists($facebook) {
	$user_id = $facebook->getUser();
	$query = mysql_query("SELECT id FROM users WHERE fb_uid = ".$user_id);
	if (mysql_num_rows($query) < 1) {
      //Need to register user
		$query = mysql_query("INSERT INTO users (fb_uid, fname, lname, txten, cellnum) VALUES (".$user_id.", '".$user_profile['first_name']."', '".$user_profile['last_name']."', 0, 0);");
		if (!$query) {
			die("Query error");
		} else {
			print "User registered!!";
		}
	}
}

function SetUpSQL() {
	$query = mysql_connect(SQL_HOST, SQL_USER, SQL_PASS);
	if (!$query) {
		die("Error connecting");
	}
	$query = mysql_query("USE ".SQL_DB.";");
	if (!$query) {
		die("Query error");
	}
}

function DeletePing($user_id, $pingid) {

}

function GetMyPendingPings($user_id) {
	//GetUnexpiredPings($user_id)
}

function InsertPing($user_id, $type, $detail, $start, $duration) {

}

function GetUnexpiredPings($user_id) {
	
	//SELECT * FROM pings WHERE start+duration > currenttime AND owner_uid = $result
}

function GetAllFriendlyPings($user_id) {
	//SELECT flid FROM group_mems WHERE fid = $user_id
	//foreach flid as $result:
		//SELECT fb_uid FROM groups WHERE flid = $result
		//GetUnexpiredPings(fb_uid)
		//append $results as potential matching pings
	//return results
}

function GetMatchingPings($user_id) {
	//GetAllFriendlyPings as potential pings
	//SELECT * from pings WHERE owner_uid = $user_id
	//foreach result:
		//foreach potential ping:
			//if types are equal and times overlap, add match to database and add to return values
	//return return values
}

function InsertPingMatch($pid1, $pid2, $uid1, $uid2) {

}
?>