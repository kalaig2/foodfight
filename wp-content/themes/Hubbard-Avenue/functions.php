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

/* ---------------------------------------------------------------------------
 * Loads Menus
 * --------------------------------------------------------------------------- */

add_action('init', 'register_foodfight_menu');

function register_foodfight_menu()
{
    register_nav_menus(array(
        'about-menu' => __('About Menu', 'foodfight'), // About Navigation
        'gallery-menu' => __('Gallery Menu', 'foodfight'), // Gallery Navigation
        'other-menu' => __('Other Menu', 'foodfight'), // other Navigation
    ));
}

function header_nav()
{
    wp_nav_menu(array(
        'theme_location' => 'about-menu',
        'menu_class' => 'nav navbar-nav navbar-right',
        'container' => false,
        'depth' => 0
    ));
}

function gallery_nav()
{
    wp_nav_menu(array(
        'theme_location' => 'gallery-menu',
        'menu_class' => 'nav navbar-nav navbar-right',
        'container' => false,
        'depth' => 0
    ));
}

function others_nav()
{
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

function theme_slug_widgets_init()
{
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

function custom_post_type_sites()
{
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
        'show_in_menu' => TRUE,
        'supports' => array('title')
            )
    );
    register_post_type('foodSlider', array(
        'labels' => array(
            'name' => __('Food Sliders'),
            'singular_name' => __('Food Sliders'),
            'has_archive' => true
        ),
        'public' => true,
        'hierarchical' => true,
        'capability_type' => 'post',
        'show_in_menu' => TRUE,
        'supports' => array('thumbnail', 'title', 'editor')
            )
    );

    flush_rewrite_rules();
}

