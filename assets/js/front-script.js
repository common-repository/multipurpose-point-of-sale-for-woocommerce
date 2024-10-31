jQuery(function ($) {

    "use strict";
    /*tmd pos script*/
    $(document).on('click', '.tmdpos_op_pd', function (e) {
        var tmd_cart_pd_option_pd = $(this).attr('tmd-pos-var-id');
        $("#tmd_pop_modal" + tmd_cart_pd_option_pd).css("display", "block");
    });

    $(document).on('click', '.cartpopclose', function (e) {
        var tmd_cart_pd_option_pd_cs = $(this).attr('tmd-pos-var-id-to-close');
        $("#tmd_pop_modal" + tmd_cart_pd_option_pd_cs).css("display", "none");
        //add disable after close product option.
        $(".option_add_to_cart").prop('disabled', true);
        $(".active_cart").attr('data-rel', '');
        $(".active_cart").removeClass("pos_add_ro_cart");
        $(".pos_add_op_cart").css({ "cursor": "wait", "pointer-events": "unset" });
        $('.grp_children_pd').removeAttr("checked");
        $('select').prop('selectedIndex', 0);
    });


    /*button add to cart option product disable when data is not selected "size and color"*/
    $(document).ready(function () {
        $(".option_add_to_cart").prop('disabled', true);
        $(".pos_add_op_cart").css({
            "cursor": "wait",
            "pointer-events": "none"
        });
        $(".active_cart").removeClass("pos_add_ro_cart");

        $('.tmd_pos_prod_img_slug').change(function () {
            if ($(this).val() == null) {
                $(".option_add_to_cart").attr('disabled', 'disabled');
            }
            else {
                $(".option_add_to_cart").removeAttr('disabled');
                $(".pos_add_op_cart").css({
                    "cursor": "pointer",
                    "pointer-events": "unset"
                });
                $(".pos_add_dummuy_cart ").css({
                    "display": "none",
                });
            }
        });
    });

    /*tmd pos order list*/
    $(document).on('click', '.tmd_order_list_btn', function () {

        $(".tmd_pos_order_list_main").css({
            "display": "block",
            "height": "86%",
            "width": "79%",
            "position": "fixed",
            "z-index": "99",
            "padding": "20px",
            "top": "46px",
            "left": "18%",
            "background-color": "#ffffff",
            "overflow-x": "hidden",
            "transition": "0.8s",
            "overflow-y": "scroll",
        });

        $('.tmdpos-title-left').show(600);

        // close other page
        $('.tmd_pos_customer_list_main').css({ "width": "0", "left": "-100px", });
        $('.tmd_pos_product_list_main').css({ "width": "0", "left": "-100px", });
        $('.tmd_pos_report_list_main').css({ "width": "0", "left": "-100px", });

        /*active button*/
        $(".tmd_order_list_btn > span").html('&#187;');
        $(".tmd_order_list_btn > span").css({ "float": "right", "color": "#ffffff", });

        //deactive button
        $(".tmd_customer_list_btn > span").html('');
        $(".tmd_product_list_btn > span").html('');
        $(".tmd_pos_report_btn > span").html('');

    });

    /*close order list*/
    $(document).on('click', '.close_order_list', function () {
        $('.tmd_pos_order_list_main').css({ "width": "0", "left": "-100px", });
        $(".tmd_order_list_btn > span").html('&#171;');
        $('.tmdpos-title-left').hide();
    });


    $(document).on('click', '.tmd_customer_list_btn', function () {

        $(".tmd_pos_customer_list_main").css({
            "display": "block",
            "height": "86%",
            "width": "79%",
            "position": "fixed",
            "z-index": "99",
            "padding": "20px",
            "top": "46px",
            "left": "18%",
            "background-color": "#ffffff",
            "overflow-x": "hidden",
            "transition": "0.8s",
            "overflow-y": "scroll",
        });

        $('.tmdpos-title-left').show(600);

        // close other page
        $('.tmd_pos_order_list_main').css({ "width": "0", "left": "-100px", });
        $('.tmd_pos_product_list_main').css({ "width": "0", "left": "-100px", });
        $('.tmd_pos_report_list_main').css({ "width": "0", "left": "-100px", });

        /*active button*/
        $(".tmd_customer_list_btn > span").html('&#187;');
        $(".tmd_customer_list_btn > span").css({ "float": "right", "color": "#ffffff", });

        //deactive button
        $(".tmd_order_list_btn > span").html('');
        $(".tmd_product_list_btn > span").html('');
        $(".tmd_pos_report_btn > span").html('');
    });

    /*close customer list*/
    $(document).on('click', '.close_customer_list', function () {
        $('.tmd_pos_customer_list_main').css({ "width": "0", "left": "-100px", });
        $(".tmd_customer_list_btn > span").html('&#171;');
        $('.tmdpos-title-left').hide();
    });


    $(document).on('click', '.tmd_product_list_btn', function () {

        $(".tmd_pos_product_list_main").css({
            "display": "block",
            "height": "86%",
            "width": "79%",
            "position": "fixed",
            "z-index": "99",
            "padding": "20px",
            "top": "46px",
            "left": "18%",
            "background-color": "#ffffff",
            "overflow-x": "hidden",
            "transition": "0.8s",
            "overflow-y": "scroll",
        });
        $('.tmdpos-title-left').show(600);
        // close other page
        $('.tmd_pos_order_list_main').css({ "width": "0", "left": "-100px", });
        $('.tmd_pos_customer_list_main').css({ "width": "0", "left": "-100px", });
        $('.tmd_pos_report_list_main').css({ "width": "0", "left": "-100px", });

        /*active button*/
        $(".tmd_product_list_btn > span").html('&#187;');
        $(".tmd_product_list_btn > span").css({ "float": "right", "color": "#ffffff", });

        //deactive button
        $(".tmd_order_list_btn > span").html('');
        $(".tmd_customer_list_btn > span").html('');
        $(".tmd_pos_report_btn > span").html('');

    });

    /*close product list*/
    $(document).on('click', '.close_product_list', function () {
        $('.tmd_pos_product_list_main').css({ "width": "0", "left": "-100px", });
        $(".tmd_product_list_btn > span").html('&#171;');
        $('.tmdpos-title-left').hide();
    });



    /*sale report page*/
    $(document).on('click', '.tmd_pos_report_btn', function () {

        $(".tmd_pos_report_list_main").css({
            "display": "block",
            "height": "86%",
            "width": "79%",
            "position": "fixed",
            "z-index": "99",
            "padding": "20px",
            "top": "46px",
            "left": "18%",
            "background-color": "#ffffff",
            "overflow-x": "hidden",
            "transition": "0.8s",
            "overflow-y": "scroll",
        });

        $('.tmdpos-title-left').show(600);

        // close other page
        $('.tmd_pos_order_list_main').css({ "width": "0", "left": "-100px", });
        $('.tmd_pos_product_list_main').css({ "width": "0", "left": "-100px", });
        $('.tmd_pos_customer_list_main').css({ "width": "0", "left": "-100px", });

        /*active button*/
        $(".tmd_pos_report_btn > span").html('&#187;');
        $(".tmd_pos_report_btn > span").css({ "float": "right", "color": "#ffffff", });

        //deactive button
        $(".tmd_order_list_btn > span").html('');
        $(".tmd_customer_list_btn > span").html('');
        $(".tmd_product_list_btn > span").html('');
    });

    /*close sale report list*/
    $(document).on('click', '.close_repost_list ', function () {
        $('.tmd_pos_report_list_main').css({ "width": "0", "left": "-100px", });
        $(".tmd_pos_report_btn > span").html('&#171;');
        $('.tmdpos-title-left').hide();
    });
    /*sale report page end*/

    /*close order update form*/
    $(document).on('click', '.close_oder_update', function () {
        $('.order_edit_main').hide();
    });

    /*success notice close*/
    $(document).on('click', '.success_notice_close', function () {
        $('.tmd_notice').hide();
    });

    /*close message box in barcode inventory while generate barcode*/
    $(document).on('click', '.close_message', function () {
        var id = $(this).attr('data-id');
        $('.tmd_message' + id).hide();
    })

    $(document).on('change', '.grp_children_pd', function () {
        grouped_chld_pd = $(this).attr('data-rel');
        $('.active_cart').addClass('pos_add_ro_cart');
        $('.active_cart').attr({ 'data-rel': grouped_chld_pd });
        $('.option_add_to_cart').prop("disabled", false);
    })

    $(document).on('click', '.layout_img', function () {
        $('.layout_img img').removeClass('pod_layout_active');
        $(this).find('img').addClass('pod_layout_active');
    });

    /*random color genrate*/
    function tmdPosRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }
    /*random color genrate END*/

    /*set random color*/
    $(document).ready(function () {
        var myColors = ['#ff0071', '#ffa500', '#ff0010', '#18a003', '#281fff', '#0ec2ba', '#a201ff', '#9db613', '#FF6513'];
        $(".random-color").each(function () {
            var randomize = Math.floor(Math.random() * myColors.length);
            $(this).css("background-color", myColors[randomize]);
        });
    });
    /*set random color END*/

    $(document).ready(function () {
        $('.tmd-pos-user').DataTable();
        $('.dataTables_filter input').attr("placeholder", "Search...");
        $('.dataTables_length').hide();
        $('.dataTables_filter').css({ 'margin-bottom': '20px' });
    });

    /*pos paid amount change*/
    $(document).on('input', '.paid_amount', function () {
        var paid_amount = $(this).val();
        var OrderTotal = $(this).attr("min");

        if (parseFloat(paid_amount) < parseFloat(OrderTotal)) {
            $(".pos_order_now").attr('disabled', 'disabled');
        }
        else {
            $(".pos_order_now").removeAttr('disabled', 'disabled');
        }
        var tmd_pos_change = parseFloat(paid_amount) - parseFloat(OrderTotal);
        $('.tmd_change').val(parseFloat(tmd_pos_change).toFixed(2));
    });

    /*new order to load order div*/
    $(document).on('click', '.tmd_pos_new_order', function () {
        $(".tmd_pos_order_data").load(" .tmd_pos_order_data > *");

    });
    /*new order to load order div end*/

    /*tmd pos back button*/
    $(document).on('click', '.tmd_back_button', function () {
        window.location.reload();
    });
    /*tmd pos back button end*/

    $(document).ready(function () {
        $('.tmd_pos_order_list').DataTable({ "oLanguage": { "sSearch": "" }, });
        $('#tmd_pos_order_list_filter input').attr("placeholder", "Search...");

        $('#tmd_pos_customer_list').DataTable({ "oLanguage": { "sSearch": "" }, });
        $('#tmd_pos_customer_list_filter input').attr("placeholder", "Search...");

        $('#tmd_product_list').DataTable({ "oLanguage": { "sSearch": "" }, });
        $('#tmd_product_list_filter input').attr("placeholder", "Product Search...");

        /*tmd pos sale report csv, print and excel*/
        $('.tmd_pos_repost_list').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'csv', text: 'Export', footer: true, className: 'tmdPosSaleReportExportButton' },
            ],
            "oLanguage": { "sSearch": "" },
        });

        $('#tmd_pos_repost_list_filter input').attr("placeholder", "Search...");
        $('#tmd_barcode_inventory_tbl').DataTable({ "oLanguage": { "sSearch": "" }, });
        $('#tmd_barcode_inventory_tbl_filter input').attr("placeholder", "Search...");
    });

    $(document).on('click', '.tmd_stock_in_close', function () {
        $('.tmd_stock_in_popup').css({ 'display': 'none', });
    })

    $(document).on('click', '.pop_hold_modal_close_div', function () {
        $('.tmd_pos_hold_order_modal').hide();
    });

    $(document).on('click', '.tmd_apply_hold', function () {
        console.log($(this).attr('data-hold'))
    })


    $(document).on('click', '.close_tmd_print_error_dilog', function () {
        $('.error_to_print_order_receipt').hide();
    });

    $(document).on('click', '.tmd-fullscreen-button', function () {
        tmdPosGoFullScreen();
    })

    $(document).on('click', '.tmd_post_outof_icon', function () {
        alert('out of stock');
    });

    /*close add new customer modal*/
    $(document).on('click', '.tmd-modal-close', function () {
        $('.tmdpos-container-madal').hide();
        $(".tmdpos-container-madal").load(' .tmdpos-container-madal > * ');
    });
    /*close add new customer modal End*/

    /*close notice modal*/
    $(document).on('click', '.tmd-notice-close', function () {
        $('.tmdpos-container-notice-madal').hide();
        $(".tmdpos-container-notice-madal").load(' .tmdpos-container-notice-madal > * ');
    });
    /*close notice modal End*/

});
/*tmd pos add grouped product to cart end*/



