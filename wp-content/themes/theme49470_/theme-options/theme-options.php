<?php
/**
 * Theme Options - fields and args
 *
 * @package MM International
 * @author
 * @link
 */

require_once( dirname( __FILE__ ) . '/options.php' );

/**
 * Options Page | Helper Functions
 */


// Background Position
function mmia_bg_position(){
	return array(
		'no-repeat;center top;;' 		=> 'Center Top No-Repeat',
		'repeat;center top;;' 			=> 'Center Top Repeat',
		'no-repeat;center;;' 			=> 'Center No-Repeat',
		'repeat;center;;' 				=> 'Center Repeat',
		'no-repeat;left top;;' 			=> 'Left Top No-Repeat',
		'repeat;left top;;' 			=> 'Left Top Repeat',
		'no-repeat;center top;fixed;' 	=> 'Center No-Repeat Fixed',
		'no-repeat;center;fixed;cover' 	=> 'Center No-Repeat Fixed Cover',
	);
}


/**
 * Options Page | Fields & Args
 */
function mmi_opts_setup(){

	// Navigation elements
	$menu = array(

		// General --------------------------------------------
		'general' => array(
			'title' 	=> __('Getting started', 'mmi-opts'),
			/*'sections' 	=> array( 'layout-general', 'layout-header', 'social', 'custom-css', 'layout-footer','layout-product' ),*/
			'sections' 	=> array( 'layout-general' ),
		),

		// Layout --------------------------------------------
		//'elements' => array(
			//'title' 	=> __('Layout', 'mmi-opts'),
			//'sections' 	=> array( 'layout-general', 'layout-header', 'social', 'custom-css', 'layout-footer'),
		//),

	);

	$sections = array();

	// General ----------------------------------------------------------------------------------------

	$sections['layout-general'] = array(
		'title' => __('General', 'mmi-opts'),
		'fields' => array(
			array(
				'id'		=> 'site_id',
				'type'		=> 'blog_select',
				'title'		=> __('Select Site name', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'email-img',
				'type'		=> 'upload',
				'title'		=> __('Email Template Image', 'mmi-opts'),
			),

		),
	);

	// Header --------------------------------------------
	$sections['layout-header'] = array(
		'title' => __('Header', 'mmi-opts'),
		'fields' => array(

			array(
				'id'		=> 'logo-img',
				'type'		=> 'upload',
				'title'		=> __('Extra Large Logo', 'mmi-opts'),
			),

			array(
				'id'		=> 'large-img',
				'type'		=> 'upload',
				'title'		=> __('Large Logo', 'mmi-opts'),
			),

			array(
				'id'		=> 'medium-img',
				'type'		=> 'upload',
				'title'		=> __('Medium Logo', 'mmi-opts'),
			),

			array(
				'id'		=> 'small-img',
				'type'		=> 'upload',
				'title'		=> __('Small Logo', 'mmi-opts'),
			),

			array(
				'id'		=> 'arrow-img',
				'type'		=> 'upload',
				'title'		=> __('Arrow Logo', 'mmi-opts'),
			),

			array(
				'id'		=> 'service-arrow-img',
				'type'		=> 'upload',
				'title'		=> __('Service Arrow Symbol', 'mmi-opts'),
			),

			
			/*array(
				'id'		=> 'header-address',
				'type'		=> 'textarea',
				'title'		=> __('Header Address', 'mmi-opts'),
				'sub_desc'	=> __('Header Address', 'mmi-opts'),
				'class'		=> 'small-text',
			),*/

			array(
				'id'		=> 'header-phone',
				'type'		=> 'text',
				'title'		=> __('Phone', 'mmi-opts'),
				'sub_desc'	=> __('Phone number', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'link-text',
				'type'		=> 'text',
				'title'		=> __('Link Text', 'mmi-opts'),
				'sub_desc'	=> __('Action Bar Slogan', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'link-url',
				'type'		=> 'text',
				'title'		=> __('Link Url', 'mmi-opts'),
				'sub_desc'	=> __('Action Bar Slogan', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'header-email',
				'type'		=> 'text',
				'title'		=> __('Email', 'mmi-opts'),
				'sub_desc'	=> __('Email address', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'show-breadcrumbs',
				'type'		=> 'switch',
				'title'		=> __('Breadcrumbs', 'mmi-opts'),
				'options'	=> array('1' => 'On','0' => 'Off'),
				'std'		=> '1'
			),

			array(
				'id' 		=> 'img-subheader-bg',
				'type' 		=> 'upload',
				'title' 	=> __('Subheader Image', 'mmi-opts'),
			),

		),
	);
	

	// Single Product Side Panet -------------------------------
	$sections['layout-product'] = array(
		'title' => __('Products', 'mmi-opts'),
		'fields' => array(

			array(
				'id' 		=> 'icon1',
				'type' 		=> 'upload',
				'title' 	=> __('Icon1', 'mmi-opts'),
			),


			array(
				'id'		=> 'title1',
				'type'		=> 'text',
				'title'		=> __('Title', 'mmi-opts'),
				'sub_desc'	=> __('Title', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'content1',
				'type'		=> 'text',
				'title'		=> __('Content', 'mmi-opts'),
				'sub_desc'	=> __('Content', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id' 		=> 'icon2',
				'type' 		=> 'upload',
				'title' 	=> __('Icon2', 'mmi-opts'),
			),


			array(
				'id'		=> 'title2',
				'type'		=> 'text',
				'title'		=> __('Title', 'mmi-opts'),
				'sub_desc'	=> __('Title', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'content2',
				'type'		=> 'text',
				'title'		=> __('Content', 'mmi-opts'),
				'sub_desc'	=> __('Content', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id' 		=> 'icon3',
				'type' 		=> 'upload',
				'title' 	=> __('Icon3', 'mmi-opts'),
			),


			array(
				'id'		=> 'title3',
				'type'		=> 'text',
				'title'		=> __('Title', 'mmi-opts'),
				'sub_desc'	=> __('Title', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'content3',
				'type'		=> 'text',
				'title'		=> __('Content', 'mmi-opts'),
				'sub_desc'	=> __('Content', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id' 		=> 'icon4',
				'type' 		=> 'upload',
				'title' 	=> __('Icon4', 'mmi-opts'),
			),


			array(
				'id'		=> 'title4',
				'type'		=> 'text',
				'title'		=> __('Title', 'mmi-opts'),
				'sub_desc'	=> __('Title', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'content4',
				'type'		=> 'text',
				'title'		=> __('Content', 'mmi-opts'),
				'sub_desc'	=> __('Content', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id' 		=> 'icon5',
				'type' 		=> 'upload',
				'title' 	=> __('Icon5', 'mmi-opts'),
			),


			array(
				'id'		=> 'title5',
				'type'		=> 'text',
				'title'		=> __('Title', 'mmi-opts'),
				'sub_desc'	=> __('Title', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'content5',
				'type'		=> 'text',
				'title'		=> __('Content', 'mmi-opts'),
				'sub_desc'	=> __('Content', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

		),
	);

	// Social Icons --------------------------------------------
	$sections['social'] = array(
		'title' => __('Social Icons', 'mmi-opts'),
		'icon' => mmi_OPTIONS_URI. 'img/icons/sub.png',
		'fields' => array(

			array(
				'id' 		=> 'social-skype',
				'type' 		=> 'text',
				'title' 	=> __('Skype', 'mmi-opts'),
				'sub_desc' 	=> __('Type your Skype login here', 'mmi-opts'),
				'desc' 		=> __('You can use <strong>callto:</strong> or <strong>skype:</strong> prefix' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-facebook',
				'type' 		=> 'text',
				'title' 	=> __('Facebook', 'mmi-opts'),
				'sub_desc' 	=> __('Type your Facebook link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-googleplus',
				'type' 		=> 'text',
				'title' 	=> __('Google +', 'mmi-opts'),
				'sub_desc' 	=> __('Type your Google + link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-twitter',
				'type' 		=> 'text',
				'title' 	=> __('Twitter', 'mmi-opts'),
				'sub_desc' 	=> __('Type your Twitter link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-vimeo',
				'type' 		=> 'text',
				'title' 	=> __('Vimeo', 'mmi-opts'),
				'sub_desc' 	=> __('Type your Vimeo link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-youtube',
				'type' 		=> 'text',
				'title' 	=> __('YouTube', 'mmi-opts'),
				'sub_desc' 	=> __('Type your YouTube link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-flickr',
				'type' 		=> 'text',
				'title' 	=> __('Flickr', 'mmi-opts'),
				'sub_desc' 	=> __('Type your Flickr link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-linkedin',
				'type' 		=> 'text',
				'title' 	=> __('LinkedIn', 'mmi-opts'),
				'sub_desc' 	=> __('Type your LinkedIn link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-pinterest',
				'type'		=> 'text',
				'title' 	=> __('Pinterest', 'mmi-opts'),
				'sub_desc' 	=> __('Type your Pinterest link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-dribbble',
				'type' 		=> 'text',
				'title' 	=> __('Dribbble', 'mmi-opts'),
				'sub_desc' 	=> __('Type your Dribbble link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

			array(
				'id' 		=> 'social-rss',
				'type' 		=> 'text',
				'title' 	=> __('RSS', 'mmi-opts'),
				'sub_desc' 	=> __('Type your RSS link here', 'mmi-opts'),
				'desc' 		=> __('Icon won`t show if you leave this field blank' , 'mmi-opts'),
			),

		),
	);

	// Footer --------------------------------------------
	$sections['layout-footer'] = array(
		'title' => __('Footer', 'mmi-opts'),
		'fields' => array(

			/*array(
				'id'		=> 'footer-logo-img',
				'type'		=> 'upload',
				'title'		=> __('Footer Logo', 'mmi-opts'),
			),
			array(
				'id'		=> 'footer-logo-img1',
				'type'		=> 'upload',
				'title'		=> __('Footer Logo1', 'mmi-opts'),
			),
			array(
				'id'		=> 'footer-logo-img2',
				'type'		=> 'upload',
				'title'		=> __('Footer Logo2', 'mmi-opts'),
			),*/
			/*array(
				'id'		=> 'footer-header',
				'type'		=> 'text',
				'title'		=> __('Footer Header', 'mmi-opts'),
				'sub_desc'	=> __('Enter Footer Header here', 'mmi-opts'),
				'class'		=> 'small-text',
			),*/

			array(
				'id'		=> 'footer-phone',
				'type'		=> 'text',
				'title'		=> __('Phone', 'mmi-opts'),
				'sub_desc'	=> __('Phone number', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			/*array(
				'id'		=> 'footer-fax',
				'type'		=> 'text',
				'title'		=> __('Fax', 'mmi-opts'),
				'sub_desc'	=> __('Fax number', 'mmi-opts'),
				'class'		=> 'medium-text',
			),
*/
			array(
				'id'		=> 'footer-email',
				'type'		=> 'text',
				'title'		=> __('Email', 'mmi-opts'),
				'sub_desc'	=> __('Email address', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'footer-address',
				'type'		=> 'text',
				'title'		=> __('Address', 'mmi-opts'),
				'sub_desc'	=> __('Address', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'footer-demo-text',
				'type'		=> 'text',
				'title'		=> __('Demo Content', 'mmi-opts'),
				'sub_desc'	=> __('Demo Content', 'mmi-opts'),
				'class'		=> 'Large-text',
			),

			array(
				'id'		=> 'footer-demo-button-text',
				'type'		=> 'text',
				'title'		=> __('Demo Button Text', 'mmi-opts'),
				'sub_desc'	=> __('Demo Button Text', 'mmi-opts'),
				'class'		=> 'small-text',
			),

			array(
				'id'		=> 'footer-demo-link',
				'type'		=> 'text',
				'title'		=> __('Demo Link', 'mmi-opts'),
				'sub_desc'	=> __('Demo Link', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'footer-contact-button-text',
				'type'		=> 'text',
				'title'		=> __('Contact Button Text', 'mmi-opts'),
				'sub_desc'	=> __('For About & News Page', 'mmi-opts'),
				'class'		=> 'small-text',
			),

			array(
				'id'		=> 'footer-contact-link',
				'type'		=> 'text',
				'title'		=> __('Contact Link', 'mmi-opts'),
				'sub_desc'	=> __('For About & News Page', 'mmi-opts'),
				'class'		=> 'medium-text',
			),

			array(
				'id'		=> 'footer-linkedin-img',
				'type'		=> 'upload',
				'title'		=> __('Footer LinkedIn Img', 'mmi-opts'),
			),


			array(
                 'id'       => 'cpy-right-text',
                 'type'     => 'text',
                 'title'    => __('Copyright Text', 'mmi-opts'),                 
                 'class'    => 'medium-text',
             ),

		),
	);

	// Custom CSS --------------------------------------------
	$sections['custom-css'] = array(
		'title' => __('Custom CSS', 'mmi-opts'),
		'fields' => array(
			array(
				'id' 		=> 'custom-css',
				'type' 		=> 'textarea',
				'title' 	=> __('Custom CSS', 'mmi-opts'),
				'sub_desc' 	=> __('Paste your custom CSS code here.', 'mmi-opts'),
			),

		),
	);


	global $mmi_Options;
	$mmi_Options = new mmi_Options( $menu, $sections );
}
//add_action('init', 'mmi_opts_setup', 0);
mmi_opts_setup();


/**
 * This is used to return option value from the options array
 */
function mmi_opts_get( $opt_name, $default = null ){
	global $mmi_Options;
	return $mmi_Options->get( $opt_name, $default );
}


/**
 * This is used to echo option value from the options array
*/
function mmi_opts_show( $opt_name, $default = null ){
	global $mmi_Options;
	$option = $mmi_Options->get( $opt_name, $default );
	if( ! is_array( $option ) ){
		echo $option;
	}
}


/**
 * Add new mimes for custom font upload
 */
add_filter('upload_mimes', 'mmi_upload_mimes');
function mmi_upload_mimes( $existing_mimes=array() ){
	$existing_mimes['woff'] = 'font/woff';
	$existing_mimes['ttf'] 	= 'font/ttf';
	$existing_mimes['svg'] 	= 'font/svg';
	$existing_mimes['eot'] 	= 'font/eot';
	return $existing_mimes;
}
?>