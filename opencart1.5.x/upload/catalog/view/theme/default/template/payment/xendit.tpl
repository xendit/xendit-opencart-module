<div class="warning">
    <p class="test_instructions"><?php if($environment == 'test') echo $text_test_instructions; ?></p>
    <p class="instructions"><?php echo $text_instructions; ?></p>
</div>

<input
    type="hidden"
    id="invoice-hash"
    name="invoice-hash"
    value="<?php echo $invoice_hash; ?>"
/>

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

<script type="text/javascript">
    $('#button-confirm').on('click', function() {
        $.ajax({
            url: 'index.php?route=payment/xendit/process_payment',
            dataType: 'json',
            beforeSend: function() {
                $('#button-confirm').attr('disabled', true);
            },
            complete: function() {
                $('#button-confirm').attr('disabled', false);
            },
            success: function(json) {
                if (json['error']) {
                    $('#button-confirm').attr('disabled', false);
                    alert('Error: ' + json['error']);
                }

                if (json['redirect']) {
                    location = json['redirect'] + '#' + $('#invoice-hash').val();
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('#button-confirm').attr('disabled', false);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
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