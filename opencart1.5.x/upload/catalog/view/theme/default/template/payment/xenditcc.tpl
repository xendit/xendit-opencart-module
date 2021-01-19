<div class="warning">
    <p class="test_instructions"><?php if($environment == 'test') echo $text_test_instructions; ?></p>
    <p class="instructions"><?php echo $text_instructions; ?></p>
</div>

<input
    type="hidden"
    id="xendit-public-key"
    name="xendit-public-key"
    value="<?php echo $xendit_public_key; ?>"
/>

<div class="content">
    <table class="form">
        <tr>
            <td>
                <input
                    type="text"
                    id="card-number"
                    name="card-number"
                    placeholder="Credit Card Number"
                    required
                    autofocus=""
                    maxlength="16"
                    onkeyup="this.value=this.value.replace(/[^\d\/]/g,'')"
                    style="font-size: 14px; width: 170px;"
                >
            </td>
        </tr>
        <tr>
            <td>
                <input
                    type="text"
                    name="card-expiry-date"
                    id="card-expiry-date"
                    placeholder="MM/YY"
                    required
                    maxlength="5"
                    size="5"
                    onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')"
                    style="font-size: 14px; width: 170px;"
                >
            </td>
        </tr>
        <tr>
            <td>
                <input
                    type="password"
                    name="card-cvn"
                    id="card-cvn"
                    placeholder="CVN"
                    required
                    maxlength="4"
                    onkeyup="this.value=this.value.replace(/[^\d\/]/g,'')"
                    style="font-size: 14px; width: 170px;"
                >
            </td>
        </tr>
    </table>
</div>

<div class="buttons">
    <div class="right">
        <input
            type="button"
            value="<?php echo $button_confirm; ?>"
            id="button-confirm"
            class="button"
        />
    </div>
</div>

<script src="https://js.xendit.co/v1/xendit.min.js"></script>
<script type="text/javascript">
    var buttonConfirm = $('#button-confirm');
    buttonConfirm.on('click', function() {
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
            buttonConfirm.attr('disabled', false);

            alert('Card information is incomplete. Please complete it and try again. Code: 200034');
            return;
        }
        
        if (!Xendit.card.validateCardNumber(data.card_number)) {
            buttonConfirm.attr('disabled', false);

            alert('Invalid Card Number. Please make sure the card is Visa / Mastercard / JCB. Code: 200030');
            return;
        }

        if (!Xendit.card.validateCvnForCardType(data.card_cvn, data.card_number)) {
            buttonConfirm.attr('disabled', false);

            alert('The CVC/CVN that you entered is less than 3 digits. Please enter the correct value and try again. Code: 200032');
            return;
        }

        if (!Xendit.card.validateExpiry(data.card_exp_month, data.card_exp_year)) {
            buttonConfirm.attr('disabled', false);

            alert('The card expiry that you entered does not meet the expected format. Please try again by entering the 2 digits of the month (MM) and the last 2 digits of the year (YY). Code: 200031');
            return;
        }

        buttonConfirm.attr('disabled', true);

        Xendit.card.createToken(data, function (err, response) {
            if (err) {
                buttonConfirm.attr('disabled', false);

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
                    buttonConfirm.attr('disabled', true);
                },
                complete: function() {
                    buttonConfirm.attr('disabled', false);
                },
                success: function(json) {
                    if (json['error']) {
                        buttonConfirm.attr('disabled', false);
                        alert('Error: ' + json['error']);
                    }

                    if (json['redirect']) {
                        location = json['redirect'];
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    buttonConfirm.attr('disabled', false);
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });
    });
</script>

<style>
    .test_instructions {
        color: #E61616;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .instructions {
        font-size: 14px;
    }
</style>