<?php

function child_js_global_variables(){
    $output = "<script>";
    $home_url = home_url();
    $output .="\n var url = '".$home_url."/my-account/'";
    $output .= "</script>";
    echo $output;
}
add_action('wp_head', 'child_js_global_variables');
add_action('admin_head', 'child_js_global_variables');

/*******************************************************************/
function mode_theme_update_mini_cart() {
   echo wc_get_template( 'cart/mini-cart.php' );
   die();
 }
 add_filter( 'wp_ajax_nopriv_mode_theme_update_mini_cart', 'mode_theme_update_mini_cart' );
 add_filter( 'wp_ajax_mode_theme_update_mini_cart', 'mode_theme_update_mini_cart' );
/* ---------------------------------------------------------------------------
 * Adding New Role
 * --------------------------------------------------------------------------- */
add_action('init', 'user_role');

function user_role() {
     add_role(
        'ffuser',
        __( 'User' ),
        array(
            'read'         => true,  // true allows this capability
            'edit_posts'   => true,
            'delete_posts' => fasle, // Use false to explicitly deny
        )
    );

     add_role(
        'ffsuperuser',
        __( 'Super Admin' ),
        array(
            'read'         => true,  // true allows this capability
            'edit_posts'   => true,
            'delete_posts' => true, // Use false to explicitly deny
        )
    );

    //remove_role( 'user' );

}
add_action( 'admin_init', 'my_remove_menu_pages' );
function my_remove_menu_pages() {

    global $user_ID;

    if ( current_user_can( 'ffuser' ) ) {
        remove_menu_page( 'edit.php' );
        remove_menu_page( 'edit.php?post_type=testi' );
        remove_menu_page( 'edit.php?post_type=services' );
        remove_menu_page( 'edit.php?post_type=faq' );
        remove_menu_page( 'edit.php?post_type=slides' );
        remove_menu_page( 'edit-comments.php' );
        remove_menu_page( 'edit.php?post_type=blog' );
        remove_menu_page( 'wpcf7' );
        remove_menu_page('profile.php');
        remove_menu_page('tools.php');
        remove_menu_page('motopress');
    }
}


/* ---------------------------------------------------------------------------
 * Loads Menus
 * --------------------------------------------------------------------------- */

add_action('init', 'register_foodfight_menu');

function register_foodfight_menu() {
    register_nav_menus(array(
        'about-menu' => __('About Menu', 'foodfight'), // About Navigation
        'gallery-menu' => __('Gallery Menu', 'foodfight'), // Gallery Navigation
        'other-menu' => __('Other Menu', 'foodfight'), // other Navigation
    ));
}

function header_nav() {
    wp_nav_menu(array(
        'theme_location' => 'about-menu',
        'menu_class' => 'nav navbar-nav navbar-right',
        'container' => false,
        'depth' => 0
    ));
}

function gallery_nav() {
    wp_nav_menu(array(
        'theme_location' => 'gallery-menu',
        'menu_class' => 'nav navbar-nav navbar-right',
        'container' => false,
        'depth' => 0
    ));
}

function others_nav() {
    wp_nav_menu(array(
        'theme_location' => 'others-menu',
        'menu_class' => 'nav navbar-nav navbar-right',
        'container' => false,
        'depth' => 0
    ));
}

/* ---------------------------------------------------------------------------
 * Loads Widget
 * --------------------------------------------------------------------------- */

add_action('widgets_init', 'theme_slug_widgets_init');

