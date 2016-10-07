<?php
class mmi_Options_tabs extends mmi_Options{	
	
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

		// enqueue js fix
		if( $meta ) $this->enqueue();
		
		$class = ( isset($this->field['class']) ) ? $this->field['class'] : '';
		$name = ( ! $meta ) ? ( $this->args['opt_name'].'['.$this->field['id'].']' ) : $this->field['id'];
		
		$count = ($this->value) ? count($this->value) : 0;
		echo '<a href="javascript:void(0);" class="btn-blue mmi-add-tab" rel-name="'. $name .'">Add tab</a>';
		echo '<input type="hidden" name="'. $name .'[count][]" class="mmi-tabs-count" value="'. $count .'" />';
		echo '<br style="clear:both;" />';
		
		echo '<ul class="tabs-ul">';
			
			if(isset($this->value) && is_array($this->value)){
				foreach($this->value as $k => $value){
					echo '<li>';
						echo '<label>'. __('Title','mmi-opts') .'</label>';
						echo '<input type="text" name="'. $name .'[title][]" value="'. htmlspecialchars(stripslashes($value['title'])) .'" />';
						echo '<label>'. __('Content','mmi-opts') .'</label>';
						echo '<textarea name="'. $name .'[content][]" value="" >'. $value['content'] .'</textarea>';
						echo '<a href="" class="mmi-btn-close mmi-remove-tab"><em>delete</em></a>';
					echo '</li>';
				}
			}
			
			// default tab to clone
			echo '<li class="tabs-default">';
				echo '<label>'. __('Title','mmi-opts') .'</label>';
				echo '<input type="text" name="" value="" />';
				echo '<label>'. __('Content','mmi-opts') .'</label>';
				echo '<textarea name="" value=""></textarea>';
				echo '<a href="" class="mmi-btn-close mmi-remove-tab"><em>delete</em></a>';
			echo '</li>';	
	
		echo '</ul>';

		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description tabs-desc">'.$this->field['desc'].'</span>':'';	
	}
	
	/**
	 * Enqueue Function.
	*/
	function enqueue(){
		wp_enqueue_script('mmi-opts-field-tabs-js', mmi_OPTIONS_URI.'fields/tabs/field_tabs.js', array('jquery'), time(), true);
	}
	
}
?>