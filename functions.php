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
	$query = mysql_query("DELETE * FROM pings WHERE owner_uid = $user_id AND $id = $pingid;");
	if (!$query) {
		die("Query error");
	}
	return true;
}

function GetMyPendingPings($user_id) {
	return GetUnexpiredPings($user_id);
}

function InsertPing($user_id, $type, $detail, $start, $duration, $flids) {
	$now = time();
	$query = mysql_query("INSERT INTO pings (owner_uid, type, detail, start, duration, time) VALUES ($user_id, $type, '".mysql_real_escape_string($detail)."', $start, $duration, $now);");
	if (!$query) {
		die("Query error");
	}
	foreach ($flids as $flid) {
		$query = mysql_query("INSERT INTO ping_perm (pingid, flid) VALUES (".mysql_insert_id().", $flid);");
		if (!$query) {
			die("Query error");
		}
	}
	return true;
}

function GetUnexpiredPings($user_id) {
	$now = time();
	$query = mysql_query("SELECT id FROM pings WHERE SUM(start + duration) > $now AND owner_uid = $user_uid");
	while ($row = mysql_fetch_assoc($query)) {
    	$ret[] = $row['id'];
	}
	return $ret;
}

function GetPingData($ping_id) {
	$query = mysql_query("SELECT * FROM pings WHERE id = $ping_id LIMIT 1");
	$row = mysql_fetch_assoc($query);
	return $row;	
}

function GetAllFriendlyPings($user_id) {
	//SELECT flid FROM group_mems WHERE fid = $user_id
	//foreach flid as $result:
		//SELECT pingid FROM ping_perm WHERE flid = $result
		//SELECT * FROM pings WHERE pingid = $result
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