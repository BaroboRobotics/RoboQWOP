<?php
	if (isset( $_SESSION['user_id'] )) {
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	    if (!mysqli_connect_errno()) {
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
			$mysqli->close();
		}
	}
?>
<div id="page-sidebar">
	<?php if (isset( $user_full_name )) : ?>
	<div class="widget">
		<p><strong><?=$user_full_name ?></strong> | <a href="logout.php" style="">Logout</a></p>
	</div>
	<?php endif; ?>
	<div class="widget">
	    <div><h3>Contribute</h3></div>
	    <div>
			<p style="margin: 0;">One time donation of any amount<p>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="H7TP3NJC76TQJ">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>
</div>