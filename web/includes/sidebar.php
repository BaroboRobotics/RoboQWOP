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
			<p>Donate to Barobo!</p>
			<form action="#" method="post"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"> <input type="hidden" name="cmd" value="_xclick-subscriptions"></form>
			<p>Recurring $2/month donation (recommended)</p>
			<form action="#" method="post"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"> <input type="hidden" name="cmd" value="_xclick-subscriptions"></form>
			<p>Recurring $20/year donation</p>
			<form action="#" method="post"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"> <input type="hidden" name="cmd" value="_xclick-subscriptions"></form>
			<p>One time donation of any amount</p>
		</div>
	</div>
</div>