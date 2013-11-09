<?php
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
			<a href = "index.php">
				<div class = "header_logo">
					<img src = "../img/pinguin_logo.png" />
					<span>Pinguin</span>
				</div>
			</a>
			<?PHP
			if($user_id) { ?>
			<div class = "go_to_my_pings">
				<span><a href = "/my_pings.php">my pings</a></span>
			</div> 
			
			<div class = "gear_button">
				<a href = "settings.php">
					<img src = "../img/whitebutton1.png" />
				</a>
			</div>
			<?PHP
			
		}
		?>
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
				<a href="time_entry.php?type=0"><div class = "type_button" id = "food_button">
					<span>Food</span>
				</div></a>
				<a href="time_entry.php?type=1"><div class = "type_button" id = "study_button">
					<span>Study</span>
				</div></a>
				<a href="time_entry.php?type=2"><div class = "type_button" id = "event_button">
					<span>Event</span>
				</div></a>
				<a href="time_entry.php?type=3"><div class = "type_button" id = "active_button">
					<span>Active</span>
				</div></a>	
			</div>
		</div>
		
		<div class = "timeline">
<?PHP
$matches = GetMatchingPings($user_id);
if (count($mateches) > 0) {
	?>
				<div class = "divider_matched">
				<span>Pingbacks</span>
				<hr>
			</div>
			<?
}
foreach ($matches as $match) {
	$ping = GetPingData($match);
?>

			<div class = "timeline_item">
<div class = "orange_dot">
	<img src = "../img/ping_icon.png" />
</div>
				<span class = "timeline_name"><?=GetName($ping['owner_uid'])?></span>
				<span class = "timeline_wants_to"><?=WantsToCaption($ping)?></span>
				<span class = "timeline_details"><?=(strlen($ping['detail']) > 0 ? "\"".$ping['detail']."\"" : "")?></span>
				<span class = "timeline_time"><?=TimeCaption($ping)?></span>
			</div>
<?PHP
}
$feed = GetAllFriendlyPings($user_id);
if (count($feed) > 0) { ?>
			<div class = "divider_all_pings">
				<span>All pings</span>
				<hr>
			</div>
<?PHP
}
foreach ($feed as $pingid) {
	$ping = GetPingData($pingid);
?>
			<div class = "timeline_item">
<div class = "orange_dot">
	<img src = "../img/ping_icon.png" />
</div>
				<span class = "timeline_name"><?=GetName($ping['owner_uid'])?></span>
				<span class = "timeline_wants_to"><?=WantsToCaption($ping)?></span>
				<span class = "timeline_details"><?=(strlen($ping['detail']) > 0 ? "\"".$ping['detail']."\"" : "")?></span>
				<span class = "timeline_time"><?=TimeCaption($ping)?></span>
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
		
		<div id="login_with_fb">
		<a href="<?=$login_url?>"><img src="../img/login_with_facebook.png" /></a>
		</div>
		
	</body>
</html>

<?PHP
}
?>