<?php=
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
    $sql = "UPDATE mobot_arenas SET status=" . $_GET['status'] . " WHERE id = " . $_GET['arena_id'];
	$mysqli->query($sql);
} catch (Exception $e) {
    echo "Database error";
}
$mysqli->close();
header('Location: index.php');
?>
