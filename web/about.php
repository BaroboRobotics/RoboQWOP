<?php
include 'config.php';
session_start();
if (isset( $_SESSION['user_id'] )) {
	$user_id = $_SESSION['user_id'];	
}
$page = 'about';    
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
        <title>About | RoboQWOP: Control Robots Online</title>
        <meta name="description" content="RoboQWOP - Control a Mobot over the web">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/style.css?v=2">
        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.21.custom.css"  />
        <script src="js/libs/modernizr-2.5.3.min.js"></script>
    </head>
    <body>
        <div role="main" id="page"> <?php include("includes/header.php") ?>
        	<div id="page-content" class="homepage">
			    <h2>About RoboQWOP</h2>
                <p>RoboQWOP is for demonstrating Mobots, <a href="http://www.barobo.com">developed by Barobo</a>, to educators and hobbyists. Anyone <a href="authenticate.php">can control a Mobot online for free</a>.</p>
                <h3>Who's robots are these?</h3>
				<p>The robots on RoboQWOP belong to Barobo, Inc.</p>
				<h3>Where are the Mobots?</h3>
				<p>The Mobots on RoboQWOP are at Barobo's office in Davis, California.</p>
				<h3>Why are you asking for donations?</h3>
				<p>Starting a hardware company is expensive. Even if you can't afford a Mobot you can help by donating a few bucks. <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="H7TP3NJC76TQJ">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form></p>
				<h3>What is the goal of RoboQWOP?</h3>
				<p>The goal for RoboQWOP is to introduce everyday hundreds of educators and hobbyists to Mobots.</p>
				<h3>Who develops RoboQWOP?</h3>
				<p>RoboQWOP is developed by Adam Ruggles and Timothy Clemans.</p>
				<p>You can help develop RoboQWOP. Fork the <a href="https://github.com/Barobo/RoboQWOP/">repo on Github</a> and send a pull request.</p>
				<div class="clearfix"></div>
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

    </body>
</html>
