<h3><?php echo $text_payment_info; ?></h3>
<div class="alert alert-success" id="xendit_transaction_msg" style="display:none;"></div>
<table class="table table-striped table-bordered">
  <tr>
    <td><?php echo $text_transaction_amount; ?></td>
    <td><?php echo $xendit_order['amount_formatted']; ?></td>
  </tr>
  <tr>
    <td><?php echo $text_refunded_amount; ?></td>
    <td id="total_refunded_amount"><?php echo $xendit_order['refunded_amount_formatted']; ?></td>
  </tr>
</table>

<h3><?php echo $text_refund_info; ?></h3>
<input type="text" width="10" id="refund_amount" /></br>
<a class="button btn btn-primary" id="btn_refund"><?php echo $button_refund; ?></a>
<span class="btn btn-primary" id="img_loading_refund" style="display:none;"><i class="fa fa-cog fa-spin fa-lg"></i></span>
<script type="text/javascript">
    $("#btn_refund").click(function () {
      if (confirm('<?php echo $text_confirm_refund; ?>')) {
        $.ajax({
          type: 'POST',
          dataType: 'json',
          data: {'order_id': <?php echo $order_id; ?>, 'amount': $('#refund_amount').val()},
          url: 'index.php?route=extension/payment/xendit/refund&token=<?php echo $token; ?>',
          beforeSend: function () {
            $('#btn_refund').hide();
            $('#refund_amount').hide();
            $('#img_loading_refund').show();
            $('#xendit_transaction_msg').hide();
          },
          success: function (data) {
            if (data.error == false) {
              html = '';
              html += '<tr>';
              html += '<td class="text-left">' + data.data.created + '</td>';
              html += '<td class="text-left">refund</td>';
              html += '<td class="text-left">' + data.data.amount + '</td>';
              html += '</tr>';

              $('#total_refunded_amount').text(data.data.refunded_amount_formatted);

              if (data.data.refund_status == 1) {
                $('.refund_text').text('<?php echo $text_yes; ?>');
              } else {
                $('#btn_refund').show();
                $('#refund_amount').val(0.00).show();
              }

              if (data.msg != '') {
                $('#xendit_transaction_msg').empty().html('<i class="fa fa-check-circle"></i> ' + data.msg).fadeIn();
              }
            }
            if (data.error == true) {
              alert(data.msg);
              $('#btn_refund').show();
            }

            $('#img_loading_refund').hide();
          }
        });
      }
    });
</script>