// tmd pos curtain menu
function openNav() {
    "use strict";
    document.getElementById("tmdpos_menu").style.width = "18%";
}

function closeNav() {
    "use strict";
    document.getElementById("tmdpos_menu").style.width = "0%";
    /*close order list*/
    jQuery('.tmd_pos_order_list_main').css({ "width": "0", "left": "-100px", });
    /*close customer list*/
    jQuery('.tmd_pos_customer_list_main').css({ "width": "0", "left": "-100px", });
    /*close product list*/
    jQuery('.tmd_pos_product_list_main').css({ "width": "0", "left": "-100px", });
    /*close sale report list*/
    jQuery('.tmd_pos_report_list_main').css({ "width": "0", "left": "-100px", });
    jQuery('.tmdpos-title-left').hide();
    /*deactive button*/
    jQuery(".tmd_order_list_btn > span").html('');
    jQuery(".tmd_customer_list_btn > span").html('');
    jQuery(".tmd_product_list_btn > span").html('');
    jQuery(".tmd_pos_report_btn > span").html('');
}


/*product size and color selected*/
function tmd_pos_product_change_s(pos_product_id) {
    "use strict"
    var variation_id = jQuery(".product_option" + pos_product_id + " option:selected").val();
    var variation_name = jQuery(".product_option" + pos_product_id + " option:selected").attr("data-name");
    var variation_stqty = jQuery(".product_option" + pos_product_id + " option:selected").attr("data-stqty");
    var variation_cost = jQuery(".product_option" + pos_product_id + " option:selected").attr("data-cost");
    var variation_img = jQuery(".product_option" + pos_product_id + " option:selected").attr("data-img");
    var variation_sku = jQuery(".product_option" + pos_product_id + " option:selected").attr("data-sku");
    var variation_currency = jQuery(".product_option" + pos_product_id + " option:selected").attr("data-currency");
    var variation_status = jQuery(".product_option" + pos_product_id + " option:selected").attr("data-status");
    var variation_backorder = jQuery(".product_option" + pos_product_id + " option:selected").attr("data-backodr");

    if (variation_backorder == 'notify') {
        var backorder = 'Available on backorder';
        jQuery(".vailiable_backorder" + pos_product_id).show();
        jQuery(".vailiable_backorder" + pos_product_id).css({
            "color": "#e9680d",
            "font-weight": 600,
        });
    }
    else if (variation_backorder == 'no') {
        jQuery(".vailiable_backorder" + pos_product_id).hide();
    }

    jQuery(".active_cart").attr('data-rel', variation_id);

    if (variation_status == 'In Stock') {
        jQuery(".option_add_to_cart").removeAttr('disabled', 'disabled');
        jQuery(".active_cart").addClass("pos_add_ro_cart");
        jQuery(".stock_status" + pos_product_id).css({   /*display block stock status*/
            "display": "block",
            "color": "green",
            "font-weight": 600,
        });
    }
    else {
        jQuery(".option_add_to_cart").attr('disabled', 'disabled');
        jQuery(".active_cart").removeClass("pos_add_ro_cart");
        jQuery(".stock_status" + pos_product_id).css({   /*display block stock status*/
            "display": "block",
            "color": "red",
            "font-weight": 600,
        });
    }

    jQuery(".image_pos_product" + pos_product_id).prop('src', variation_img); /*chnage image slug according to color size*/
    jQuery(".stock_status" + pos_product_id).html(variation_status); /*display stock status*/
    jQuery(".stock_qty" + pos_product_id).html('Stock Quantity : ' + variation_stqty); /*Display Stock Qty*/
    jQuery(".vailiable_backorder" + pos_product_id).html(backorder); /*Display Stock Qty*/
    jQuery(".stock_qty" + pos_product_id).show(); /*Display Block Stock Qty*/
    jQuery(".name_pos_product" + pos_product_id).html(variation_name); /*chnage product name according to color size*/
    jQuery(".price_pos_product" + pos_product_id).html(variation_currency + variation_cost); /*chnage product price according to color size*/
}
/*product size and color selected end*/

