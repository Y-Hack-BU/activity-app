<?PHP
ini_set('display_errors',1); 
error_reporting(E_ALL);
require_once("config.php");

SetUpSQL()
SetUpTwilio();
GetAllPingsToNotify();

// connect to database
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

// initialize Twilio account
function SetUpTwilio() {
    require "twilio-php-latest/Services/Twilio.php";
 
    // set AccountSid and AuthToken from www.twilio.com/user/account
    $AccountSid = "ACc0a7830f8d8a4a6e838b4217a96b9112";
    $AuthToken = "7a2b7c9d7d98b5aff647e94f29417731";
 
    // instantiate a new Twilio REST Client
    $client = new Services_Twilio($AccountSid, $AuthToken);
}



// identify users to ping
function GetAllPingsToNotify() {

	$query = mysql_query("SELECT fb_uid FROM users;")

	// get all user ids in the database
	while ($row = mysql_fetch_assoc($query)) {
    	$uids[] = $row['fb_uid'];
	}

	// get all matching pings for each user
	foreach ($uids as $uid) {
		$pings = GetMatchingPings($uid);

		// look in ping_match
			// for each pings as ping:
				// Select ping = pid2 && uid1 = uid (current user id)
					// in selected row, select txt1
					// Notify if txt1 is zero
						// get necessary information from users
					// update txt1 to 1

		foreach ($pings as $ping) {
			$query = mysql_query("SELECT txt1 FROM ping_match WHERE pid2 = $ping and uid1 = $uid;");

			$txt = mysql_fetch_assoc($query);

			// if not yet notified (txt = 0)
			if ($txt = 0) {

				// get number of user
				$query = mysql_query("SELECT * FROM users WHERE fb_uid = $uid;");
				$res = mysql_fetch_assoc($query);
				$number = $res['cellnum'];
				$fname = $res['fname'];
				$lname = $res['lname'];
				$txten = $res['texten'];


				if ($txten) { // if user has enabled texting

					// get type, time, duration of ping
					$query = mysql_query("SELECT * FROM pings WHERE id = $ping;");
					$res = mysql_fetch_assoc($query);
					$type = $res['type'];
					$detail = $res['detail'];
					$start = $res['start'];
					//date("m.d.y h:m", $time)
					$duration = $res['duration'];

					// format times


				// send notification
				SendNotification($number, $fname, $lname, $type, $detail, $start, $duration);
				}
			}
		}
	}
}

// notify users
function SendNotification($number, $fname, $lname, $type, $detail, $start, $duration) {

	$sms = $client->account->messages->sendMessage(

		"617-340-8283", // Pinguin's TRIAL Twilio number

		"+15087855723", // User's number

		// message:
		"This is a test!"
		//"\n $first $last\nwants to $type\n\"$detail\"\n".TimeCaption($start,$duration);

	);
}

function TimeCaption($start, $duration) {
	if (intval($start) < time()) {
		return "@ Now   (".date("h")." h)";
	} else {
		return "@ ".date("D m.d.y", intval($pingdata['start']));
	}
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

// end ?PHP
?>