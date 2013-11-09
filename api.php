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
<head></head>
<body>
<?PHP
if($user_id) {
  try {
    $user_profile = $facebook->api('/me','GET');
    echo "Name: " . $user_profile['name'];
  } catch(FacebookApiException $e) {
    print($e->getMessage(array("scope" => "read_friendlists")));
    $login_url = $facebook->getLoginUrl(); 
    echo 'Please <a href="' . $login_url . '">login.</a>';
  }   
  SetUpSQL();
  //Register user if not registered
  RegisterIfNotExists($facebook);
  //Add friends, update if already exist
  UpdateFriendsList($facebook);
} else {
  $login_url = $facebook->getLoginUrl(array("scope" => "read_friendlists"));
  echo 'Please <a href="' . $login_url . '">login.</a>';
}
?>
</body>
</html>

