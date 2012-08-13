<?php
include 'config.php';
header('Content-type: application/json');

$ret_val = '{}';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    echo '{"error":true,"msg":"Connection failed" }';
    exit();
}
try {
    $robots_val = "[";
    $queue_val = "[";
    $controllers_val = "[";
    // Get robot information.
    $sql = "SELECT name, number, address FROM robots WHERE status = 1";
    if ($result = $mysqli->query($sql)) {
        $comma = false;
        while ($row = $result->fetch_object()) {
            if ($comma) {
                $robots_val .= ',';
            } else {
                $comma = true;
            }
            $robots_val .= '{"name":"' . $row->name . '", "number":' . $row->number
                . ', "address":"' . $row->address . '" }';
        }
        $result->close();
    }
    // Get queue information.
    $sql = "SELECT q.user_id, u.first_name, u.last_name, u.country, r.name, q.robot_number
                FROM queue q INNER JOIN users AS u on u.id = q.user_id 
                INNER JOIN robots AS r on r.number = q.robot_number
                ORDER BY q.robot_number, q.created asc";

    if ($result = $mysqli->query($sql)) {
        $comma = false;
        while ($row = $result->fetch_object()) {
            if ($comma) {
                $queue_val .= ',';
            } else {
                $comma = true;
            }
            $queue_val .= '{"user_id":' . $row->user_id . ',"first_name":"' . $row->first_name
                         . '", "last_name":"' . $row->last_name . '", "country":"' . $row->country
                         . '", "robot_name":"' . $row->name . '", "robot_number":' . $row->robot_number . '}';
        }
        $result->close();
    }
    $sql = "SELECT c.created, c.control_time, c.user_id, u.first_name, u.last_name, u.country, r.name, c.robot_number
        FROM controllers c
        INNER JOIN users AS u on u.id = c.user_id
        INNER JOIN robots AS r on r.number = c.robot_number";
    if ($result = $mysqli->query($sql)) {
        $comma = false;
        while ($row = $result->fetch_object()) {
            if ($comma) {
                $controllers_val .= ',';
            } else {
                $comma = true;
            }
            // Calculate the number of seconds left to control the mobot.
            $created_date = new DateTime($row->created);
            $created_date->add(new DateInterval("PT" . $row->control_time . "S"));
            $now_date = new DateTime();
            $interval = $created_date->diff($now_date);
    
            $controllers_val .= '{"user_id":' . $row->user_id . ',"first_name":"' . $row->first_name
                 . '", "last_name":"' . $row->last_name . '", "country":"' . $row->country
                 . '", "robot_name":"' . $row->name . '","robot_number":' . $row->robot_number
                 . ', "time_left":' . $interval->format('%s') 
                 . ',"control_time":' . $row->control_time . ' }';
        }
        $result->close();
    }
    $robots_val .= "]";
    $queue_val .= "]";
    $controllers_val .= "]";
    $ret_val = '{"error":false, "admin":false, "robots":' . $robots_val . ', "queue":' . $queue_val
        . ', "controllers":' . $controllers_val . ',"stats":[]}';
} catch (Exception $e) {
    $mysqli->rollback();
    $ret_val = '"error":true,"msg":"' . $e->getMessage() . '"}';
}
$mysqli->close();
echo $ret_val;
?>