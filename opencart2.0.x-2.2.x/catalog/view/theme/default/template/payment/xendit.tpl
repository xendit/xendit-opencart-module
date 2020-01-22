<div class="row">
    <?php if ($environment == 'test'): ?>
    <div class="col-md-12">
        <p class="test_instructions"><?php echo $text_test_instructions ?></p>
    </div>
    <?php endif; ?>
    <div class="col-md-12">
        <p class="instructions"><?php echo $text_instructions ?></p>
    </div>
</div>

<input
        type="hidden"
        id="invoice-hash"
        name="invoice-hash"
        value="<?php echo $invoice_hash ?>"
/>

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
<script type="text/javascript"><!--
    $('#button-confirm').on('click', function() {
        $.ajax({
            url: 'index.php?route=payment/xendit/process_payment',
            dataType: 'json',
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
    //--></script>

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