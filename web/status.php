<?php
include 'config.php';
header('Content-type: application/json');
session_start();
if (!isset($_SESSION['id'])): ?>
{ "result":"error","msg":"invalid session id" }
<?php
        exit();
endif;

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()): ?>
{ "result":"error","msg":"Connection failed" }
<?php 
        exit();
endif;

try {
	$ret_val = '{"active":false,"status":"Error occurred retrieving status."}';
	// Do the following operations iwthin the transaction.
	$mysqli->autocommit(FALSE);
	// Update the active session.
	$mysqli->query("update sessions set active = 0");
	$mysqli->query("update sessions set active = 1 where active = 0 order by created asc limit 1");
	$mysqli->query("update sessions set started = NULL where active = 0");
	$mysqli->query("update sessions set last_active = CURRENT_TIMESTAMP where id = " . $_SESSION['id']);
	// Set session time limit based on number of users
	if ($stmt = $mysqli->prepare("SELECT count(*) from sessions")) {
		$stmt->execute();
		$stmt->bind_result($total_users);
		$stmt->fetch(); $stmt->close();
	}	
	if (($total_users >= 2) && ($total_users <= 8)) {
	    $seconds = 180 - (($total_users - 2) * 20); // 2 users equals 180 seconds 3 users equals 160 seconds 4 users equals 120 seconds
	} else {
	    $seconds = 40;
	}
	// $mysqli->query("DELETE FROM sessions WHERE last_active < (NOW() - INTERVAL 1 MINUTE)");
	$mysqli->query("DELETE FROM sessions WHERE last_active < (NOW() - INTERVAL " . $seconds . " SECONDS)");
	// Get the current record.
	$today_min_30_seconds = new DateTime();
	$today_min_30_seconds->sub(new DateInterval('PT60S'));
	if ($stmt = $mysqli->prepare("SELECT started,created,active FROM sessions WHERE id = ?")) {
		$stmt->bind_param('i', $session_id_val);
        	$session_id_val = $_SESSION['id'];
		$stmt->execute();
        	$stmt->bind_result($started_val, $created_val, $active_val);
        	$stmt->fetch(); $stmt->close();
		$started_dt = new DateTime($started_val);
		if ($active_val == 1 && is_null($started_val)) {
			// The session has just been activated.
			$mysqli->query("UPDATE sessions set started = CURRENT_TIMESTAMP where id = " . $_SESSION['id']);
			$ret_val = '{"active":true, "status":"You are controlling the robot."}';
			$handle = fopen("/tmp/mobot_movement.data", "a");
			if ($handle) {
        			fwrite($handle, "0,0,0,0,45\n");
			}				
		} else if ($active_val == 1 && $started_dt > $today_min_30_seconds) {
			// The session is still active.
			$ret_val = '{"active":true, "status":"You are controlling the robot."}';
		} else if ($active_val == 1) {
			// The session will no longer be valid after this call.
			$mysqli->query("UPDATE sessions set created = CURRENT_TIMESTAMP where id = " . $_SESSION['id']);
			if ($results = $mysqli->query("select count(*) as number from sessions")) {
				$obj = $results->fetch_object();
				if ($obj->number > 1) {
					$ret_val = '{"active":false, "status":"There are '. ($obj->number - 1) . ' users in front of you."}';
				} else {
                                        $ret_val = '{"active":true, "status":"You are controlling the robot."}';
                                }
			}
		} else {
			$ret_val = '{"active":false, "status":"issue with query ' . $created_val . '" }';
			

			if ($stmt = $mysqli->prepare("SELECT count(*) from sessions WHERE created < ?")) {
				$stmt->bind_param('s', $created_val);
				$stmt->execute();
				$stmt->bind_result($number_val);
				$stmt->fetch(); $stmt->close();
				$ret_val = '{"active":false, "status":"There are '. $number_val . ' users in front of you."}';
			}	
			
		}
	}	
	$mysqli->commit();
} catch (Exception $e) {
	$mysqli->rollback();
}
$mysqli->close();
echo $ret_val;
?>
