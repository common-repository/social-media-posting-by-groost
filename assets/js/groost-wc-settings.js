jQuery(document).ready(function () {
    jQuery('form#groost-wc-disconnect_form input[type="submit"]').on('click', function (e) {
        e.preventDefault();

        if (confirm(jQuery(this).attr('data-confirm'))) {
            jQuery('form#groost-wc-disconnect_form').submit();
        }

        return false;
    });

    jQuery('p#groost-wc-change_page_link a').on('click', function (e) {
        e.preventDefault();

        jQuery('form#groost_change_fb_page').css('display', 'block');
        jQuery(this).hide();
        jQuery('form#groost-wc-disconnect_form').hide();

        return false;
    })
});
