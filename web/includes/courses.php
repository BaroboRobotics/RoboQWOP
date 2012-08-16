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
	if ($_SESSION['show_tutorial']) {
?>
<div id="assignment">
   <h3 id="course">Assignment #<span id="assignment_number"></span> for <?=$course_title ?>: <span id="assignment_objective"></span></h3>
   <p id="assignment_instructions"></p>
   <div id="assignment_youtube"></div>
   <input type="button" id="hide_tutorial" value="Hide <?=$course_title ?>" style="float:right;" /><input type="button" id="completed_assignment" value="I've completed this assignment" /> 
</div>
<?php } ?>