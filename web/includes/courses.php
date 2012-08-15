<?php
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!mysqli_connect_errno()) {
    	// Get course title
		$sql = "SELECT title FROM courses WHERE id = 1";
    	$result = $mysqli->query($sql);
		$row = $result->fetch_object();
		$course_title = $row->title;
		$mysqli->close();
	}
?>
<div id="assignment">
   <h3 id="course">Assignment #<span id="assignment_number"></span> for <?=$course_title ?>: <span id="assignment_objective"></span></h3>
   <p id="assignment_instructions"></p>
   <input type="button" id="completed_assignment" value="I've completed this assignment" />
</div>