jQuery(document).ready(function() {
    jQuery('#billing_company').keyup(function() {
        jQuery(document.body).trigger("update_checkout");
    });

    jQuery(document.body).on('change', ".abilitaBirthday",function (e) {
        jQuery('.abilitaBirthday').val(jQuery(this).find(":selected").val());
    });
    jQuery(document.body).on('change', ".abilitaBirthmonth",function (e) {
        jQuery('.abilitaBirthmonth').val(jQuery(this).find(":selected").val());
    });
    jQuery(document.body).on('change', ".abilitaBirthyear",function (e) {
        jQuery('.abilitaBirthyear').val(jQuery(this).find(":selected").val());
    });
});