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

function DeletePing($pingid) {
	$query = mysql_query("DELETE FROM pings WHERE id = $pingid;");
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
	$query = mysql_query("INSERT INTO pings (owner_uid, type, detail, start, duration, timestamp) VALUES ($user_id, $type, '".mysql_real_escape_string($detail)."', $start, $duration, $now);");
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
	$query = mysql_query("SELECT id FROM pings WHERE start + duration > $now AND owner_uid = $user_id;");
	if (!$query) {
		die("Query error:".mysql_error());
	}
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
		//foreach:
			//SELECT * FROM pings WHERE pingid = $result
			//append $results as potential matching pings
	//return result
	$now = time();
	$ret = array();
	$query = mysql_query("SELECT flid FROM group_mems WHERE fid = $user_id;");
	if (!$query) {
		die("Query error".mysql_error());
	}
	while ($row = mysql_fetch_assoc($query)) {
    	$flid = $row['flid'];
    	$subquery = mysql_query("SELECT pingid FROM ping_perm WHERE flid = $flid;");
    	if (!$subquery) {
    		die("Query error".mysql_error());
    	}
		while ($subrow = mysql_fetch_assoc($subquery)) {
			$pingid = $subrow['pingid'];
    		$subsubquery = mysql_query("SELECT start, duration FROM pings WHERE id = $pingid");
    		if (!$subsubquery) {
    			die("Query error");
    		}
    		$res = mysql_fetch_assoc($subsubquery);
    		if (intval($res['start'])+intval($res['duration']) > $now) {
    			$ret[] = $pingid;
    		}
    	}
	}
	$ret = array_unique($ret, SORT_NUMERIC);
	return $ret;
}

function GetMatchingPings($user_id) {
	//GetAllFriendlyPings as potential pings
	//SELECT * from pings WHERE owner_uid = $user_id
	//foreach result:
		//foreach potential ping:
			//if types are equal and times overlap, add match to database and add to return values
			//times overlap if: NOT(end of at least one of the time frames comes before the other)
	//return return values
	$friendly = GetAllFriendlyPings($user_id);
	$mine = GetUnexpiredPings($user_id);
	foreach ($mine as $m) {
		foreach ($friendly as $p) {
			$m = GetPingData($m);
			$p = GetPingData($p);
			$endm = $m['start'] + $m['duration'];
			$endp = $p['start'] + $p['duration'];
			if ($m['type'] == $p['type'] && ($endm - $p['start']) > 0 && ($endp - $m['start']) > 0) {
				InsertPingMatch($m['id'], $p['id'], $m['owner_uid'], $p['owner_uid']);
				InsertPingMatch($p['id'], $m['id'], $p['owner_uid'], $m['owner_uid']);
				$ret[] = $p['id'];
			}
		}
	}
	$ret = array_unique($ret, SORT_NUMERIC);
	return $ret;
}

function InsertPingMatch($pid1, $pid2, $uid1, $uid2) {
	$now = time();			
	$query = mysql_query("INSERT INTO `ping_match` (pid1, pid2, uid1, uid2, txt1, txt2, timestamp) SELECT $pid1, $pid2, $uid1, $uid2, 0, 0, $now FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `ping_match` WHERE pid1=$pid1 AND pid2=$pid2 AND uid1=$uid1 AND uid2=$uid2) LIMIT 1;");
	if (!$query) {
		die("Query error");
	}
	return true;
}

function GetName($user_id) {
	$query = mysql_query("SELECT fname, lname FROM users WHERE fb_uid = $user_id;");
	if (!$query) {
		die("Query error");
	}
	$res = mysql_fetch_assoc($query);
	return $res['fname']." ".$res['lname'];
}

function WantsToCaption($pingdata) {
	if ($pingdata['type'] == 0) {
		return "Wants to get food";
	} else if ($pingdata['type'] == 1) {
		return "Wants to study";
	} else if ($pingdata['type'] == 2) {
		return "Wants to go to an event";
	} else {
		return "Wants to get active";
	}
}

function TimeCaption($pingdata) {
	if (intval($pingdata['start']) < time()) {
		return "@ Now";
	} else {
		return date("D m.d.y", intval($pingdata['start']));
	}
}
?>