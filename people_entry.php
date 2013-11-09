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
		
		<form action="submit.php" method="post">
		<?PHP
			foreach ($_POST as $k=>$v) {
				?>
					<input type="hidden" name="<?=$k?>" value="<?=$v?>" />
				<?PHP
			}
		?>
		<div class = "people_to_share">
			
			<span>With whom do you want to share with?</span>
			
			<div class = "groups_checklist">

			<?PHP
				$flists = GetFriendsLists($facebook);
				foreach ($flists as $flist) {
			?>
				<div class = "group_checkbox">
					<span><?=$flist['name']?></span>
					<input type="checkbox" name="<?=$flist['flid']?>" unchecked>
				</div>
				
				<hr>
				<?PHP
			}
			?>


			</div>
			
		</div>
		
		<div class = "who_submit_button">
				<span><input type="submit" value="Submit" /></span>
		</div>
		</form>
	</body>
</html>
<?PHP
} else {
	header("Location: index.php");
}
?>