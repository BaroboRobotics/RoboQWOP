<?php
include 'config.php';
header('Content-type: application/json');
session_start();
// Validate session information.
if (!isset($_SESSION['user_id'])) {
    echo '{"success":false,"msg":"invalid session id" }';
    exit();
}

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    echo '{"success":false,"msg":"Connection failed" }';
    exit();
}

$robot_number = NULL;
// Validate the active user and get the robot number.
if ($stmt = $mysqli->prepare("SELECT robot_number FROM controllers WHERE user_id = ?")) {
    $stmt->bind_param('i', $session_id_val);
    $session_id_val = $_SESSION['user_id'];
    $stmt->execute();
    $stmt->bind_result($robot_number);
    $stmt->fetch();
    $stmt->close();
}

if (is_null($robot_number)) {
    echo '{"success":false,"msg":"You are not controlling the Mobot(s)" }';
    exit();
}


function getResetMessage() {
    return "1";
}
function getRoboQWOPMessage() {
    $fp1 = 0;
    $fp2 = 0;
    $bj1 = 0;
    $bj2 = 0;
    // Face Plate 1
    if ($_GET["q"] == "1") {
        $fp1 = -1;
    } else if ($_GET["w"] == "1") {
        $fp1 = 1;
    }
    // Face Plate 2
    if ($_GET["o"] == "1") {
            $fp2 = 1;
    } else if ($_GET["p"] == "1") {
            $fp2 = -1;
    }
    // Body Joint 1
    if ($_GET["u"] == "1") {
            $bj1 = -1;
    } else if ($_GET["i"] == "1") {
            $bj1 = 1;
    }
    // Body Joint 2
    if ($_GET["e"] == "1") {
            $bj2 = -1;
    } else if ($_GET["r"] == "1") {
            $bj2 = 1;
    }
    return "$fp1,$fp2,$bj1,$bj2";
}
function getSpeedMessage() {
    $speed = 125.0;
    if (isset($_GET["speed"])) {
        $speed = floatval($_GET["speed"]);
        if ($speed < 15.0) {
            $speed = 15.0;
        } else if ($speed > 125.0) {
            $speed = 125.0;
        }
    }
    return "$speed,$speed,$speed,$speed";
}
function getJointMessage() {
    $j1 = 0;
    $j2 = 0;
    $j3 = 0;
    $j4 = 0;
    if (isset($_GET["j1"])) {
        $j1 = intval($_GET["j1"]);
        if ($j1 < -90) {
            $j1 = -90;
        } else if ($j1 > 90) {
            $j1 = 90;
        }
    }
    if (isset($_GET["j2"])) {
        $j2 = intval($_GET["j2"]);
        if ($j2 < -90) {
            $j2 = -90;
        } else if ($j2 > 90) {
            $j2 = 90;
        }
    }
    if (isset($_GET["j3"])) {
        $j3 = intval($_GET["j3"]);
        if ($j3 < -90) {
            $j3 = -90;
        } else if ($j3 > 90) {
            $j3 = 90;
        }
    }
    if (isset($_GET["j4"])) {
        $j4 = intval($_GET["j4"]);
        if ($j4 < -90) {
            $j4 = -90;
        } else if ($j4 > 90) {
            $j4 = 90;
        }
    }
    return "$j1,$j2,$j3,$j4";
}

$host = "localhost";
$port = 8082 + $robot_number;

$mode = 0;
if (isset($_GET['mode'])) {
    $mode = intval($_GET['mode']);
}
$message = "$robot_number,";
$response = "";
switch ($mode) {
    case 0:
        // Get status information.
        $message .= "0";
        break;
    case 1:
        // Reset/Move to Zero.
        $message .= "1," . getResetMessage();
        break;
    case 2:
        // RoboQWOP controls
        $message .= "2," . getRoboQWOPMessage();
        break;
    case 3:
        // Speed Controls
        $message .= "3," . getSpeedMessage();
        break;
    case 4:
        // Joint Controls
        $message .= "4," . getJointMessage();
        break;
    case 5:
        // Directional controls
        break;
    case 6:
        // Action/Motion controls
        break;
    default:
        // Get status information.
        break;
}

if (!$fp = fsockopen($host, $port, $errno, $errstr, 10)) {
    echo '{"success":false, "msg": "' . $errstr . '"}';
    exit();
}
fwrite($fp, $message . '\n');
$response = fread($fp, 256);
fclose($fp);
echo '{"success":true,"msg":"' . $response . '","sent":"' . $message . '"}';

?>
