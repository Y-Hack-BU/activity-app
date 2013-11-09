<?PHP
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
$a2 = explode(":", $_POST['stime']);
$duration = intval($_POST['duration']);
$offset = intval($a2[0]) * 3600 + intval($a2[1])*60;
$flids = array();
$start = strtotime($_POST['sdate']) + $offset;
foreach ($_POST as $k=>$v) {
	if (substr($k, 0, 5) == "flid_") {
		$flids[] = end(explode("flid_", $k));
	}
}
//print("Inserted with user_id=".$user_id.", type=".$_GET['type'].", details=".$_POST['details'].", start=".$start.", dur=".$duration.", flids=".print_r($flids));

InsertPing($user_id, $_GET['type'], $_POST['details'], $start, $duration, $flids);
header("Location: success.php");
?>