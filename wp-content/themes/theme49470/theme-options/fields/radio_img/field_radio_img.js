/*
 *
 * mmi_Options_radio_img function
 * Changes the radio select option, and changes class on images
 *
 */

function mmi_radio_img_select(relid, labelclass){
	jQuery(this).prev('input[type="radio"]').prop('checked');
	jQuery('.mmi-radio-img-'+labelclass).removeClass('mmi-radio-img-selected');	
	jQuery('label[for="'+relid+'"]').addClass('mmi-radio-img-selected');
}