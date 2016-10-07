/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery(document).ready(function ($) {
    load_card_data();
    /*$("#loader_container").hide();*/
    $("#card_error").hide();
    $("#card_success").hide();
    $('#loader_container').hide();
    /*$('#loader_container').bind('ajaxStart', function () {
        $(this).show();
    }).bind('ajaxStop', function () {
        $(this).hide();
    });*/
    function load_card_data() {
        //$('#loader_container').show();
        var html = "",status;
        var sno = 1;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            dataType: "json",
            data: {action: 'list_cards'}
        }).done(function (data) {
            //alert(data)
            if( data == '' ) {
                html += "<tr><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>";
            } else {
                //$('#loader_container').hide();
                $.each(data, function (idx, obj) {
                    if(obj.active == 0) {
                        status = "Purchased";
                    } else {
                        status = "Available";
                    }
                    var givex_status= "";
                    if(obj.givex_status == 1) {
                        givex_status = "Activated";
                    } else {
                        givex_status = "Not Activated";
                    }

                    html += "<tr>\
                                <input type='hidden' class='id' value='"+obj.id+"'>\
                                <td class='card-num'>" + obj.card_number + "</td>\
                                <td class='price'>" + obj.price + "</td>\
                                <td class='card_type'>" + obj.card_type + "</td>\
                                <td class='status'>" + status + "</td>\
                                <td class='givex_status'>" + givex_status + "</td>\
                                <td colspan='2'>\
                                    <button type='button' class='edit_gc btn btn-info btn-md' data-toggle='modal' \
                                    data-target='#myModal' value='" + obj.card_number + "'>Edit</button>\
                                    <button class='btn btn-info btn-md' id='delete_gc' value='" + obj.card_number + "'>\
                                    Delete</button>\
                                </td>\
                            </tr>";
                    sno += 1;
                });
            }
            $("#list_cards table").find("tr:gt(0)").remove();
            $("#list_cards table tr").eq(0).after(html);
        });
    }

    //Ajax function for adding card
    $("#add_card").click(function () {
        $('#loader_container').show();
        var gc_number = $("#gc_number").val();
        var no_of_card = $("#no_of_card").val();
        var card_type = $("#card_type").val();
        if (gc_number != "" && no_of_card != "") {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {action: 'add_card', gc_number: gc_number,no_of_card:no_of_card, card_type: card_type}
            }).done(function (msg) {
                $("#card_success").text(msg);
                load_card_data();
            });
            $("#gc_number").val("");
            $("#no_of_card").val("");
            $("#card_error").hide();
            $("#card_success").show();
            $('#loader_container').hide();
            $("#card_success").text("Card has successfully added.");
        } else {
            $("#card_success").hide();
            $("#card_error").show();
            $('#loader_container').hide();
            $("#card_error").text("Please enter the mandatory fields");
            return false;
        }
    });

    //Get an values for editing
    $(document).on('click',".edit_gc", function(){
        //$('#loader_container').show();
        var modal_html = "";
        var card_number = $(this).closest('tr').find('.card-num').text();
        var price = $(this).closest('tr').find('.price').text();
        var selected_card_type = $(this).closest('tr').find('.card_type').text();
        var card_type_html = $("#card_type").html();
        var options = $('#card_type option');
        var values = $.map(options ,function(option) {
            return option.value;
        });
        var id = $(this).closest('tr').find('input').val();
        modal_html += "<tr><input type='hidden' class='edit_id' name='id' value='"+id+"'>";
        modal_html += "<td><input type='text' class='card_num' name='card-num' value='"+card_number+"' ></td>";
        modal_html += "<td><input type='text' class='edit_price' name='edit_price' value='"+price+"' ></td>";
        modal_html += "<td><select id='edit_card_type'>";
        $( values ).each(function(i) {
          if( values[i] == selected_card_type )
            modal_html+= "<option value='"+values[i]+"' selected>"+values[i]+"</option>";
          else
            modal_html+= "<option value='"+values[i]+"'>"+values[i]+"</option>";
        });        
        modal_html += "</select></td>";
        modal_html += "<td><input type='button' name='edit' class='edit_card' value='Submit' ></td>";
        modal_html += "</tr>";
        $(".modal-card-values table").find("tr:gt(0)").remove();
        $(".modal-card-values table tr").eq(0).after(modal_html);
    });

    //Ajax function for updating/edit function
    $(document).on('click',".edit_card", function(){
        var check = confirm("Are you sure?");
        if( check ) {
            //$('#loader_container').show();
            var gc_number = $(".card_num").val();
            var price = $(".edit_price").val();
            var card_type = $( "#edit_card_type option:selected" ).text();
            var id   = $(".edit_id").val();
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {action: 'edit_card',id:id,card_num: gc_number, price :price,card_type:card_type}
            }).done(function (msg) {
                $("#card_error").hide();
                $("#card_success").show();
                $("#card_success").text(msg);
                $('#myModal').modal('hide');
                load_card_data();
            });
        } else {
            return false;
        }
    });

    //Ajax function for deleting
    $(document).on('click',"#delete_gc", function(){
        var check = confirm("Are you sure?");
        if( check ) {
            $('#loader_container').show();
            var id = $(this).closest('tr').find('input').val();
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {action: 'delete_card',id:id}
            }).done(function (msg) {
                $("#card_error").hide();
                $("#card_success").show();
                $("#card_success").text(msg);
                $('#loader_container').hide();
                load_card_data();
            });
        } else {
            return false;
        }
    });
})
