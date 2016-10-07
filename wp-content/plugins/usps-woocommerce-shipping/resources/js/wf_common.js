jQuery(document).ready(function($){
	$('.enable_flat_rate_boxes').change(function(){
		if( $(this).val()=='yes' ){
			$(".selected_flat_rate_boxes").closest("tr").show();
		}
		else
			$(".selected_flat_rate_boxes").closest("tr").hide();
	});
	
});
