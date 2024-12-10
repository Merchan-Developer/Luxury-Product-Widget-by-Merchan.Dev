jQuery(document).ready(function($) {
    $('.luxury-products').on('click', '.open-popup-link', function(e) {
        e.preventDefault();
        
        var productID = $(this).closest('.luxury-product').data('product-id');

        $.magnificPopup.open({
            items: {
                src: '#product-popup-' + productID,
                type: 'inline'
            },
            closeBtnInside: true,
            mainClass: 'mfp-fade'
        });
    });
});

/*Arturo Merchan | Merchan.Dev® 2025*/