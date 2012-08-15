<?php
include 'config.php';
// Connect to the robot with least of users in its queue or one of the robots with the least users in queue

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    exit();
}
try {
    // get robot numbers
	$robots = array();
	$sql = "SELECT number FROM robots";
	if ($stmt = $mysqli->prepare($sql)) {
        $stmt->execute();
        $stmt->bind_result($robot_number);
        while ($stmt->fetch()) {
            $robots[$robot_number] = 0;
        }
        $stmt->close();
    }
	foreach ($robots as $key => $val) {
        $sql = "SELECT robot_number FROM controllers WHERE robot_number = " . $key . ";";
		$result = $mysqli->query($sql);
		
		if ( $result->num_rows )
		{
		    $result->close();
			$robots[$key] = 1;
			$sql = "SELECT count(id) FROM queue WHERE robot_number = " . $key . ";";
			if ($stmt = $mysqli -> prepare($sql)) {
                $stmt -> execute();
				$stmt->bind_result($n);
				while ($stmt->fetch()) {
					$robots[$key] = $robots[$key] + $n;
				}
				
				$stmt ->close();
			}
		} else { $result->close(); }
    }
    $robot_number = array_keys($robots, min($robots));
	$robot_number = $robot_number[0];
} catch (Exception $e) {
    $mysqli->rollback();
}
$mysqli->close();
header('Location: authenticate.php?robot=' . $robot_number);
?>