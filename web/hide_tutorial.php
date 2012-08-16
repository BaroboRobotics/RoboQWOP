<?php
include 'config.php';
header('Content-type: application/json');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$user_id = $_POST['user_id'];
$sql = "UPDATE users SET show_tutorial = 0 WHERE id = " . $user_id . ";";
$mysqli->query($sql);
$mysqli->close();
?>
