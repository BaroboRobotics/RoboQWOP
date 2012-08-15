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
        <script src="js/libs/modernizr-2.5.3.min.js"></script>
    </head>
    <body>
        <div role="main" id="page">
        	<div id="page-content" class="homepage">
            	<a href="http://www.barobo.com"><img src="img/logo.png" alt="Barobo" title="Barobo" /></a>
	            <h1>RoboQWOP</h1>
	            <img src="img/roboqwop.jpg" style="text-align: center; margin: 0 auto;" width="640" height="480" alt="RoboQWOP" title="RoboQWOP - best way to test drive a mobot." />
	            <p><a href="connect.php" style="font-size:3em; clear:left;">Click here to control the robot</a></p>
	            <div id="info-display" class="clearfix" style="width: 410px; margin:5px auto;" >
	                
	            </div>
            </div>
	        <?php include("includes/sidebar.php") ?>
	        <div class="clearfix"></div>
            <?php include("includes/footer.php"); ?>
        </div>
        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script>
            window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')
        </script>
        <script src="js/libs/jquery-ui-1.8.21.custom.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/script.js"></script>
        <script type="text/javascript">
            function infoDisplay() {
                $.getJSON('get_info.php', function(json) {
                    if (json.error) {
                        $('#info-display').html('<p>' + json.msg + '</p>');
                        return;
                    }
                    $('#info-display').html(controller.getQueueHTML(json));
                });
            }

            $(function() {
                infoDisplay();
				setInterval(infoDisplay, 1000);
		    });

        </script>
    </body>
</html>
