<?php
include 'config.php';
$page = 'user stats';
session_start();
if (!$_SESSION['is_admin']) {
    header('Location: index.php');	
}
$user_id = $_SESSION['user_id'];
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$users = array();
$sql = "SELECT count(id) FROM assignments WHERE course_id = 1";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->execute();
	$stmt->bind_result($number_of_assignments);
	$stmt->fetch();
	$stmt->close();
}
$sql = "SELECT id, first_name, last_name, show_tutorial FROM users ORDER BY last_seen DESC";
if ($stmt = $mysqli->prepare($sql)) {
	$stmt->execute();
	$stmt->bind_result($id, $first_name, $last_name, $show_tutorial);
	while ($stmt->fetch()) {
		$users[$id] = array("$first_name $last_name", $show_tutorial);  
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
        <title>User Stats | RoboQWOP</title>
        <meta name="description" content="RoboQWOP - Control a Mobot over the web">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.21.custom.css"  />
        <script src="js/libs/modernizr-2.5.3.min.js"></script>
    </head>
    <body>
        <div role="main" id="page"> <?php include("includes/header.php") ?>
        	<div id="page-content">
	            
				<h2>User Stats</h2>
				<table id="user_stats">
				<tr><th>Name</th><th id="tutorial_progress">Tutorial Progress</th></tr>
				<?php
foreach ($users as $key => $val) {
    if ($val[1]) {
		$sql = "SELECT last_completed_assignment_number FROM users_last_completed_assignment_for_course WHERE user_id = " . $key . " and course_id = 1;";
		$result = $mysqli->query($sql);
		
		if ( $result->num_rows )
		{
			$row = $result->fetch_object();
			$last_completed = $row->last_completed_assignment_number;
			
			
		} else { $last_completed = 0; }
		$result->close();
		if ( $last_completed == $number_of_assignments ) {
			echo "<tr><td>$val[0]</td><td class=\"completed_tutorial\">$last_completed/$number_of_assignments</td></tr>";
		} else {
			echo "<tr><td>$val[0]</td><td>$last_completed/$number_of_assignments</td></tr>";
		}
    } else {
	    echo "<tr><td>$val[0]</td><td>User hid tutorial</td></tr>";
	}
}
	?>
				</table>
            </div> <!-- /page-content -->
            <?php include("includes/sidebar.php") ?>
	        <div class="clearfix"></div>
            <?php include("includes/footer.php"); ?>
        </div>
        
    </body>
</html>