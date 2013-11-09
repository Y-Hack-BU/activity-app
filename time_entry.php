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

SetUpSQL();
if($user_id) {
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />
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
    <form action="people_entry.php?type=<?=$_GET['type']?>" method="post">
		<div class = "add_details">
			<span>Do you want to add any details?</span>
			<input type = "text" name="details" class = "details_question_input" line = "2"/>
		</div>
		
		<div class = "when">
			
			<span>When do you want to do this?</span>
			
			<div class = "now_or_later">
				<input type="radio" name="group1" value="Now"> Now
				<input type="radio" name="group1" value="Later" checked> Later<br>
			</div>
			
			<div class = "when_start_time">
				<input type="date" name="sdate" class = "start_date_entry"/>
				<input type="time" name="stime" class = "start_time_entry"/>
			</div>
			
			<div class = "when_end_time">
				<input type="date" name="edate" class = "end_date_entry"/>
				<input type="time" name="etime" class = "end_time_entry"/>
			</div>
			
		</div>
		
		<div class = "when_next_button">
				<span><input type="submit" value="Next" /></span>
		</div>
	</form>

		
	</body>
</html>
<?PHP
} else {
	header("Location: index.php");
}
?>