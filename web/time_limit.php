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

try {
	$ret_val = '{"active":false,"status":"Error occurred retrieving status."}';
	// Do the following operations iwthin the transaction.
	$mysqli->autocommit(FALSE);
	// Update the active session.
	$mysqli->query("update sessions set active = 0");
	$mysqli->query("update sessions set active = 1 where active = 0 order by created asc limit 1");
	$mysqli->query("update sessions set started = NULL where active = 0");
	$mysqli->query("update sessions set last_active = CURRENT_TIMESTAMP where id = " . $_SESSION['id']);
	// Set session time limit based on number of users
	if ($stmt = $mysqli->prepare("SELECT count(*) from sessions")) {
		$stmt->execute();
		$stmt->bind_result($total_users);
		$stmt->fetch(); $stmt->close();
	}	
	if (($total_users >= 2) && ($total_users <= 8)) {
	    $seconds = 180 - (($total_users - 2) * 20; // 2 users equals 180 seconds 3 users equals 160 seconds 4 users equals 120 seconds
	} else {
	    $seconds = 40;
	}

$mysqli->commit();
} catch (Exception $e) {
	$mysqli->rollback();
}
$mysqli->close();
if (($total_users == 1)) {
echo "No time limit"
} else {
echo $seconds;
}
?>
