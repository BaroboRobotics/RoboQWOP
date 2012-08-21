<?php 
function arena_status($arena_id) 
{ 
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!mysqli_connect_errno()) {

		$sql = "SELECT status FROM mobot_arenas WHERE id = " . $arena_id;
		$result = $mysqli->query($sql);
		$row = $result->fetch_object();
		$status = $row->status;
		$result->close();
		// Close the connection.
		$mysqli->close();
		return $status;
	}
} 
?>