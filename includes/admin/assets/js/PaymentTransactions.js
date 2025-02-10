window.onload=function(){
    const modalRefund      = document.getElementById('modalRefund');
    const modalCancel      = document.getElementById('modalCancel');
    const modalReauthorize = document.getElementById('modalReauthorize');

    jQuery('.showModalRefund').each(function(){
        jQuery(this).click(function() {
            modalRefund.showModal();
        })
    });

    jQuery('.paymentAction').each(function(){
        jQuery(this).change(function() {
            if (jQuery(this).val() == 'reauthorize') {
                jQuery('#authorizationOrderId').val(jQuery(this).find(':selected').attr('data-order-id'));
                jQuery('#authorizationPaymentType').val(jQuery(this).find(':selected').attr('data-payment-type'));
                jQuery('#authorizationPaymentTypeTranslated').val(jQuery(this).find(':selected').attr('data-payment-type-translated'));
                jQuery('#authorizationAmount').val(jQuery(this).find(':selected').attr('data-amount'));
                jQuery('#authorizationTransactionId').val(jQuery(this).find(':selected').attr('data-transaction-id'));
                jQuery('#authorizationAmountNew, #authorizationComment').val('');
                jQuery('#modalReauthorizeSubmit').show();
                modalReauthorize.showModal();
            } else if (jQuery(this).val() == 'cancel') {
                jQuery('#cancelOrderId').val(jQuery(this).find(':selected').attr('data-order-id'));
                jQuery('#cancelPaymentType').val(jQuery(this).find(':selected').attr('data-payment-type'));
                jQuery('#cancelPaymentTypeTranslated').val(jQuery(this).find(':selected').attr('data-payment-type-translated'));
                jQuery('#cancelAmount').val(jQuery(this).find(':selected').attr('data-amount'));
                jQuery('#cancelTransactionId').val(jQuery(this).find(':selected').attr('data-transaction-id'));
                jQuery('#cancelComment').val('');
                jQuery('#modalCancelSubmit').show();
                modalCancel.showModal();
            } else if (jQuery(this).val() == 'refund') {
                jQuery('#refundOrderId').val(jQuery(this).find(':selected').attr('data-order-id'));
                jQuery('#refundPaymentType').val(jQuery(this).find(':selected').attr('data-payment-type'));
                jQuery('#refundPaymentTypeTranslated').val(jQuery(this).find(':selected').attr('data-payment-type-translated'));
                jQuery('#refundAmount').val(jQuery(this).find(':selected').attr('data-amount'));
                jQuery('#refundTransactionId').val(jQuery(this).find(':selected').attr('data-transaction-id'));
                jQuery('#refundComment').val('');
                jQuery('#modalCancelSubmit').show();
                modalRefund.showModal();
            }
        })
    });

    jQuery('#modalReauthorizeSubmit').click(function() {
        jQuery('.modalHeaderSuccess, .modalHeaderError').hide();
        let formData = new FormData()
        formData.append("authorizationOrderId", jQuery('#authorizationOrderId').val());
        formData.append("authorizationPaymentType", jQuery('#authorizationPaymentType').val());
        formData.append("authorizationTransactionId", jQuery('#authorizationTransactionId').val());
        formData.append("authorizationAmountNew", jQuery('#authorizationAmountNew').val());
        formData.append("authorizationComment", jQuery('#authorizationComment').val());

        fetch('admin.php?page=abilita_settings_page&tab=PaymentReauthorize', {
            method: "POST",
            body:  formData
        })
            .then(function(response){
                return response.json();
            })
            .then(function(data){
                if (data.status == 'error' && data.code > 0) {
                    jQuery('.modalHeaderError').text('Es ist ein Fehler aufgetreten (Code: '+data.code+')');
                    jQuery('.modalHeaderError').show();
                } else if (data.status == 'invalid' && data.code > 0) {
                    jQuery('.modalHeaderError').text(data.message);
                    jQuery('.modalHeaderError').show();
                } else {
                    jQuery('.modalHeaderSuccess').show();
                    jQuery('#modalReauthorizeSubmit').hide();

                    window.setTimeout(function(){
                        jQuery('#transactionForm').submit();
                    }, 2000);
                }

                window.setTimeout(function(){
                    jQuery('.modalHeaderSuccess, .modalHeaderError').hide();
                }, 2000);
            });
    });

    jQuery('#modalCancelSubmit').click(function() {
        jQuery('.modalHeaderSuccess, .modalHeaderError').hide();
        let formData = new FormData()
        formData.append("cancelOrderId", jQuery('#cancelOrderId').val());
        formData.append("cancelPaymentType", jQuery('#cancelPaymentType').val());
        formData.append("cancelTransactionId", jQuery('#cancelTransactionId').val());
        formData.append("cancelComment", jQuery('#cancelComment').val());

        fetch('admin.php?page=abilita_settings_page&tab=PaymentCancel', {
            method: "POST",
            body:  formData
        })
            .then(function(response){
                return response.json();
            })
            .then(function(data){
                if (data.status == 'error' && data.code > 0) {
                    jQuery('.modalHeaderError').text('Es ist ein Fehler aufgetreten (Code: '+data.code+')');
                    jQuery('.modalHeaderError').show();
                } else if (data.status == 'invalid' && data.code > 0) {
                    jQuery('.modalHeaderError').text(data.message);
                    jQuery('.modalHeaderError').show();
                } else {
                    jQuery('.modalHeaderSuccess').show();
                    jQuery('#modalCancelSubmit').hide();

                    window.setTimeout(function(){
                        jQuery('#transactionForm').submit();
                    }, 2000);
                }

                window.setTimeout(function(){
                    jQuery('.modalHeaderSuccess, .modalHeaderError').hide();
                }, 3000);
            });
    });

    jQuery('#modalRefundSubmit').click(function() {
        jQuery('.modalHeaderSuccess, .modalHeaderError').hide();

        if (jQuery('#refundAmountNew').val() > jQuery('#refundAmount').val()) {
            jQuery('.modalHeaderError').text('Fehler: Der Rückerstattungsbetrag ist höher als die Gesamtsumme.');
            jQuery('.modalHeaderError').show();
            window.setTimeout(function(){
                jQuery('.modalHeaderSuccess, .modalHeaderError').hide();
            }, 4000);
            return;
        }

        let formData = new FormData()
        formData.append("refundOrderId", jQuery('#refundOrderId').val());
        formData.append("refundTransactionId", jQuery('#refundTransactionId').val());
        formData.append("refundAmountNew", jQuery('#refundAmountNew').val());
        formData.append("refundComment", jQuery('#refundComment').val());

        fetch('admin.php?page=abilita_settings_page&tab=PaymentRefund', {
            method: "POST",
            body:  formData
        })
            .then(function(response){
                return response.json();
            })
            .then(function(data){
                if (data.status == 'error' && data.code > 0) {
                    jQuery('.modalHeaderError').text('Es ist ein Fehler aufgetreten (Code: '+data.code+')');
                    jQuery('.modalHeaderError').show();
                } else if (data.status == 'invalid' && data.code > 0) {
                    jQuery('.modalHeaderError').text(data.message);
                    jQuery('.modalHeaderError').show();
                } else {
                    jQuery('.modalHeaderSuccess').show();
                    jQuery('#modalRefundSubmit').hide();

                    window.setTimeout(function(){
                        jQuery('#transactionForm').submit();
                    }, 2000);
                }

                window.setTimeout(function(){
                    jQuery('.modalHeaderSuccess, .modalHeaderError').hide();
                }, 3000);
            });
    });
}