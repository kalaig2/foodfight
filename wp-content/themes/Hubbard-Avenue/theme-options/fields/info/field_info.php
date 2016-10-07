<?php
class mmi_Options_info extends mmi_Options{	
	
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
		if( key_exists('desc', $this->field) ){
			echo '<p class="description" style="margin-left:-220px;">'.$this->field['desc'].'</p>';
		}
	}
	
}
?>