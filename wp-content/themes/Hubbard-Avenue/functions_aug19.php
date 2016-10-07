<?php

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
        'show_in_menu' => TRUE,
        'supports' => array('title')
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
            echo '<li id="gc-image-' . $i . '"><div class="gc-inner-image">';
            echo '<img name="gc-image-' . $i . '" src="' . $gc_image['url'] . '">';
            echo '</div></li>';
            $i++;
        }
        echo "</ul>";
        echo "<input type='hidden' name='gc-hidden-image' id='gc-image'>";
        echo '</div>';
        $i = 1;
        echo "<h4>2. Enter the gift card details</h4>";
        echo '<div class="gc-prices">';
        echo " <label>Amount</label>";
        while (have_rows('price_options')) {
            the_row();
            $gc_price = get_sub_field('prices');
            echo '<input type="button" name="gc-prices" id="price-' . $i . '" value="' . $gc_price . '" >';
            $i++;
        }
        echo "<input type='hidden' name='gc-hidden-price' id='gc-price'>";
        echo '</div>';
        echo '<div class="gc-email">
                <label>Email</label>
                <input type="email" name="gc-mail">
              </div>';
        echo '<div class="gc-name">
                <label>Name</label>
                <input type="text" name="gc-name">
            </div>';
        echo '<div class="gc-message">
                <label>Message</label>
                <textarea name="gc-msg" id="gc-msg"></textarea>
            </div>';
    }
}

add_action('woocommerce_before_add_to_cart_button', 'add_fields_on_standard_product');

/* ---------------------------------------------------------------------------
 * Custom validation in woocommerce products
 * --------------------------------------------------------------------------- */

function standard_product_validation() {
    if (has_term('gift-cards', 'product_cat')) {
        if (empty($_REQUEST['gc-mail'])) {
            wc_add_notice(__('Please enter a Email', 'woocommerce'), 'error');
            return false;
        } else if (empty($_REQUEST['gc-name'])) {
            wc_add_notice(__('Please enter a Name', 'woocommerce'), 'error');
            return false;
        } /* else if ( empty( $_REQUEST['gc-prices'] ) ) {
          wc_add_notice( __( 'Please Select the price', 'woocommerce' ), 'error' );
          return false;
          } */
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
    return $cart_item_data;
}

add_action('woocommerce_add_cart_item_data', 'save_product_field', 10, 2);

function get_cart_items_from_session($item, $values, $key) {
    if (array_key_exists('gc_price', $values))
        $item['gc_price'] = $values['gc_price'];
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
    return $custom_items;
}

add_filter('woocommerce_get_item_data', 'render_meta_on_cart_and_checkout', 10, 2);

function custom_new_product_image($product_get_image, $cart_item, $cart_item_key) {
    $class = 'attachment-shop_thumbnail wp-post-image'; // Default cart thumbnail class.
    $src = $cart_item['gc_image'];

    // Construct your img tag.
    $product_get_image = '<img';
    $product_get_image .= ' src="' . $src . '"';
    $product_get_image .= ' class="' . $class . '"';
    $product_get_image .= ' />';
    // Output.
    return $product_get_image;
}

add_filter('woocommerce_cart_item_thumbnail', 'custom_new_product_image', 10, 3);

function filter_woocommerce_cart_item_price($wc, $cart_item, $cart_item_key) {
    $wc = $cart_item['gc_price'];
    // make filter magic happen here... 
    return $wc;
}

;

// add the filter 
add_filter('woocommerce_cart_item_price', 'filter_woocommerce_cart_item_price', 10, 3);

function tshirt_order_meta_handler($item_id, $values, $cart_item_key) {
    if (isset($values['gc_name'])) {
        wc_add_order_item_meta($item_id, "Name", $values['gc_name']);
    }
    if (isset($values['gc_email'])) {
        wc_add_order_item_meta($item_id, "Email", $values['gc_email']);
    }
}

add_action('woocommerce_add_order_item_meta', 'tshirt_order_meta_handler', 1, 3);


//Krishna Edits

add_action('woocommerce_thankyou', 'register_givex_card', 10, 1);

//add_action( 'woocommerce_payment_complete', 'register_givex_card', 10, 1 );
//add_action( 'woocommerce_before_add_to_cart_button', 'get_givex_cred', 10, 1 );
//
//function get_givex_cred(){
//    echo get_option( 'givex_user_id' );
//}

function register_givex_card($order_id) {
    $order = new WC_Order($order_id);
    echo "<pre>";
    print_R($order);
    $orderItems = $order->get_items();
    foreach ($orderItems as $eachItems) {
        $eachItems['name'];
        $eachItems['qty'];
        $eachItems['line_total'];
        $eachItems['product_id'];
        print_r($eachItems['item_meta']);
    }
    //echo "billing_email:".$order->billing_email;
//    $givex_amount = "100.00";
//    $givex_number = register_givex($givex_amount);
//    send_givex_mail($givex_number);
}

function send_givex_mail($givex_number) {
    //$givex_number = "60600645195100019210";
    $to = "krishna@goaugment.io";
    $subject = "Testing - Givex Number ";
    $content = "Your givex number is :" . $givex_number;
    $status = wp_mail($to, $subject, $content);
}

function register_givex($givex_amount) {
    $givex_amount = "100.00";
    $params = array(
        "en",
        "100",
        get_option('givex_user_id'),
        get_option('givex_password'),
        $givex_amount);
    $data = array(
        "jsonrpc" => "2.0",
        "id" => "curltext",
        "method" => "904",
        "params" => $params
    );
    $data_string = json_encode($data);
    $curl = curl_init();
    $method = "POST";
    curl_setopt_array($curl, array(
        CURLOPT_PORT => "50104",
        CURLOPT_URL => "https://dev-dataconnect.givex.com",
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

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
        die;
    } else {
        $result_array = json_decode($response);
        $result = $result_array->result;
//        print_R($result);die;
        if ($result['1'] == 0) {
            return $result['3'];
        } else {
            echo $result['2'];
            die;
        }
    }
}
