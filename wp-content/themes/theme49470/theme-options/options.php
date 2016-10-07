<?php
if ( ! class_exists('mmi_Options') ){

	if(!defined('mmi_OPTIONS_DIR')){
		define('mmi_OPTIONS_DIR', trailingslashit(dirname(__FILE__)));
	}

	if(!defined('mmi_OPTIONS_URI'))
	{
//		define('mmi_OPTIONS_URI', site_url(str_replace(ABSPATH, '', trailingslashit(dirname(__FILE__)))));
		define('mmi_OPTIONS_URI', THEME_URI ."/theme-options/");
	}


	class mmi_Options{

		public $dir 		= mmi_OPTIONS_DIR;
		public $url 		= mmi_OPTIONS_URI;
		public $page 		= '';
		public $args 		= array();
		public $sections 	= array();
		public $extra_tabs 	= array();
		public $errors 		= array();
		public $warnings 	= array();
		public $options 	= array();

		public $menu 		= array();


		/**
		 * Class Constructor. Defines the args for the theme options class
		*/
		function __construct( $menu = array(), $sections = array() ){

			$this->menu = apply_filters('mmi-opts-menu', $menu);

			$defaults = array();

			$defaults['opt_name'] 		= 'MM';

			$defaults['menu_icon'] 		= mmi_OPTIONS_URI.'/img/menu_icon.png';
			$defaults['menu_title'] 	= __('Theme Options', 'mmi-opts');
			$defaults['page_icon'] 		= 'icon-themes';
			$defaults['page_title'] 	= __('Theme Options', 'mmi-opts');
			$defaults['page_slug'] 		= 'theme_options';
			$defaults['page_cap'] 		= 'manage_options';
			$defaults['page_type'] 		= 'menu';
			$defaults['page_parent'] 	= '';
			$defaults['page_position'] 	= 100;

			//get args
			$this->args = $defaults;
			$this->args = apply_filters('mmi-opts-args', $this->args);
			$this->args = apply_filters('mmi-opts-args-'.$this->args['opt_name'], $this->args);

			//get sections
			$this->sections = apply_filters('mmi-opts-sections', $sections);
			$this->sections = apply_filters('mmi-opts-sections-'.$this->args['opt_name'], $this->sections);

			//set option with defaults
			add_action('init', array(&$this, '_set_default_options'));

			//options page
			add_action('admin_menu', array(&$this, '_options_page'));

			//register setting
			add_action('admin_init', array(&$this, '_register_setting'));

			//add the js for the error handling before the form
			add_action('mmi-opts-page-before-form', array(&$this, '_errors_js'), 1);

			//add the js for the warning handling before the form
			add_action('mmi-opts-page-before-form', array(&$this, '_warnings_js'), 2);

			//hook into the wp feeds for downloading the exported settings
			add_action('do_feed_mmi-opts-'.$this->args['opt_name'], array(&$this, '_download_options'), 1, 1);

			//get the options for use later on
			$this->options = get_option($this->args['opt_name']);

		}


		/**
		 * This is used to return and option value from the options array
		*/
		function get($opt_name, $default = null){

			if( ( ! is_array($this->options) ) || ( ! key_exists($opt_name, $this->options) ) ) return $default;

			return ( ( ! empty($this->options[$opt_name])) || ($this->options[$opt_name]==='0') ) ? $this->options[$opt_name] : $default;
//			return (!empty($this->options[$opt_name])) ? $this->options[$opt_name] : $default;

		}


		/**
		 * This is used to set an arbitrary option in the options array
		 */
		function set($opt_name, $value) {
			$this->options[$opt_name] = $value;
			update_option($this->args['opt_name'], $this->options);
		}


		/**
		 * This is used to echo and option value from the options array
		*/
		function show($opt_name, $default = null){
			$option = $this->get($opt_name, $default);
			if(!is_array($option)){
				echo $option;
			}
		}


		/**
		 * Get default options into an array suitable for the settings API
		*/
		function _default_values(){
			$defaults = array();

			foreach($this->sections as $k => $section){

				if(isset($section['fields'])){

					foreach($section['fields'] as $fieldk => $field){
						if(!isset($field['std'])){
							$field['std'] = '';
						}
						$defaults[$field['id']] = $field['std'];
					}

				}

			}

			$defaults['last_tab'] = 0;
			return $defaults;
		}


		/**
		 * Set default options on admin_init if option doesnt exist (theme activation hook caused problems, so admin_init it is)
		*/
		function _set_default_options(){
			if(!get_option($this->args['opt_name'])){
				add_option($this->args['opt_name'], $this->_default_values());
			}
			$this->options = get_option($this->args['opt_name']);
		}


		/**
		 * Class Theme Options Page Function, creates main options page.
		*/
		function _options_page(){

			$this->page = add_theme_page(
				$this->args['page_title'],
				$this->args['menu_title'],
				$this->args['page_cap'],
				$this->args['page_slug'],
				array(&$this, '_options_page_html')
			);

			add_action('admin_print_styles-'.$this->page, array(&$this, '_enqueue'));
		}


		/**
		 * enqueue styles/js for theme page
		*/
		function _enqueue(){

			wp_register_style( 'mmi-opts-css', $this->url.'css/options.css', array('farbtastic'), time(), 'all');
			wp_enqueue_style( 'mmi-opts-css' );

			wp_enqueue_style( 'mmi-opts-font', 'http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,600' );

			wp_enqueue_script( 'mmi-opts-js', $this->url.'js/options.js', array('jquery'), time(), true );

			do_action('mmi-opts-enqueue');
			do_action('mmi-opts-enqueue-'.$this->args['opt_name']);

			foreach($this->sections as $k => $section){

				if(isset($section['fields'])){

					foreach($section['fields'] as $fieldk => $field){

						if(isset($field['type'])){

							$field_class = 'mmi_Options_'.$field['type'];

							if(!class_exists($field_class)){
								require_once($this->dir.'fields/'.$field['type'].'/field_'.$field['type'].'.php');
							}

							if(class_exists($field_class) && method_exists($field_class, 'enqueue')){
								$enqueue = new $field_class('','',$this);
								$enqueue->enqueue();
							}

						}

					}

				}

			}

		}


		/**
		 * Download the options file, or display it
		*/
		function _download_options(){

			if( ! isset( $_GET['secret'] ) || $_GET['secret'] != md5(AUTH_KEY.SECURE_AUTH_KEY)){
				wp_die('Invalid Secret for options use');
				exit;
			}
			if( ! isset( $_GET['feed']) ){
				wp_die('No Feed Defined');
				exit;
			}

			$backup_options = get_option(str_replace('mmi-opts-','',$_GET['feed']));
			$backup_options['mmi-opts-backup'] = '1';
			$content = '###'.serialize($backup_options).'###';

			if( isset( $_GET['action'] ) && $_GET['action'] == 'download_options' ){
				header('Content-Description: File Transfer');
				header('Content-type: application/txt');
				header('Content-Disposition: attachment; filename="'.str_replace('mmi-opts-','',$_GET['feed']).'_options_'.date('d-m-Y').'.txt"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				echo $content;
				exit;
			} else {
				echo $content;
				exit;
			}
		}


		/**
		 * Register Option for use
		*/
		function _register_setting(){

			register_setting($this->args['opt_name'].'_group', $this->args['opt_name'], array(&$this,'_validate_options'));

			foreach($this->sections as $k => $section){

				add_settings_section($k.'_section', $section['title'], array(&$this, '_section_desc'), $k.'_section_group');

				if(isset($section['fields'])){

					foreach($section['fields'] as $fieldk => $field){

						if(isset($field['title'])){
							$th = (isset($field['sub_desc']))?$field['title'].'<span class="description">'.$field['sub_desc'].'</span>':$field['title'];
						}else{
							$th = '';
						}

						add_settings_field($fieldk.'_field', $th, array(&$this,'_field_input'), $k.'_section_group', $k.'_section', $field); // checkbox

					}

				}

			}

			do_action('mmi-opts-register-settings');
			do_action('mmi-opts-register-settings-'.$this->args['opt_name']);

		}


		/**
		 * Validate the Options options before insertion
		*/
		function _validate_options($plugin_options){

			set_transient('mmi-opts-saved', '1', 1000 );

			// import
			if( ! empty( $plugin_options['import'] ) ){

				if($plugin_options['import_code'] != ''){
					$import = $plugin_options['import_code'];
				}elseif($plugin_options['import_link'] != ''){
					$import = wp_remote_retrieve_body( wp_remote_get($plugin_options['import_link']) );
				}

				$imported_options = unserialize( trim( $import,'###' ) );
				if( is_array($imported_options) && isset($imported_options['mmi-opts-backup']) && $imported_options['mmi-opts-backup'] == '1' ){
					$imported_options['imported'] = 1;

					return $imported_options;
				}
			}

			// defaults
			if( ! empty( $plugin_options['defaults'] ) ){
				$plugin_options = $this->_default_values();
				return $plugin_options;
			}

			// validate fields (if needed)
			$plugin_options = $this->_validate_values($plugin_options, $this->options);

			if($this->errors){
				set_transient('mmi-opts-errors', $this->errors, 1000 );
			}

			if($this->warnings){
				set_transient('mmi-opts-warnings', $this->warnings, 1000 );
			}

			do_action('mmi-opts-options-validate', $plugin_options, $this->options);

			do_action('mmi-opts-options-validate-'.$this->args['opt_name'], $plugin_options, $this->options);

			unset($plugin_options['defaults']);
			unset($plugin_options['import']);
			unset($plugin_options['import_code']);
			unset($plugin_options['import_link']);

			return $plugin_options;

		}


		/**
		 * Validate values from options form (used in settings api validate function)
		 * calls the custom validation class for the field so authors can override with custom classes
		*/
		function _validate_values($plugin_options, $options){
			foreach($this->sections as $k => $section){

				if(isset($section['fields'])){

					foreach($section['fields'] as $fieldk => $field){
						$field['section_id'] = $k;

						if(isset($field['type']) && $field['type'] == 'multi_text'){continue;}//we cant validate this yet

						if(!isset($plugin_options[$field['id']]) || $plugin_options[$field['id']] == ''){
							continue;
						}

						//force validate of custom filed types

						if(isset($field['type']) && !isset($field['validate'])){
							if($field['type'] == 'color' || $field['type'] == 'color_gradient'){
								$field['validate'] = 'color';
							}elseif($field['type'] == 'date'){
								$field['validate'] = 'date';
							}
						}//if

						if(isset($field['validate'])){
							$validate = 'mmi_Validation_'.$field['validate'];

							if(!class_exists($validate)){
								require_once($this->dir.'validation/'.$field['validate'].'/validation_'.$field['validate'].'.php');
							}//if

							if(class_exists($validate)){
								$validation = new $validate($field, $plugin_options[$field['id']], $options[$field['id']]);
								$plugin_options[$field['id']] = $validation->value;
								if(isset($validation->error)){
									$this->errors[] = $validation->error;
								}
								if(isset($validation->warning)){
									$this->warnings[] = $validation->warning;
								}
								continue;
							}
						}

						if(isset($field['validate_callback']) && function_exists($field['validate_callback'])){

							$callbackvalues = call_user_func($field['validate_callback'], $field, $plugin_options[$field['id']], $options[$field['id']]);
							$plugin_options[$field['id']] = $callbackvalues['value'];

							if(isset($callbackvalues['error'])){
								$this->errors[] = $callbackvalues['error'];
							}

							if(isset($callbackvalues['warning'])){
								$this->warnings[] = $callbackvalues['warning'];
							}

						}


					}

				}

			}
			return $plugin_options;
		}


		/**
		 * HTML OUTPUT.
		*/
		function _options_page_html(){

			echo '<div id="mmi-wrapper">';
				echo '<form method="post" action="options.php" enctype="multipart/form-data" id="mmi-form-wrapper">';
					settings_fields($this->args['opt_name'].'_group');
					echo '<input type="hidden" id="last_tab" name="'.$this->args['opt_name'].'[last_tab]" value="'.$this->options['last_tab'].'" />';

					echo '<div id="mmi-aside">';
						echo '<div class="mmi-logo">Theme Options</div>';

						// menu items -----------------------------------------------
						echo '<ul class="mmi-menu">';
							foreach($this->menu as $k => $menu_item)
							{
								echo '<li class="mmi-menu-li mmi-menu-li-'. $k .'">';
									echo '<a href="javascript:void(0);" class="mmi-menu-a"><span class="icon"></span>'. $menu_item['title']. '</a>';

									if( is_array( $menu_item['sections'] ) )
									{
										echo '<ul class="mmi-submenu">';
										foreach( $menu_item['sections'] as $sub_item ){
											echo '<li id="'.$sub_item.'-mmi-submenu-li" class="mmi-submenu-li">';
												echo '<a href="javascript:void(0);" class="mmi-submenu-a" data-rel="'.$sub_item.'"><span>'. $this->sections[$sub_item]['title'] .'</span></a>';
											echo '</li>';
										}
										echo '</ul>';
									}

								echo '</li>';
							}

							//echo '<li class="mmi-menu-li mmi-menu-li-import">';
//								echo '<a href="javascript:void(0);" class="mmi-menu-a"><span class="icon"></span>'. __('Import & Export', 'mmi-opts'). '</a>';
//								echo '<ul class="mmi-submenu">';
//									echo '<li id="import-mmi-submenu-li" class="mmi-submenu-li">';
//										echo '<a href="javascript:void(0);" class="mmi-submenu-a" data-rel="import"><span>'. __('Import & Export', 'mmi-opts'). '</span></a>';
//									echo '</li>';
//								echo '</ul>';
//							echo '</li>';

						echo '</ul>';
						// end: menu items -------------------------------------------

					echo '</div>';

					echo '<div id="mmi-main">';
						echo '<div class="mmi-header">';
							echo '<input type="submit" name="submit" value="'.__('Save Changes', 'mmi-opts').'" class="mmi-popup-save" />';
						echo '</div>';

						// sections -------------------------------------------------
						echo '<div class="mmi-sections">';
							foreach($this->sections as $k => $section){
								echo '<div id="'.$k.'-mmi-section'.'" class="mmi-section">';
									do_settings_sections($k.'_section_group');
									echo '<div class="mmi-sections-footer">';
										echo '<input type="submit" name="'. $this->args['opt_name'] . '[defaults]" value="'.__('Reset to Defaults', 'mmi-opts').'" class="mmi-popup-reset" />';
										echo '<input type="submit" name="submit" value="'.__('Save Changes', 'mmi-opts').'" class="mmi-popup-save" />';
									echo '</div>';
								echo '</div>';
							}

							// import -------------------------------------------------
							//echo '<div id="import-mmi-section" class="mmi-section">';
//								echo '<h3>'. __('Import & Export', 'mmi-opts'). '</h3>';
//
//								echo '<div class="mmi-import-wrapper">';
//
//									// imp -------------------------------------------------
//									echo '<div class="mmi-import-box mmi-import-imp">';
//										echo '<h4>'.__('Import Options', 'mmi-opts').'</h4>';
//
//										echo '<p>';
//											echo '<a href="javascript:void(0);" class="mmi-btn mmi-import-imp-code-btn">'.__('Import from file', 'mmi-opts').'</a>&nbsp;';
//											echo '<a href="javascript:void(0);" class="mmi-btn mmi-import-imp-link-btn">'.__('Import from URL', 'mmi-opts').'</a>';
//										echo '</p>';
//
//										echo '<div class="mmi-import-imp-code-wrapper">';
//											echo '<p class="description">'.__('Input your backup file below and hit Import to restore your sites options from a backup.', 'mmi-opts').'</p>';
//											echo '<textarea name="'.$this->args['opt_name'].'[import_code]" class="large-text" rows="8"></textarea>';
//										echo '</div>';
//
//										echo '<div class="mmi-import-imp-link-wrapper">';
//											echo '<p class="description">'.__('Input the URL to another sites options set and hit Import to load the options from that site.', 'mmi-opts').'</p>';
//											echo '<input type="text" name="'.$this->args['opt_name'].'[import_link]" class="large-text" value="" />';
//										echo '</div>';
//
//										echo '<p class="mmi-import-imp-action">';
//											echo '<input type="submit" id="mmi-opts-import" name="'.$this->args['opt_name'].'[import]" class="mmi-btn mmi-btn-primary" value="'.__('Import', 'mmi-opts').'">';
//											echo '<span>'.__('WARNING! This will overwrite any existing options, please proceed with caution!', 'mmi-opts').'</span>';
//										echo '</p>';
//
//									echo '</div>';
//
//									// exp -------------------------------------------------
//									echo '<div class="mmi-import-box mmi-import-exp">';
//										echo '<h4>'.__('Export Options', 'nhp-opts').'</h4>';
//										echo '<p class="description">'. __('Here you can copy/download your themes current option settings. Keep this safe as you can use it as a backup should anything go wrong. Or you can use it to restore your settings on this site (or any other site). You also have the handy option to copy the link to yours sites settings. Which you can then use to duplicate on another site.', 'nhp-opts') .'</p>';
//
//										echo '<p>';
//											echo '<a href="javascript:void(0);" class="mmi-btn mmi-import-exp-code-btn">'.__('Copy', 'mmi-opts').'</a>&nbsp;';
//											echo '<a href="'.add_query_arg(array('feed' => 'mmi-opts-'.$this->args['opt_name'], 'action' => 'download_options', 'secret' => md5(AUTH_KEY.SECURE_AUTH_KEY)), site_url()).'" class="mmi-btn mmi-btn-primary mmi-import-exp-download-btn">'.__('Download', 'mmi-opts').'</a>&nbsp;';
//											echo '<a href="javascript:void(0);" class="mmi-btn mmi-import-exp-link-btn">'.__('Copy Link', 'mmi-opts').'</a>';
//										echo '</p>';
//
//										$backup_options = $this->options;
//										$backup_options['mmi-opts-backup'] = '1';
//										$encoded_options = '###'.serialize($backup_options).'###';
//
//										echo '<textarea class="large-text mmi-import-exp-code" rows="8">';
//											print_r($encoded_options);
//										echo '</textarea>';
//										echo '<input type="text" class="large-text mmi-import-exp-link" value="'.add_query_arg(array('feed' => 'mmi-opts-'.$this->args['opt_name'], 'secret' => md5(AUTH_KEY.SECURE_AUTH_KEY)), site_url()).'" />';
//
//									echo '</div>';
//
//								echo '</div>';
//
//								echo '<div class="mmi-sections-footer">';
//									echo '<input type="submit" name="'. $this->args['opt_name'] . '[defaults]" value="'.__('Reset to Defaults', 'mmi-opts').'" class="mmi-popup-reset" />';
//									echo '<input type="submit" name="submit" value="'.__('Save Changes', 'mmi-opts').'" class="mmi-popup-save" />';
//								echo '</div>';
//							echo '</div>';
//
//						echo '</div>';
						// end: sections --------------------------------------------

					echo '</div>';

					echo '<div class="clear">&nbsp;</div>';
				echo '</form>';
			echo '</div>';
		}

		/**
		 * JS to display the errors on the page
		*/
		function _errors_js(){

			if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && get_transient('mmi-opts-errors')){
				$errors = get_transient('mmi-opts-errors');
				$section_errors = array();
				foreach($errors as $error){
					$section_errors[$error['section_id']] = (isset($section_errors[$error['section_id']]))?$section_errors[$error['section_id']]:0;
					$section_errors[$error['section_id']]++;
				}

				echo '<script type="text/javascript">';
					echo 'jQuery(document).ready(function(){';
						echo 'jQuery("#mmi-opts-field-errors span").html("'.count($errors).'");';
						echo 'jQuery("#mmi-opts-field-errors").show();';

						foreach($section_errors as $sectionkey => $section_error){
							echo 'jQuery("#'.$sectionkey.'_section_group_li_a").append("<span class=\"mmi-opts-menu-error\">'.$section_error.'</span>");';
						}

						foreach($errors as $error){
							echo 'jQuery("#'.$error['id'].'").addClass("mmi-opts-field-error");';
							echo 'jQuery("#'.$error['id'].'").closest("td").append("<span class=\"mmi-opts-th-error\">'.$error['msg'].'</span>");';
						}
					echo '});';
				echo '</script>';
				delete_transient('mmi-opts-errors');
			}
		}


		/**
		 * JS to display the warnings on the page
		*/
		function _warnings_js(){

			if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && get_transient('mmi-opts-warnings')){
					$warnings = get_transient('mmi-opts-warnings');
					$section_warnings = array();
					foreach($warnings as $warning){
						$section_warnings[$warning['section_id']] = (isset($section_warnings[$warning['section_id']]))?$section_warnings[$warning['section_id']]:0;
						$section_warnings[$warning['section_id']]++;
					}


					echo '<script type="text/javascript">';
						echo 'jQuery(document).ready(function(){';
							echo 'jQuery("#mmi-opts-field-warnings span").html("'.count($warnings).'");';
							echo 'jQuery("#mmi-opts-field-warnings").show();';

							foreach($section_warnings as $sectionkey => $section_warning){
								echo 'jQuery("#'.$sectionkey.'_section_group_li_a").append("<span class=\"mmi-opts-menu-warning\">'.$section_warning.'</span>");';
							}

							foreach($warnings as $warning){
								echo 'jQuery("#'.$warning['id'].'").addClass("mmi-opts-field-warning");';
								echo 'jQuery("#'.$warning['id'].'").closest("td").append("<span class=\"mmi-opts-th-warning\">'.$warning['msg'].'</span>");';
							}
						echo '});';
					echo '</script>';
					delete_transient('mmi-opts-warnings');
				}

		}


		/**
		 * Section HTML OUTPUT.
		*/
		function _section_desc($section){

			$id = str_replace('_section', '', $section['id']);

			if(isset($this->sections[$id]['desc']) && !empty($this->sections[$id]['desc'])) {
				echo '<div class="mmi-opts-section-desc">'.$this->sections[$id]['desc'].'</div>';
			}

		}


		/**
		 * Field HTML OUTPUT.
		*/
		function _field_input($field){

			if(isset($field['callback']) && function_exists($field['callback'])){
				$value = (isset($this->options[$field['id']]))?$this->options[$field['id']]:'';
				do_action('mmi-opts-before-field', $field, $value);
				do_action('mmi-opts-before-field-'.$this->args['opt_name'], $field, $value);
				call_user_func($field['callback'], $field, $value);
				do_action('mmi-opts-after-field', $field, $value);
				do_action('mmi-opts-after-field-'.$this->args['opt_name'], $field, $value);
				return;
			}

			if(isset($field['type'])){

				$field_class = 'mmi_Options_'.$field['type'];

				if(class_exists($field_class)){
					require_once($this->dir.'fields/'.$field['type'].'/field_'.$field['type'].'.php');
				}

				if(class_exists($field_class)){
					$value = (isset($this->options[$field['id']]))?$this->options[$field['id']]:'';
					do_action('mmi-opts-before-field', $field, $value);
					do_action('mmi-opts-before-field-'.$this->args['opt_name'], $field, $value);
					$render = '';
					$render = new $field_class($field, $value, $this);
					$render->render();
					do_action('mmi-opts-after-field', $field, $value);
					do_action('mmi-opts-after-field-'.$this->args['opt_name'], $field, $value);
				}

			}

		}

	}//class
}//if
?>