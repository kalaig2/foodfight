<?php
class mmi_Options_radio_img extends mmi_Options{	
	
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
		
		$class = ( isset( $this->field['class'])) ? 'class="'.$this->field['class'].'" ' : '';
		$name = ( ! $meta ) ? ( $this->args['opt_name'].'['.$this->field['id'].']' ) : $this->field['id'];
		
		echo '<fieldset '.$class.'>';
			foreach($this->field['options'] as $k => $v){
				echo '<div class="mmi-radio-item">';
					$selected = (checked($this->value, $k, false) != '')?' mmi-radio-img-selected':'';
					echo '<label class="mmi-radio-img'.$selected.' mmi-radio-img-'.$this->field['id'].'" for="'.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'">';
						echo '<input type="radio" id="'.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'" name="'. $name . '" value="'.$k.'" '.checked($this->value, $k, false).'/>';
						echo '<img src="'.$v['img'].'" alt="'.$v['title'].'" onclick="jQuery:mmi_radio_img_select(\''.$this->field['id'].'_'.array_search($k,array_keys($this->field['options'])).'\', \''.$this->field['id'].'\');" />';
					echo '</label>';
					echo '<span class="description">'.$v['title'].'</span>';
				echo '</div>';
			}
			echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br style="clear:both;"/><span class="description">'.$this->field['desc'].'</span>':'';
		echo '</fieldset>';
		
	}
	
	/**
	 * Enqueue Function.
	*/
	function enqueue(){	
		wp_enqueue_script('mmi-opts-field-radio_img-js', mmi_OPTIONS_URI.'fields/radio_img/field_radio_img.js', array('jquery'),time(),true);	
	}
	
}
?>