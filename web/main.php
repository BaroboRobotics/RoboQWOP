<?php
include 'config.php';
$page = 'main';
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
    // Get the username.
    $user_full_name = "Unknown User";
    $sql = "SELECT first_name, last_name FROM users WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($first_name, $last_name);
        while ($stmt->fetch()) {
            $user_full_name = "$first_name $last_name";  
        }
        $stmt->close();
    }
    // Get the colors for the robots.
    $color1_hex = "000000";
    $color2_hex = "ffffff";
    $color1_name = "Black";
    $color2_name = "White";
    $sql = "SELECT color1_hex, color2_hex, color1_name, color2_name FROM robots WHERE number = " . $_SESSION['robot'];
    $result = $mysqli->query($sql);
    $row = $result->fetch_object();

    $color1_hex = $row->color1_hex;
    $color2_hex = $row->color2_hex;
    $color1_name = $row->color1_name;
    $color2_name = $row->color2_name;
    $result->close();
    // Close the connection.
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
        <title>RoboQWOP</title>
        <meta name="description" content="RoboQWOP - Control a Mobot over the web">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.21.custom.css"  />
        <style>
            .color1 {
                background:#<?=$color1_hex ?>;
				<?php 
				if ($color1_name == "White") {
                    echo "color:#000;";
                } else {
				    echo "color:#FFF;";
				}
                ?>				
            }
            
            .color2 {
                background:#<?=$color2_hex ?>;
				<?php 
				if ($color2_name == "White") {
                    echo "color:#000;";
                } else { 
				    echo "color:#FFF;";
				}
                ?>	
            }
        </style>
        <script src="js/libs/modernizr-2.5.3.min.js"></script>
    </head>
    <body>
        <div role="main" id="page"><?php include("includes/header.php") ?>
        	<div id="page-content">
	            
				<?php include 'includes/courses.php' ?>
				<div>
	            	<div id="info-display" style="float:right;"></div>
	            	<div class="clearfix"></div>
				</div>
				<p><span id="status">Retrieving status information.</span> <span id="time_left"></span></p>
				<div id="action-errors"></div>
				<p>You can use your keyboard's arrow buttons. If <?=$color2_name ?> is on left then pressing down arrow moves the Mobot up not down. When moving left to right and <?=$color2_name ?> is on bottom hitting up arrow moves the Mobot to the right and hitting left arrows spins the Mobot to the right.</p>
	            <div id="control-tabs">
	                <div id="robomancer-controls">
	                    <div class="clearfix">
	                        <img class="box" width="338" height="246" style="float:left;" src="img/mobot-diagram-robomancer.png" title="Mobot Diagram" alt="Mobot Diagram" />
	                        <div class="box arrow-controls">
	                            <div id="mancer-btngrp-1">
	                                <button id="mancer-up" onclick="controller.doDirection(true,false,false,false);"><img src="img/icons/arrow-up.png" alt="Up" title="Up" width="48" height="48" /></button>
	                            </div>
	                            <div id="mancer-btngrp-2">
	                                <button id="mancer-left" onclick="controller.doDirection(false,false,true,false);"><img src="img/icons/arrow-left.png" alt="Left" title="Left" width="48" height="48" /></button><button id="mancer-down" onclick="controller.doDirection(false,true,false,false);"><img src="img/icons/arrow-down.png" alt="Down" title="Down" width="48" height="48" /></button><button id="mancer-right" onclick="controller.doDirection(false,false,false,true);"><img src="img/icons/arrow-right.png" alt="Right" title="Right" width="48" height="48" /></button>
	                            </div>
	                            <div id="mancer-btngrp-3">
	                                <button id="mancer-reset" onclick="controller.reset();"><img src="img/icons/reset.png" alt="Reset" title="Reset" width="48" height="48" /></button><button id="mancer-stop" onclick="controller.doDirection(false,false,false,false);"><img src="img/icons/stop.png" alt="Stop" title="Stop" width="48" height="48" /></button>
	                            </div>
								
	                        </div>
	                    </div>
	                    <div class="clearfix">
	                        <div class="box positions">
	                            <p>Joint Positions</p>
	                            <table id="robomancer-joint-table">
	                                <thead><tr>
	                                    <th>1</th>
	                                    <th>2</th>
	                                    <th>3</th>
	                                    <th>4</th>
	                                </tr></thead>
	                                <tbody>
	                                    <tr>
	                                        <td class="color1"><input class="key-button" type="button" onmousedown="handleKeyEvent(87, true);" value="W" /></td>
	                                        <td class="color1"><input class="key-button" type="button" onmousedown="handleKeyEvent(82, true);" value="R" /></td>
	                                        <td class="color2"><input class="key-button" type="button" onmousedown="handleKeyEvent(73, true);" value="I" /></td>
	                                        <td class="color2"><input class="key-button" type="button" onmousedown="handleKeyEvent(79, true);" value="O" /></td>
	                                    </tr>
	                                    <tr>
	                                        <td><div id="mancer-joint-1"></div></td>
	                                        <td><div id="mancer-joint-2"></div></td>
	                                        <td><div id="mancer-joint-3"></div></td>
	                                        <td><div id="mancer-joint-4"></div></td>
	                                    </tr>
	                                    <tr>
	                                        <td><input id="mancer-joint-val-1" type="text" onkeypress="controller.degreeCheck(event);" maxlength="3" /></td>
	                                        <td><input id="mancer-joint-val-2" type="text" onkeypress="controller.degreeCheck(event);" maxlength="3" /></td>
	                                        <td><input id="mancer-joint-val-3" type="text" onkeypress="controller.degreeCheck(event);" maxlength="3" /></td>
	                                        <td><input id="mancer-joint-val-4" type="text" onkeypress="controller.degreeCheck(event);" maxlength="3" /></td>
	                                    </tr>
	                                    <tr>
	                                        <td colspan="4"><button onclick="controller.moveJointsTo();">Move To</button></td>
	                                    </tr>
	                                    <tr>
	                                        <td class="color1"><input class="key-button" type="button" onmousedown="handleKeyEvent(81, true);" value="Q" /></td>
	                                        <td class="color1"><input class="key-button" type="button" onmousedown="handleKeyEvent(69, true);" value="E" /></td>
	                                        <td class="color2"><input class="key-button" type="button" onmousedown="handleKeyEvent(85, true);" value="U" /></td>
	                                        <td class="color2"><input class="key-button" type="button" onmousedown="handleKeyEvent(80, true);" value="P" /></td>
	                                    </tr>
	                                    <tr>
	                                        <td style="padding-top: 5px; vertical-align: middle;">Speed </td>
	                                        <td style="padding-top: 5px; vertical-align: middle;" colspan="3"><div id="mancer-speed" class="speed-slider"></div></td>
	                                    </tr>
	                                </tbody>
	                            </table>
	                        </div>
	                        <div class="box motions">
	                            <p>Motions</p>
								<input type="button" value="Arch" onclick="controller.doMotion(1)" class="motion_button"/>
								<input type="button" value="Inchworm Left" onclick="controller.doMotion(2)" class="motion_button"/>
								<input type="button" value="Inchworm Right" onclick="controller.doMotion(3)" class="motion_button"/>
								<input type="button" value="Roll Backward" onclick="controller.doMotion(4)" class="motion_button"/>
								<input type="button" value="Roll Forward" onclick="controller.doMotion(5)" class="motion_button"/>
								<input type="button" value="Skinny Pose" onclick="controller.doMotion(6)" class="motion_button"/>
								<input type="button" value="Turn Left" onclick="controller.doMotion(8)" class="motion_button"/>
								<input type="button" value="Turn Right" onclick="controller.doMotion(9)" class="motion_button"/>

	                        </div>
	                    </div>
	                </div> <!-- /robomancer controls -->
	                
	            </div>
            </div> <!-- /page-content -->
            <?php include("includes/sidebar.php") ?>
	        <div class="clearfix"></div>
            <?php include("includes/footer.php"); ?>
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
			var color1_name = "<?=$color1_name ?>";
			var color2_name = "<?=$color2_name ?>";
			var current_user_id = "<?=$user_id ?>";
            var countDownThread = null;
			var time_left = 0;
            var send = false;
            var active = false;
            var count = 0;
            function enableSend(oldval, newval) {
                if (oldval !== newval) {
                    send = true;
                }
            }
            function countDown() {
                // time_left = time_left - 1;
                if (time_left <= 0) {
                    $('#time_left').hide();
                    clearInterval(countDownThread);
                    countDownThread = null;
                } else {
                    $('#time_left').text('You have ' + time_left + ' seconds left.').show();
                }
			}
			function startCountDown() {
			    if (countDownThread !== null) {
			        clearInterval(countDownThread);
			    }
                countDownThread = setInterval(countDown, 1000);
            }
            function handleKeyEvent(keyCode, down) {
			    if (!$("#sequence").is(":focus")) {
					if (active) {
						executeKeyEvent(keyCode, down);
					} else {
					    if (jQuery.inArray(keyCode, [38, 40, 37, 39, 76, 81, 87, 69, 82, 85, 73, 79, 80, 38, 40]) > -1) { // only show error if the key pressed is a control key
                           
    						$('#action-errors').html('<p><strong>Error:</strong> You not in control of the robot.</p>').show().delay( 10000 ).hide( 0 );
					    }
					}
				}
            }
            function executeKeyEvent(keyCode, down) {
                var oldval;
                switch (keyCode) {
					case 76: // l
					    controller.reset();
						break;  
				    default:
				        controller.event(keyCode, down);
                }
            }

            function updateStatus() {
			    $.getJSON('status.php', function(data) {
					$('#status').html(data.status);
					time_left = data.timeleft;
					if (time_left > 0  && countDownThread == null) {
					    startCountDown();
					}
					if (!active && data.active) {
						playSound();
					}
					active = data.active;
					if (active) {
						$('#status').css({'color':'red', 'font-weight':'bold'});
					} else {
						$('#status').css({'color':'black', 'font-weight':'normal'});
					}
					/*
					if (data.stats.length > 0) {
					    controller.updateSliders(data.stats);
					}
					*/
					$('#info-display').html(controller.getQueueHTML(data));
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
                if (active) {
                    controller.sendAction();
                }
                if (count >= 1000) {
                    count = 0;
                    updateStatus();
                }
            }
            $(document).keydown(function(event) {
                handleKeyEvent(event.keyCode, true);
				if ($.inArray(event.keyCode, [38, 40, 37, 39]) > -1) { // if arrow key pressed don't move the page up/down/left/right
					event.preventDefault();
					return false;
				}
            });
            $(document).keyup(function(event) {
                handleKeyEvent(event.keyCode, false);
				if ($.inArray(event.keyCode, [38, 40, 37, 39]) > -1) { // if arrow key pressed don't move the page up/down/left/right
					event.preventDefault();
					return false;
				}
            });
            $(function() {
			    showAssignment();
				$("#completed_assignment").click(function() {
				    $.post('completed_assignment.php', 'user_id='+current_user_id+'&assignment_number='+$('#assignment_number').text());
					// not sure if processing time issue but you have to hit the completed assignment button twice before the next assignment appears
					showAssignment();
				});
				$("#hide_tutorial").click(function() {
				    $.post('hide_tutorial.php', 'user_id='+current_user_id);
					// not sure if processing time issue but you have to hit the completed assignment button twice before the next assignment appears
					$("#assignment").hide();
				});
                $( "#control-tabs" ).tabs();
                controller.init();
                updateStatus();
				setInterval(executeAction, 100);
				
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
                    $("#on_left_is_red").prop('value', color1_name + ' is on left');
                    $("#on_left_is_green").prop('value', color2_name + ' is on left');
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
                    $("#on_left_is_red").prop('value', color1_name + ' is on bottom');
                    $("#on_left_is_green").prop('value', color2_name + ' is on bottom');
                    $(".quad").hide();
                    if ($("#on_left_is_red").is('.active')) {
                        $("#bottom_is_red_face_east_west").show();
                    } else {
                        $("#bottom_is_green_face_east_west").show();
                    }
                });
				$("#execute-sequence-button").click(function() {
				    $("#sequence").val($("#sequence").val().toUpperCase());
				    var sequence = $("#sequence").val();
					sequence = sequence.split('');
					for (var i = 0; i < sequence.length; i++) {
					    executeKeyEvent(sequence[i].charCodeAt(0), true);
					}
				});
            });
        </script>
    </body>
</html>
