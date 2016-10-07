(function($) {
    $(function(){
    	//Dropdown cart in header
		$('.cart-holder > h3').click(function(){
			if($(this).hasClass('cart-opened')) {
				$(this).removeClass('cart-opened').next().slideUp(300);
			} else {
				$(this).addClass('cart-opened').next().slideDown(300);
			}
		});
		//Popup rating content
		$('.star-rating').each(function(){
			rate_cont = $(this).attr('title');
			$(this).append('<b class="rate_content">' + rate_cont + '</b>');
		});
		//Fix contact form not valid messages errors
		jQuery(window).load(function() {
			jQuery('.wpcf7-not-valid-tip').live('mouseover', function(){
				jQuery(this).fadeOut();
			});

			jQuery('.wpcf7-form input[type="reset"]').live('click', function(){
				jQuery('.wpcf7-not-valid-tip, .wpcf7-response-output').fadeOut();
			});
		});
    });
    $(".gc-images ul li div img").click(function(){
    	$(".gc-images ul li div").removeClass("selected");
    	$(this).parent().addClass("selected");
    	
    	$(".product .images img").attr("src",$(this).attr("src"));
    	$("#gc-image").val($(this).attr("src"));
    });

    $(".gc-prices input").click(function(){
    	$(".gc-prices input").removeClass("priceSelected");
    	$(this).addClass("priceSelected");
    	$(".preview-amount").text($(this).val());
    	$("#gc-price").val($(this).val());
    });
    $("#gc-msg").keyup(function(){
    	$(".preview-msg").text($(this).val());
    });
    //$(".gc-prices")
})(jQuery);

jQuery(document).ready(function(){
	jQuery(".quantity").prepend("<label>Quantity:</label>");
	jQuery("#gc-image-1 .gc-inner-image img").trigger('click');
});