<?php if (!($page == 'user stats') && !($page == 'manage courses')) { ?>
<div id="page-sidebar">

	<div class="widget">
	    <div id="promo" class="rounded_corners">
			<iframe src="http://www.youtube.com/embed/videoseries?list=PL1D8ED3B2BA1B3726" width="198" height="111" frameborder="0" allowfullscreen></iframe>
			<a href="http://store.barobo.com/mobot/mobot.html" id="toppromo" class="promolink" style="margin-top:-5px;"><span>Buy a Mobot $269.95</span></a>
			<a href="http://store.barobo.com/mobot/gorilla-bundle.html" id="bottompromo" class="promolink rounder_corners_bottom"><span>Buy three Mobots with connectors $827.75</span></a>
		</div>
	    <div><h3>Support RoboQWOP</h3></div>
	    <div>
			<p style="margin: 0;">Your donatations help keep RoboQWOP online<p>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="H7TP3NJC76TQJ">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
		<!-- load the hover images so they are ready to be shown when user hovers --><div class="hidden">
		<img src="img/promos/sidebar/top_hover.jpg" /><img src="img/promos/sidebar/bottom_hover.jpg" /></div>
		
		<div><h3>Help develop RoboQWOP. </h3></div>
	    <div>
			<p style="margin: 0;">RoboQWOP is free open source code software written in PHP, Javascript, and C. <p>
<iframe src="http://ghbtns.com/github-btn.html?user=Barobo&repo=RoboQWOP&type=fork"
  allowtransparency="true" frameborder="0" scrolling="0" width="53px" height="20px"></iframe>
		</div>
	</div>
</div>
<?php } else { if ($page == 'manage courses') { echo "<input type=\"button\" id=\"save_course_changes\" value=\"Save course changes\" />"; } ?>

<?php } ?> 