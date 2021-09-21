<div class="row">
    <?php if ($environment == 'test') { ?>
    <div class="col-md-12">
        <p class="test_instructions"><? echo $text_test_instructions; ?></p>
    </div>
    <?php } ?>
    <div class="col-md-12">
        <p class="instructions"><? echo $text_instructions; ?></p>
    </div>
</div>

<input
        type="hidden"
        id="invoice-hash"
        name="invoice-hash"
        value="<? echo $invoice_hash; ?>"
/>

<div class="buttons">
    <div class="pull-right">
        <input
                type="button"
                value="<? echo $button_confirm; ?>"
                id="button-confirm"
                data-loading-text="<? echo $text_loading; ?>"
                class="btn btn-primary"
        />
    </div>
</div>
<script type="text/javascript">
    $('#button-confirm').on('click', function() {
        $.ajax({
            url: 'index.php?route=extension/payment/xendit/process_payment',
            dataType: 'json',
            type: 'POST',
            data: { invoice_hash: $('#invoice-hash').val() },
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
                    location = json['redirect'] + '#' + $('#invoice-hash').val();
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
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