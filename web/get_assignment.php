<?php
include 'config.php';
header('Content-type: application/json');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$user_id = $_POST['user_id'];

$sql = "SELECT last_completed_assignment_number FROM users_last_completed_assignment_for_course WHERE user_id = " . $user_id . " and course_id = 1;";
$result = $mysqli->query($sql);

if ($result->num_rows == 0) {
    $number = 1;
} else {
    $row = $result->fetch_object();
    $number = $row->last_completed_assignment_number + 1;
}
$sql = "SELECT show_tutorial FROM users WHERE id = " . $user_id . ";";
$result = $mysqli->query($sql);
$row = $result->fetch_object();
$show_tutorial = $row->show_tutorial;
$sql = "SELECT objective, instructions, youtube_url FROM assignments WHERE number = " . $number . " and course_id = 1;";
$result = $mysqli->query($sql);
if ($result->num_rows == 0 || $show_tutorial == 0) {
    echo '{"completed": true}';
} else {
	$row = $result->fetch_object();
	$objective = $row->objective;
	$instructions = $row->instructions;
	$youtube_url = $row->youtube_url;
	echo '{"completed": false, "number": ' . $number . ', "objective": "' . $objective .'", "instructions": "' . $instructions . '", "youtube_url": "' . $youtube_url . '"}';

}
$mysqli->close();
?>
