<?php
require 'includes/lightopenid/openid.php';
include 'config.php';
session_start();
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (!mysqli_connect_errno()) {
	// Check for admin users if no admins create one
	if ($result = $mysqli->query("SELECT id FROM admins")) {
		$row_cnt = $result -> num_rows;
		
		
		if ($row_cnt == 0) {
			if (!isset( $_SESSION['user_id'] )) { // if not logged in then log the user in 
				$openid = new LightOpenID($_SERVER['HTTP_HOST']);

				if(!$openid->mode) {
					//do the login
					
					//The google openid url
					$openid->identity = 'https://www.google.com/accounts/o8/id';
						
					//Get additional google account information about the user , name , email , country
					$openid->required = array('contact/email' , 'namePerson/first' , 'namePerson/last' , 'pref/language' , 'contact/country/home'); 
						
					//start discovery
					header('Location: ' . $openid->authUrl());
				} else if($openid->mode == 'cancel') {
					header('Location: index.php');
				} else if ($openid->validate()) {
					//User logged in
					$d = $openid->getAttributes();
					
					$first_name = $d['namePerson/first'];
					$last_name = $d['namePerson/last'];
					$email = $d['contact/email'];
					$language_code = $d['pref/language'];
					$country_code = $d['contact/country/home'];
					$user_id = NULL;
					// See if there is an existing user record.
					if ($stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?")) {
						$stmt->bind_param('s', $email);
						$stmt->execute();
						$stmt->bind_result($user_id);
						$stmt->fetch();
						$stmt->close();
					}
					// Create or update a user record.
					if (is_null($user_id) || $user_id <= 0) {
						// New user record.
						if ($stmt = $mysqli->prepare("INSERT INTO users (email, first_name, last_name, country, created, last_seen) values (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)")) {
							$stmt->bind_param('ssss', $email, $first_name, $last_name, $country_code);
							$stmt->execute();
							$user_id = $stmt->insert_id;
							$_SESSION['user_id'] = $user_id;
							$stmt->close();
						}
					} else {
						// Existing user record.
						if ($stmt = $mysqli->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, country = ?, last_seen = CURRENT_TIMESTAMP where email = ?")) {
							$stmt->bind_param('sssss', $email, $first_name, $last_name, $country_code, $email);
							$stmt->execute();
							$_SESSION['user_id'] = $user_id;
							$stmt->close();
						}
					}
				}	
			}
		    $user_id = $_SESSION['user_id'];
			$sql = "INSERT INTO admins (user_id) values (?)";
            if ($stmt = $mysqli -> prepare($sql)) {
                $stmt -> bind_param('i', $user_id);
                $stmt -> execute();
                $stmt -> close();
            }   
		}

	}
}
$mysqli->close();
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
        <title>Robo QWOP</title>
        <meta name="description" content="Robo QWOP - Control a Mobot over the web">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.21.custom.css"  />
        <script src="js/libs/modernizr-2.5.3.min.js"></script>
    </head>
    <body>
        <div role="main" id="page" class="homepage">
            <a href="http://www.barobo.com"><img src="img/logo.png" alt="Barobo" title="Barobo" /></a>
            <h1>Robo QWOP</h1>
            <p>
                Best way to play with a mobot without owning one.
            </p>
            
                <table id="queue" class="center"></table><br/>
            <div class="social-widget">
                <a target="_blank" href="http://twitter.com/BaroboRobotics"> <img src="img/icons/twitter.png" alt="Twitter" width="40" /> </a>
                <a target="_blank" href="http://www.facebook.com/barobo"> <img src="img/icons/facebook.png" alt="Facebook" width="40" /> </a>
                <a target="_blank" href="https://plus.google.com/110706245535499996481?prsrc=3" rel="publisher"> <img src="img/icons/googleplus.png" alt="Google Plus" width="40" /> </a>
                <a target="_blank" href="http://www.youtube.com/BaroboRobotics"> <img src="img/icons/youtube.png" alt="Youtube" width="40" /> </a>
            </div>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script>
            window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')
        </script>
        <script src="js/libs/jquery-ui-1.8.21.custom.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/script.js"></script>
		        <script src="js/libs/jquery-ui-1.8.21.custom.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/script.js"></script>
        <script type="text/javascript">
            
            function queueBox() {
			    $.getJSON('queue_box.php', function(data) {
					var robotNames = [];
					var number_of_columns = data.control.length;
					var sub_queues = [];
					var number_of_rows_in_each_column = [];
					var html = '<table><tr>'
					var queue_len = data.queue.length;
					
					// store robot names
					for (var column = 0; column < number_of_columns; column++) {
						robotNames.push(data.control[column].robot_name);
					}
					console.log(robotNames);
					// ensure the queue box columns are the same robot each time by sorting alphabetically
					var robotNames = robotNames.sort();
					var robotIds = [];
					// because of sorting need to create control var
					var control = [];
					for (var column = 0; column < number_of_columns; column++) {
						for (var canidate = 0; canidate < number_of_columns; canidate++) {
							if (data.control[canidate].robot_name == robotNames[column]) {
								control.push(data.control[canidate]);
								robotIds.push(data.control[canidate].robot_id);
							}
						}
					}
					
					// count number of rows in each column
					for (var column = 0; column < number_of_columns; column++) {
						sub_queues[column] = [];
						for (var row = 0; row < queue_len; row++) {
							equals = (data.queue[row].robot_name == robotNames[column]);
							if (data.queue[row].robot_name == robotNames[column]) {
								sub_queues[column][sub_queues[column].length] = row;
							}
							number_of_rows_in_each_column[column] = sub_queues[column].length;
						}
					}
					
					
					console.log(JSON.stringify(data));
					console.log(data.queue);
					console.log(number_of_rows_in_each_column);
					// print robot names
					for (var column = 0; column < number_of_columns; column++) {
						html = html + '<th colspan="2"><a href="authenticate.php?robot='+robotIds[column]+'">Connect to the '+robotNames[column]+"</a></th>";
					}
					
					var number_of_rows = Math.max.apply(Math, number_of_rows_in_each_column);
					$('#queue').css('width', 200 * number_of_columns + number_of_columns + 1);
					$('#queue th').css('width', 200);
					html = html + '</tr><tr>';
					
					// print users controlling each robot
					for (var column = 0; column < number_of_columns; column++) {
					    // make sure someone is controlling the given robot before trying to print name
						if (control[column].exists == "no") {
						    html = html + '<td colspan="2" style="border:0;background:#c2e8f1;"></td>'
						} else {
						    // don't show the time left if no one else is in the queue
							if ((number_of_rows_in_each_column[column] == 0) || (data.queue.length == 0)) {
								html = html + '<td>1</td><td>'+control[column].first_name+" "+control[column].last_name+"</td>";
							} else {
								var timeleft = control[column].timeleft;
								html = html + '<td>1</td><td>'+control[column].first_name+" "+control[column].last_name+"<br/>("+timeleft+" seconds left)</td>";
							}
						}
						
					}
					
					html = html + '</tr>';
					// show the users in the queue for each robot
					for (var row = 0; row < number_of_rows; row++) {
						html = html + '<tr>';
						var position = row + 2;
						for (var column = 0; column < number_of_columns; column++) {
							
							if (row < number_of_rows_in_each_column[column]) {
								console.log("Robot column: %s", robotNames[column]);
								for (var canidate = 0; canidate < queue_len; canidate++) {
									if (((row + 1) == data.queue[canidate].position) && (robotNames[column] == data.queue[canidate].robot_name)) {
										html = html + '<td>'+position+'</td><td>'+data.queue[canidate].first_name+" "+data.queue[canidate].last_name+'</td>'
									}
									if (data.queue[canidate].first_name == 'Timothy') {
										console.log("%d %d %s", row + 1, data.queue[canidate].position, robotNames[column], data.queue[canidate].robot_name);
									}
												
								}
								
							} else {
								html = html + '<td colspan="2" style="border:0;background:#c2e8f1;"></td>'
							}
						}
						html = html + '</tr>';
					}
					html = html + '</table>';
					$('#queue').html(html);
            
				});
            }

            $(function() {
			    queueBox();
				setInterval(queueBox, 1000);
		    });

        </script>
    </body>
</html>
