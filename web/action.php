<?php
include 'config.php';
header('Content-type: application/json');
session_start();
if (!isset($_SESSION['id'])): ?>
{ "result":"error","msg":"invalid session id" }
<?
	exit(); 
endif;
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()): ?>
{ "result":"error","msg":"Connection failed" }
<? 
	exit();
endif;
if ($stmt = $mysqli->prepare("SELECT active FROM sessions WHERE id = ?")) {
	$stmt->bind_param('i', $session_id_val);
        $session_id_val = $_SESSION['id'];
        $stmt->execute();
      	$stmt->bind_result($active_val);
	$stmt->fetch();
	$stmt->close();
	if ($active_val != 1): ?>
{ "result":"error","msg":"not the active user" }
	<?
		exit(); 	
	endif;
}

$fp1 = 0;
$fp2 = 0;
$bj1 = 0;
$bj2 = 0;
$speed = 45.0;
// Speed
if (isset($_POST["speed"])) {
	$speed = floatval($_POST["speed"]);
	if ($speed < 15.0) {
		$speed = 15.0; 
	} else if ($speed > 125.0) {
		$speed = 125.0;
	}
}
// Face Plate 1
if ($_POST["q"] == "1") {
	$fp1 = -1;
} else if ($_POST["w"] == "1") {
	$fp1 = 1;
}
// Face Plate 2
if ($_POST["o"] == "1") {
        $fp2 = 1;
} else if ($_POST["p"] == "1") {
        $fp2 = -1;
}
// Body Joint 1
if ($_POST["u"] == "1") {
        $bj1 = -1;
} else if ($_POST["i"] == "1") {
        $bj1 = 1;
}
// Body Joint 2
if ($_POST["e"] == "1") {
        $bj2 = -1;
} else if ($_POST["r"] == "1") {
        $bj2 = 1;
}
$host = "localhost";
$port = 8082;

if (!$fp = fsockopen($host, $port, $errno, $errstr, 2)) {
    echo '{"result":"error", "msg": "' . $errstr . '"}';
    exit();
}
fputs($fp, "$robot_number,0,$fp1,$fp2,$bj1,$bj2,$speed\n");
fclose($fp);

?>
{ "result":"success" }
