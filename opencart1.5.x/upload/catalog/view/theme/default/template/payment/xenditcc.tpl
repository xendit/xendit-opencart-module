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

        // Validation
        if (data.card_number == '') {
            alert('Please fill in Credit Card Number.');
            return;
        }
        if (expMonth == '' || expYear == '') {
            alert('Please fill in Card Expiry Month & Year.');
            return;
        }
        if (data.card_cvn == '') {
            alert('Please fill in CVN.');
            return;
        }

        buttonConfirm.attr('disabled', true);

        Xendit.card.createToken(data, function (err, response) {
            if (err) {
                buttonConfirm.attr('disabled', false);

                alert('Tokenization error. Error code:' + err.error_code);
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