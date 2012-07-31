<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: main.php');
    exit();
}
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    header('Location: main.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mysqli->query("DELETE FROM controllers WHERE user_id = " . $user_id); // remove user from controllers
$mysqli->query("DELETE FROM queue WHERE user_id = " . $user_id); // remove user from queue
unset($_SESSION['user_id']); // delete the session 
$mysqli->close();
header('Location: main.php');
?>
