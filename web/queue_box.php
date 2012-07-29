<?php
include 'config.php';
header('Content-type: application/json');

$ret_val = '{}';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    echo '{ "result":"error","msg":"Connection failed" }';
    exit();
}
try {
    
    // Get the statistics, used to show users the status of the queue and their position.
    $status = "Error retrieving status information";
    // Get the users in the queue.
    $queue_result = '"queue":[';
    $sql = "SELECT number FROM robots";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->execute();
        $stmt->bind_result($robot_number);
        $comma = false;
        while ($stmt->fetch()) {
		    //echo "$robot_number";
			$mysqli2 = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $sql2 = "SELECT q.user_id, u.first_name, u.last_name, u.country, r.name
				FROM queue q INNER JOIN users AS u on u.id = q.user_id 
				INNER JOIN robots AS r on r.number = q.robot_number
				WHERE q.robot_number = ? ORDER BY q.created asc";
			if ($stmt2 = $mysqli2->prepare($sql2)) {
			    $stmt2->bind_param('i', $robot_number);
				$stmt2->execute();
				$stmt2->bind_result($r_user_id, $r_first_name, $r_last_name, $r_country, $r_robotname);
				$position = 1;
				while ($stmt2->fetch()) {
					if ($comma) {
						$queue_result = $queue_result . ',';
					} else {
						$comma = true;
					}
					$queue_result .= '{ "user_id":' . $r_user_id . ',"first_name":"' . $r_first_name
						 . '", "last_name":"' . $r_last_name . '", "country":"' . $r_country
						 . '", "position":' . $position . ', "robot_name":"' . $r_robotname . '" }';
					
					$position += 1;
				}
				// Free result set
				$stmt2->close();
			}
        }
        // Free result set
        $stmt->close();
    }
    $queue_result .= ']';
    
    // Get the users controlling the Mobot.
    $timeleft = 0;
    $control_result = '"control":[';
    $sql = "SELECT number, name FROM robots";
    if ($result = $mysqli->prepare($sql)) {
		$result->execute();
		$result->bind_result($robot_number, $robot_name);
        // Cycle through results
        $comma = false;
        while ($row = $result->fetch()) {
            $exists = "no";
            $mysqli2 = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			$sql2 = "SELECT c.created, c.control_time, c.user_id, u.first_name, u.last_name, u.country, r.name, r.id
				FROM controllers c
				INNER JOIN users AS u on u.id = c.user_id
				INNER JOIN robots AS r on r.number = c.robot_number and c.robot_number = ?";
			if ($result2 = $mysqli2->prepare($sql2)) {
			    $result2->bind_param('i', $robot_number);
				$result2->execute();
				$result2->bind_result($c_created, $c_control_time, $c_user_id, $u_first_name, $u_last_name, $u_country, $r_name, $r_id);
				// Cycle through results
				while ($row2 = $result2->fetch()) {
					if ($comma) {
						$control_result = $control_result . ',';
					} else {
						$comma = true;
					}
					// Calculate the number of seconds left to control the mobot.
					$created_date = new DateTime($c_created);
					$created_date->add(new DateInterval("PT" . $c_control_time . "S"));
					$now_date = new DateTime();
					$interval = $now_date->diff($created_date);
                    $exists = "yes";
					$control_result .= '{ "exists":"yes", "user_id":' . $c_user_id . ',"first_name":"' . $u_first_name
						 . '", "last_name":"' . $u_last_name . '", "country":"' . $u_country
						 . '", "robot_name":"' . $r_name . '", "robot_id":' . $r_id . ', "timeleft":' . $interval->format('%s') 
						 . ',"controltime":' . $c_control_time . ' }';
					
				}
				// Free result set
				$result2->close();
				
			}
			if ($exists == "no") {
				if ($comma) {
					$control_result = $control_result . ',';
				} else {
					$comma = true;
				}
				$control_result .= '{ "exists":"no", "robot_name":"' . $robot_name . '"}';
			}
            
        }
        // Free result set
        $result->close();
    }

    $control_result .= ']';

    $mysqli->commit();
    $mysqli->close();
    $ret_val = '{' . $queue_result . ', ' . $control_result . '}';
} catch (Exception $e) {
    $mysqli->rollback();
    $ret_val = '{}';
}

echo $ret_val;
?>