<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
require_once('php-sdk/src/facebook.php');
require_once('functions.php');

$config = array(
  'appId' => '753931801289347',
  'secret' => 'e95fb268d31c8b910bc5831cfeb65e83',
  );

$facebook = new Facebook($config);
$user_id = $facebook->getUser();
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="styles/style.css">
		<link media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" 
		href="styles/mobile.css" type="text/css" rel="stylesheet" />
	</head>
	<body>
		<!-- Header -->
		<div class = "header">
			<div class = "header_logo">
				<img src = "../img/pinguin_logo.png" />
				<span>Pinguin</span>
			</div>
		</div>
<?PHP
SetUpSQL();
if($user_id) {
	RegisterIfNotExists($facebook);
  ?>
<div class = "choose_type">
			<!-- What do you want to do? -->
			<div id = "main_question"> 
				What do you want to do?
			</div>
			<!-- Buttons to choose type of activity -->
			<div id = "main_buttons">
				<div class = "type_button" id = "food_button">
					<span>Food</span>
				</div>
				<div class = "type_button" id = "study_button">
					<span>Study</span>
				</div>
				<div class = "type_button" id = "event_button">
					<span>Event</span>
				</div>
				<div class = "type_button" id = "active_button">
					<span>Active</span>
				</div>	
			</div>
		</div>
		
		<div class = "timeline">
<?PHP
$matches = GetMatchingPings($user_id);
foreach ($matches as $match) {
	$ping = GetPingData($match);
?>
			<div class = "timeline_item">
				<img src = "../img/x-it.png" />
				<span class = "timeline_name"><?=GetName($ping['owner_uid'])?></span>
				<span class = "timeline_wants_to"><?=WantsToCaption($ping)?></span>
				<span class = "timeline_time"><?=TimeCaption($ping)?></span>
				<span class = "timeline_details"><?=(strlen($ping['detail']) > 0 ? "\"".$ping['detail']."\"" : "")?></span>
			</div>
<?PHP
}
?>
<br /><br />
<?PHP
$feed = GetAllFriendlyPings($user_id);
foreach ($feed as $pingid) {
	$ping = GetPingData($pingid);
?>
			<div class = "timeline_item">
				<img src = "../img/x-it.png" />
				<span class = "timeline_name"><?=GetName($ping['owner_uid'])?></span>
				<span class = "timeline_wants_to"><?=WantsToCaption($ping)?></span>
				<span class = "timeline_time"><?=TimeCaption($ping)?></span>
				<span class = "timeline_details"><?=(strlen($ping['detail']) > 0 ? "\"".$ping['detail']."\"" : "")?></span>
			</div>	
<?PHP
}
?>
		</div>
<?PHP
} else {
  $login_url = $facebook->getLoginUrl(array("scope" => "read_friendlists"));
?>		
		<img id = "login_pinguin" src = "../img/pinguin_logo.png" />
		
		<div style="margin:0 auto;display: block;position: relative;left: 50%;margin-left: -365px;margin-top: 0px;width: 160px;height: 200px;">
		<a href="<?=$login_url?>"><img src="login_with_facebook.png" /></a>
		</div>
		
	</body>
</html>

<?PHP
}
?>
