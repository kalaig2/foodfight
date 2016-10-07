<?php
	// Loading child theme textdomain
	load_child_theme_textdomain( CURRENT_THEME, get_stylesheet_directory() . '/languages' );

	// Remove phone styles for IOS
	add_action( 'wp_head', 'tm_remove_phone_styles' );
	function tm_remove_phone_styles() {
		echo '<meta name="format-detection" content="telephone=no" />';
	}

	// Include scripts and styles for Child Theme
	add_action( 'wp_enqueue_scripts', 'tm_enqueue_assets', 40 );
	function tm_enqueue_assets() {
		global $wp_styles;
		wp_dequeue_style( 'woocommerce-smallscreen' );
		wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/js/custom-script.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_style( 'theme_ie', get_stylesheet_directory_uri() . '/css/ie.css' );
		$wp_styles->add_data( 'theme_ie', 'conditional', 'lt IE 9' );
	}
	
	// Add live chat code to bottom of page
	add_action( 'wp_footer', 'tm_live_chat_code' );
	function tm_live_chat_code() {
		$chat_id = of_get_option( 'live_chat_id' );
		if ( !$chat_id ) {
			return;
		}
		?>
		<!-- begin olark code -->
		<script data-cfasync="false" type='text/javascript'>
		window.olark||(function(c){var f=window,d=document,l=f.location.protocol=="https:"?"https:":"http:",z=c.name,r="load";var nt=function(){
		f[z]=function(){
		(a.s=a.s||[]).push(arguments)};var a=f[z]._={
		},q=c.methods.length;while(q--){(function(n){f[z][n]=function(){
		f[z]("call",n,arguments)}})(c.methods[q])}a.l=c.loader;a.i=nt;a.p={
		0:+new Date};a.P=function(u){
		a.p[u]=new Date-a.p[0]};function s(){
		a.P(r);f[z](r)}f.addEventListener?f.addEventListener(r,s,false):f.attachEvent("on"+r,s);var ld=function(){function p(hd){
		hd="head";return["<",hd,"></",hd,"><",i,' onl' + 'oad="var d=',g,";d.getElementsByTagName('head')[0].",j,"(d.",h,"('script')).",k,"='",l,"//",a.l,"'",'"',"></",i,">"].join("")}var i="body",m=d[i];if(!m){
		return setTimeout(ld,100)}a.P(1);var j="appendChild",h="createElement",k="src",n=d[h]("div"),v=n[j](d[h](z)),b=d[h]("iframe"),g="document",e="domain",o;n.style.display="none";m.insertBefore(n,m.firstChild).id=z;b.frameBorder="0";b.id=z+"-loader";if(/MSIE[ ]+6/.test(navigator.userAgent)){
		b.src="javascript:false"}b.allowTransparency="true";v[j](b);try{
		b.contentWindow[g].open()}catch(w){
		c[e]=d[e];o="javascript:var d="+g+".open();d.domain='"+d.domain+"';";b[k]=o+"void(0);"}try{
		var t=b.contentWindow[g];t.write(p());t.close()}catch(x){
		b[k]=o+'d.write("'+p().replace(/"/g,String.fromCharCode(92)+'"')+'");d.close();'}a.P(2)};ld()};nt()})({
		loader: "static.olark.com/jsclient/loader0.js",name:"olark",methods:["configure","extend","declare","identify"]});
		/* custom configuration goes here (www.olark.com/documentation) */
		olark.identify('<?php echo $chat_id; ?>');
		</script>
		<noscript>
			<a href="https://www.olark.com/site/<?php echo $chat_id; ?>/contact" title="Contact us" target="_blank">Questions? Feedback?</a> powered by <a href="http://www.olark.com?welcome" title="Olark live chat software">Olark live chat software</a>
		</noscript>
		<!-- end olark code -->
		<?php
	}
	
	// Product title length
	add_filter( 'the_title', 'tm_product_title_length', 10, 2 );
	function tm_product_title_length($title) {
		if (is_admin()) 
			return $title;
		global $post, $woocommerce_loop;
		$post_type = get_post_type( $post );
		if ($woocommerce_loop) {
			if ( 'product' == $post_type ) {
				$length_limit = intval( of_get_option('cat_title_length_limit') );
				if ( "" != $length_limit ) {
					$words = explode(' ', $title, ($length_limit + 1));
					if( count($words) > $length_limit ) {
						array_pop($words);
						$title = implode(' ', $words) . '... ';
					}
				}
			}
		}
		return $title;
	}

	//Layot change
	add_filter( 'cherry_layout_content_column', 'tm_content_column' );
	add_filter( 'cherry_layout_sidebar_column', 'tm_sidebar_column' );
	function tm_content_column() {
		return "span9";
	}
	function tm_sidebar_column() {
		return "span3";
	}

	//Change Slider Parameters
	add_filter( 'cherry_slider_params', 'tm_rewrite_slider_params' );
	function tm_rewrite_slider_params( $params ) {

		$params['height'] = "'54.443%'";
		$params['minHeight'] = "'100px'";

		return $params;
	}



	// Shop Options Management
	include_once ( CHILD_DIR . '/includes/options-management.php' );
	
	// Works only if Woocommerce activated
	if (class_exists('Woocommerce')) {

		add_filter('body_class','tm_add_plugin_name_to_body_class');
		function tm_add_plugin_name_to_body_class($classes) {
			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				$classes[] = 'has_woocommerce has_shop';
			}
			return $classes;
		}

		// Empty cart message
		add_action( 'wp_footer', 'empty_cart', 80 );
		function empty_cart() {
			$empty_cart_mess = of_get_option( 'empty_cart_mess' );
			?>
			<script>
			(function($) {
				$(window).load(function() {
					if ($('.widget_shopping_cart_content').is(':empty')) {
						$('.widget_shopping_cart_content').text('<?php echo $empty_cart_mess; ?>');
					}
				});
			})(jQuery);
			</script>
			<?php
		}
		
		// Products per page
		add_filter( 'loop_shop_per_page', 'tm_product_per_page', 20 );
		function tm_product_per_page() {
			$prod_number = of_get_option( 'prod_per_page' );
			if (!$prod_number) $prod_number = 8;
			return $prod_number;
		}

		//Related products limit
		function tm_related_products_limit() {
			global $product;
			$orderby = '';
			$columns = 4;
			$related = $product->get_related( 4 );
			$args = array(
				'post_type' => 'product',
				'no_found_rows' => 1,
				'posts_per_page' => 3,
				'ignore_sticky_posts' => 1,
				'orderby' => $orderby,
				'post__in' => $related,
				'post__not_in' => array($product->id)
			);
			return $args;
		}
		add_filter( 'woocommerce_related_products_args', 'tm_related_products_limit' );

		// Theme Actions
		get_template_part( 'includes/theme-actions' );
		// Theme Shortcodes
		get_template_part( 'includes/child-shortcodes' );

		// Template Wrappers
		function tm_open_shop_content_wrappers(){
			echo '<div class="motopress-wrapper content-holder clearfix woocommerce">
					<div class="container">
						<div class="row">
							<div class="span12" data-motopress-type="static" data-motopress-static-file="static/static-title.php">';
								echo get_template_part("static/static-title");
			echo 			'</div>
						</div>
						<div class="row">
							<div class="' . cherry_get_layout_class( 'content' ) . '" id="content">';
		}
		function tm_close_shop_content_wrappers(){
			echo			'</div>
							<div class="sidebar ' . cherry_get_layout_class( 'sidebar' ) . '" id="sidebar" data-motopress-type="static-sidebar"  data-motopress-sidebar-file="sidebar.php">';
								get_sidebar();
			echo			'</div>
						</div>
					</div>
				</div>';
		}

		function tm_prepare_shop_wrappers(){
			/* Woocommerce */
			remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
			remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5, 0);
			remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
			remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

			add_action('woocommerce_before_main_content', 'tm_open_shop_content_wrappers', 10);
			add_action('woocommerce_after_main_content', 'tm_close_shop_content_wrappers', 10);
			/* end Woocommerce */	
		}
		add_action('wp_head', 'tm_prepare_shop_wrappers');
		add_theme_support( 'woocommerce' );

		add_action('woocommerce_share', 'tm_product_share');
		function tm_product_share() {
			get_template_part( 'includes/post-formats/share-buttons' );
		}
add_action( 'cherry_before_slider', 'before_slider_output' );
function before_slider_output() {
    echo "<div class='slider-box'>";
}
add_action( 'cherry_after_slider', 'after_slider_output' );
function after_slider_output() {
 echo "</div><div class='widget-box'>";
 dynamic_sidebar( 'slider-widget-area' );
 echo "</div>";
}
//Change product on catalog page
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_price', 50 );

add_action( 'woocommerce_before_shop_loop_item_title','tm_product_open_wrap', 9);
 function tm_product_open_wrap() {
  echo "<div class='prod-inner-wrap'>";
 }
 add_action( 'woocommerce_before_shop_loop_item_title','tm_product_close_wrap', 11);
 function tm_product_close_wrap() {
  echo "</div>";
 }

		// Custom Links for Shop Menu
		function login_out_function ($nav, $args){
			
		  if( 'shop_menu' === $args -> theme_location ) {
			if(of_get_option("login_display_id")=="yes"){
	      		$username = (get_current_user_id()!=0) ? get_userdata(get_current_user_id())->user_login : '';
	      		$user_title = str_replace("%username%", $username, of_get_option("site_admin"));
			    $link_string_site = "<a href=\"".get_bloginfo('wpurl')."/wp-admin/index.php\" class='register-link' title=\"".$user_title."\">".$user_title."</a>";
				$link_string_logout = '<a href="'. wp_logout_url($_SERVER['REQUEST_URI']) .'" title="'.of_get_option("log_out").'">'.of_get_option("log_out").'</a>';
				$link_string_register = "<a href=\"".get_bloginfo('wpurl')."/wp-login.php?action=register&amp;redirect_to=".$_SERVER['REQUEST_URI']."\" class='register-link' title=\"".of_get_option("sign_up")."\">".of_get_option("sign_up")."</a>";
				$link_string_login = "<a href=\"".get_bloginfo('wpurl')."/wp-login.php?action=login&amp;redirect_to=".$_SERVER['REQUEST_URI']."\" title=\"".of_get_option("sign_in")."\">".of_get_option("sign_in")."</a>";
		
				if (!is_user_logged_in()) {
		        	$login_links = "<li>".$link_string_register."</li><li>".$link_string_login."</li>";
		     	}else{
		        	$login_links = "<li>".$link_string_site."</li><li>".$link_string_logout."</li>";
				}
				$nav = $login_links.$nav;
				return $nav;
			} else {
				return $nav;
			}
		  } else {
			  return $nav;
		  }
		}
		add_filter('wp_nav_menu_items','login_out_function', 10, 2);

		// Change columns number
		// ---------------------
		add_filter( 'loop_shop_columns', 'tm_product_columns', 5);
		function tm_product_columns($columns) {
			if ( is_shop() || is_product_category() || is_product_tag() ) {
				$columns = 3;
			}
			return $columns;
		}

	}

	// WP Pointers
	add_action('admin_enqueue_scripts', 'myHelpPointers');
	function myHelpPointers() {
	//First we define our pointers 
	$pointers = array(
	   	array(
	       'id' => 'xyz1',   // unique id for this pointer
	       'screen' => 'options-permalink', // this is the page hook we want our pointer to show on
	       'target' => '#submit', // the css selector for the pointer to be tied to, best to use ID's
	       'title' => theme_locals("submit_permalink"),
	       'content' => theme_locals("submit_permalink_desc"),
	       'position' => array( 
	                          'edge' => 'top', //top, bottom, left, right
	                          'align' => 'left', //top, bottom, left, right, middle
	                          'offset' => '0 5'
	                          )
	       ),

	    array(
	       'id' => 'xyz2',   // unique id for this pointer
	       'screen' => 'themes', // this is the page hook we want our pointer to show on
	       'target' => '#toplevel_page_options-framework', // the css selector for the pointer to be tied to, best to use ID's
	       'title' => theme_locals("import_sample_data"),
	       'content' => theme_locals("import_sample_data_desc"),
	       'position' => array( 
	                          'edge' => 'bottom', //top, bottom, left, right
	                          'align' => 'top', //top, bottom, left, right, middle
	                          'offset' => '0 -10'
	                          )
	       ),

	    array(
	       'id' => 'xyz3',   // unique id for this pointer
	       'screen' => 'toplevel_page_options-framework', // this is the page hook we want our pointer to show on
	       'target' => '#toplevel_page_options-framework', // the css selector for the pointer to be tied to, best to use ID's
	       'title' => theme_locals("import_sample_data"),
	       'content' => theme_locals("import_sample_data_desc_2"),
	       'position' => array( 
	                          'edge' => 'left', //top, bottom, left, right
	                          'align' => 'top', //top, bottom, left, right, middle
	                          'offset' => '0 18'
	                          )
	       )
	    // more as needed
	    );
		//Now we instantiate the class and pass our pointer array to the constructor 
		$myPointers = new WP_Help_Pointer($pointers); 
	};
?>