/*open tmd pos admin page in new tab */
function openTmdPosAdmin(adminUrl) {
    window.open(adminUrl, '_blank').focus();
}

function tmdPosGoFullScreen(e) {
    e = e || document.documentElement;
    if (!document.fullscreenElement && !document.mozFullScreenElement &&
        !document.webkitFullscreenElement && !document.msFullscreenElement) {
        if (e.requestFullscreen) {
            e.requestFullscreen();
        } 
        else if (e.msRequestFullscreen) {
            e.msRequestFullscreen();
        } 
        else if (e.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } 
        else if (e.webkitRequestFullscreen) {
            e.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } 
    else{
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } 
        else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        } 
        else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } 
        else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }
}

/*pos add shipping cost*/
function shipping() {
    if (jQuery(".shipping_total").val() != null) {
        var shipping_total = jQuery(".shipping_total").val();
        var OrderTotal = jQuery(".shipping_total").attr("data-total");
        if (shipping_total == '') { shipping_total = 0; }
        var amount_with_ship = parseFloat(shipping_total) + parseFloat(OrderTotal);
        jQuery(".paid_amount").attr({ "min": parseFloat(amount_with_ship).toFixed(2), });
        jQuery(".discount_total").attr({ "data-total": parseFloat(amount_with_ship).toFixed(2) });
        jQuery(".wt_ship_total").val(parseFloat(amount_with_ship).toFixed(2));
        jQuery('input[name=change]').val('');
        discount();
    }
}

/*pos add discount*/
function discount() {
    var discount_total = jQuery(".discount_total").val();
    var paid_amount = jQuery(".discount_total").attr("data-total");
    if (discount_total == '') { var discount_total = 0; }
    var amount_with_dis = parseFloat(paid_amount) - parseFloat(discount_total);
    jQuery(".paid_amount").attr({ "min": parseFloat(amount_with_dis).toFixed(2), });
    jQuery(".paid_amount").val(parseFloat(amount_with_dis).toFixed(2));
    jQuery(".wt_dis_total").val(parseFloat(amount_with_dis).toFixed(2));
    jQuery('input[name=change]').val('');
}

	