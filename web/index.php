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
		    <table id="queue"></table>
            <a href="http://www.barobo.com"><img src="img/logo.png" alt="Barobo" title="Barobo" /></a>
            <h1>Robo QWOP</h1>
            <p>
                Best way to play with a mobot without owning one.
            </p>
            <p>
                <?php
                    require 'config.php';
                    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                    if (mysqli_connect_errno()) {
                        echo 'The database is Offline: ' . mysqli_connect_error();
                    } else {
                        if ($results = $mysqli->query("SELECT name, number FROM robots")) {
                            while ($row = $results->fetch_object()) {
                                echo '<p><a href="authenticate.php?robot=' . $row->number . '">Connect to the ' . $row->name . '</a></p> ';
                            }
                            // Free result set
                            $results->close();
                        }
                        $mysqli->close();
                    }
                ?>
            </p>
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
					var len = data.control.length;
					var sub_queues = [];
					var lens = [];
					var html = '<table><tr>'
					var subLen = data.queue.length;
					for (var i = 0; i < len; i++) {
						robotNames.push(data.control[i].robot_name);
					}
					var robotNames = robotNames.sort();
					for (var i = 0; i < len; i++) {
						sub_queues[i] = [];
						for (var j = 0; j < subLen; j++) {
							equals = (data.queue[j].robot_name == robotNames[i]);
							if (data.queue[j].robot_name == robotNames[i]) {
								sub_queues[i][sub_queues[i].length] = j;
							}
							lens[i] = sub_queues[i].length;
						}
					}
					for (var i = 0; i < len; i++) {
						html = html + '<th colspan="2">'+robotNames[i]+"</th>";
					}
					
					var maxLen = Math.max.apply(Math, lens);
					$('#queue').css('width', 200 * len + len + 1);
					$('#queue').css('float', 'right');
					$('#queue th').css('width', 200);
					html = html + '</tr><tr>';
					var newOrder = [];
					for (var i = 0; i < len; i++) {
						for (var j = 0; j < len; j++) {
							if (robotNames[i] == data.control[j].robot_name) {
								newOrder[i] = j
							}
						}
					}
					for (var i = 0; i < len; i++) {
						if ((lens[i] == 0) || (data.queue.length == 0)) {
							html = html + '<td>1</td><td>'+data.control[newOrder[i]].first_name+" "+data.control[newOrder[i]].last_name+"</td>";
						} else {
							var timeleft = data.control[newOrder[i]].timeleft;
							html = html + '<td>1</td><td>'+data.control[newOrder[i]].first_name+" "+data.control[newOrder[i]].last_name+"<br/>("+timeleft+" seconds left)</td>";
						}
					}
					html = html + '</tr>';
					for (var i = 0; i < maxLen; i++) {
						html = html + '<tr>';
						for (var j = 0; j < len; j++) {
							var position = i + 2;
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
				});
            }

            $(function() {
				setInterval(queueBox, 1000);
		    });

        </script>
    </body>
</html>
