<?php
	if (isset( $_SESSION['user_id'] )) {
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	    if (!mysqli_connect_errno()) {
	        $user_id = $_SESSION['user_id'];
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
			if (!($page == 'user stats')) {
			    $mysqli->close();
		    }
		}
	}
?>
<div id="header">
    <?php if (isset( $user_full_name )) : ?>
    	<div id="navbar">
    		<?php if ($page == 'user stats') : ?>
    		    <a href="authenticate.php">Control robot</a> | <a href="index.php">Watch queue</a> | 
            <?php elseif ($_SESSION['is_admin']) :?>
                <a href="user_stats.php">User stats</a> | 
            <?php endif; ?>
            <?php if ($page == 'main') : ?>
                <a href="exit_queue.php" style="">Exit Queue</a> | 
            <?php endif; ?>
            <a href="logout.php">Logout</a> | 
            <strong><?=$user_full_name ?></strong>
    	</div>
	<?php else: ?>
    	<div id="navbar">
    		<a href="login.php">Login</a>
    	</div>
	<?php endif; ?>
    <h1><a href="http://www.barobo.com"><span>RoboQWOP</span></a></h1>
</div>