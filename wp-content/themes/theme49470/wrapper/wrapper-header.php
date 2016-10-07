<?php /* Wrapper Name: Header */ ?>
<div class="row">
	<div class="span12">
		<?php get_template_part("static/static-logo"); ?>
		<?php dynamic_sidebar( 'header-sidebar' ); ?>
		<div class="search_shop">
			<?php get_template_part("static/static-search"); ?>
			<?php get_template_part("static/static-shop-nav"); ?>
		</div>
		<div class="social-icon">
			<?php dynamic_sidebar("header_area_1"); ?>
		</div>
	</div>
</div>
<div class="row">
	<div class="span12 menu_cart">
		<?php get_template_part("static/static-nav"); ?>
		<?php dynamic_sidebar( 'cart-holder' ); ?>
	</div>
</div>
