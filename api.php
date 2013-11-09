<?php
ini_set('display_errors',1); 
 error_reporting(E_ALL);
  // Remember to copy files from the SDK's src/ directory to a
  // directory in your application on the server, such as php-sdk/
  require_once('php-sdk/src/facebook.php');

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

  <?php
    if($user_id) {
      try {

        $user_profile = $facebook->api('/me','GET');



        echo "Name: " . $user_profile['name'];
          

      } catch(FacebookApiException $e) {
        // If the user is logged out, you can have a 
        // user ID even though the access token is invalid.
        // In this case, we'll get an exception, so we'll
        // just ask the user to login again here.
        print($e->getMessage());
        $login_url = $facebook->getLoginUrl(); 
        echo 'Please <a href="' . $login_url . '">login.</a>';
        error_log($e->getType());
        error_log($e->getMessage());
      }   
      //Register user if not registered
      $query = mysql_connect("localhost", "pinguinadmin", "BU\|/H4X!!");
      if (!$query) {
        die("Error connecting");
      }
      $query = mysql_query("USE pinguin;");
      if (!$query) {
        die("Error selecting database");
      }
      $query = mysql_query("SELECT id FROM users WHERE fb_uid = ".$user_id);
      if (mysql_num_rows($query) < 1) {
        //Need to register user
        $query = mysql_query("INSERT INTO users (fb_uid, fname, lname, txt, cellnum) VALUES (".$user_id.", '".$user_profile['first_name']."', '".$user_profile['last_name']."', 0, 0);");
        if (!$query) {
          die("Error with query");
        } else {
          print "User registered!!";
        }
      }

      //Add friends, update if already exist



      // We have a user ID, so probably a logged in user.
      // If not, we'll get an exception, which we handle below.

    } else {

      // No user, print a link for the user to login
      $login_url = $facebook->getLoginUrl(array("scope" => "read_friendlists"));
      echo 'Please <a href="' . $login_url . '">login.</a>';

    }

  ?>

  </body>
</html>

