jQuery(document).ready(function($){
   $("#check_balance").on("click",function(){
       $("#check_balance").text("Checking...");
       var givex_number = $("#givex_number").val() ;
       if(givex_number==""){
            alert("Givex Number is empty");
           $("#check_balance").text("Check");
           return false;
       }

       var data = {
           action: 'givex_check_balance',
           givex_number:givex_number
       };

       $.post(givex_ajax.ajaxurl, data, function(response) {
           $("#givex_number").val("");
           alert(response);
           $("#check_balance").text("Check");
       });
   });

   $("#get_history").on("click",function(){
       $("#get_history").text("Checking...");
       var givex_number = $("#givex_number").val() ;
       if(givex_number==""){
            alert("Givex Number is empty");
           $("#get_history").text("Get History");
           return false;
       }

       var data = {
           action: 'givex_get_history',
           givex_number:givex_number
       };

       $.post(givex_ajax.ajaxurl, data, function(response) {
           $("#givex_number").val("");
           alert(response);
           $("#get_history").text("Get History");
       });
   });
    $("#register_card").on("click",function(){
           $("#register_card").text("Checking...");
           var givex_amount = $("#givex_amount").val();
           if(givex_amount==""){
               alert("Please enter a amount");
               $("#register_card").text("Register");
               return false;
           }

           var data = {
               action: 'givex_register_card',
               givex_amount:givex_amount
           };

           $.post(givex_ajax.ajaxurl, data, function(response) {
               $("#register_card").val("");
               alert(response);
               $("#register_card").text("Register");
           });
       });



    $("#tranfer_balance").on("click",function(){
       $("#tranfer_balance").text("Checking...");
       var givex_from_number = $("#givex_from_number").val();
       var givex_to_number = $("#givex_to_number").val();
       var givex_amount = $("#givex_amount").val();
       if(givex_from_number =="" || givex_to_number =="" || givex_amount ==""){
           alert("Enter all fields");
           $("#tranfer_balance").text("Transfer");
           return false;
       }
        if(givex_from_number =="" && givex_to_number =="" && givex_amount ==""){
           alert("Enter all fields");
           $("#tranfer_balance").text("Transfer");
           return false;
       }

       var data = {
           action: 'givex_transfer_balance',
           givex_from_number:givex_from_number,
           givex_to_number:givex_to_number,
           givex_amount:givex_amount
       };

       $.post(givex_ajax.ajaxurl, data, function(response) {
           $("#givex_number").val("");
           alert(response);
           $("#tranfer_balance").text("Transfer");
       });
   });

    $("#redeem_card").on("click",function(){
       $("#redeem_card").text("Checking...");
       var givex_from_number = $("#givex_from_number").val();
       var givex_amount = $("#givex_amount").val();
       if(givex_from_number =="" || givex_amount ==""){
           alert("Enter all fields");
           $("#redeem_card").text("Redeem");
           return false;
       }
        if(givex_from_number =="" && givex_amount ==""){
           alert("Enter all fields");
           $("#redeem_card").text("Redeem");
           return false;
       }

       var data = {
           action: 'givex_redeem_card',
           givex_from_number:givex_from_number,
           givex_amount:givex_amount
       };

       $.post(givex_ajax.ajaxurl, data, function(response) {
           $("#givex_number").val("");
           alert(response);
           $("#redeem_card").text("Redeem");
       });
   });
    $("#activate_card").on("click",function(){
        $("#activate_card").text("Checking...");
        var givex_from_number = $("#givex_from_number").val();
        var givex_amount = $("#givex_amount").val();
        if(givex_from_number =="" || givex_amount ==""){
            alert("Enter all fields");
            $("#activate_card").text("Activate Card");
            return false;
        }
        if(givex_from_number =="" && givex_amount ==""){
            alert("Enter all fields");
            $("#activate_card").text("Activate Card");
            return false;
        }

        var data = {
            action: 'givex_activate_card',
            givex_from_number:givex_from_number,
            givex_amount:givex_amount
        };

        $.post(givex_ajax.ajaxurl, data, function(response) {
            $("#givex_number").val("");
            alert(response);
            $("#activate_card").text("Activate Card");
        });
    });

    $("#increment_balance").on("click",function(){
        $("#increment_balance").text("Checking...");
        var givex_from_number = $("#givex_from_number").val();
        var givex_amount = $("#givex_amount").val();
        if(givex_from_number =="" || givex_amount ==""){
            alert("Enter all fields");
            $("#increment_balance").text("Add Money");
            return false;
        }
        if(givex_from_number =="" && givex_amount ==""){
            alert("Enter all fields");
            $("#increment_balance").text("Add Money");
            return false;
        }

        var data = {
            action: 'givex_increment_balance',
            givex_from_number:givex_from_number,
            givex_amount:givex_amount
        };

        $.post(givex_ajax.ajaxurl, data, function(response) {
            $("#givex_number").val("");
            alert(response);
            $("#increment_balance").text("Add Money");
        });
    });

    $("#get_details").on("click",function(){
        $("#register_card").text("Checking...");
        var givex_number = $("#givex_number").val();
        if(givex_number==""){
            alert("Please enter card number");
            $("#register_card").text("Account Details");
            return false;
        }

        var data = {
            action: 'givex_account_details',
            givex_from_number:givex_number
        };

        $.post(givex_ajax.ajaxurl, data, function(response) {
            $("#register_card").val("");
            alert(response);
            $("#register_card").text("Account Details");
        });
    });

});
