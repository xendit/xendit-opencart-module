<?php echo $header; ?> <?php echo $column_left; ?> <?php echo $column_right; ?>
<div class="container"><?php echo $content_top; ?>
    <h2 class="text-center">Xendit payment failed!</h2>
    <p class="text-center"><?php echo $text_failure; ?></p>
    <p class="text-center"><?php echo $message; ?></p>
    <a href="<?php echo $checkout_url; ?>">
        <div class="text-center">
            <button class="btn btn-primary">Back to cart</button>
        </div>
    </a>
    <?php echo $content_bottom; ?>
</div>
<?php echo $footer; ?>