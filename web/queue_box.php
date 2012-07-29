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
    $sql = "SELECT c.created, c.control_time, c.user_id, u.first_name, u.last_name, u.country, r.id, r.name
        FROM controllers c
        INNER JOIN users AS u on u.id = c.user_id
        INNER JOIN robots AS r on r.number = c.robot_number";
    if ($result = $mysqli->query($sql)) {
        $count = mysqli_num_rows($result);
        // Cycle through results
        $comma = false;
        while ($row = $result->fetch_object()) {
            if ($comma) {
                $control_result = $control_result . ',';
            } else {
                $comma = true;
            }
            // Calculate the number of seconds left to control the mobot.
            $created_date = new DateTime($row->created);
            $created_date->add(new DateInterval("PT" . $row->control_time . "S"));
            $now_date = new DateTime();
            $interval = $now_date->diff($created_date);

            $control_result .= '{ "user_id":' . $row->user_id . ',"first_name":"' . $row->first_name
                 . '", "last_name":"' . $row->last_name . '", "country":"' . $row->country
                 . '", "robot_name":"' . $row->name . '","timeleft":' . $interval->format('%s') 
                 . ',"controltime":' . $row->control_time . ' }';
            
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