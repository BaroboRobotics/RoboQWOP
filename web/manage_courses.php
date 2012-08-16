<?php
include 'config.php';
$page = 'manage courses';
session_start();
if (!$_SESSION['is_admin']) {
    header('Location: index.php');	
}
$user_id = $_SESSION['user_id'];
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$courses = array();
$sql = "SELECT id, title FROM courses;";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->execute();
	$stmt->bind_result($id, $title);
	while ($stmt->fetch()) {
		$courses[$id] = $title;  
	}
	$stmt->close();
}
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en">
    <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Manage Courses | RoboQWOP</title>
        <meta name="description" content="RoboQWOP - Control a Mobot over the web">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.21.custom.css"  />
        <script src="js/libs/modernizr-2.5.3.min.js"></script>
		<script src="js/libs/jquery-1.7.2.min.js"></script>
		<script>
        function createAssignment(course_id) {
		    assignment_number = parseInt($('#n_of_assignments_course_'+course_id).text()) + 1;
		    var assignment_box = "<tr class=\"course_"+course_id+"\"><th colspan=\"2\" class=\"assignment\">Assignment #"+assignment_number+": <input class=\"objective\" type=\"text\" name=\"objective_for_assignment_$id\" /><input type=\"button\" onclick=\"deleteAssignment($id);\" value=\"Delete assignment\" /></th></tr>";
            assignment_box += "<tr class=\"course_"+course_id+"\"><th class=\"assignment_element\">Instructions</th><td><textarea class=\"assignment_element\" name=\"instructions_for_assignment_$id\"></textarea>";
			assignment_box += "<tr class=\"course_"+course_id+"\"><th class=\"assignment_element\">Youtube Url</th><td><input class=\"assignment_element\" type=\"text\" name=\"youtube_url_for_assignment_$id\" />";
            $('#manage_courses tr:last').after(assignment_box);
		}

		function positionSaveCourseChangesButton() {
		    $('#save_course_changes').css('right', ($(window).width() - 980) / 2);
			
		}
		
		function deleteCourse(course_id) {
		    $('.course_' + course_id).hide();
		    // $.post('delete_course.php', 'course_id='+course_id);
		}
		
		function deleteAssignment(assignment_id) {
		    $('.assignment_' + assignment_id).hide();
		    // $.post('delete_assignment.php', 'assignment_id='+assignment_id);
		}
		
		$(document).ready(function(){
		    positionSaveCourseChangesButton();
			$(window).resize(function() {
				positionSaveCourseChangesButton();
			});
		});
		</script>
    </head>
    <body>
        <div role="main" id="page"> <?php include("includes/header.php") ?>
        	<div id="page-content">
	            
				<h2>Manage Courses</h2>
				<form action="update_courses.php" method="post" />
				<table id="manage_courses" class="data_table">
				<?php
foreach ($courses as $key => $val) {
    echo "<tr class=\"course_$key\"><th colspan=\"2\">Course: <input class=\"course_title\" type=\"text\" name=\"title_for_course_$key\" value=\"$val\" /><input type=\"button\" onclick=\"createAssignment($key);\" value=\"Create assignment\" /><input type=\"button\" onclick=\"deleteCourse($id);\" value=\"Delete course\" /></th></tr>"; 
	$sql = "SELECT id, number, objective, instructions, youtube_url FROM assignments WHERE course_id = " . $key . " ORDER BY number;";
	if ($stmt = $mysqli->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($id, $number, $objective, $instructions, $youtube_url);
		$stmt->store_result();
		echo "<tr style=\"display:none\"><td id=\"n_of_assignments_course_$key\">$stmt->num_rows</td></tr>";
		while ($stmt->fetch()) {
			echo "<tr class=\"course_$key assignment_$id\"><th colspan=\"2\" class=\"assignment\">Assignment #$number: <input class=\"objective\" type=\"text\" name=\"objective_for_assignment_$id\" value=\"$objective\" /><input type=\"button\" onclick=\"deleteAssignment($id);\" value=\"Delete assignment\" /></th></tr>";
            echo "<tr class=\"course_$key assignment_$id\"><th class=\"assignment_element\">Instructions</th><td><textarea class=\"assignment_element\" name=\"instructions_for_assignment_$id\">$instructions</textarea>";
			echo "<tr class=\"course_$key assignment_$id\"><th class=\"assignment_element\">Youtube Url</th><td><input class=\"assignment_element\" type=\"text\" name=\"youtube_url_for_assignment_$id\" value=\"$youtube_url\" />";
		}
		$stmt->close();
	}
}
	?>
				</table></form>
</div> <!-- /page-content -->
            <?php include("includes/sidebar.php") ?>
	        <div class="clearfix"></div>
            <?php include("includes/footer.php"); ?>
        </div>
        
    </body>
</html>