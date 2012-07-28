<?php
include 'config.php';
header('Content-type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '{ "result":"error","msg":"invalid session id" }';
    exit();
}
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    echo '{ "result":"error","msg":"Connection failed" }';
    exit();
}

$ret_val = '{"active":false,"status":"Error occurred retrieving status."}';

try {
    // Manage queue and controllers.
    $mysqli->autocommit(FALSE);
    // Update the last active time for the current user.
    $mysqli->query("UPDATE queue SET last_active = CURRENT_TIMESTAMP WHERE user_id = " . $_SESSION['user_id']);
    // Remove any controllers that are over their timelimit.
    $sql = "SELECT id, user_id, robot_number
        FROM controllers where created < (NOW() - INTERVAL control_time SECOND)";
    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_object()) {
            $user_id = $row->user_id;
            $robot_num = $row->robot_number;
            $id = $row->id;
            // Delete the controller entry.
            $mysqli->query("DELETE FROM controllers where id = " . $id);
            // Move the controller entry to back to the queue.
            $sql = "INSERT INTO queue (created, last_active, user_id, robot_number)
                values (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, ?)";
            if ($stmt = $mysqli -> prepare($sql)) {
                $stmt -> bind_param('ii', $user_id, $robot_num);
                $stmt -> execute();
                $_SESSION['queue_id'] = $stmt -> insert_id;
                $stmt -> close();
            }          
        }
        // Free result set
        $result->close();
    }
    $robot_number = 0;
    if (isset( $_SESSION['robot'] )) {
        $robot_number = $_SESSION['robot'];
    }
    // Remove inactive users from the queue.
    $mysqli->query("DELETE FROM queue WHERE last_active < (NOW() - INTERVAL 30 SECOND)");
    // Find out how many robots are available and make sure the controllers are set.
    if ($stmt = $mysqli->prepare("SELECT count(*) from controllers where robot_number = ?")) {
        $stmt->bind_param('i', $robot_number);
        $stmt->execute();
        $stmt->bind_result($total_users);
        $stmt->fetch();
        $stmt->close();
    }
    if ($total_users == 0) {
        // Insert a controller record for the user on the top of the queue for the user's robot number
        $sql = "SELECT q.id, q.user_id, u.control_time FROM queue q
                INNER JOIN users as u on u.id = q.user_id WHERE q.robot_number = $robot_number
                ORDER BY q.created asc LIMIT 1";
        $queue_id = NULL;
        if ($sub_results = $mysqli -> query($sql)) {
            
            while ($sub_row = $sub_results->fetch_object()) {
                $control_time = $sub_row->control_time;
                $queue_id = $sub_row->id;
                $user_id = $sub_row->user_id;
                $sql = "INSERT INTO controllers (created, control_time, user_id, robot_number)
                    values (CURRENT_TIMESTAMP, ?, ?, ?)";
                if ($stmt = $mysqli -> prepare($sql)) {
                    $stmt -> bind_param('iii', $control_time, $user_id, $robot_number);
                    $stmt -> execute();
                    $stmt -> close();
                }
            }
            $sub_results -> close();
        }
        // Remove the queue record.
        if (!is_null($queue_id)) {
            $mysqli->query("DELETE FROM queue where id = " . $queue_id);
        } 
    }
    
    // Get the statistics, used to show users the status of the queue and their position.
    $active = false;
    $status = "Error retrieving status information";
    // Get the users in the queue.
    $queue_result = '"queue":[';
    $sql = "SELECT q.user_id, u.first_name, u.last_name, u.country, r.name
        FROM queue q INNER JOIN users AS u on u.id = q.user_id 
        INNER JOIN robots AS r on r.number = q.robot_number
        WHERE q.robot_number = ? ORDER BY q.created asc";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('i', $robot_number);
        $stmt->execute();
        $stmt->bind_result($r_user_id, $r_first_name, $r_last_name, $r_country, $r_robotname);
        $position = 1;
        $comma = false;
        while ($stmt->fetch()) {
            if ($comma) {
                $queue_result = $queue_result . ',';
            } else {
                $comma = true;
            }
            $queue_result .= '{ "user_id":' . $r_user_id . ',"first_name":"' . $r_first_name
                 . '", "last_name":"' . $r_last_name . '", "country":"' . $r_country
                 . '", "position":' . $position . ', "robot_name":"' . $r_robotname . '" }';
            if ($r_user_id == $_SESSION['user_id']) {
                if ($position == 1) {
                    $status = "There is 1 user in front of you.";
                } else {
                    $status = "There are $position users in front of you.";
                }
            }
            $position += 1;
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
            
            if ($row->user_id == $_SESSION['user_id']) {
                $active = true;
				$robot_id = $row->id;
                $status = "You are controlling the Robot.";
                $timeleft = $interval->format('%s');
            }
        }
        // Free result set
        $result->close();
    }
    $control_result .= ']';
    $mysqli->commit();
    $mysqli->close();
    $ret_val = '{"active":' . (($active) ? 'true' : 'false') . ', "status":"' . $status . '", ' . $queue_result
        . ', ' . $control_result . ', "timeleft":"' . $timeleft . '", "controlling_robot_id": ' . $robot_id . '}';
} catch (Exception $e) {
    $mysqli->rollback();
    $ret_val = '{"active":false,"status":' . $e->getMessage() . '}';
}

echo $ret_val;
?>