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
	if (isset($_POST['cellnum'])) {
		UpdateNotificationSettings($user_id, isset($_POST['txten']), intval($_POST['cellnum']));
		$settingschanged = true;
	}
	$settings = GetNotificationSettings($user_id);
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
			
			<div class = "go_to_all_pings">
				<span><a href = "/index.php">All pings</a></span>
			</div>
		</div>

		<form action="settings.php" method="post">
		<div class = "settings_details">
		<?PHP
		if($settingschanged) {
			?>
		<span style="border-bottom:1px solid #000000; padding-bottom:5px;">Notification settings updated!</span>
		<a href = "index.php">
			<div class = "go_home_button">
				<span>Go back to the main page</span>
			</div>
		</a>
		<?PHP
	} else {
		?>
		<div class = "notifications_title">
			<span style="border-bottom:1px solid #000000; padding-bottom:5px;">Notifications</span>
		</div>
		<table cellpadding="5"><tr><td>
		Phone number: </td><td><input type="text" name="cellnum" value="<?=$settings['cellnum']?>"/></td></tr><tr><td>
		Receive text notifications? </td><td><input type="checkbox" name="txten" <?=($settings['txten'] ? "checked" : "unchecked")?>/></td></tr></table>
		<div class = "settings_button" onclick="document.forms[0].submit();">
				<span>Save</span>
		</div>
		<?PHP
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