function theme_slug_widgets_init() {
    register_sidebar(array(
        'name' => __('Header Area 1', 'theme-slug'),
        'id' => 'header_area_1',
        'class' => 'form-group',
        'description' => __('Widgets in this area will be shown on all posts and pages.', 'theme-slug'),
        'before_title' => '<h4>',
        'after_title' => '</h4>',
        'before_widget' => '<div>',
        'after_widget' => '</div>',
    ));

    register_sidebar(array(
        'name' => __('Footer Area 4', 'theme-slug'),
        'id' => 'footer_area_4',
        'class' => 'form-group',
        'description' => __('Widgets in this area will be shown on all posts and pages.', 'theme-slug'),
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));

    register_sidebar(array(
        'name' => __('Footer Area 5', 'theme-slug'),
        'id' => 'footer_area_5',
        'class' => 'form-group',
        'description' => __('Widgets in this area will be shown on all posts and pages.', 'theme-slug'),
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));

    register_sidebar(array(
        'name' => __('Footer Area 6', 'theme-slug'),
        'id' => 'footer_area_6',
        'class' => 'form-group',
        'description' => __('Widgets in this area will be shown on all posts and pages.', 'theme-slug'),
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
}

/* ---------------------------------------------------------------------------
 * For Theme Options
 * --------------------------------------------------------------------------- */
define('THEME_DIR', get_stylesheet_directory());
define('THEME_URI', get_stylesheet_directory_uri());

define('THEME_NAME', 'twentyfourteen');
define('THEME_VERSION', '2.3');

define('LIBS_DIR', THEME_DIR . '/functions');
define('LIBS_URI', THEME_URI . '/functions');
define('LANG_DIR', THEME_DIR . '/languages');

add_filter('widget_text', 'do_shortcode');
require( THEME_DIR . '/theme-options/theme-options.php' );



/* ---------------------------------------------------------------------------
 * Loads Custom post type for Multisite
 * --------------------------------------------------------------------------- */

function custom_post_type_sites() {
    register_post_type('blog', array(
        'labels' => array(
            'name' => __('Multisite'),
            'singular_name' => __('Multisite'),
            'has_archive' => true
        ),
        'public' => true,
        'hierarchical' => true,
        'capability_type' => 'post',
        'show_in_menu' => TRUE,
        'supports' => array('title')
            )
    );

    register_post_type('giftcards', array(
        'labels' => array(
            'name' => __('Gift Cards'),
            'singular_name' => __('All Gift Cards'),
            'has_archive' => true
        ),
        'public' => true,
        'hierarchical' => true,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
          ),
        'show_in_menu' => TRUE,
        'supports' => array('title')
            )
    );
    flush_rewrite_rules();
}

add_action('init', 'custom_post_type_sites');

function hide_menu_item() {
   echo '<style type="text/css">
           /*.wp-first-item{display:none;}*/
         </style>';
         ?>
         <script>jQuery(document).ready(function($){
            $(".wp-has-submenu:contains('Gift Cards')").siblings("ul").children('li.wp-first-item').hide();
            $(".wp-has-submenu:contains('Gift Cards')").attr("href", "#");
         });</script>
         <?php

}

add_action('admin_head', 'hide_menu_item');


/* ---------------------------------------------------------------------------
 * Loads Custom Taxonomy for NEWS
 * --------------------------------------------------------------------------- */
/*
  add_action( 'init', 'create_giftcard' );

  function create_giftcard() {
  register_taxonomy(
  'giftcards_category',
  'giftcards',
  array(
  'label' => __( 'Giftcards Category' ),
  'rewrite' => array( 'slug' => 'giftcards_category' ),
  'hierarchical' => true,
  )
  );
  flush_rewrite_rules();
  } */


/* ---------------------------------------------------------------------------
 * Trigger the new multisite
 * --------------------------------------------------------------------------- */

/**
 * Example of wpmu_new_blog usage
 *
 * @param int    $blog_id Blog ID.
 * @param int    $user_id User ID.
 * @param string $domain  Site domain.
 * @param string $path    Site path.
 * @param int    $site_id Site ID. Only relevant on multi-network installs.
 * @param array  $meta    Meta data. Used to set initial site options.
 */
function wporg_wpmu_new_blog_example($blog_id, $user_id) {
    $blog_details = get_blog_details($blog_id);
    $new_post = array(
        'post_title' => $blog_details->blogname,
        //'post_content' => $blog_id,
        'post_status' => 'publish',
        'post_date' => date('Y-m-d H:i:s'),
        'post_author' => $user_id,
        'post_type' => 'blog',
        'post_category' => array(0)
    );
    $post_id = wp_insert_post($new_post);
    update_field('ff_site_id', $blog_id, $post_id);
}

add_action('wpmu_new_blog', 'wporg_wpmu_new_blog_example', 10, 6);

/* ---------------------------------------------------------------------------
 * Loads StyleSheets and Scripts
 * --------------------------------------------------------------------------- */

add_action('wp_enqueue_scripts', 'update_jquery_for_cherry_framework', 11);

function update_jquery_for_cherry_framework() {
	wp_enqueue_style( 'dashicons' );
    wp_deregister_script('jquery');
    wp_register_script('jquery', '/wp-includes/js/jquery/jquery.js', false, false, true);
    wp_enqueue_script('jquery');
}

/* ---------------------------------------------------------------------------
 * Add fields in woocommerce products
 * --------------------------------------------------------------------------- */

function add_fields_on_standard_product() {
    if (has_term('gift-cards', 'product_cat')) {
        $gift_card_images = get_field('gift_card_images');
        $i = 1;
        echo '<div class="gc-images">';
        echo "<h4>1. Select the Card</h4>";
        echo "<ul>";
        while (have_rows('gift_card_images')) {
            the_row();
            $gc_image = get_sub_field('image');
            $gc_type = get_sub_field('type');
            echo '<li id="gc-image-' . $i . '"><div class="gc-inner-image">';
            echo '<img data-type="'.$gc_type.'" name="gc-image-' . $i . '" src="' . $gc_image['url'] . '">';
            echo '</div></li>';
            $i++;
        }
        echo "</ul>";
        echo "<input type='hidden' name='gc-hidden-image' id='gc-image'>";
        echo '</div>';
        $i = 1;
        echo "<h4>2. Enter the gift card details</h4>";
        echo '<div class="gc-prices">';
            echo " <label>Amount*</label>";
            while (have_rows('price_options')) {
                the_row();
                $gc_price = get_sub_field('prices');
                echo '<input type="button" name="gc-prices" class="amt-but" id="price-' . $i . '" value="$ ' . $gc_price . '" >';
                $i++;
            }
            echo "<input type='hidden' name='gc-hidden-price' id='gc-price'>";

			echo "<input type='button' name='other' class='amt-but' id='other' value='Other'>";
            echo "<input type='text' name='custom-price' id='custom-price' class='custom-price other-price'>";
        echo '</div>';


        if (get_the_title() == "Standard") {
            echo '<div class="gc-to">
	            <label>To*</label>
                <input type="email" name="gc-mail">
	        </div>';
            echo '<div class="gc-from">
	            <label>From*</label>
	            <input type="text" name="gc-name">
	        </div>';
            echo '<div class="gc-message">
                <label>Message</label>
                <textarea name="gc-msg" id="gc-msg"></textarea>
                <input type="hidden" name="gc_card_type" id="gc_card_type"/>
            </div>';
        }
        if (get_the_title() == "E-Certificate") {
            echo '<div class="gc-email">
                <label>Email*</label>
                <input type="email" name="gc-mail">
              </div>';
            echo '<div class="gc-name">
                <label>Name*</label>
                <input type="text" name="gc-name">
            </div>';
            echo '<div class="gc-message">
                <label>Message</label>
                <textarea name="gc-msg" id="gc-msg"></textarea>
                <input type="hidden" name="gc_card_type" id="gc_card_type"/>
            </div>';
        }
    }
}

add_action('woocommerce_before_add_to_cart_button', 'add_fields_on_standard_product');

/* ---------------------------------------------------------------------------
 * Custom validation in woocommerce products
 * --------------------------------------------------------------------------- */

function standard_product_validation($true, $product_id, $quantity) {
    $term_list = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
    $cat_id = (int) $term_list[0];
    $term = get_term($cat_id, 'product_cat');

    if ($term->slug == "gift-cards") {
        $true = 1;
        if (isset($_REQUEST['gc-mail']) && empty($_REQUEST['gc-mail'])) {
            wc_add_notice(__('Please enter an email', 'woocommerce'), 'error');
            $true = 0;
        }
        if (isset($_REQUEST['gc-name']) && empty($_REQUEST['gc-name'])) {
            wc_add_notice(__('Please enter a name', 'woocommerce'), 'error');
            $true = 0;
        }
        if (isset($_REQUEST['gc-hidden-price']) && empty($_REQUEST['gc-hidden-price'])) {
            wc_add_notice(__('Please select the price', 'woocommerce'), 'error');
            $true = 0;
        }

        if ($true == 0) {
            return false;
        }
    }
    return true;
}

add_action('woocommerce_add_to_cart_validation', 'standard_product_validation', 10, 3);

/* ---------------------------------------------------------------------------
 * Custom item in woocommerce products cart
 * --------------------------------------------------------------------------- */

function save_product_field($cart_item_data, $product_id) {
    if (isset($_REQUEST['gc-hidden-image'])) {
        $cart_item_data['gc_image'] = $_REQUEST['gc-hidden-image'];
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    if (isset($_REQUEST['gc-hidden-price'])) {
        $cart_item_data['gc_price'] = $_REQUEST['gc-hidden-price'];
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    if (isset($_REQUEST['gc_card_type'])) {
        $cart_item_data['gc_card_type'] = $_REQUEST['gc_card_type'];
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    if (isset($_REQUEST['gc-mail'])) {
        $cart_item_data['gc_email'] = $_REQUEST['gc-mail'];
        /* below statement make sure every add to cart action as unique line item */
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    if (isset($_REQUEST['gc-name'])) {
        $cart_item_data['gc_name'] = $_REQUEST['gc-name'];
        /* below statement make sure every add to cart action as unique line item */
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    if (isset($_REQUEST['gc-msg'])) {
        $cart_item_data['gc_msg'] = $_REQUEST['gc-msg'];
        /* below statement make sure every add to cart action as unique line item */
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    if (isset($_REQUEST['custom-price'])) {
        $cart_item_data['custom_price'] = $_REQUEST['custom-price'];
        /* below statement make sure every add to cart action as unique line item */
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    return $cart_item_data;
}

add_action('woocommerce_add_cart_item_data', 'save_product_field', 10, 2);

function get_cart_items_from_session($item, $values, $key) {
    if( !empty($values['gc_price']) ) {
        if (array_key_exists('gc_price', $values)) {
            $item['gc_price'] = $values['gc_price'];
            $item['line_total'] = $values['gc_price'];
            $item['line_subtotal'] = $values['gc_price'];
            $item['data']->price = $values['gc_price'];
        }
    } else if(!empty($values['custom_price']) ){
        $item['custom_price'] = $values['custom_price'];
        $item['line_total'] = $values['custom_price'];
        $item['line_subtotal'] = $values['custom_price'];
        $item['data']->price = $values['custom_price'];
    }
    return $item;
}

add_filter('woocommerce_get_cart_item_from_session', 'get_cart_items_from_session', 1, 3);

function render_meta_on_cart_and_checkout($cart_data, $cart_item = null) {
    $custom_items = array();
    /* Woo 2.4.2 updates */
    if (!empty($cart_data)) {
        $custom_items = $cart_data;
    }
    if (!empty($cart_item['gc_name'])) {
        $custom_items[] = array("name" => 'Name', "value" => $cart_item['gc_name']);
    }
    if (!empty($cart_item['gc_email'])) {
        $custom_items[] = array("name" => 'Email', "value" => $cart_item['gc_email']);
    }
    if (!empty($cart_item['gc_card_type'])) {
        $custom_items[] = array("name" => 'CardType', "value" => $cart_item['gc_card_type']);
    }
    return $custom_items;
}

add_filter('woocommerce_get_item_data', 'render_meta_on_cart_and_checkout', 10, 2);

function custom_new_product_image($product_get_image, $cart_item, $cart_item_key) {
    if(!empty($cart_item['gc_image'])) {
        $src = $cart_item['gc_image'];
        $class = 'attachment-shop_thumbnail wp-post-image'; // Default cart thumbnail
        // Construct your img tag.
        $product_get_image = '<img';
        $product_get_image .= ' src="' . $src . '"';
        $product_get_image .= ' class="' . $class . '"';
        $product_get_image .= ' />';
        // Output.
    }
    return $product_get_image;
}

add_filter('woocommerce_cart_item_thumbnail', 'custom_new_product_image', 10, 3);


function checkout_order_meta_handler($item_id, $values, $cart_item_key) {
    if (isset($values['gc_name'])) {
        wc_add_order_item_meta($item_id, "Name", $values['gc_name']);
    }
    if (isset($values['gc_email'])) {
        wc_add_order_item_meta($item_id, "Email", $values['gc_email']);
    }
    if (isset($values['gc_card_type'])) {
        wc_add_order_item_meta($item_id, "CardType", $values['gc_card_type']);
    }
    if (isset($values['gc_image'])) {
        $product_image = '<img src="'.$values['gc_image'].'" width="20%" >';
//        wc_add_order_item_meta($item_id, "gc_image", $values['gc_image']);
        wc_add_order_item_meta($item_id, "Image", $product_image);
    }
    if (isset($values['gc_msg'])) {
        wc_add_order_item_meta($item_id, "Message", $values['gc_msg']);
    }
}

add_action('woocommerce_add_order_item_meta', 'checkout_order_meta_handler', 1, 3);

function filter_woocommerce_quantity_input_max( $var, $product ) {
    // make filter magic happen here...
    $product_cats = wp_get_post_terms( $product->id, 'product_cat' );
    if($product_cats[0]->slug == "gift-cards"){
      $var =1;
    }
    return $var;
};

// add the filter
add_filter( 'woocommerce_quantity_input_max', 'filter_woocommerce_quantity_input_max', 10, 2 );
// define the woocommerce_cart_item_quantity callback
function filter_woocommerce_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
    // make filter magic happen here...
    $slug = find_cat_slug($cart_item['product_id']);
    if( $slug == "gift-cards") {
        $max_value = 1;
        $product_quantity = woocommerce_quantity_input( array(
                'input_name'  => "cart[{$cart_item_key}][qty]",
                'input_value' => $cart_item['quantity'],
                'max_value'   => $max_value,
                'min_value'   => '1'
        ), $_product, false );
    }
    return $product_quantity;
};

// add the filter
add_filter( 'woocommerce_cart_item_quantity', 'filter_woocommerce_cart_item_quantity', 10, 3 );

//Krishna Edits

//add_filter( 'woocommerce_cart_item_price', 'custom_new_product_price' );
add_action('woocommerce_thankyou', 'register_givex_card', 10, 1);

function register_givex_card($order_id) {
    $order = new WC_Order($order_id);
    $orderItems = $order->get_items();
    if($order->get_item_count() >0){
        foreach ($orderItems as  $key => $eachItems) {
            if(!isset($eachItems['givex_status'])){
                $product_id = $eachItems['product_id'];
                $product_name = $eachItems['name'];
                $product_qty = $eachItems['qty'];
                $givex_amount = $eachItems['line_total'];
                $formatted_image = $eachItems['Image'];
                $array = array();
                preg_match( '/src="([^"]*)"/i', $formatted_image, $array ) ;
                $template_image = $array[1];
                $template_msg = $eachItems['Message'];
                $template_cardtype = $eachItems['CardType'];
                $template_name = $eachItems['Name'];
                $template_mail = strip_tags($eachItems['Email']);
                $prodType = getProdType($product_id);

                if($order->get_item_count() > 0 && $product_name != "E-Certificate"){
                   $order->update_status( 'processing' );
                }
                if ($prodType == "gift-cards" && $product_name == "Standard") {
                    global $wpdb;
                    $table_name = $wpdb->prefix . "gift_cards";
                    $results = $wpdb->get_row("SELECT `card_number` FROM $table_name WHERE `order_id`=$order_id order by card_number asc limit 1");
                    if (count($results) == 0) {
                        $user_email = "";
                        $card_type = $eachItems['CardType'];
                        $avail_givex_number = getAvailableGivexNumber($card_type);
                        if ($avail_givex_number != null) {
                            $activated_response = activate_givex_number($givex_amount, $avail_givex_number,$order_id);
                            if($activated_response =="Success"){
                                setGivexNumberInActive($avail_givex_number, $givex_amount, $user_email, $template_image, $template_name, $template_mail, $template_msg, $order_id,$card_type);// To update givex_number status and details
                                wc_add_order_item_meta($key,"givex_status","complete");
                            }else{
                                setGivexNumberInActive($avail_givex_number, $givex_amount, $user_email, $template_image, $template_name, $template_mail, $template_msg, $order_id,$card_type);// To update givex_number status and details
                                wc_add_order_item_meta($key,"givex_status","failed");
                                notify_admin($order_id);
                            }
                        }else{
                            wc_add_order_item_meta($key,"givex_status","failed");
                            notify_admin($order_id);
                        }
                    }
                }

                if ($prodType == "gift-cards" && $product_name == "E-Certificate") {
                    $givex_response = register_givex($givex_amount,$order_id);
                    if($givex_response['status'] =="Success"){
                        $givex_number = $givex_response['givex_number'];
                        wc_add_order_item_meta($key,"givex_number",$givex_number);
                        wc_add_order_item_meta($key,"givex_status","complete");
                        if($order->get_item_count() == 1){
                            $order->update_status( 'completed' );
                        }
                        send_givex_mail($givex_number, $givex_amount, $template_image, $template_msg, $template_name, $template_mail,$template_cardtype);
                    }else{
                        wc_add_order_item_meta($key,"givex_number","");
                        wc_add_order_item_meta($key,"givex_status","failed");
//                        wc_add_notice(__('Givex transaction has failed. Please contact FooodFight support', 'woocommerce'), 'error');
//                        wc_print_notices();
                        if($order->get_item_count() == 1){
                            $order->update_status( 'processing' );
                            //send mail to admin
                            notify_admin($order_id);
                        }
                        echo '<script>
                            jQuery(document).ready(function($){
                              $(".woocommerce-thankyou-order-received").append("<p class=\'error_msg\'>Unfortunately there was an issue generating gift card. Please contact the foodfight support.</p>");
                              $(".error_msg").css("color","red");
                            });
                        </script>';
                    }
                }
            }
        }
    }
}
function getAvailableGivexNumber($card_type) {
    global $wpdb;
    $table_name = $wpdb->prefix . "gift_cards";
    $results = $wpdb->get_row("SELECT `card_number` FROM $table_name WHERE `active`=1 AND `card_type`='$card_type' order by card_number asc limit 1");
    if(!empty($results)){
        return $results->card_number;
    }else{
        return null;
    }

}

function setGivexNumberInActive($givex_number,$givex_amount,$user_email,$img_url,$from_name,$to_email,$template_msg,$order_id,$card_type) {
    global $wpdb,$current_user;
    $table_name = $wpdb->prefix . "gift_cards";
    if($current_user->ID==0){
        $current_user_id="";
        $current_user_email="";
    }else{
        $current_user_id= $current_user->ID;
        $current_user_email= $current_user->user_email;
    }
    $data = array(
        "price" => $givex_amount,
        "active" => 0,
        "purchased_user_id"=>$current_user_id,
        "purchased_user"=>$from_name,
        "purchased_user_email"=>$current_user_email,
        "image_url"=>$img_url,
        "to_email" =>$to_email,
        "notes"=>$template_msg,
        "order_id"=>$order_id,
        "card_type"=>$card_type
    );
//    print_r($data);die;
    $wpdb->update($table_name,$data,array("card_number" => $givex_number));


}

function getProdType($product_id) {
    //$product_cats = wp_get_post_terms( "2245", 'product_cat' );
    $product_cats = wp_get_post_terms($product_id, 'product_cat');
    return $product_cats['0']->slug;
}

function send_givex_mail($givex_number, $givex_amount, $template_image, $template_msg, $template_name, $template_mail, $template_cardtype) {
//    echo "mail_fun";
    $to = $template_mail;
    $banner_image = mmi_opts_get('email-img');
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $subject = "E-Certificate";
    //$content = "<img style='max-width: 50%;' src='" .$banner_image. "'>";
    $content .= "<br><img style='max-width: 50%;' src='" . $template_image . "'>";
    $content .= "<br><br><div style='max-width: 50%;'><label>Valued at:<label> <span style='font-size=25px'>$ {$givex_amount}</span></div><br>
                 <div style='max-width: 50%;'><label>From:</label> {$template_name}</div> <br>
                 <div style='max-width: 50%;'><label>Card Type:</label> {$template_cardtype}</div> <br>
                <div style='max-width: 50%;'>e-Gift Card Number: " . $givex_number . "</strong></div><br>
                <div style='max-width: 50%;'><label>Message:</label> {$template_msg}</div><br>";
    $status = wp_mail($to, $subject, $content,$headers);
}

function notify_admin($order) {
    $to = get_bloginfo('admin_email');
    $subject = "Issue with Gift card generation";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $content = "Hi, <br>There was an issue with givex transaction. For more details check the order.<br>";
    $link = '<p><a href="'. admin_url( 'post.php?post=' . absint( $order ) . '&action=edit' ) .'" >';
    $link .= __( 'Click here to go to the order page', 'your_domain' );
    $link .= '</a></p>';
    $content .=  $link;
    wp_mail($to, $subject, $content,$headers);
}


function register_givex($givex_amount,$order_id) {
    //$givex_amount = "100.00";
    $params = array(
        "en",
        "100",
        get_option('givex_user_id'),
        get_option('givex_password'),
        $givex_amount);
    $data = array(
        "jsonrpc" => "2.0",
        "id" => "givexRegisterCard",
        "method" => "904",
        "params" => $params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    curl_setopt_array($curl, array(
        CURLOPT_PORT => get_option('givex_port'),
        CURLOPT_URL => get_option('givex_url'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $data_string,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            'Content-Length: ' . strlen($data_string)
        ),
    ));
    $i=0;$err=true;
    while ($i++ <3) {
        $response=curl_exec($curl);
        if ($response){
            $err=false;
            break;
        }else{
            $err = curl_error($curl);
        }
        if ($i<3) sleep($i);
    }

//    $response = curl_exec($curl);
//    $err = curl_error($curl);

    $res =array();
    if ($err) {
        $res['status']="Error";
        $res['message']= curl_errno($curl) . $err;
        log_error(curl_errno($curl) .":". $err,"order_id:".$order_id);
        curl_close($curl);
        return $res;
    } else {
        curl_close($curl);
        $result_array = json_decode($response);
        $result = $result_array->result;
//        print_R($result);die;
        if ($result['1'] == 0) {
            $res['status']="Success";
            $res['givex_number']=$result['3'];
            $res['message']=$result['3'];
            return $res;
        } else {
            $res['status']="Error";
            $res['message']=$result['2'];
            return $res;
        }
    }
}

function activate_givex_number($givex_amount, $givex_number,$order_id) {
    $test_givex_number = "603628446622001019287"; // card D from Givex certification test doc
    //$givex_amount = "100.00";
    $params = array(
        "en",
        "100",
        get_option('givex_user_id'),
        get_option('givex_password'),
        $test_givex_number,
        $givex_amount);
    $data = array(
        "jsonrpc" => "2.0",
        "id" => "givexActivateCard",
        "method" => "906",
        "params" => $params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    curl_setopt_array($curl, array(
        CURLOPT_PORT => get_option('givex_port'),
        CURLOPT_URL => get_option('givex_url'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $data_string,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            'Content-Length: ' . strlen($data_string)
        ),
    ));
    /*$response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);*/
    $i=0;$err=true;
    while ($i++ <3) {
        $response=curl_exec($curl);
        if ($response){
            $err=false;
            break;
        }else{
            $err = curl_error($curl);
        }
        if ($i<3) sleep($i);
    }
    if ($err) {
        $res['status']="Error";
        $res['message']= curl_errno($curl) . $err;
        global $wpdb;
        $table_name = $wpdb->prefix . "gift_cards";
        $data = array("response" => curl_errno($curl) .":". $err);
        $wpdb->update($table_name,$data,array( 'card_number' => $givex_number ));
        log_error(curl_errno($curl) .":". $err,"order_id:".$order_id);
        curl_close($curl);
        return $res;
    } else {
        curl_close($curl);
        $result_array = json_decode($response);
        $result = $result_array->result;
        global $wpdb;
        $table_name = $wpdb->prefix . "gift_cards";
        $data = array("response" => $response);
        $wpdb->update($table_name,$data,array( 'card_number' => $givex_number ));
        if ($result['1'] == 0) {
            //return $result['5'];
            $res['status']="Success";
            $res['message']=$result['5'];
            $data = array("givex_status" => 1);
            $wpdb->update($table_name,$data,array( 'card_number' => $givex_number ));
            return $res;
        } else {
            $res['status']="Error";
            $res['message']=$result['2'];
            return $res;
        }
    }
}
function log_error($message,$notes){
    global $wpdb;
    $table_name = $wpdb->prefix . "givex_error_log";
    $date = current_time( 'mysql' );
    $data = array(
        "message" => $message,
        "created_date" => $date,
        "notes" => $notes
    );
    $wpdb->insert($table_name,$data);
}
function set_content_type($content_type){
return 'text/html';
}

add_filter('wp_mail_content_type','set_content_type');

/*function action_woocommerce_checkout_before_customer_details(  ) {
    // make action magic happen here...
    echo "<div id='custom_billing_form'>";
};

// add the action
add_action( 'woocommerce_before_checkout_billing_form', 'action_woocommerce_checkout_before_customer_details', 10, 0 );

function action_woocommerce_checkout_after_customer_details(  ) {
    // make action magic happen here...
    echo "</div>";
};

// add the action
add_action( 'woocommerce_after_checkout_billing_form', 'action_woocommerce_checkout_after_customer_details', 10, 0 );

function wc_custom_addresses_labels( $translated_text, $text, $domain )
{
    switch ( $translated_text )
    {
        case 'Billing Details' : // Back-end
            if(is_checkout()) {
                $translated_text = __('Billing detail<span  id=\'expand_shipping\' class="dashicons dashicons-minus"></span>', 'woocommerce');
            }else{
                $translated_text = __('Billing detail', 'woocommerce');
            }
            break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'wc_custom_addresses_labels', 20, 3 );*/


//To avoid adding more than one gift card
/*function so_validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {
    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $prodType = getProdType($product_id);
    foreach($items as $item => $values) {
        $existing_product = $values['product_id'];
        if ($prodType == "gift-cards" && $existing_product==$product_id){
            $passed = false;
            wc_add_notice( __( 'Gift cards can be bought only one at a time', 'woocommerce' ), 'error' );
            return $passed;
        }
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'so_validate_add_cart_item', 10, 5 );*/

//remove default shop page short description
add_action( 'init', 'remove_my_action' );
function remove_my_action(){
    remove_action( 'woocommerce_after_shop_loop_item', 'tm_catalog_product_description', 5);
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

    add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
}

function shop_page_short_description() {
    $excerpt        = get_the_excerpt();
    $excerpt_substr = substr($excerpt, 0,60); ?>
    <div class="short_desc">
        <?php echo $excerpt_substr."..."; ?>
    </div>
<?php
}
add_action( 'woocommerce_after_shop_loop_item', 'shop_page_short_description', 40 );

function find_cat_slug($product_id) {
    $term_list = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
    $cat_id = (int) $term_list[0];
    $term = get_term($cat_id, 'product_cat');

    return $term->slug;
}

/*function my_hide_shipping_when_free_is_available( $rates ) {
    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $new_rates = array();
    foreach($items as $item) {
        $slug = find_cat_slug($item['product_id']);
        if( $slug == 'gift-cards' ) {
            unset($rates['wf_shipping_usps:D_PRIORITY_MAIL']);
            $new_rates = $rates;
        } else {
          unset($rates['free_shipping:8']);
            $new_rates = $rates;
        }
    }
    return $new_rates;
}*/
function my_hide_shipping_when_free_is_available( $rates ) {
    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $new_rates = array();
    $slugs = array();
    foreach($items as $item) {
        $slug = find_cat_slug($item['product_id']);
        $slugs[] = $slug;
}
  $slugs = array_unique($slugs);
      if (count($slugs) == 1 && in_array("gift-cards",$slugs)) {
      $found = false;
      foreach($rates as $key => $rate) {
          if ($rate->method_id == "wf_shipping_usps") {
              $found = true;
              break;
          }
      }
     if ($found) unset($rates[$key]);
        $new_rates = $rates;

        return $new_rates;
    } else {
      $found = false;
      foreach($rates as $key => $rate) {
          if ($rate->method_id == "free_shipping") {
              $found = true;
              break;
          }
      }

      if ($found) unset($rates[$key]);
        $new_rates = $rates;

        return $new_rates;

    }


}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );

// define the woocommerce_cart_no_shipping_available_html callback
function filter_woocommerce_cart_no_shipping_available_html( $p____there_are_no_shipping_methods_available_please_double_check_your_address_or_contact_us_if_you_need_any_help_woocommerce_p ) {
    // make filter magic happen here...
    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $new_rates = array();
    foreach($items as $item) {
        $slug = find_cat_slug($item['product_id']);
        if( $slug == 'gift-cards' ) {
            $new_msg = "There is no shipping available for gift card product";
        } else {
            $new_msg = $p____there_are_no_shipping_methods_available_please_double_check_your_address_or_contact_us_if_you_need_any_help_woocommerce_p;
        }
    }
    return $new_msg;
};

// add the filter
add_filter( 'woocommerce_cart_no_shipping_available_html', 'filter_woocommerce_cart_no_shipping_available_html', 10, 1 );


add_filter('woocommerce_available_payment_gateways', 'show_custom_payment_gateways');
function show_custom_payment_gateways( $available_gateways){
  foreach($available_gateways as $available_gateway) {
    if ($available_gateway->title != 'Gift card ' ){
        $available_gateway->chosen = false;
    } else {
        $available_gateway->chosen = true;
    }
  }
  //echo "<pre>";
  //print_r($available_gateways); die;
  return $available_gateways;
}

 add_action('widgets_init', 'override_woocommerce_widgets', 15);

function override_woocommerce_widgets()
{
    if (class_exists('WC_Widget_Cart')) {
        unregister_widget('WC_Widget_Cart');
        include_once( 'cust_woo_widgets/class-wc-widget-cart.php' );
        register_widget('Custom_WooCommerce_Widget_Cart');
    }
}

