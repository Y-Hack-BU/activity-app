<?PHP
ini_set('display_errors',1); 
error_reporting(E_ERROR);
require_once("config.php");
require_once("functions.php");
// initialize Twilio account
require_once ("twilio-php-latest/Services/Twilio.php");

SetUpSQL();
GetAllPingsToNotify();

// identify users to ping
function GetAllPingsToNotify() {

	$query = mysql_query("SELECT fb_uid FROM users;");
	if(!$query){
		die("Query Error!");
	}

	// get all user ids in the database
	while ($row = mysql_fetch_assoc($query)) {
    	$uids[] = $row['fb_uid'];
	}

	// get all matching pings for each user
	foreach ($uids as $uid) {
		print($uid);
		$pings = GetMatchingPings($uid);
		// look in ping_match
			// for each pings as ping:
				// Select ping = pid2 and uid1 = uid (current user id)
					// in selected row, select txt1
					// Notify if txt1 is zero
						// get necessary information from users
					// update txt1 to 1

		foreach ($pings as $ping) {

			print($ping);

			$query = mysql_query("SELECT txt1 FROM ping_match WHERE pid2 = $ping and uid1 = $uid;");
				if(!$query){
					die("Query Error!");
				}

			$txt = mysql_fetch_assoc($query);

			// if not yet notified (txt = 0)
			if (intval($txt['txt1']) == 0) {

				// get number of user
				$query = mysql_query("SELECT * FROM users WHERE fb_uid = $uid;");
				if(!$query){
					die("Query Error!");
				}
				$res = mysql_fetch_assoc($query);
				$number = $res['cellnum'];
				$txten = $res['txten'];

				if ($txten) { // if user has enabled texting

					// get type, time, duration of ping
					$query = mysql_query("SELECT * FROM pings WHERE id = $ping;");
					if(!$query){
						die("Query Error!");
					}
					$res = mysql_fetch_assoc($query);
					$type = $res['type'];
					$detail = $res['detail'];
					$start = $res['start'];
					//date("m.d.y h:m", $time)
					$duration = $res['duration'];

					$owner_uid = $res['owner_uid'];

					// get names of sender
					$query = mysql_query("SELECT fname, lname, cellnum FROM users WHERE fb_uid = $owner_uid;");
					if(!$query){
						die("Query Error!");
					}
					$res = mysql_fetch_assoc($query);
					$fname = $res['fname'];
					$lname = $res['lname'];

					// update txt
					$query = mysql_query("UPDATE ping_match SET txt1 = 0 WHERE pid2 = $ping and uid1 = $uid;");	
					if(!$query){
						die("Query Error!");
					}

				// send notification
				SendNotification($res, $number, $fname, $lname, $type, $detail, $start, $duration);
				}
			}
		}
	}
}

// notify users
function SendNotification($res, $number, $fname, $lname, $type, $detail, $start, $duration) {

    // set AccountSid and AuthToken from www.twilio.com/user/account
    $AccountSid = "ACc0a7830f8d8a4a6e838b4217a96b9112";
    $AuthToken = "7a2b7c9d7d98b5aff647e94f29417731";
 
    // instantiate a new Twilio REST Client
    $client = new Services_Twilio($AccountSid, $AuthToken);

	//print("\n $first $last\nwants to $type\n\"$detail\"\n");

	$sms = $client->account->messages->sendMessage(

		"617-340-8283", // Pinguin's TRIAL Twilio number

		$number, // User's number

		// message:
		"\n\n$fname $lname\n".WantsToCaption($res)."\n\"$detail\"\n".TimeCaption($res)

	);
}

// end ?PHP
?>