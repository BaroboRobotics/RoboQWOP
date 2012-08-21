<?php
include 'config.php';
session_start();
if (isset( $_SESSION['user_id'] )) {
	$user_id = $_SESSION['user_id'];	
}
$page = 'index';    
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
        <link rel="stylesheet" href="css/style.css?v=2">
        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.21.custom.css"  />
        <script src="js/libs/modernizr-2.5.3.min.js"></script>
    </head>
    <body>
        <div role="main" id="page"> <?php include("includes/header.php") ?>
        	<div id="page-content" class="homepage">
                <a href="authenticate.php" id="drive_mobot" class="rounded_corners">Drive a Mobot</a>
                <p id="site_summary" class="rounded_corners"><strong>RoboQWOP</strong> lets you drive <strong>Mobots</strong> online for free. 
                    Mobots, <a href="http://www.barobo.com">developed by Barobo</a>, help interest students in computing,
                    science, technology, engineering, and math. They're small enough for each student to have one.
                </p>
                <div class="clearfix"></div>
                <p>Put the Google Hangout and RoboQWOP windows next to each other.</p>
                <img src="img/split_screen_demo.jpg" />
                <div id="info-display" class="clearfix" style="margin-top: 1em;"></div>
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
        <script src="js/plugins.js?v=1"></script>
        <script src="js/script.js?v=2"></script>
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
