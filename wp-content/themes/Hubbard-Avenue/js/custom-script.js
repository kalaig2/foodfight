(function ($) {
    $(function () {
        jQuery('#datetimepicker').datetimepicker();

        var data = {
             'action': 'mode_theme_update_mini_cart'
           };
           $.post(wc_cart_fragments_params.ajax_url,data, function(response){
               $('.widget_shopping_cart_content').html(response); // Repopulate the specific element with the new content
             }
           );
        //Dropdown cart in header
        $('.cart-holder > h3').click(function () {
            if ($(this).hasClass('cart-opened')) {
                $(this).removeClass('cart-opened').next().slideUp(300);
            } else {
                $(this).addClass('cart-opened').next().slideDown(300);
            }
        });
        //Popup rating content
        $('.star-rating').each(function () {
            rate_cont = $(this).attr('title');
            $(this).append('<b class="rate_content">' + rate_cont + '</b>');
        });
        //Fix contact form not valid messages errors
        jQuery(window).load(function () {
            jQuery('.wpcf7-not-valid-tip').live('mouseover', function () {
                jQuery(this).fadeOut();
            });

            jQuery('.wpcf7-form input[type="reset"]').live('click', function () {
                jQuery('.wpcf7-not-valid-tip, .wpcf7-response-output').fadeOut();
            });
        });
    });
    $(".gc-images ul li div img").click(function () {
        $(".gc-images ul li div").removeClass("selected");
        $(this).parent().addClass("selected");

        $(".product .images img").attr("src", $(this).attr("src"));
        $("#gc-image").val($(this).attr("src"));
    });

    $(".gc-prices input").click(function () {
        $("#custom-price").val("");
        $(".custom-price").hide();
        $(".gc-prices input").removeClass("priceSelected");
        $(this).addClass("priceSelected");
        $(".preview-amount").text($(this).val());
        var splittedSource = $(this).val().replace(/\s{2,}/g, ' ').split(' ');
        $("#gc-price").val(splittedSource[1]);
    });
    $(".custom-price").hide();
    $("#other").click(function(){
        $(".custom-price").show();
        $(".gc-prices input").removeClass("priceSelected");
        $("#gc-price").val("");
        $(".preview-amount").text("");
    });
    $("#custom-price").keyup(function () {
        $(".preview-amount").text($(this).val());
        $("#gc-price").val($(this).val());
    });
    $("#gc-msg").keyup(function () {
        $(".preview-msg").text($(this).val());
    });
    /*$( "#expand_shipping" ).click(function() {
        $( "#custom_billing_form, .woocommerce-shipping-fields" ).toggle( "slow", function() {
            // Animation complete.
        });
        //$( "#expand_shipping").toggleClass("dashicons-minus");
        $( "#expand_shipping").toggleClass("dashicons-plus dashicons-minus");

    });

    $( "#expand_order" ).click(function() {
        $( ".shop_table" ).toggle( "slow", function() {
            // Animation complete.
        });
        //$( "#expand_order").toggleClass("dashicons-minus");
        $( "#expand_order").toggleClass("dashicons-plus dashicons-minus");

    });

    $(document).on("click","#expand_payment",function() {
        $( ".payment_methods" ).toggle( "slow", function() {
            // Animation complete.
        });
        $( "#expand_payment").toggleClass("dashicons-plus dashicons-minus");

    });

    $(document).on("click","#place_order",function(){
        $("#expand_shipping").toggleClass("dashicons-plus dashicons-minus");
        $("#custom_billing_form,.woocommerce-shipping-fields").toggle();
        $("#expand_order").toggleClass("dashicons-plus dashicons-minus");
        $( ".woocommerce-checkout-review-order-table" ).toggle();

        $('html, body').animate({
            scrollTop: $("#customer_details").offset().top
        }, 2000);
        //return false;
    });*/
    //$(".gc-prices")
})(jQuery);

jQuery(document).ready(function () {
    //jQuery( "#shopnav li:nth-child(3)" ).hide();
    jQuery( "#shopnav li:nth-child(2) a[title='Log In']").attr("href",url);
    //jQuery( "#shopnav li:nth-child(2) a[title='Log In']").attr("href","http://202.129.197.46:3245/foodfight_latest/hubbard-avenue/my-account/");
    //jQuery(".quantity").prepend("<label>Quantity:</label>");
    jQuery("#gc-image-1 .gc-inner-image img").trigger('click');
});
