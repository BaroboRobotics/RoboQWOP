<?php
include 'config.php';
session_start();
if (!isset($_SESSION['id'])) {
	// INSERT INTO DATABASE 
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	if ($stmt = $mysqli->prepare("INSERT INTO sessions (session_id, last_active, active) values (?, CURRENT_TIMESTAMP, ?)")) {
		$stmt->bind_param('si', $session_id_val, $active_val);
		$session_id_val = session_id();
		$active_val = 0;
		$stmt->execute();
		$_SESSION['id'] = $stmt->insert_id;
		$stmt->close();
	}	
} else {
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
        }
	if ($result = $mysqli->query("SELECT session_id FROM sessions WHERE id = " . $_SESSION['id'] )) {
		/* determine number of rows result set */
		$row_cnt = $result->num_rows;
		$result->close();
		if ($row_cnt == 0) {
			if ($stmt = $mysqli->prepare("INSERT INTO sessions (session_id, last_active, active) values (?, CURRENT_TIMESTAMP, ?)")) {
                		$stmt->bind_param('si', $session_id_val, $active_val);
                		$session_id_val = session_id();
                		$active_val = 0;
                		$stmt->execute();
                		$_SESSION['id'] = $stmt->insert_id;
                		$stmt->close();
       	 		}
		}
	}
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
            <a style="margin: 0 auto; display: block;" href="http://www.barobo.com"><img src="img/logo.png" alt="Barobo" title="Barobo" /></a>
            <h1>Robo QWOP</h1>
            <img src="img/imobot_diagram.png" alt="Mobot Diagram" title="Mobot Diagram" />
            <p id="status">
                Retrieving status information.
            </p>
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
            <p>
                Speed Slider
                <div id="slider" style="width: 250px; margin: 10px 0;"></div>
            </p>
            <div class="social-widget" style="margin-top: 50px;">
                <a target="_blank" href="http://twitter.com/BaroboRobotics"> <img src="img/icons/twitter.png" alt="Twitter" width="40" /> </a>
                <a target="_blank" href="http://www.facebook.com/barobo"> <img src="img/icons/facebook.png" alt="Facebook" width="40" /> </a>
                <a target="_blank" href="https://plus.google.com/110706245535499996481?prsrc=3" rel="publisher"> <img src="img/icons/googleplus.png" alt="Google Plus" width="40" /> </a>
                <a target="_blank" href="http://www.youtube.com/BaroboRobotics"> <img src="img/icons/youtube.png" alt="Youtube" width="40" /> </a>
            </div>
        </div>
		<table>
<tr><th rowspan="2">Orientation</th><td><input type="button" value="Red is on left" id="on_left_is_red" class="button active" /></td><td><input type="button" value="Green is on left" id="on_left_is_green" class="button" /></td></tr>
<tr><td><input type="button" id="facing_north_south" value="Facing North/South" class="button active" /></td><td><input type="button" id="facing_east_west" value="Facing East/West" class="button" /></td></tr>
</table>
<table id="left_is_red_face_north_south" class="quad">
<tr><th rowspan="4">Controls</th><td><input type="button" value="Spin clockwise" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" /></td><td><input type="button" value="Spin counter clockwise"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" /></td></tr>

<tr><td><input type="button" value="North" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" /></td><td><input type="button" value="South" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" /></td></tr>
<tr><td><input type="button" value="Northeast" onmousedown="handleKeyEvent(82, true);" class="red" /></td><td><input type="button" value="Northwest" onmousedown="handleKeyEvent(85, true);" class="green" /></td></tr>
<tr><td><input type="button" value="Southeast" onmousedown="handleKeyEvent(69, true);" class="red" /></td><td><input type="button" value="Southwest" onmousedown="handleKeyEvent(73, true);" class="green" /></td></tr>
</table>
<table id="left_is_green_face_north_south" class="quad">
<tr><th rowspan="4">Controls</th><td><input type="button" value="Spin clockwise" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" /></td><td><input type="button" value="Spin counter clockwise"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" /></td></tr>

<tr><td><input type="button" value="North" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" /></td><td><input type="button" value="South" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" /></td></tr>
<tr><td><input type="button" value="Northwest" onmousedown="handleKeyEvent(73, true);" class="green" /></td><td><input type="button" value="Northeast" onmousedown="handleKeyEvent(69, true);" class="red" /></td></tr>
<tr><td><input type="button" value="Southwest" onmousedown="handleKeyEvent(85, true);" class="green" /></td><td><input type="button" value="Southeast" onmousedown="handleKeyEvent(82, true);" class="red" /></td></tr>
</table>

