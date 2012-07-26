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
		    <div id="queue"></div>
			<div id="debug"></div>
            <a style="margin: 0 auto; display: block;" href="http://www.barobo.com"><img src="img/logo.png" alt="Barobo" title="Barobo" /></a>
            <h1>Robo QWOP</h1>
            <img src="img/imobot_diagram.png" alt="Mobot Diagram" title="Mobot Diagram" />
			<p><span id="status">Retrieving status information.</span> <span id="time_left"></span></p>
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
                            <input type="button" value="Spin clockwise" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="Spin counter clockwise"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="North" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" />
                            </td><td>
                            <input type="button" value="South" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Northeast" onmousedown="handleKeyEvent(82, true);" class="red" />
                            </td><td>
                            <input type="button" value="Northwest" onmousedown="handleKeyEvent(85, true);" class="green" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Southeast" onmousedown="handleKeyEvent(69, true);" class="red" />
                            </td><td>
                            <input type="button" value="Southwest" onmousedown="handleKeyEvent(73, true);" class="green" />
                            </td>
                        </tr>
                    </table>
                    <table id="left_is_green_face_north_south" class="quad">
                        <tr>
                            <th rowspan="4">Controls</th><td>
                            <input type="button" value="Spin clockwise" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="Spin counter clockwise"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="North" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="South" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Northwest" onmousedown="handleKeyEvent(73, true);" class="green" />
                            </td><td>
                            <input type="button" value="Northeast" onmousedown="handleKeyEvent(69, true);" class="red" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Southwest" onmousedown="handleKeyEvent(85, true);" class="green" />
                            </td><td>
                            <input type="button" value="Southeast" onmousedown="handleKeyEvent(82, true);" class="red" />
                            </td>
                        </tr>
                    </table>
                    
                    <table id="bottom_is_green_face_east_west" class="quad">
                        <tr>
                            <th rowspan="4">Controls</th><td>
                            <input type="button" value="Spin clockwise" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="Spin counter clockwise"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="West" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="East" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="Southwest" onmousedown="handleKeyEvent(82, true);" class="red" />
                            </td><td>
                            <input type="button" value="Southeast" onmousedown="handleKeyEvent(69, true);" class="red" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Northwest" onmousedown="handleKeyEvent(85, true);" class="green" />
                            </td><td>
                            <input type="button" value="Northeast" onmousedown="handleKeyEvent(73, true);" class="green" />
                            </td>
                        </tr>
                    </table>
                    <table id="bottom_is_red_face_east_west" class="quad">
                        <tr>
                            <th rowspan="4">Controls</th><td>
                            <input type="button" value="Spin clockwise" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" />
                            </td><td>
                            <input type="button" value="Spin counter clockwise"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="West" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" />
                            </td><td>
                            <input type="button" value="East" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" />
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                            <input type="button" value="Southwest" onmousedown="handleKeyEvent(73, true);" class="green" />
                            </td><td>
                            <input type="button" value="Southeast" onmousedown="handleKeyEvent(85, true);" class="green" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <input type="button" value="Northwest" onmousedown="handleKeyEvent(69, true);" class="red" />
                            </td><td>
                            <input type="button" value="Northeast" onmousedown="handleKeyEvent(82, true);" class="red" />
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
            var q = 0; var w = 0; var o = 0; var p = 0;
            var u = 0; var i = 0; var e = 0; var r = 0;
			var time_left = 61;
			var countdown = false;
            var send = false;
            var active = false;
            var count = 0;
            function enableSend(oldval, newval) {
                if (oldval !== newval) {
                    send = true;
                }
            }
            function countDown() {
			    if (countdown == true) {
				    time_left = time_left - 1;
			        $('#time_left').text('You have '+time_left+' seconds left.');
				}
				if (time_left == 0) {
				    countdown = false;
					$('#time_left').text('')
				}
			}
            function handleKeyEvent(keyCode, down) {
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
            function updateStatus() {
			    $.getJSON('status.php', function(data) {
				    $('#status').html(data.status);
				    var robotNames = [];
					
					var len = data.control.length;
				    var sub_queues = [];
					//alert(data.queue.length);
					$('#debug').text('');
					$('#debug').append('Length of data.queue: '+data.queue.length+'<br/>');
					$('#debug').hide();
					var lens = [];
					//var len
					//var maxLen = Math.max.apply(Math, lens);
					//var maxLen = lens.max();
					var html = '<table><tr>'
					subLen = data.queue.length;
					//alert(maxLen);
					//alert(subLen);
					for (var i = 0; i < len; i++) {
						robotNames.push(data.control[i].robot_name);
					}
					var robotNames = robotNames.sort();
					for (var i = 0; i < len; i++) {
						for (var j = 0; j < subLen; j++) {
						    sub_queues[i] = [];
						    if (data.queue[j].robot_name == robotNames[j]) {
							    sub_queues[i].push(0);
							}
							lens[i] = sub_queues[i].length;
						}
						html = html + '<th colspan="2">'+data.control[i].robot_name+"</th>";
					}
					
					var maxLen = Math.max.apply(Math, lens);
					$('#debug').append('Lens: '+lens+'<br/>');
					$('#debug').append('maxLen: '+maxLen+'<br/>');
				    $('#queue').css('width', 200 * len + len + 1);
					$('#queue').css('float', 'right');
					$('#queue th').css('width', 200);
				    //alert(lens);
					html = html + '</tr><tr>';
					for (var i = 0; i < len; i++) {
						
						html = html + '<td>1</td><td>'+data.control[i].first_name+" "+data.control[i].last_name+"<br/>("+data.control[i].timeleft+" seconds left)</td>";
					}
					html = html + '</tr>';
					for (var i = 0; i < maxLen; i++) {
						
						html = html + '<tr>';
						for (var j = 0; j < len; j++) {
						    position = i + 2;
							
						    if (i < lens[j]) {
							    for (var k = 0; k < subLen; k++) {
								    if (((i + 1) == data.queue[k].position) && (robotNames[j] == data.queue[k].robot_name)) {
									    html = html + '<td>'+position+'</td><td>'+data.queue[k].first_name+" "+data.queue[k].last_name+'</td>'
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
					//alert(html);
					//alert($('#queue').html());
					/*
					$('#status').html(data.status);
					$('#queue').html('<tr>');
					$.each(data.control, function(i, dataq) {
					   
					   var queue_data = "<th colspan='2'>"+dataq.robot_name+"</th>";
					   
					   $(queue_data).appendTo("#queue");
					});
					$('#queue').append('</tr>')
					$.each(data.control, function(i, dataq) {
					   var queue_data = "<tr><td>1</td><td>"+dataq.first_name+" "+dataq.last_name+"</td></tr>";
					   
					   $(queue_data).appendTo("#queue");
					});
					
					$.each(data.queue, function(i, dataq) {
					   var position = dataq.position + 1;
					   var queue_data = "<tr><td>"+position+"</td><td>"+dataq.first_name+" "+dataq.last_name+"</td></tr>";
					   
					   $(queue_data).appendTo("#queue");
					});
					*/
					if (!active && data.active) {
					    time_left = 61;
						countdown = true;
					    
						soundHandle = document.getElementById('soundHandle');
						soundHandle.src = 'sounds/beep.mp3';
						soundHandle.play();
					}
					active = data.active;
					if (active) {
						$('#status').css({'color':'red', 'font-weight':'bold'});
					} else {
						$('#status').css({'color':'black', 'font-weight':'normal'});
					}
				});
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
                        success : function() {
                        },
                        dataType : 'html'
                    });
                }
                if (count >= 1000) {
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
				//setInterval(countDown, 1000);

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
