<?php
require_once('php-sdk/src/facebook.php');
require_once('functions.php');

$config = array(
  'appId' => '753931801289347',
  'secret' => 'e95fb268d31c8b910bc5831cfeb65e83',
  );

$facebook = new Facebook($config);
$user_id = $facebook->getUser();

SetUpSQL();
if($user_id) {
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
			<div class = "go_to_my_pings">
				<span><a href = "/my_pings.php">my pings</a></span>
			</div> 
			
			<div class = "gear_button">
				<a href = "settings.php">
					<img src = "../img/whitebutton1.png" />
				</a>
			</div>
		</div>
		
		<div class = "my_pings_timeline">
			<div class = "divider_all_pings">
				<span>My Pings</span>
				<hr>
				<?PHP
					$mypings = GetUnexpiredPings($user_id);
					if (count($mypings) == 0) {
						?>
		<br /><span style="text-align:center">You don't have any pings right now</span>
		<?PHP
	}
				?>
			</div>
<?php
	foreach ($mypings as $ping) {
		$ping = GetPingData($ping);
?>
			<div class = "timeline_item">
				<a href="delete.php?pid=<?=$ping['id']?>"><img src = "../img/x-it.png" /></a>
<div class = "orange_dot">
	<img src = "../img/ping_icon.png" />
</div>
				<span class = "timeline_name"><?=GetName($ping['owner_uid'])?></span>
				<span class = "timeline_wants_to"><?=WantsToCaption($ping)?></span>
				<span class = "timeline_details"><?=(strlen($ping['detail']) > 0 ? "\"".$ping['detail']."\"" : ". . .")?></span>
				<span class = "timeline_time"><?=TimeCaption($ping)?></span>
			</div>	
			<?php
		}
		?>
			
		</div>
		
	</body>
</html>
<?PHP
} else {
	header("Location: index.php");
}
?>