<table id="bottom_is_green_face_east_west" class="quad">
<tr><th rowspan="4">Controls</th><td><input type="button" value="Spin clockwise" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" /></td><td><input type="button" value="Spin counter clockwise"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" /></td></tr>

<tr><td><input type="button" value="West" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" /></td><td><input type="button" value="East" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" /></td></tr>

<tr><td><input type="button" value="Southwest" onmousedown="handleKeyEvent(82, true);" class="red" /></td><td><input type="button" value="Southeast" onmousedown="handleKeyEvent(69, true);" class="red" /></td></tr>
<tr><td><input type="button" value="Northwest" onmousedown="handleKeyEvent(85, true);" class="green" /></td><td><input type="button" value="Northeast" onmousedown="handleKeyEvent(73, true);" class="green" /></td></tr>
</table>
<table id="bottom_is_red_face_east_west" class="quad">
<tr><th rowspan="4">Controls</th><td><input type="button" value="Spin clockwise" onmousedown="handleKeyEvent(87, true); handleKeyEvent(80, true);" class="button" /></td><td><input type="button" value="Spin counter clockwise"  onmousedown="handleKeyEvent(81, true); handleKeyEvent(79, true);" class="button" /></td></tr>

<tr><td><input type="button" value="West" onmousedown="handleKeyEvent(87, true); handleKeyEvent(79, true);" class="button" /></td><td><input type="button" value="East" onmousedown="handleKeyEvent(81, true); handleKeyEvent(80, true);" class="button" /></td></tr>

<tr><td><input type="button" value="Southwest" onmousedown="handleKeyEvent(73, true);" class="green" /></td><td><input type="button" value="Southeast" onmousedown="handleKeyEvent(85, true);" class="green" /></td></tr>
<tr><td><input type="button" value="Northwest" onmousedown="handleKeyEvent(69, true);" class="red" /></td><td><input type="button" value="Northeast" onmousedown="handleKeyEvent(82, true);" class="red" /></td></tr>
</table>
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
            var send = false;
            var active = false;
            var count = 0;
            function enableSend(oldval, newval) {
                if (oldval !== newval) {
                    send = true;
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
            old_active = False;
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
                        //url: '../cgi-bin/weblistener.cgi',
                        url : 'action.php',
                        data : data,
                        success : function() {
                        },
                        dataType : 'html'
                    });
                }
                if (count >= 10000) {
                    count = 0;
                    $.getJSON('status.php', function(data) {
                        $('#status').html(data.status);
                        active = data.active;
                        if (active) {
                            $('#status').css({'color':'red', 'font-weight':'bold'});
                        } else {
                            $('#status').css({'color':'black', 'font-weight':'normal'});
                        }
                    });
                }
            }
            $(document).keydown(function(event) {
                handleKeyEvent(event.keyCode, true);
            });
            $(document).keyup(function(event) {
                handleKeyEvent(event.keyCode, false);
            });
            $(function() {
                $("#slider").slider({
                    "max" : 120,
                    "min" : 15,
                    "value" : 45
                });
                $.getJSON('status.php', function(data) {
                    $('#status').html(data.status);
                    active = data.active;
					if (oldactive != active) {
                        $.playSound('/sounds/beep.mp3');
					}
                    oldactive = active;					
                    if (active) {
                        $('#status').css({'color':'red', 'font-weight':'bold'});
                    } else {
                        $('#status').css({'color':'black', 'font-weight':'normal'});
                    }
                    setInterval(executeAction, 100);
                });
				    $("#left_is_red_face_north_south").show();
	$("#on_left_is_red").click(function(){
	   $("#on_left_is_red").addClass('active');
	   $("#on_left_is_green").removeClass('active');
	   $(".quad").hide();
	   if ($("#facing_north_south").is('.active')) {
          $("#left_is_red_face_north_south").show();
       } else {
          $("#bottom_is_red_face_east_west").show();
       }
	});
	$("#on_left_is_green").click(function(){
	   $("#on_left_is_green").addClass('active');
	   $("#on_left_is_red").removeClass('active');
	   $(".quad").hide();
	   if ($("#facing_north_south").is('.active')) {
          $("#left_is_green_face_north_south").show();
       } else {
          $("#bottom_is_green_face_east_west").show();
       }
	});
	$("#facing_north_south").click(function(){
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
	$("#facing_east_west").click(function(){
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
                q = 0; w = 0; o = 0; p = 0;
                u = 0; i = 0; e = 0; r = 0;
                send = true;
            });
        </script>
    </body>
</html>
