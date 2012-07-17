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
<!DOCTYPE html>
<html>
<head>
	<title>Mobot Demo</title>
	<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.21.custom.css" rel="stylesheet" />	
	<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script> 
	<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script> 
</head>
<body>
<h1>Mobot Demo</h1>
<img src="imobot_diagram.png" alt="Mobot Diagram" title="Mobot Diagram" />
<p id="status">Retrieving status information.</p>
<ul>
	<li style="background-color: red; color: white;"><strong>q</strong> - Face plate 1 moves backwards</li>
	<li style="background-color: red; color: white;"><strong>w</strong> - Face plate 1 moves forwards</li>
	<li style="background-color: green; color: white;"><strong>o</strong> - Face plate 2 moves backwards</li>
	<li style="background-color: green; color: white;"><strong>p</strong> - Face plate 2 moves forwards</li>
	<li style="background-color: green; color: white;"><strong>u</strong> - Body joint 1 moves backwards</li>
	<li style="background-color: green; color: white;"><strong>i</strong> - Body joint 1 moves forwards</li>
	<li style="background-color: red; color: white;"><strong>e</strong> - Body joint 2 moves backwards</li>
	<li style="background-color: red; color: white;"><strong>r</strong> - Body joint 2 moves forwards</li>
</ul>
<p>&nbsp;</p>
<table>
  <tr>
    <td>
	<input type="button" onmousedown="handleKeyEvent(81, true);" value="Q" />
    </td>
    <td>
	<input type="button" onmousedown="handleKeyEvent(87, true);" value="W" />
    </td>
    <td>
        <input type="button" onmousedown="handleKeyEvent(79, true);" value="O" />
    </td>
    <td>
        <input type="button" onmousedown="handleKeyEvent(80, true);" value="P" />
    </td>
  </tr>
  <tr>
    <td>
        <input type="button" onmousedown="handleKeyEvent(85, true);" value="U" />
    </td>
    <td>
        <input type="button" onmousedown="handleKeyEvent(73, true);" value="I" />
    </td>
    <td>
        <input type="button" onmousedown="handleKeyEvent(69, true);" value="E" />
    </td>
    <td>
        <input type="button" onmousedown="handleKeyEvent(82, true);" value="R" />
    </td>
  </tr>
</table>
<p>&nsp;</p>
<div style="margin-left: 30px;">Speed Slider</div>
<div id="slider" style="width: 250px; margin: 10px 30px;"></div>
</body>
<script type="text/javascript">
  var q = 0;
  var w = 0;
  var o = 0;
  var p = 0;
  var u = 0;
  var i = 0;
  var e = 0;
  var r = 0;
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
  function executeAction() {
	count += 100;
	if (send && active) {
		send = false;
                var data = { "q":q, "w":w, "e":e, "r":r, "u":u, "i":i, "o":o, "p":p, "speed":$("#slider").slider("option", "value") };
                $.ajax({
                  type: 'POST',
                  //url: '../cgi-bin/weblistener.cgi',
                  url: 'action.php',
		  data: data,
                  success: function() { },
                  dataType: 'html'
                });
	}
	if (count >= 10000) {
		count = 0;
		$.getJSON('status.php', function(data) {
                	$('#status').html(data.status);
                	active = data.active;
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
	$( "#slider" ).slider({ "max": 120, "min": 15, "value":45 });
	$.getJSON('status.php', function(data) {
		$('#status').html(data.status);
		active = data.active;
		setInterval(executeAction, 100);
	});
	//setInterval(executeAction, 100);
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
</html>
