<?php
class mmi_Options_blog_select extends mmi_Options{	
	
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
		

		$class = ( isset( $this->field['class']) ) ? 'class="'.$this->field['class'].'" ' : '';
		$name = ( ! $meta ) ? ( $this->args['opt_name'].'['.$this->field['id'].']' ) : $this->field['id'];
		global $wpdb;
		$blog = $wpdb->prefix . 'blogs';
		$blog_id = $wpdb->get_results( 'SELECT blog_id FROM '.$blog);
		echo '<select name="'. $name .'" '.$class.'rows="6" >';			
			echo '<option value="">'. __('-- select --','mmi-opts') .'</option>';
			foreach ( $blog_id as $id ) {
				$blog_details = get_blog_details($id->blog_id);
				echo '<option value="'.$id->blog_id.'"'.selected($this->value, $id->blog_id, false).'>'.$blog_details->blogname.'</option>';
			}
		echo '</select>';
		/*$class = ( isset( $this->field['class']) ) ? 'class="'.$this->field['class'].'" ' : '';
		$name = ( ! $meta ) ? ( $this->args['opt_name'].'['.$this->field['id'].']' ) : $this->field['id'];
		
		$pages = get_pages('sort_column=post_title&hierarchical=0'); 
		echo '<select name="'. $name .'" '.$class.'rows="6" >';	
			echo '<option value="">'. __('-- select --','mmi-opts') .'</option>';
			foreach ( $pages as $page ) {
				echo '<option value="'.$page->ID.'"'.selected($this->value, $page->ID, false).'>'.$page->post_title.'</option>';
			}
		echo '</select>';
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';*/
		
	}
	
}
?>