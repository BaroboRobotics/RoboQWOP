<?php
require 'includes/functions/auth-functions.php';
require 'config.php';
session_start();

function getRobotNumber($mysqli) {
    $robot_number = 0;
    if (isset( $_GET['robot'])) {
        $robot_number = intval($_GET['robot']);
    } else {
        $sql = "SELECT r.name, r.number, count(q.id), count(c.id) FROM robots r 
            LEFT JOIN queue as q on q.robot_number = r.number
            LEFT JOIN controllers as c on c.robot_number = r.number
            WHERE r.status = 1 GROUP BY r.name, r.number ORDER BY count(q.id) ASC, count(c.id) ASC LIMIT 1;";
        if ($result = $mysqli->query($sql)) {
            $row = $result->fetch_object();
            $robot_number = $row->number;
            $result->close();
        }
    }
    return $robot_number;
}

function changeRobot($new_number, $mysqli) {
    $_SESSION['robot'] = $new_number;
    if (isset($_SESSION['user_id'])) {
        $mysqli->query("DELETE FROM controllers WHERE user_id = " . $_SESSION['user_id']); // remove user from controllers
        $mysqli->query("DELETE FROM queue WHERE user_id = " . $_SESSION['user_id']); // remove user from queue
    }
}

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (mysqli_connect_errno()) {
        die('Connection Error: ' . mysqli_connect_error());
    }
    changeRobot(getRobotNumber($mysqli), $mysqli);
    if (!isset( $_SESSION['user_id'] )) {
        doAuthentication($mysqli, 'main.php');
    } else {
        header('Location: main.php');
    }
    $mysqli->close();
} catch(ErrorException $e) {
    echo $e->getMessage();
}
?>
