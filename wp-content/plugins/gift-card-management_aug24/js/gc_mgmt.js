/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery(document).ready(function ($) {
    load_card_data();
    $("#loader_container").hide();
    $("#card_error").hide();
    $("#card_success").hide();
    $('#loader_container').bind('ajaxStart', function () {
        $(this).show();
    }).bind('ajaxStop', function () {
        $(this).hide();
    });
    function load_card_data() {
        var html = "";
        $.ajax({
            type: "POST",
            url: ajaxurl,
            dataType: "json",
            data: {action: 'list_cards'}
        }).done(function (data) {
            $.each(data, function (idx, obj) {
                html += "<tr><td>" + obj.card_number + "</td> <td>" + obj.price + "</td><td>" + obj.purchased_user + "</td></tr>";
            });
            $("#list_cards table").html("<tr><th>Card Numbers</th><th>Price</th><th>User</th></tr>" + html);
        });
    }
    $("#add_card").click(function () {
        $('#loader_container').sh
        var gc_number = $("#gc_number").val();
        if (gc_number != "") {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {action: 'add_card', gc_number: gc_number}
            }).done(function (msg) {
                if (msg == "error") {
                    $("#card_error").show();
                    $("#card_success").hide();
                } else {
                    $("#card_success").show();
                    $("#card_error").hide();
                    load_card_data();
                }
            });
            $("#gc_number").val("");
        } else {
            $("#card_success").hide();
            $("#card_error").show();
        }
    });
})