add_action('init', 'custom_post_type_sites');

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
function wporg_wpmu_new_blog_example($blog_id, $user_id)
{
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

function update_jquery_for_cherry_framework()
{
    wp_deregister_script('jquery');
    wp_register_script('jquery', '/wp-includes/js/jquery/jquery.js', false, false, true);
    wp_enqueue_script('jquery');
    wp_enqueue_script('add-to-cart-variation', '/wp-content/plugins/woocommerce/assets/js/frontend/add-to-cart-variation.js',array('jquery'),'1.0',true);
    wp_enqueue_style('bootstrap-datetimepicker-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.css');
    wp_enqueue_script('bootstrap-datetimepicker','https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js',array('jquery'),'1.0',true);
}

add_action( 'init', 'my_action' );
function my_action(){	

	remove_action( 'woocommerce_after_shop_loop_item', 'tm_catalog_product_description', 5);

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


// add the filter 

add_action('widgets_init', 'override_woocommerce_widgets', 15);
function override_woocommerce_widgets()
{
    if (class_exists('WC_Widget_Cart')) {
        unregister_widget('WC_Widget_Cart');
        include_once( 'cust_woo_widgets/class-wc-widget-cart.php' );
        register_widget('Custom_WooCommerce_Widget_Cart');
    }
}

add_filter('wp_mail_content_type','set_content_type');

/********************************************************************/
/*Collapse code*/
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


function find_cat_slug($product_id) {
    $term_list = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
    $cat_id = (int) $term_list[0];
    $term = get_term($cat_id, 'product_cat');

    return $term->slug;
}
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
      if (count($slugs) == 1 && in_array("pies",$slugs)) {
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

//Check the create account in checkout page by default
add_filter('woocommerce_create_account_default_checked' , function ($checked){
    return true;
});


/*-------------------------------------------------------------------------*/


// Add Variation Settings
add_action( 'woocommerce_product_after_variable_attributes', 'variation_settings_fields', 10, 3 );
// Save Variation Settings
add_action( 'woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2 );
/**
 * Create new fields for variations
 *
*/
function variation_settings_fields( $loop, $variation_data, $variation ) {

    // Hour Field
    woocommerce_wp_text_input( 
        array( 
            'id'          => '_hour_field[' . $variation->ID . ']', 
            'label'       => __( 'Hours', 'woocommerce' ), 
            'desc_tip'    => 'true',
            'description' => __( 'Enter the preparation time duration of products in Hours', 'woocommerce' ),
            'value'       => get_post_meta( $variation->ID, '_hour_field', true ),
            'custom_attributes' => array(
                            'step'  => 'any',
                            'min'   => '0'
                        ) 
        )
    );

    // Minutes Field
    woocommerce_wp_text_input( 
        array( 
            'id'          => '_min_field[' . $variation->ID . ']', 
            'label'       => __( 'Minutes', 'woocommerce' ), 
            'desc_tip'    => 'true',
            'description' => __( 'Enter the preparation time duration of products in minutes', 'woocommerce' ),
            'value'       => get_post_meta( $variation->ID, '_min_field', true ),
            'custom_attributes' => array(
                            'step'  => 'any',
                            'min'   => '0'
                        ) 
        )
    );
}
/**
 * Save new fields for variations
 *
*/
function save_variation_settings_fields( $post_id ) {
    // Number Field
    $number_field = $_POST['_hour_field'][ $post_id ];
    if( ! empty( $number_field ) ) {
        update_post_meta( $post_id, '_hour_field', esc_attr( $number_field ) );
    }

    $min_field = $_POST['_min_field'][ $post_id ];
    if( ! empty( $min_field ) ) {
        update_post_meta( $post_id, '_min_field', esc_attr( $min_field ) );
    }
}

// Add New Variation Settings
add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields' );
/**
 * Add custom fields for variations
 *
*/
function load_variation_settings_fields( $variations ) {
    
    // duplicate the line for each field
    $variations['duration'] = "<label>Lead Time:</label>";
    $hour = get_post_meta( $variations[ 'variation_id' ], '_hour_field', true );
    $min = get_post_meta( $variations[ 'variation_id' ], '_min_field', true );
    /*$variations['_hour_field'] = get_post_meta( $variations[ 'variation_id' ], '_hour_field', true )." Hour";

    $variations['_min_field'] = get_post_meta( $variations[ 'variation_id' ], '_min_field', true )." Minutes";*/
    if(!empty($hour) ) {
        $variations['_hour_field'] = $hour." Hour";
    }else {
        $variations['_hour_field'] = '0'." Hour";
    } 

    if( !empty($min) ) {
       $variations['_min_field'] = $min." Minutes"; 
    } else { 
        $variations['_min_field'] = '0'." Minutes";
    }
    
    return $variations;
}


/* ---------------------------------------------------------------------------
 * Add fields in woocommerce products
 * --------------------------------------------------------------------------- */

function add_fields_on_standard_product()
{
    echo '<div class="datetimepicker">
            <label>Pickup Date:</label>
            <input id="datetimepicker" type="text" name="datetimepicker">
        </div>';
}

add_action('woocommerce_before_add_to_cart_button', 'add_fields_on_standard_product');

/* ---------------------------------------------------------------------------
 * Custom validation in woocommerce products
 * --------------------------------------------------------------------------- */

function standard_product_validation($true, $product_id, $quantity)
{
    $term_list = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
    $cat_id = (int) $term_list[0];
    $term = get_term($cat_id, 'product_cat');

    $true = 1;
    if (isset($_REQUEST['datetimepicker']) && empty($_REQUEST['datetimepicker'])) {
        wc_add_notice(__('Please select date and time', 'woocommerce'), 'error');
        $true = 0;
    }
    
    if ($true == 0) {
        return false;
    }
    return true;
}

add_action('woocommerce_add_to_cart_validation', 'standard_product_validation', 10, 6);

/* ---------------------------------------------------------------------------
 * Custom item in woocommerce products cart
 * --------------------------------------------------------------------------- */

function save_product_field($cart_item_data, $product_id)
{
    if (isset($_REQUEST['datetimepicker'])) {
        $cart_item_data['_datetimepicker'] = $_REQUEST['datetimepicker'];
        /* below statement make sure every add to cart action as unique line item */
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    return $cart_item_data;
}

add_action('woocommerce_add_cart_item_data', 'save_product_field', 10, 2);

function render_meta_on_cart_and_checkout($cart_data, $cart_item = null)
{
    $custom_items = array();
    /* Woo 2.4.2 updates */
    if (!empty($cart_data)) {
        $custom_items = $cart_data;
    }
    if (!empty($cart_item['_datetimepicker'])) {
        $custom_items[] = array("name" => 'Pickup', "value" => $cart_item['_datetimepicker']);
    }
    return $custom_items;
}

add_filter('woocommerce_get_item_data', 'render_meta_on_cart_and_checkout', 10, 2);
function tshirt_order_meta_handler($item_id, $values, $cart_item_key)
{
    if (isset($values['_datetimepicker'])) {
        wc_add_order_item_meta($item_id, "Pickup", $values['_datetimepicker']);
    }
    
}

add_action('woocommerce_add_order_item_meta', 'tshirt_order_meta_handler', 1, 3);

