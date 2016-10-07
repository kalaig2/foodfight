<?php
class mmi_Options_upload extends mmi_Options{

	/**
	 * Field Constructor.
	*/
	function __construct( $field = array(), $value ='', $parent = NULL ){
		if( is_object($parent) ) parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;		
	}

	
	/**
	 * Field Render Function.
	*/
	function render( $meta = false ){
		
		$class = ( isset($this->field['class']) ) ? $this->field['class'] : 'image';
		$name = ( ! $meta ) ? ( $this->args['opt_name'].'['.$this->field['id'].']' ) : $this->field['id'];
		
		echo '<input type="hidden" name="'. $name .'" value="'.$this->value.'" class="'.$class.'" />';
		if( $class == 'image' ) echo '<img class="mmi-opts-screenshot '.$class.'" src="'.$this->value.'" />';

		if($this->value == ''){
			$remove = ' style="display:none;"';$upload = '';}else{$remove = '';$upload = ' style="display:none;"';
		}
		
		echo ' <a href="javascript:void(0);" data-choose="Choose a File" data-update="Select File" class="mmi-opts-upload"'.$upload.' ><span></span>'.__('Browse', 'mmi-opts').'</a>';
		echo ' <a href="javascript:void(0);" class="mmi-opts-upload-remove"'.$remove.'>'.__('Remove Upload', 'mmi-opts').'</a>';
		echo ( isset($this->field['desc']) && ! empty($this->field['desc']) ) ? '<span class="description">'.$this->field['desc'].'</span>' : '';
	}

    /**
     * Enqueue Function.
    */
    function enqueue() {
        $wp_version = floatval( get_bloginfo( 'version' ) );
//         print_r($wp_version);

        if ( $wp_version < "3.5" ) {
            wp_enqueue_script(
                'mmi-opts-field-upload-js', 
                mmi_OPTIONS_URI . 'fields/upload/field_upload_3_4.js', 
                array('jquery', 'thickbox', 'media-upload'),
                time(),
                true
            );
            wp_enqueue_style('thickbox');
        } else {
            wp_enqueue_script(
                'mmi-opts-field-upload-js', 
                mmi_OPTIONS_URI . 'fields/upload/field_upload.js', 
                array('jquery'),
                time(),
                true
            );
            wp_enqueue_media();
        }
        wp_localize_script('mmi-opts-field-upload-js', 'mmi_upload', array('url' => $this->url.'fields/upload/blank.png'));
    }
}
