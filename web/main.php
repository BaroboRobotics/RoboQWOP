<?php
include 'config.php';
session_start();
if (!isset( $_SESSION['user_id'] )) {
	header('Location: index.php');	
} else {
    $user_id = $_SESSION['user_id'];
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!mysqli_connect_errno()) {
        // Create or update the queue record.
        if ($result = $mysqli->query("SELECT id FROM queue WHERE user_id = " . $user_id )) {
            $row_cnt = $result -> num_rows;
            $result -> close();
            if ($row_cnt == 0) {
                // Check the controllers.  We don't want to add a queue if the user is currently controlling the Robot.
                if ($result = $mysqli->query("SELECT id FROM controllers WHERE user_id = " . $user_id )) {
                    $row_cnt = $result -> num_rows;
                    $result -> close();
                }
                if ($row_cnt == 0) {
                    $robot_number = 0;
                    if (isset( $_SESSION['robot'] )) {
                        $robot_number = $_SESSION['robot'];
                    }
                    $sql = "INSERT INTO queue (created, last_active, user_id, robot_number)
                        values (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, ?)";
                    if ($stmt = $mysqli -> prepare($sql)) {
                        $stmt -> bind_param('ii', $user_id, $robot_number);
                        $stmt -> execute();
                        $_SESSION['queue_id'] = $stmt -> insert_id;
                        $stmt -> close();
                    }
                }
            } else {
                if ($stmt = $mysqli->prepare("UPDATE queue SET last_active = CURRENT_TIMESTAMP where user_id = ?")) {
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }
    $mysqli->close();
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
        <title>Robo QWOP</title>
        <meta name="description" content="Robo QWOP - Control a Mobot over the web">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.21.custom.css"  />
        <script src="js/libs/modernizr-2.5.3.min.js"></script>
    </head>
    <body>
        <div role="main" id="page">
		    <p style="float:right"><strong><?php 
	// the user's name is printed to help debuggers track which user they are logged into when they have multiple windows open with different users
    // use Google Chrome profiles feature to have many windows opened with different Google accounts	
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$sql = "SELECT first_name, last_name FROM users WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($first_name, $last_name);
		while ($stmt->fetch()) {
            echo "$first_name $last_name";	
        }
    }
    $mysqli->close();	?></strong> | <a href="logout.php" style="">Logout</a></p>
		    <table id="queue" style="float:right"></table>
            <a style="margin: 0 auto; display: block;" href="http://www.barobo.com"><img src="img/logo.png" alt="Barobo" title="Barobo" /></a>
            <h1>Robo QWOP</h1>
            <img src="img/imobot_diagram.png" alt="Mobot Diagram" title="Mobot Diagram" />
			<p><span id="status">Retrieving status information.</span> <span id="time_left"></span></p>
			<div id="action-errors"></div>
            <div id="control-tabs">
                <ul>
                    <li><a href="#default-controls">Default Controls</a></li>
                    <li><a href="#oriented-controls">Oriented Controls</a></li>
                </ul>
                <div id="default-controls">
                    <table class="controls">
                        <thead>
                            <tr>
                                <th>Direction</th>
                                <th>Face Plate / Joint 1</th>
                                <th>Body Joint / Joint 2</th>
                                <th>Body Joint / Joint 3</th>
                                <th>Face Plate / Joint 4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Forward</td>
                                <td class="red"><input class="key-button" type="button" onmousedown="handleKeyEvent(87, true);" value="W" /></td>
                                <td class="red"><input class="key-button" type="button" onmousedown="handleKeyEvent(82, true);" value="R" /></td>
                                <td class="green"><input class="key-button" type="button" onmousedown="handleKeyEvent(73, true);" value="I" /></td>
                                <td class="green"><input class="key-button" type="button" onmousedown="handleKeyEvent(79, true);" value="O" /></td>
                            </tr>
                            <tr>
                                <td>Backwards</td>
                                <td class="red"><input class="key-button" type="button" onmousedown="handleKeyEvent(81, true);" value="Q" /></td>
                                <td class="red"><input class="key-button" type="button" onmousedown="handleKeyEvent(69, true);" value="E" /></td>
                                <td class="green"><input class="key-button" type="button" onmousedown="handleKeyEvent(85, true);" value="U" /></td>
                                <td class="green"><input class="key-button" type="button" onmousedown="handleKeyEvent(80, true);" value="P" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="oriented-controls">
                    <table>
                        <tr>
                            <th rowspan="2">Orientation</th><td>
                            <input type="button" value="Red is on left" id="on_left_is_red" class="button active" />
                            </td><td>
                            <input type="button" value="Green is on left" id="on_left_is_green" class="button" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" id="facing_north_south" value="Facing North/South" class="button active" />
                            </td><td>
                            <input type="button" id="facing_east_west" value="Facing East/West" class="button" />
                            </td>
                        </tr>
                    </table>
                    <table id="left_is_red_face_north_south" class="quad">
                        <tr>
                            <th rowspan="4">Controls</th><td>
                            <input type="button" value="Spin clockwise P&amp;W" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="Spin counter clockwise Q&amp;O"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="North W&amp;O" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" />
                            </td><td>
                            <input type="button" value="South O&amp;P" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Northeast R" onmousedown="handleKeyEvent(82, true);" class="red" />
                            </td><td>
                            <input type="button" value="Northwest U" onmousedown="handleKeyEvent(85, true);" class="green" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Southeast E" onmousedown="handleKeyEvent(69, true);" class="red" />
                            </td><td>
                            <input type="button" value="Southwest I" onmousedown="handleKeyEvent(73, true);" class="green" />
                            </td>
                        </tr>
                    </table>
                    <table id="left_is_green_face_north_south" class="quad">
                        <tr>
                            <th rowspan="4">Controls</th><td>
                            <input type="button" value="Spin clockwise P&amp;W" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="Spin counter clockwise Q&amp;O"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="North Q&amp;P" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="South W&amp;O" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Northwest I" onmousedown="handleKeyEvent(73, true);" class="green" />
                            </td><td>
                            <input type="button" value="Northeast E" onmousedown="handleKeyEvent(69, true);" class="red" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Southwest U" onmousedown="handleKeyEvent(85, true);" class="green" />
                            </td><td>
                            <input type="button" value="Southeast R" onmousedown="handleKeyEvent(82, true);" class="red" />
                            </td>
                        </tr>
                    </table>
                    
                    <table id="bottom_is_green_face_east_west" class="quad">
                        <tr>
                            <th rowspan="4">Controls</th><td>
                            <input type="button" value="Spin clockwise P&amp;W" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="Spin counter clockwise Q&amp;O"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="West Q&amp;P" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="East W&amp;O" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="Southwest R" onmousedown="handleKeyEvent(82, true);" class="red" />
                            </td><td>
                            <input type="button" value="Southeast E" onmousedown="handleKeyEvent(69, true);" class="red" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Northwest U" onmousedown="handleKeyEvent(85, true);" class="green" />
                            </td><td>
                            <input type="button" value="Northeast I" onmousedown="handleKeyEvent(73, true);" class="green" />
                            </td>
                        </tr>
                    </table>
                    <table id="bottom_is_red_face_east_west" class="quad">
                        <tr>
                            <th rowspan="4">Controls</th><td>
                            <input type="button" value="Spin clockwise P&amp;W" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="Spin counter clockwise Q&amp;O"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="West Q&amp;P" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" />
                            </td><td>
                            <input type="button" value="East W&amp;O" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="Southwest R" onmousedown="handleKeyEvent(73, true);" class="green" />
                            </td><td>
                            <input type="button" value="Southeast E" onmousedown="handleKeyEvent(85, true);" class="green" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Northwest U" onmousedown="handleKeyEvent(69, true);" class="red" />
                            </td><td>
                            <input type="button" value="Northeast I" onmousedown="handleKeyEvent(82, true);" class="red" />
                            </td>
                        </tr>
                    </table>
					
                </div>
				<p>Speed Slider</p>
                <div id="slider" style="width: 250px; margin: 10px 0;"></div>
            </div>
            <div class="social-widget" style="margin-top: 50px;">
                <a target="_blank" href="http://twitter.com/BaroboRobotics"> <img src="img/icons/twitter.png" alt="Twitter" width="40" /> </a>
                <a target="_blank" href="http://www.facebook.com/barobo"> <img src="img/icons/facebook.png" alt="Facebook" width="40" /> </a>
                <a target="_blank" href="https://plus.google.com/110706245535499996481?prsrc=3" rel="publisher"> <img src="img/icons/googleplus.png" alt="Google Plus" width="40" /> </a>
                <a target="_blank" href="http://www.youtube.com/BaroboRobotics"> <img src="img/icons/youtube.png" alt="Youtube" width="40" /> </a>
            </div>
        </div>
        <audio id="soundHandle" style="display: none;"></audio>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script>
            window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')
        </script>
        <script src="js/libs/jquery-ui-1.8.21.custom.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/script.js"></script>
		<script type="text/javascript">
		    var current_robot = <?php echo $_SESSION['robot']; ?>;
			console.log("current robot id: %d", current_robot);
		</script>
        <script type="text/javascript">
            var q = 0; var w = 0; var o = 0; var p = 0;
            var u = 0; var i = 0; var e = 0; var r = 0;
            var countDownThread = null;
			var time_left = 0;
            var send = false;
            var active = false;
            var count = 0;
			function oc(a)
			{
			  var o = {};
			  for(var i=0;i<a.length;i++)
			  {
				o[a[i]]='';
			  }
			  return o;
			}
            function enableSend(oldval, newval) {
                if (oldval !== newval) {
                    send = true;
                }
            }
            function handleKeyEvent(keyCode, down) {
                if (active) {
                    executeKeyEvent(keyCode, down);
                } else {
				    // only show error if the key pressed is one of the control keys not arrow or CTRL buttons
					if (keyCode in oc([81, 87, 69, 82, 85, 73, 79, 80])) {
                        $('#action-errors').html('<p><strong>Error:</strong> You are not in control of the robot.</p>').show().delay(10000).fadeOut(); // show error message for ten seconds
                    }  
    			}
            }
            function executeKeyEvent(keyCode, down) {
                var oldval;
                switch (keyCode) {
                    case 81:
                        oldval = q;
                        q = (down) ? 1 : 0;
                        enableSend(oldval, q);
                        break;
                    case 87:
                        oldval = w;
                        w = (down) ? 1 : 0;
                        enableSend(oldval, w);
                        break;
                    case 69:
                        oldval = e;
                        e = (down) ? 1 : 0;
                        enableSend(oldval, e);
                        break;
                    case 82:
                        oldval = r;
                        r = (down) ? 1 : 0;
                        enableSend(oldval, r);
                        break;
                    case 85:
                        oldval = u;
                        u = (down) ? 1 : 0;
                        enableSend(oldval, u);
                        break;
                    case 73:
                        oldval = i;
                        i = (down) ? 1 : 0;
                        enableSend(oldval, i);
                        break;
                    case 79:
                        oldval = o;
                        o = (down) ? 1 : 0;
                        enableSend(oldval, o);
                        break;
                    case 80:
                        oldval = p;
                        p = (down) ? 1 : 0;
                        enableSend(oldval, p);
                        break;
                }
            }
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
					    console.log("robot id: %d", robotIds[column]);
					    if (robotIds[column] == current_robot) {
						    html = html + '<th colspan="2">'+robotNames[column]+"</th>";
						} else {
						    html = html + '<th colspan="2"><a href="authenticate.php?robot='+robotIds[column]+'">Switch to the '+robotNames[column]+"</a></th>";
					    }
					}
					
					var number_of_rows = Math.max.apply(Math, number_of_rows_in_each_column);
					$('#queue').css('width', 200 * number_of_columns + number_of_columns + 1);
					$('#queue').css('float', 'right');
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
								if ((current_robot == robotIds[column]) && (active == true)) {
								    $('#time_left').text('You have '+timeleft+' seconds left.');
								}
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

            function updateStatus() {
			    $.getJSON('status.php', function(data) {
					$('#status').html(data.status);
					if (!active && data.active) {
						playSound();
					}
					active = data.active;
					if (active) {
						$('#status').css({'color':'red', 'font-weight':'bold'});
						$('#time_left').show();
					} else {
						$('#status').css({'color':'black', 'font-weight':'normal'});
						$('#time_left').hide();
					}
				});
			}
			function playSound() {
			    soundHandle = document.getElementById('soundHandle');
			    // TODO look into cross browser sound file (ogg doesn't play in all browsers').
                soundHandle.src = 'sounds/beep.ogg';
                soundHandle.play();
			}
            function executeAction() {
                count += 100;
                if (send && active) {
                    send = false;
                    var data = {
                        "q" : q, "w" : w, "e" : e, "r" : r,
                        "u" : u, "i" : i, "o" : o, "p" : p,
                        "speed" : $("#slider").slider("option", "value")
                    };
                    $.ajax({
                        type : 'POST',
                        url : 'action.php',
                        data : data,
                        success : function(response) {
                            if (response.result == "error") {
                                $('#action-errors').html('<p>Error performing action [' + response.msg + ']</p>').show();
                            } else {
                                $('#action-errors').hide();
                            }
                        },
                        dataType : 'json'
                    });
                }
                if (count >= 5000) {
                    count = 0;
                    updateStatus();
                }
            }
            $(document).keydown(function(event) {
                handleKeyEvent(event.keyCode, true);
            });
            $(document).keyup(function(event) {
                handleKeyEvent(event.keyCode, false);
            });
            $(function() {
                $( "#control-tabs" ).tabs();
                $("#slider").slider({
                    "max" : 120,
                    "min" : 15,
                    "value" : 120
                });
                
                updateStatus();
				setInterval(executeAction, 100);
                setInterval(queueBox, 1000);
				
                $("#left_is_red_face_north_south").show();
                $("#on_left_is_red").click(function() {
                    $("#on_left_is_red").addClass('active');
                    $("#on_left_is_green").removeClass('active');
                    $(".quad").hide();
                    if ($("#facing_north_south").is('.active')) {
                        $("#left_is_red_face_north_south").show();
                    } else {
                        $("#bottom_is_red_face_east_west").show();
                    }
                });
                $("#on_left_is_green").click(function() {
                    $("#on_left_is_green").addClass('active');
                    $("#on_left_is_red").removeClass('active');
                    $(".quad").hide();
                    if ($("#facing_north_south").is('.active')) {
                        $("#left_is_green_face_north_south").show();
                    } else {
                        $("#bottom_is_green_face_east_west").show();
                    }
                });
                $("#facing_north_south").click(function() {
                    $("#facing_north_south").addClass('active');
                    $("#facing_east_west").removeClass('active');
                    $("#on_left_is_red").prop('value', 'Red is on left');
                    $("#on_left_is_green").prop('value', 'Green is on left');
                    $(".quad").hide();
                    if ($("#on_left_is_red").is('.active')) {
                        $("#left_is_red_face_north_south").show();
                    } else {
                        $("#left_is_green_face_north_south").show();
                    }
                });
                $("#facing_east_west").click(function() {
                    $("#facing_east_west").addClass('active');
                    $("#facing_north_south").removeClass('active');
                    $("#on_left_is_red").prop('value', 'Red is on bottom');
                    $("#on_left_is_green").prop('value', 'Green is on bottom');
                    $(".quad").hide();
                    if ($("#on_left_is_red").is('.active')) {
                        $("#bottom_is_red_face_east_west").show();
                    } else {
                        $("#bottom_is_green_face_east_west").show();
                    }
                });
            });

            $(document).mouseup(function(event) {
                q = 0;
                w = 0;
                o = 0;
                p = 0;
                u = 0;
                i = 0;
                e = 0;
                r = 0;
                send = true;
            });

        </script>
    </body>
</html>
