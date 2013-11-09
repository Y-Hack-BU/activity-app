<?PHP
require_once("config.php");
function UpdateFriendsList($facebook) {
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
				die("Query error 1");
			}
			if (mysql_num_rows($query) < 1) {
				$query = mysql_query("INSERT INTO groups (fb_uid, flid, name) VALUES ($user_id, $flid, '".$name."');");
				if (!$query) {
					die("Query error 2");
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
					die("Query error 3".mysql_error());
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
		$query = mysql_query("INSERT INTO users (fb_uid, fname, lname, txt, cellnum) VALUES (".$user_id.", '".$user_profile['first_name']."', '".$user_profile['last_name']."', 0, 0);");
		if (!$query) {
			die("Error with query");
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
		die("Error selecting database");
	}
}
?>