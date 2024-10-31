/**
 * Tmd pos ajax call
 */
(function ($) {
    "use strict";
    $(document).on('click', '.pos_add_ro_cart', function (e) {
        e.preventDefault();
        var product_id = $(this).attr('data-rel');
        var currency = $(this).attr('currency');

        /*condition to load cart page when cart page have order paid data*/
        var data_new = $(this).attr('data-new');
        if (data_new != '') {
            $('.pos_add_ro_cart').attr({ 'data-new': '' });
        }
        /*condition to load cart page when cart page have order paid data end*/

        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            data: {
                product_id: product_id,
                currency: currency,
                action: "tmdpos_varaiation_filter",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                $("#tmd-pos-progress-bar").hide();
                $('.table-responsive').animate({ scrollTop: 9999 }, 'slow');
                loadcart();
                tmdpos_cart_checkout();
            },

        });
    });
    /* get session data*/
    function loadcart() {
        var row = 0;
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            dataType: "json",
            data: {
                action: "tmdpos_cart_session",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                  tmd__pos_progress_bar();
            },
            success: function (json) {
                if (json != '') {
                    var items = json['pos_items'];
                    var html = '';

                    for (var i = 0; i < items.length; ++i) {

                        html += '<tr class="duplicate' + items[i]['product_id'] + '">';

                        html += '<td>' + items[i]['product_name'] + '</td>';

                        html += '<td><span>' + items[i]['currency'] + '</span><span>' + parseFloat(items[i]['product_price']).toFixed(2) + '</span></td>';

                        html += '<td><input class="qty textpad numpad' + items[i]['product_id'] + '" data-rel-q="' + items[i]['product_id'] + '" type="number"  min="1" value="' + parseInt(items[i]['product_qty']) + '" class="posproduct_qty' + items[i]['product_id'] + '" /><div class="tmd-pos-numpad' + items[i]['product_id'] + '"></div></td>';

                        html += '<td class="text-right"><span>' + items[i]['currency'] + '</span><span class="product_inline_total' + items[i]['product_id'] + '">' + parseFloat(items[i]['product_cost']).toFixed(2) + '</span></td>';

                        html += '<td class="tmd-text-right"><span class="cursor_pointer tmdpos_remove dashicons dashicons-trash bg-light" data-rel-r="' + items[i]['product_id'] + '"></span></td>';
                        html += '</tr>';

                        $('.poschangeoverTable tbody').html(html);
                        $("#tmd-pos-progress-bar").hide();
                    }
                    row++;
                } 
                else {
                    $('.poschangeoverTable tbody').html('');
                    $("#tmd-pos-progress-bar").hide();
                }
            }
        });
    }

    /*remove cart items*/
    $(document).on('click', '.tmdpos_remove', function (e) {
        e.preventDefault();
        var remove_id = $(this).attr('data-rel-r');
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: "html",
            data: {
                remove_id: remove_id,
                action: "tmdpos_remove_cart_items",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {
                loadcart();
                tmdpos_cart_checkout();
                $("#tmd-pos-progress-bar").hide();
            },
        });
    });
    /*remove cart items end*/

    /*update cart inline price*/
    $(document).on('input', '.qty', function (e) {
        e.preventDefault();
        cart_id  = $(this).attr('data-rel-q');
        inputval = $(this).val();
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            data: {
                cart_id: cart_id,
                inputval: inputval,
                action: "tmdpos_update_cart",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                loadcart();
                tmdpos_cart_checkout();
                $("#tmd-pos-progress-bar").hide();
            },

        });

    });

    /*update cart inline price*/

    tmdpos_cart_checkout();
    /*tmd pos cart checkout*/
    function tmdpos_cart_checkout() {
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: 'POST',
            data: {
                action: "tmdpos_cart_checkout",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                var subtotal  = json['subtotal'];
                var total_tax = json['total_tax'];
                var total     = json['total'];
                var currency  = json['currency'];
                if (subtotal === undefined || subtotal === null || subtotal === '') {
                    $(".pos_inline_Sub_totalSpan").html('');
                    $(".pos_inline_tax_totalSpan").html('');
                    $(".pos_inline_totalSpan").html('');
                    $(".pos_inline_Sub_totalSpan").attr({ 'cart_subtotal': '' });
                } 
                else {
                    $(".pos_inline_Sub_totalSpan").html(currency + parseFloat(subtotal).toFixed(2));
                    $(".pos_inline_Sub_totalSpan").attr({ 'cart_subtotal': parseFloat(subtotal).toFixed(2) });
                    $(".pos_inline_tax_totalSpan").html(currency + parseFloat(total_tax).toFixed(2));
                    $(".pos_inline_tax_totalinput").val(parseFloat(total_tax).toFixed(2));
                    $(".pos_inline_totalSpan").html(currency + parseFloat(total).toFixed(2));
                    $('.pos_inline_total').val(parseFloat(total).toFixed(2));
                }
                $("#tmd-pos-progress-bar").hide();

                /*coupon data*/
                if (json['tmd_coupon'] != null) {
                    $('.tmd_coupon_span').html(json['tmd_coupon']);
                } 
                else {
                    $('.tmd_coupon_span').html('');
                }
            },
        });
    }
    /*tmd pos cart checkout end*/

    /*tmd pos coupon code strat */
    $(document).on('click', '.tmdpos_apply_coupon', function () {
        var coupon_code   = $('.tmd_coupon_input').val();
        var cart_subtotal = $('.pos_inline_Sub_totalSpan').attr('cart_subtotal');
        var cart_tax      = $('.pos_inline_tax_totalinput').val();
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: 'json',
            data: {
                coupon_code: coupon_code,
                cart_subtotal: cart_subtotal,
                cart_tax: cart_tax,
                action: "tmdpos_apply_coupon",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                $("#tmd-pos-progress-bar").hide();
                if (json['message1'] != undefined) {
                    $('.tmd_error_message_for_coupon').html(json['message1']);
                }
                if (json['message2'] != undefined) {
                    $('.tmd_error_message_for_coupon').html(json['message2']);
                }
                if (json['tmd_coupon']['data1'] != undefined) {
                    $('.tmd_coupon_span').html(json['tmd_coupon']['data1']);
                    $('.tmd_pos_coupon_model').hide();
                }
                if (json['tmd_coupon']['data2'] != undefined) {
                    $('.tmd_coupon_span').html(json['tmd_coupon']['data2']);
                    $('.tmd_pos_coupon_model').hide();
                }
                tmdpos_cart_checkout(); /*trigger cart function*/
            },
        });
    });
    /*tmd pos coupon code strat end*/

    /*tmd pos hold order*/
    $(document).on('click', '.tmd_apply_hold', function () {
        var hold_note = $('.tmd_hold_input').val();
        if (hold_note != '') {
            $.ajax({
                url: tmd_ajax_url.ajax_url,
                type: "POST",
                dataType: 'json',
                data: {
                    hold_note: hold_note,
                    action: "tmdpos_hold_order",
                },
                beforeSend: function () {
                    $("#tmd-pos-progress-bar").show();
                    tmd__pos_progress_bar();
                },
                success: function (json) {
                    $("#tmd-pos-progress-bar").hide();
                    loadcart(); /*after hold load cart*/
                    tmdpos_cart_checkout(); /* after hold load checkout */
                    $('.ifmessage_present').html(json);
                    $('.tmd_error_message_for_hold').html('');
                    $('.hold_order_list_main').load(' .hold_order_list_main > * ');
                },
            });
        } 
        else {
            $('.tmd_error_message_for_hold').html('<p><span style="color: red;">Please Enter Hold Order Reason.</span></p>');
        }
    });
    /*tmd pos hold order end*/


    /*tmd pos hold order*/
    $(document).on('click', '.hold_order_add_to_cart', function () {
        var hold_order_id = $(this).attr('data-hold-id');
        if (hold_order_id != '') {
            $.ajax({
                url: tmd_ajax_url.ajax_url,
                type: "POST",
                dataType: 'json',
                data: {
                    hold_order_id: hold_order_id,
                    action: "tmdpos_hold_order_to_cart",
                },
                beforeSend: function () {
                    $("#tmd-pos-progress-bar").show();
                    tmd__pos_progress_bar();
                },
                success: function (json) {
                    $("#tmd-pos-progress-bar").hide();
                    loadcart(); /*after hold load cart*/
                    tmdpos_cart_checkout(); /* after hold load checkout */
                    $('.tmd_pos_holdorder_list').hide(); /*hide after load cart*/
                    $('.hold_order_list_main').load(' .hold_order_list_main > * ');
                },
            });
        } 
        else {
            $('.for_message_div').html('<p style="margin-left: 35px;"><span style="color: red;">There is an error to cart this hold order.</span></p>');
        }
    })
    /*tmd pos hold order end*/
    loadcart();

    /*scan filter product to trigger direct to cart*/

    /*remove space form search input*/
       $(document).on('paste', '#product_search_filter', function (e) {
        window.setTimeout(function () {
            var withoutSpaces = $("#product_search_filter").val();
            withoutSpaces = withoutSpaces.replace(/\s+/g, '');
            $("#product_search_filter").val(withoutSpaces);
        }, 1);
    });
	


    /*tmd pos product filter*/
	    /*tmd pos product filter*/
    $(document).on('click', '.tmd_pos_search_icon', function () {
        var product_title = $('#product_search_filter').val();
        if (product_title !== '' && product_title !== undefined) {
            tmd_pos_product_filter_add(product_title);
        }
        else {
            $(".tmdpos_products").load(window.location.href + " .tmdpos_products");
        }
    });
	
    $(document).on('keypress', '#product_search_filter', function (e) {
	
        if (e.which == 13) {
		  var product_title = $('#product_search_filter').val();
            if (product_title !== '' && product_title !== undefined) {
			
                tmd_pos_product_filter_add(product_title);
            }
            else {
			
                $(".tmdpos_products").load(window.location.href + " .tmdpos_products");
            }
        }
    });
    function tmd_pos_product_filter_add(product_title) {
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: 'POST',
            dataType: 'html',
            data: {
                product_title: product_title,
                action: "tmdpos_product_filter_by_name_sku",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {
                $("#tmd-pos-progress-bar").hide();
                $('.tmdpos_products').html(html);
                $('#product_search_filter').select();

                var numItems = $('.tmd_post_cart_icon').length;
                if (numItems == '1') {
                    $('.tmd_post_cart_icon').trigger('click');
                }
            },
        });
    }
    /*tmd pos product filter end*/

    /*order now start*/

    $(document).on('click', '.pos_order_now', function () {

        if ($('select[name=shop_customer]').find('option:selected').text() == '---Select Customer---') {
            var shop_customer = $('input[name=tmd_pos_customer]').val();
        } 
        else {
            shop_customer = $('select[name=shop_customer]').find('option:selected').text();
        }

        var payment_id = $('select[name=payment_method]').find('option:selected').attr('orderpaymode');

        var tmd_order_data = {};

        tmd_order_data['tmd_pos_customer'] = $('input[name=tmd_pos_customer]').val();
        tmd_order_data['shop_customer']    = $('select[name=shop_customer]').val();
        tmd_order_data['payment_method']   = $('select[name=payment_method]').val();
        tmd_order_data['payment_id']       = payment_id;
        tmd_order_data['coupon_code']      = $(this).attr('coupon_code');
        tmd_order_data['coupon_amount']    = $(this).attr('data-amount');
        tmd_order_data['order_status']     = $('select[name=order_status]').val();
        tmd_order_data['_subtotal']        = $('.pos_inline_Sub_totalSpan ').attr('cart_subtotal');
        tmd_order_data['order_total']      = $('input[name=order_total]').val();
        tmd_order_data['shipping_cost']    = $('input[name=shipping_cost]').val();
        tmd_order_data['discount']         = $('input[name=discount]').val();
        tmd_order_data['paid_amount']      = $('input[name=paid_amount]').val();
        tmd_order_data['change']           = $('input[name=change]').val();
        tmd_order_data['order_note']       = $('textarea[name=order_note]').val();
        tmd_order_data['wt_ship_total']    = $('input[name=wt_ship_total]').val();
        tmd_order_data['wt_dis_total']     = $('input[name=wt_dis_total]').val();
        tmd_order_data['tax_total']        = $('input[name=tax_total]').val();
        tmd_order_data['cashier']          = $('input[name=tmd_pos_cashier]').val();
        tmd_order_data['cashier_id']       = $('input[name=tmd_pos_cashier_id]').val();
        tmd_order_data['existing_customer']= shop_customer;

        var existing_customer = $('select[name=shop_customer]').val();
        var coupon_code = $(this).attr('coupon_code');

        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "post",
            dataType: "json",
            data: {
                tmd_order_data: tmd_order_data,
                coupon_code: coupon_code,
                existing_customer: existing_customer,
                payment_id: payment_id,
                action: "tmdpos_order_now",
            },
            beforeSend: function () {
                $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                $('.tmd_data_loader').hide(); /*hide loader when data loaded*/
                $("#tmd-pos-progress-bar").hide();
                var pos_order_id = json['data'];
                $('.pos_add_ro_cart').attr({ 'data-new': pos_order_id });
                $('.active_cart').attr({ 'data-new': pos_order_id });

                /*close order popup page*/
                $("#tmd_pos_checkout_pop").css({ "display": "none", });

                /*load cart after order success*/
                $(".poschangeoverTable").load(" .poschangeoverTable > *");
                $(".pos_inline_totalSpan").load(" .pos_inline_totalSpan > *");
                $("#tmd_pos_tax").load(" #tmd_pos_tax > *");

                /*pos total empty after order success to return cart validation*/
                $('input[name=pos_total]').val('');

                /*order success message with print btn and email*/
                var html = '';
                html += '<div class="tmd_order_success_message">';
                html += '<div class="meaage_container">';
                html += '<div class="messgae">Order Complete <p>Amount Paid:' + json['data_currency'] + tmd_order_data['wt_dis_total']+'</p></div>';
                html += '</div>';
                html += '<div class="tmd_button">';
                html += '<button class="print_button" data-order="' + pos_order_id + '">Print</button>';
                html += '</div>';
                html += '<button class="tmd_pos_new_order">Close To Create New Order</div>';
                html += '</div>';

                $('.tmd_pos_order_data').html(html);
                /*order success message with print btn and email end*/

                $(".tmd_pos_repost_list").load(' .tmd_pos_repost_list > * '); /*after order complete reload sale report list*/
                $(".tmd_pos_order_list").load(' .tmd_pos_order_list > * '); /*after order complete reload order list*/
            },

        });


    });

    /*order now end*/


    /*category Filter*/
    $(document).on('click', '.product_cat', function (e) {
        e.preventDefault();
        var category_id = $(this).attr('data-cat');
        if (category_id !== '' && category_id !== undefined) {
	        $.ajax({
                url: tmd_ajax_url.ajax_url,
                type: "POST",
                dataType: "html",
                data: {
                    category_id: category_id,
                    action: "tmdpos_filter_product_by_cat",
                },
                beforeSend: function () {
                   //$('.tmd_data_loader').show(); /*Show loader when data load*/
                    $("#tmd-pos-progress-bar").show();
                    tmd__pos_progress_bar();
                },

                success: function (html) {

                  //  $('.tmd_data_loader').hide(); /*hide loader when data loaded*/
                    $("#tmd-pos-progress-bar").hide();
                    $('.tmdpos_products').html(html);

                },
            });

        }
        else {
            $(".tmdpos_products").load(window.location.href + " .tmdpos_products");
        }
    });

    /*category Filter end*/

    /*tmd pos order print after order success*/
    $(document).on('click', '.print_button', function () {
        var pos_order_id = $(this).attr('data-order');
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: "html",
            data: {
                pos_order_id: pos_order_id,
                action: "tmdpos_order_print",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {
                // $('.tmd_data_loader').hide(); /*hide loader when data loaded*/
                $("#tmd-pos-progress-bar").hide();

                if (html == '<p class="print_order_message">Order Is Not Completed Please Complete Order First To Print Order Receipt.</p>') {
                    $('.error_to_print_order_receipt').show();
                    $('.tmd_print_error_message').html(html);
                } else {
                    $('.tmd_order_invoice_print').html(html);/*invoice print page*/
                    var w = window.open();
                    w.document.write($('.tmd_order_invoice_print').html());
                    w.print();
                    w.close($('.tmdpos_order_print_div').hide());
                }

            },

        });


    });
    /*tmd pos order print after order success end*/

    /*add new customer*/

    $(document).on('click', '.cancel_new_customer', function () {
        $('.tmd_add_customer').html('');
    });

    $(document).on('click', '.add_new_customer', function () {
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "GET",
            dataType: "json",
            data: {
                action: "tmdpos_get_country",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                $("#tmd-pos-progress-bar").hide();

                var countries = json['countries'];
                var country_code = json['country_code'];

                /*add new customer form*/
                var html = '';

                html += '<tbody><tr>';
                html += '<th class="tmd_pos_th">Name&nbsp;<span style="color:red;">*</span></th>';
                html += '<td class="tmd_pos_td"><input type="text" name="customer_name" class="tmd_msg_n" value="" placeholder="Enter Name" /><span class="name_error" style="color:red;"></span></td></tr>';

                html += '<tr><th class="tmd_pos_th">Email&nbsp;<span style="color:red;">*</span></th>';
                html += '<td class="tmd_pos_td"><input class="tmd_msg_e" type="email" name="customer_email" value="" placeholder="Enter Email" /><span class="email_error" style="color:red;"></span></td></tr>';

                html += '<tr><th class="tmd_pos_th">Phone&nbsp;<span style="color:red;">*</span></th>';
                html += '<td class="tmd_pos_td"><input class="tmd_msg_p" type="number" name="customer_phone" value="" placeholder="Enter Phone Number" /><span class="phone_error" style="color:red;"></span></td></tr>';

                html += '<tr><th class="tmd_pos_th">Address</th>';
                html += '<td class="tmd_pos_td"><input type="text" placeholder="Enter Address" name="customer_address" value="" /></td></tr>';

                html += '<tr><th class="tmd_pos_th">Address 2</th>';
                html += '<td class="tmd_pos_td"><input type="text" placeholder="Enter Address 2" name="customer_address2" value="" /></td></tr>';

                html += '<tr><th class="tmd_pos_th">City</th>';
                html += '<td class="tmd_pos_td"><input type="text" placeholder="Enter City" name="customer_city" value="" /></td></tr>';

                html += '<tr><th class="tmd_pos_th">Postcode&nbsp;<span style="color:red;">*</span></th>';
                html += '<td class="tmd_pos_td"><input placeholder="Enter Postcode" type="number" name="customer_postcode" value="" /><span class="postcode_error" style="color:red;"></span></td></tr>';

                html += '<tr><th class="tmd_pos_th">Country</th>';
                html += '<td class="tmd_pos_td"><select name="customer_country" class="customer_select customer_select_country"><option value="null" disabled selected>---Select Country---</option>';

                for (var i = 0; i < countries.length; ++i) {
                    for (var i = 0; i < country_code.length; ++i) {

                        html += '<option value="' + country_code[i] + '" data-country="' + countries[i] + '">' + countries[i] + '</option>';
                    }
                }

                html += '</select></td></tr>';

                html += '<tr><th class="tmd_pos_th state_td">State</th>';
                html += '<td class="tmd_pos_td state_td"><select name="customer_state" class="customer_select customer_select_state"><option value="null" disabled selected>---Select State---</option></select></td></tr>';

                html += '<tr><td class=""><p class="tmd-pos-flex-center"><button class="button cancel_new_customer">Cancel</button>&nbsp;&nbsp;&nbsp;';
                html += '<button class="button button-primary save_new_customer">Save</button></p></td></tr>';

                html += '</tbody>';
                $('.tmd_add_customer').html(html);

            },

        });

    });


    $(document).on('change', '.customer_select_country', function () {

        var country_code = $(this).val();
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: "json",
            data: {
                country_code: country_code,
                action: "tmdpos_get_state",
            },
            beforeSend: function () {
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                $("#tmd-pos-progress-bar").hide();
                var state = json['country_state'];
                var state_code = json['country_state_code'];
                var option = '';
                if (state != undefined) {
                    for (var i = 0; i < state.length; ++i) {
                        for (var i = 0; i < state_code.length; ++i) {

                            option += '<option value="' + state_code[i] + '">' + state[i] + '</option>';

                        }
                    }
                    $('.customer_select_state').html(option);
                    $('.state_td').show();

                } else {

                    option += '<option value="null" disabled selected>---Select Country First---</option>';
                    $('.state_td').hide();

                }
            },
        });
    });


    /*save new customer data*/
      $(document).on('click', '.save_new_customer', function () {
        var error = true;
        var customer_name = $('.tmd_msg_n').val();
        var customer_id = $('input[name=customer_id]') ? $('input[name=customer_id]').val() : '';
        var customer_email = $('input[name=customer_email]').val();
        var customer_phone = $('input[name=customer_phone]').val();
        var customer_postcode = $('input[name=customer_postcode]').val();
        var customer_data = {};

        customer_data['name'] = customer_name;
        customer_data['id'] = customer_id;
        customer_data['email'] = customer_email;
        customer_data['phone'] = customer_phone;
        customer_data['address'] = $('input[name=customer_address]').val();
        customer_data['address2'] = $('input[name=customer_address2]').val();
        customer_data['city'] = $('input[name=customer_city]').val();
        customer_data['postcode'] = customer_postcode;
        customer_data['country'] = $('select[name=customer_country]').val();
        customer_data['state'] = $('select[name=customer_state]').val();

        /*email validation*/
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

        if (!regex.test(customer_email)) {
            $('.email_error').html('Please Enter valid Email');
            $('.tmd_msg_e').css({ 'border': '1px solid red', });
            error = false;
        }
        else {
            $('.email_error').html('');
            $('.tmd_msg_e').css({ 'border': '1px solid #8c8f94', });
        }

        /*name*/
        if (customer_name == '') {
            $('.name_error').html('Please Enter Name');
            $('.tmd_msg_n').css({ 'border': '1px solid red', });
            error = false;
        }
        else {
            $('.name_error').html('');
            $('.tmd_msg_n').css({ 'border': '1px solid #8c8f94', });
        }
        /*phone*/
        if (customer_phone == '') {
            $('.phone_error').html('Please Enter Phone Number');
            $('.tmd_msg_p').css({ 'border': '1px solid red', });
            error = false;
        }
        else {
            $('.phone_error').html('');
            $('.tmd_msg_p').css({ 'border': '1px solid #8c8f94', });
        }

        /*postcode*/
        if (customer_postcode == '') {
            $('.postcode_error').html('Please Enter Postcode');
            $('input[name=customer_postcode]').css({ 'border': '1px solid red', });
            error = false;
        }
        else {
            $('.postcode_error').html('');
            $('input[name=customer_postcode]').css({ 'border': '1px solid #8c8f94', });
        }
        if (error != false) {
            $.ajax({
                url: tmd_ajax_url.ajax_url,
                type: "POST",
                dataType: "json",
                data: {
                    customer_data: customer_data,
                    action: "tmd_save_customer_data",
                },
                beforeSend: function () {
                    $("#tmd-pos-progress-bar").show();
                    tmd__pos_progress_bar();
                },
                success: function (json) {
                    $("#tmd-pos-progress-bar").hide();
                    $('.tmd_notice').html(json); /*notice*/
                    $(".shop_customer_select ").load(" .shop_customer_select  > *"); /*load customer select*/
                    $('.tmd_add_customer').html('');/*after adding customer form close*/
                    $('.tmdpos-container-madal').hide();
                    $(".tmdpos-container-madal").load(" .tmdpos-container-madal  > *"); /*load customer select*/
                    $("#tmd_pos_customer_list").load(" #tmd_pos_customer_list  > *"); /*load customer select*/

                },
            });
					location.reload();
        }

    });
    /*add new customer end*/

    /*tmd update order*/
    $(document).on('click', '.edit_order_details', function () {
        var order_id = $(this).attr('data-orderid');
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: "html",
            data: {
                order_id: order_id,
                action: "tmdpos_update_order_form",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {

                $("#tmd-pos-progress-bar").hide();
                // $('.tmd_data_loader').hide(); /*hide loader when data loaded*/
                $('.tmd_pos_order_update_container').html(html);
                $('.order_edit_main').show();

            },
        });

    });


    $(document).on('click', '.update_order', function () {
        var order_id = $('.update_order_id').val();
        var order_status = $('.up_order_status').val();
        var order_payment = $('.up_order_payment').val();
        var order_note = $('.up_order_note').val();
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: 'json',
            data: {
                order_id: order_id,
                order_status: order_status,
                order_payment: order_payment,
                order_note: order_note,
                action: "tmdpos_update_order",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                // $('.tmd_data_loader').hide(); /*hide loader when data loaded*/
                $("#tmd-pos-progress-bar").hide();

                $("#tmd_pos_order_list").load(" #tmd_pos_order_list > *");
                $('.tmd_notice_update').html(json);
            },
        });


    });
    /*tmd update order end*/

    /*checkout popup*/
    $(document).on('click', '#tmd_pos_checkout', function () {
        $(".order_status").load(" .order_status > *");
        $(".payment_select").load(" .payment_select > *");
        var OrderTotal = $('.pos_inline_totalSpan').text();
        var OrderTotalInp = $('.pos_inline_total').val();
        var data_coupon = $('.tmd_coupon_type').attr('data-coupon');
        var data_amount = $('.tmd_coupon_value').attr('data-amount');
        var customer = $('.tmd-pos-selected-customer').val();

        $(".pos_order_now").attr({ 'coupon_code': data_coupon });
        $(".pos_order_now").attr({ 'data-amount': data_amount });

        if (OrderTotal !== '') {

            $.ajax({
                beforeSend: function () {
                    // $('.tmd_data_loader').show(); /*Show loader when data load*/
                    $("#tmd-pos-progress-bar").show();
                    tmd__pos_progress_bar();

                    /*if customer is selected case to auto select customer*/
                    if (customer !== '' && customer !== undefined) {
                        $(".shop_customer option").each(function () {
                            $(this).removeAttr('selected');

                            if ($(this).val() == customer) {
                                $('#shop_guest').removeAttr('checked');
                                $('.shop_customer').val(customer).change();
                            }
                        });
                    } else {

                        $('#shop_guest').prop('checked', true);
                        $('.shop_customer option').removeAttr('disabled', 'disabled');
                        $('.shop_customer').val(0).change();
                    }
                },
                success: function () {
                    $("#tmd-pos-progress-bar").hide();
                    // $('.tmd_data_loader').hide(); /*hide loader when data loaded*/

                    $('.checkout-main-div').show(); /*show checkout popup*/
                    $("#tmd_pos_checkout_pop").css({ "display": "flex" });

                    $('.order_now_total').val(parseFloat(OrderTotalInp));
                    $(".paid_amount").attr({ "min": parseFloat(OrderTotalInp), });
                    $(".discount_total").attr({ "data-total": OrderTotalInp, });
                    $(".shipping_total").attr({ "data-total": parseFloat(OrderTotalInp) });

                    shipping();/*trigger shipping function*/
                    discount();/*trigger shipping discount*/

                },
            });


        } else {

            alert('Cart Is Empty');

        }

    });

    $(document).on('click', '#dismiss-checkout', function () {
        jQuery("#tmd_pos_checkout_pop").css({
            "display": "none",
        });
    });
    /*checkout popup end*/

    /*print update order */
    $(document).on('click', '.print_order_details', function () {
        var order_id = $(this).attr('data-orderid');

        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: 'html',
            data: {
                order_id: order_id,
                action: "tmdpos_order_print_from_list",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {
                // $('.tmd_data_loader').hide(); /*hide loader when data loaded*/
                $("#tmd-pos-progress-bar").hide();


                if (html == '<p class="print_order_message">Order Is Not Completed Please Complete Order First To Print Order Receipt.</p>') {
                    $('.error_to_print_order_receipt').show();
                    $('.tmd_print_error_message').html(html);
                } else {
                    $('.tmd_order_invoice_print').html(html); /*invoice print page*/
                    var w = window.open();
                    w.document.write($('.tmd_order_invoice_print').html());
                    w.print();
                    w.close($('.tmdpos_order_print_div').hide());
                }
            },
        });


    });
    /*print update order end*/

    /*tmd pos generate barcode*/
    $(document).on('click', '.generate_barcode', function () {
        var product_id = $(this).attr('tmd-pd-id');

        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: 'json',
            data: {
                product_id: product_id,
                action: "tmd_pos_barcode_generate",
            },
            beforeSend: function () {
                $('.tmd_loader' + product_id).show(); /*Show loader when data load*/
                $('.button_name' + product_id).hide(); /*Hide Button Name*/
            },
            success: function (json) {
                $(".barcode_img" + product_id).load(" .barcode_img" + product_id + " > *");
                $('.tmd_loader' + product_id).hide(); /*hide loader when data loaded*/
                $('.button_name' + product_id).show(); /*show Button Name*/
                $('.tmd_message' + product_id).show(); /*show message div Name*/
                $('.tmd_message' + product_id).html(json);

            },
        });

    });

    /*tmd pos generate barcode end*/

    /*tmd pos scan to add product stock */
    $(document).on('keypress', '.tmd_scan_product', function (e) {

        if (e.which == 13)  // the enter key code
        {
            var product_sku = $('.tmd_scan_product').val();

            $.ajax({
                url: tmd_ajax_url.ajax_url,
                type: "POST",
                dataType: 'html',
                data: {
                    product_sku: product_sku,
                    action: "tmdpos_stock_in",
                },
                beforeSend: function () {
                    // $('.tmd_data_loader').show(); /*Show loader when data load*/
                    $("#tmd-pos-progress-bar").show();
                    tmd__pos_progress_bar();
                },
                success: function (html) {
                    // $('.tmd_data_loader').hide(); /*hide loader when data load*/
                    $("#tmd-pos-progress-bar").hide();

                    $('.tmd_stock_in_popup').css({ 'display': 'flex', });
                    $('.tmd_stock_in_popup').html(html); /*stock in popup*/
                },
            });
        }
    });
    /*tmd pos stock in product end*/

    /*on esc key to close (hide) all popup*/
    $(document).on('keydown', function (event) {
        if (event.key == "Escape") {
            $('.tmd_pos_coupon_model').hide();
            $('.tmd_pos_holdorder_list').hide();
            $('.tmdpos-container-madal').hide();
            $('#tmd_pos_checkout_pop').hide();
            $('.tmd_pos_hold_order_modal').hide();
            $('.tmd_stock_in_popup').hide();
        }
    });
    /*on esc key to close (hide) all popup END*/


    /* tmd pos update stock qty */
    $(document).on('click', '.tmd_stock_in_btn', function () {
        product_id = $("input[name='product_id']").val();
        product_qty = $("input[name='product_qty']").val();

        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: "POST",
            dataType: 'json',
            data: {
                product_id: product_id,
                product_qty: product_qty,
                action: "tmdpos_stock_update",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (json) {
                // $('.tmd_data_loader').hide(); /*hide loader when data load*/
                $("#tmd-pos-progress-bar").hide();

                $('.stock_in_success_msg').html(json);
                $('#tmd_product_list').load(" #tmd_product_list  > *");
            },
        });

    });
    /* tmd pos update stock qty end*/


    /*tmd pos print sale report*/
    $(document).on('click', '.print_sale_report', function () {

        $.ajax({
            url: tmd_ajax_url.ajax_url,
            dataType: 'html',
            data: {
                action: "tmdpos_sale_report_print",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {
                // $('.tmd_data_loader').hide(); /*hide loader when data load*/
                $("#tmd-pos-progress-bar").hide();

                $('.tmd_pos_sale_report_main_div').html(html);

                var TableToPrint = document.getElementById('tmd_pos_sale_report_tbl');
                var htmlToPrint = '' +
                    '<style type="text/css">' +
                    'table th, table td {' +
                    'padding: 5px; ' +
                    '}' +
                    ' #tmd_pos_sale_report_tbl{padding: 4px; border: 1px solid; width:100%; border-collapse:collapse; font; font-size:12pt;}' +
                    ' th{ border-bottom: solid black 1px; border-right: solid black 1px!important; }' +
                    ' td{ border-bottom: solid black 1px; border-right: solid black 1px!important; }' +
                    ' td{ border-bottom: solid black 1px; border-right: solid black 1px!important; border-left: solid black 1px!important; }' +
                    '</style>';
                htmlToPrint += TableToPrint.outerHTML;
                reportPage = window.open("");
                reportPage.document.write(htmlToPrint);
                reportPage.print();
                reportPage.close($('.tmd_pos_sale_report_main_div').hide());

            },
        });

    });
    /*tmd pos print sale report end*/

    /*tmd pos load more product*/
    var count = 0;
    $(document).on('click', '.tmd_pos_load_more_btn', function () {
        count += 1;
        var load_more_pd = parseInt(count * '12');
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: 'POST',
            dataType: 'html',
            data: {
                load_more_pd: load_more_pd,
                action: "tmdpos_load_more_product",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {
                // $('.tmd_data_loader').hide(); /*hide loader when data load*/
                $("#tmd-pos-progress-bar").hide();

                $('.tmdpos_products').html(html);
            },
        });
    });

    /*tmd pos load more product end*/
	  $(document).on('click', '.tmd-pos-user-login-btn', function () {
        $('.tmd_danger_notice').hide();
        var udata = {
            'uname': $('input[name="pos_uname"]').val(),
            'upass': $('input[name="pos_upass"]').val(),
            'remember': $('input[name="pos_remember"]').val()
        }
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: 'post',
            dataType: 'json',
            data: {
                udata: udata,
                action: 'tmd_pos_user_login'
            },
            beforeSend: function () {
                $('body').css('cursor', 'progress');
            },
            success: function (json) {
                $('body').css('cursor', '');
                if (json.status === 'fail') {
                    $('.tmd-login-error-message').html(json.message);
                    $('.tmd_danger_notice').fadeIn(600);
                }
                else{
                    $('.tmd-login-error-message').html(json.message);
                    $('.tmd_success_notice').fadeIn(600);
                    setTimeout( function(){ 
                        location.reload();
                    }  , 2000 );
                }
            }
        });
    });
	
    /*tmd pos user login END*/

    /*layout two/three tmd pos product filter*/
    $(document).on('input', '#lyout_product_search_filter', function (e) {

        product_title = $('#lyout_product_search_filter').val();

        if (product_title !== '' && product_title !== undefined) {
            tmd_pos_product_filter_add_lyot2(product_title);

        }
        else {
            $(".product-inner-product-cnt").load(window.location.href + " .product-inner-product-cnt");
        }

    });

    function tmd_pos_product_filter_add_lyot2(product_title) {
        $.ajax({
            url: tmd_ajax_url.ajax_url,
            type: 'POST',
            dataType: 'html',
            data: {
                product_title: product_title,
                action: "tmdpos_layout_two_product_filter_by_name_sku",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {
                // $('.tmd_data_loader').hide(); /*hide loader when data load*/
                $("#tmd-pos-progress-bar").hide();

                $('.product-inner-product-cnt').html(html);
                $('#lyout_product_search_filter').select();

                var numItems = $('.tmd_post_cart_icon').length;
                if (numItems == '1') {
                    $('.tmd_post_cart_icon').trigger('click');

                }
            },
        });
    }
    /*layout two/three tmd pos product filter end*/


    /*tmd pos layout two/three product filter by category*/
    $(document).on('change', '.select-pos-category', function () {

        var category_id = $(this).val();

        if (category_id !== '' && category_id !== undefined) {

            $.ajax({
                url: tmd_ajax_url.ajax_url,
                type: "POST",
                dataType: "html",
                data: {
                    category_id: category_id,
                    action: "tmdpos_layout_two_product_filter_by_category",
                },
                beforeSend: function () {
                    // $('.tmd_data_loader').show(); /*Show loader when data load*/
                    $("#tmd-pos-progress-bar").show();
                    tmd__pos_progress_bar();
                },
                success: function (html) {
                    // $('.tmd_data_loader').hide(); /*hide loader when data loaded*/
                    $("#tmd-pos-progress-bar").hide();

                    $('.product-inner-product-cnt').html(html);

                },

            });
        }
        else {
            $(".product-inner-product-cnt").load(window.location.href + " .product-inner-product-cnt");
        }

    });
    /*tmd pos layout two/three product filter by category END*/


    /*---------------------- tmd pos layout two/three load more --------------*/
    var count = 0;
    $(document).on('click', '.tmd_pos_lyt2_load_more_btn', function () {

        count += 1;
        var load_more_pd = parseInt(count * '10');
        $.ajax({

            url: tmd_ajax_url.ajax_url,
            type: 'POST',
            dataType: 'html',
            data: {
                load_more_pd: load_more_pd,
                action: "tmdpos_layout_two_load_more",
            },
            beforeSend: function () {
                // $('.tmd_data_loader').show(); /*Show loader when data load*/
                $("#tmd-pos-progress-bar").show();
                tmd__pos_progress_bar();
            },
            success: function (html) {
                // $('.tmd_data_loader').hide(); /*hide loader when data load*/
                $("#tmd-pos-progress-bar").hide();

                $('.product-inner-product-cnt').html(html);
            },
        });

    });
    /*------------------ tmd pos layout two/three load more END --------------*/


    /*--------------------------- layout two/three customer search ---------------------------*/
    $(document).on('input', '.tmd-lout2-cus-srch', function () {

        $('.tmd-pos-selected-customer').val('');

        $(".tmd-lout2-cus-srch").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: tmd_ajax_url.ajax_url,
                    type: "post",
                    data: {
                        request,
                        action: "tmdpos_layout_two_customer_search"
                    },
                    success: function (data) {

                        if (data !== '' && data !== undefined) {
                            response($.map(data, function (el) {
                                return {
                                    label: el.name,
                                    value: el.id
                                };
                            }));
                        }
                    }
                });
            },
            select: function (event, ui) {
                this.value = ui.item.label;
                $(".tmd-pos-selected-customer").val(ui.item.value);
                event.preventDefault();
            }
        });

    });

    /*------------------------- layout two/three customer search END -------------------------*/

    /*--------------------- add new customer on layout two/three -----------------------------*/
    $(document).on('click', '.add-new-customer', function () {
        $('.tmdpos-container-madal').show();
    });
    /*------------------- add new customer on layout two/three END ---------------------------*/

    /*tmd pos notice (message) modal (popup) */
    function tmd_pos_alert_notice(message) {
        var text = message !== '' ? message : message = 'undefined';
        $('.tmdpos-container-notice-madal').show();
        $('.main-content .notice-txt').html(text);
    }
    /*tmd pos notice (message) modal (popup) End */

    function tmd__pos_progress_bar(){
        var width = 1;
        var id = setInterval(frame, 20);
        function frame() {
            if (width >= 100) {
                clearInterval(id);
                var i = 0;
              } else {
                width++;
                $('#tmd-pos-progress-bar').css('width', width + '%');
            }
         }
     }

}(jQuery));
