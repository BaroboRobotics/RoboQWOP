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
    $sql = "UPDATE robots SET status=" . $_POST['status'] . " WHERE number = " . $_POST['robot_number'];
	$mysqli->query($sql);
} catch (Exception $e) {
    echo "Database error";
}
$mysqli->close();
echo $sql;
?>
