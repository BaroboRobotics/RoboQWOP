<?php
include 'config.php';
header('Content-type: application/json');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$user_id = $_POST['user_id'];
$assignment_number = $_POST['assignment_number'];
$sql = "SELECT last_completed_assignment_number FROM users_last_completed_assignment_for_course WHERE user_id = " . $user_id . " and course_id = 1;";
$result = $mysqli->query($sql);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users_last_completed_assignment_for_course(user_id, last_completed_assignment_number, course_id) VALUES(" . $user_id . ", 1, 1);";
	$mysqli->query($sql);
} else {
    $sql = "UPDATE users_last_completed_assignment_for_course SET last_completed_assignment_number=" . $assignment_number . " WHERE user_id = " . $user_id . " and course_id = 1;";
	$mysqli->query($sql);
}
$mysqli->close();
?>
