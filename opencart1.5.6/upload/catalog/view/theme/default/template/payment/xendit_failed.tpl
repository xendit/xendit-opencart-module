<?php echo $header; ?>
<div class="container" style="text-align: center;">
    <h1>Xendit payment failed!</h1>
    <p><?php echo $text_failure; ?></p>
    <a href="<?php echo $checkout_url; ?>">
        <button class="btn btn-primary">Back to cart</button>
    </a>
    <br><br>
</div>
<?php echo $footer; ?>