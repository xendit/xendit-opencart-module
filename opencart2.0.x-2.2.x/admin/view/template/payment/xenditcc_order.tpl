<h2><?php echo $text_payment_info; ?></h2>
<div class="alert alert-success" id="xendit_transaction_msg" style="display:none;"></div>
<table class="table table-striped table-bordered">
  <tr>
	<td><?php echo $text_order_total; ?></td>
	<td><?php echo $xenditcc_order['total_formatted']; ?></td>
  </tr>
  <tr>
	<td><?php echo $text_order_available; ?></td>
	<td id="total_available_formatted"><?php echo $xenditcc_order['total_available_formatted']; ?></td>
  </tr>
  <tr>
	<td><?php echo $text_refund_history; ?>:</td>
	<td>
	  <table class="table table-striped table-bordered" id="xendit_transactions">
		<thead>
		  <tr>
			<td class="text-left"><strong><?php echo $text_column_date_added; ?></strong></td>
			<td class="text-left"><strong><?php echo $text_column_amount; ?></strong></td>
		  </tr>
		</thead>
		<tbody>
		  <?php foreach ($xenditcc_order['refund_history'] as $refund_history) { ?>
			  <tr>
				<td class="text-left"><?php echo $refund_history['date_added']; ?></td>
				<td class="text-right"><?php echo $refund_history['amount_formatted']; ?></td>
			  </tr>
		  <?php } ?>
		</tbody>
	  </table>
	</td>
  </tr>
  <tr>
	<td><?php echo $text_total_refunded; ?></td>
	<td id="total_refunded_formatted"><?php echo $xenditcc_order['total_refunded_formatted']; ?></td>
  </tr>
  <tr>
	<td><?php echo $text_refund; ?></td>
	<td>
        <?php if ($xenditcc_order['total_available'] > 0) { ?>
			<label id="refund_label">
            	Refund amount <input type="text" width="10" id="refund_amount" /> <br/>
			</label>
            <button class="button btn btn-primary" id="btn_refund_manual"><?php echo $btn_refund_manual; ?></button>
            <button class="button btn btn-primary" id="btn_refund_xendit"><?php echo $btn_refund_xendit; ?></button>
            <span class="btn btn-primary" id="img_loading_refund" style="display:none;"><i class="fa fa-cog fa-spin fa-lg"></i></span>
        <?php } ?>
	</td>
  </tr>
  
</table>

<script type="text/javascript">
	$("#btn_refund_manual").click(function () {
		if (confirm('<?php echo $text_confirm_refund ?>')) {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {'order_id': <?php echo $order_id; ?>},
				url: 'index.php?route=payment/xenditcc/refundManual&token=<?php echo $token; ?>',
				beforeSend: function () {
					$('#btn_refund_manual').hide();
					$('#btn_refund_xendit').hide();
					$('#refund_label').hide();
					$('#img_loading_refund').show();
				},
				success: function (data) {
					if (data.error == false) {
						html = '';
						html += '<tr>';
						html += '<td class="text-left">' + data.data.created + '</td>';
						html += '<td class="text-right">' + data.data.amount + '</td>';
						html += '</tr>';

						$('#xendit_transactions').append(html);
						
						$('#total_available_formatted').text(data.data.total_available_formatted);
						$('#total_refunded_formatted').text(data.data.total_refunded_formatted);
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
	
	$("#btn_refund_xendit").click(function () {
		if (confirm('<?php echo $text_confirm_refund ?>')) {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {'order_id': <?php echo $order_id; ?>, 'refund_amount': $('#refund_amount').val()},
				url: 'index.php?route=payment/xenditcc/refundXendit&token=<?php echo $token; ?>',
				beforeSend: function () {
					$('#btn_refund_manual').hide();
					$('#btn_refund_xendit').hide();
					$('#refund_label').hide();
					$('#img_loading_refund').show();
				},
				success: function (data) {
					if (data.error == false) {
						html = '';
						html += '<tr>';
						html += '<td class="text-left">' + data.data.created + '</td>';
						html += '<td class="text-right">' + data.data.amount + '</td>';
						html += '</tr>';

						$('#xendit_transactions').append(html);
						
						$('#total_available_formatted').text(data.data.total_available_formatted);
						$('#total_refunded_formatted').text(data.data.total_refunded_formatted);
					}
					if (data.error == true) {
						alert(data.msg);
						$('#btn_refund_manual').show();
						$('#btn_refund_xendit').show();
						$('#refund_label').show();
						$('#img_loading_refund').hide();
					}

					$('#img_loading_refund').hide();
				}
			});
	  	}
	});
</script>