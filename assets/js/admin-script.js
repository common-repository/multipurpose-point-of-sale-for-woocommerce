//tmd pos get media link to input field function
jQuery(document).ready(function($){
    "use strict";

    $('#tmd_barcode_inventory_tbl').DataTable();
    $('#tmd-pos-upload-btn').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            var uploaded_image = image.state().get('selection').first();
            $('#reciept_logo').val(uploaded_image.toJSON().url);
            document.getElementById('recieptlogo').style.display = 'block';
            document.getElementById('recieptlogo').src = reciept_logo;
        });
    });
    
    $('#recieptlogo').click(function () { 
        $('#tmd-pos-upload-btn').trigger('click');
    })

    if ($('#tmdpos_payment_title').length) {
        var payment_title = JSON.parse( $('#tmdpos_payment_title').val() );
        var payment_total = JSON.parse( $('#tmdpos_payment_total').val() );
        var user_title    = JSON.parse( $('#tmdpos_user_title').val() );
        var user_total    = JSON.parse( $('#tmdpos_user_total').val() );

        /*payment sale report graph*/
        const tmd_ctx = document.getElementById('tmd-pos-report');
        const data = new Chart(tmd_ctx, {
            type: 'pie',
            data: {
                labels: payment_title,
                datasets: [{
                    label: 'Payment Report',
                    data: payment_total,
                    backgroundColor: [
                        '#ff6513',
                        '#88a609',
                        '#0fc39b',
                        '#810fdd',
                        '#ea0339',
                        '#ea0339',
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        /*user sale report graph*/
        const tmd_c = document.getElementById('tmd-pos-cashier-report');
        const uData = new Chart(tmd_c, {
            type: 'pie',
            data: {
                labels: user_title,
                datasets: [{
                    label: 'Cashier Report',
                    data: user_total,
                    backgroundColor: [
                        '#0aa074',
                        '#8728d5',
                        '#135e96',
                        '#6aa000',
                        '#ffb500',
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }


    
});