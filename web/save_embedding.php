<?php
include 'config.php';
session_start();
if ((!isset($_SESSION['user_id'])) || (!$_SESSION['is_admin'])) {
    echo 'Must be admin';
    exit();
}
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    echo 'Database error';
    exit();
}

try {
    $sql = "UPDATE mobot_arenas SET google_hangout_url='" . $_POST['google_hangout_url'] . "', ustream_profile_url='" . $_POST['ustream_profile_url'] . "', ustream_embed_url='" . $_POST['ustream_embed_url'] . "' WHERE id = 1";
	$mysqli->query($sql);
} catch (Exception $e) {
    echo "Database error";
}
$mysqli->close();
header('Location: index.php');
?>
