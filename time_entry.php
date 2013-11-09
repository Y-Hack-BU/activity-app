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
		<script>
			function toggleinputs() {
				if (document.getElementById('group1now').checked) {
					document.getElementById('when_start_time').style.visibility = 'hidden';
				} else {
					document.getElementById('when_start_time').style.visibility = '';
				}
			}
		</script>
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
    <form action="people_entry.php?type=<?=$_GET['type']?>" method="post">
		<div class = "add_details">
			<span>Any important details?</span>
			<input type = "text" name="details" class = "details_question_input" line = "2"/>
		</div>
		
		<div class = "when">
			
			<span>When do you want to do this?</span>
			
			<div class = "now_or_later" >
				<input type="radio" name="group1" id="group1now" value="Now" onclick="toggleinputs();" onchange="toggleinputs();"> Now
				<input type="radio" name="group1" id="group1later" value="Later" onclick="toggleinputs();" onchange="toggleinputs();" checked> Later<br>
			</div>
			
			<div class = "when_start_time" id="when_start_time">
				<input type="date" name="sdate" class = "start_date_entry"/>
				<input type="time" name="stime" class = "start_time_entry"/>
			</div>
			<?PHP
			if (intval($_GET['type']) == 2) {
			?>
			<div class = "when_duration">
				<span>Duration</span>
				<select name="duration" class = "duration_entry">
					<option value = "0">0 hours (event)</option>
				</select>
			</div>
			<?PHP
		} else { ?>
			<div class = "when_duration">
				<span>Duration</span>
				<select name="duration" class = "duration_entry">
					<option value = "3600">1 hour</option>
					<option value = "7200">2 hours</option>
					<option value = "10800">3 hours</option>
					<option value = "18000">5 hours</option>
					<option value = "36000">10 hours</option>
					<option value = "86400">1 day</option>
					<option value = "172800">2 days</option>
					<option value = "345600">4 days</option>
					<option value = "604800">1 week</option>
				</select>
			</div>
			<?PHP } ?>
			
		</div>
		
		<div class = "when_next_button" onclick="document.forms[0].submit();">
				<span>Next</span>
		</div>
	</form>

		
	</body>
</html>
<?PHP
} else {
	header("Location: index.php");
}
?>