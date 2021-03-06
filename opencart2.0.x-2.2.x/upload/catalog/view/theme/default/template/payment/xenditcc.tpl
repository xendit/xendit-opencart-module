<div class="row">
    <?php if ($environment == 'test'): ?>
    <div class="col-md-12">
        <p class="test_instructions col-md-12"><?php echo $text_test_instructions ?></p>
    </div>
    <?php endif; ?>
    <div class="col-md-12">
        <p class="instructions col-md-12"><?php echo $text_instructions ?></p>
    </div>
</div>

<input
        type="hidden"
        id="xendit-public-key"
        name="xendit-public-key"
        value="<?php echo $xendit_public_key ?>"
/>

<div class="row">
    <div class="col-md-6">
        <form class="" id="payment-form">
            <fieldset>
                <div class="form-group required col-sm-8">
                    <div>
                        <input
                                type="text"
                                class="form-control"
                                id="card-number"
                                name="card-number"
                                placeholder="Credit Card Number"
                                required
                                autofocus=""
                                maxlength="16"
                                onkeyup="this.value=this.value.replace(/[^\d\/]/g,'')"
                                style="font-size: 18px;"
                        >
                    </div>
                </div>
                <div class="row"></div>
                <div class="form-group required col-sm-3">
                    <div>
                        <input
                                type="text"
                                class="form-control"
                                name="card-expiry-date"
                                id="card-expiry-date"
                                placeholder="MM/YY"
                                required
                                maxlength="5"
                                size="5"
                                onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')"
                                style="font-size: 18px;"
                        >
                    </div>
                </div>
                <div class="form-group required col-sm-3">
                    <div>
                        <input
                                type="password"
                                class="form-control"
                                name="card-cvn"
                                id="card-cvn"
                                placeholder="CVN"
                                required
                                maxlength="4"
                                onkeyup="this.value=this.value.replace(/[^\d\/]/g,'')"
                                style="font-size: 18px;"
                        >
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>

<div class="buttons">
    <div class="pull-right">
        <input
                type="button"
                value="<?php echo $button_confirm ?>"
                id="button-confirm"
                data-loading-text="<?php echo $text_loading ?>"
                class="btn btn-primary"
        />
    </div>
</div>

<script src="https://js.xendit.co/v1/xendit.min.js"></script>
<script type="text/javascript">
    var buttonConfirm = $('#button-confirm');
    buttonConfirm.on('click', function() {
        buttonConfirm.button('loading');

        Xendit.setPublishableKey($('#xendit-public-key').val());

        var expDate = $('#card-expiry-date').val().split('/');
        var expMonth = expDate[0];
        var expYear = expDate[1];
        var data = {
            card_number: $('#card-number').val(),
            card_exp_month: expMonth,
            card_exp_year: '20' + expYear,
            card_cvn: $('#card-cvn').val(),
            is_multiple_use: true
        };

        if (!data.card_number || !data.card_cvn || !data.card_exp_month || !data.card_exp_year) {
            buttonConfirm.button('reset');

            alert('Card information is incomplete. Please complete it and try again. Code: 200034');
            return;
        }
        
        if (!Xendit.card.validateCardNumber(data.card_number)) {
            buttonConfirm.button('reset');

            alert('Invalid Card Number. Please make sure the card is Visa / Mastercard / JCB. Code: 200030');
            return;
        }

        if (!Xendit.card.validateCvnForCardType(data.card_cvn, data.card_number)) {
            buttonConfirm.button('reset');

            alert('The CVC/CVN that you entered is less than 3 digits. Please enter the correct value and try again. Code: 200032');
            return;
        }

        if (!Xendit.card.validateExpiry(data.card_exp_month, data.card_exp_year)) {
            buttonConfirm.button('reset');

            alert('The card expiry that you entered does not meet the expected format. Please try again by entering the 2 digits of the month (MM) and the last 2 digits of the year (YY). Code: 200031');
            return;
        }

        Xendit.card.createToken(data, function (err, response) {
            if (err) {
                buttonConfirm.button('reset');

                alert('We encountered an issue while processing the checkout. Please contact us. Code: 200035');

                return;
            }

            var token = response.id;

            $.ajax({
                url: 'index.php?route=payment/xenditcc/process_payment',
                type: 'post',
                dataType: 'json',
                data: {
                    token_id: token
                },
                beforeSend: function() {
                    $('#button-confirm').button('loading');
                },
                complete: function() {
                    $('#button-confirm').button('reset');
                },
                success: function(json) {
                    if (json['error']) {
                        alert(json['error']);
                    }
                    if (json['redirect']) {
                        location = json['redirect'];
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });
    });
</script>

<style>
    .test_instructions {
        color: #E61616;
        font-size: 18px;
        margin-bottom: 4px;
    }

    .instructions {
        font-size: 18px;
    }
</style>