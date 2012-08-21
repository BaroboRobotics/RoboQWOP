<?php if (!($page == 'user stats') && !($page == 'manage courses')) { ?>
<div id="page-sidebar">

	<div class="widget">
	<!-- load the hover images so they are ready to be shown when user hovers --><div class="hidden">
		<img src="img/promos/sidebar/top_hover.jpg" /><img src="img/promos/sidebar/bottom_hover.jpg" /></div>
	    <div id="promo" class="rounded_corners">
			<iframe src="http://www.youtube.com/embed/videoseries?list=PL1D8ED3B2BA1B3726" width="198" height="111" frameborder="0" allowfullscreen></iframe>
			<a href="http://store.barobo.com/mobot/gorilla-bundle.html" style="margin-top:-5px;" id="bottompromo" class="promolink rounder_corners_bottom">
			    <span>Buy three Mobots with connectors $827.75</span>
			</a>
            <div id="slideshow2" class="slideshow">
                <img src="img/big_mobot.jpg" alt="Big Mobot" class="active" />
                <img src="img/rescue_mobot.jpg" alt="Rescue Mobots" />
                <img src="img/snake_mobot_1.jpg" alt="Snake Mobot " />
                <img src="img/snake_mobot_2.jpg" alt="Snake Mobot" />
                <img src="img/triangle_mobot.jpg" alt="Triangle Mobot" />
                <img src="img/mobot_standing_head_to_side.jpg" alt="Standing Head to Side" />
            </div>
            <a href="http://store.barobo.com/mobot/mobot.html" id="toppromo" class="promolink"><span>Buy a Mobot $269.95</span></a>
			<div id="slideshow1" class="slideshow">
                <img src="img/mobot.jpg" alt="A Mobot" class="active" />
                <img src="img/four_mobots.jpg" alt="Four Mobots" />
                <img src="img/smooth_mobot.jpg" alt="Smooth Mobot" />
            </div>
		</div>
	</div>
</div>
<?php } else { if ($page == 'manage courses') { echo "<input type=\"button\" id=\"save_course_changes\" value=\"Save course changes\" />"; } ?>

<?php } ?> 