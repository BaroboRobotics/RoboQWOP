<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '{ "result":"error","msg":"invalid session id" }';
    exit();
}
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    echo '{ "result":"error","msg":"Connection failed" }';
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $mysqli->query("DELETE FROM controllers WHERE user_id = " . $user_id); // remove user from controllers
	$mysqli->query("DELETE FROM queue WHERE user_id = " . $user_id); // remove user from queue
	unset($_SESSION['user_id']); // delete the session 
} catch (Exception $e) {
    $mysqli->rollback();
}

?>
<html>
<head>
<meta http-equiv="refresh" content="0;URL='index.php'">
</head>
<body></body>
</html>