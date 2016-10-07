<?php /* Wrapper Name: Footer */ ?>
<div class="row footer-widgets">
	<div class="foot-logo" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-1">
		<?php dynamic_sidebar("footer-sidebar-1"); ?>
	</div>
	<div class="foot-address" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-2">
		<?php dynamic_sidebar("footer-sidebar-2"); ?>
	</div>
	<div class="foot--phone" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-3">
		<?php dynamic_sidebar("footer-sidebar-3"); ?>
	</div>
	<div class="foot-email" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-4">
		<?php dynamic_sidebar("footer_area_4"); ?>
	</div>
	<div class="email-update" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-6">
		<?php dynamic_sidebar("footer_area_6"); ?>
	</div>
</div>
<div class="row">
	<div class="span12 copyright">
		<div class="row">
			<div class="span5 pull-right" data-motopress-type="static" data-motopress-static-file="static/static-footer-nav.php">
				<?php get_template_part("static/static-footer-nav"); ?>
			</div>
			<div class="span7 pull-left" data-motopress-type="static" data-motopress-static-file="static/static-footer-text.php">
				<?php get_template_part("static/static-footer-text"); ?>
			</div>
		</div>
	</div>
</div>

