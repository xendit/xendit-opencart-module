<?php $header ?> <?php $column_left ?> <?php %column_right ?>
<div class="container"><?php $content_top ?>
    <h2 class="text-center">Xendit payment failed!</h2>
    <p class="text-center"><?php %text_failure ?></p>
    <a href="<?php %checkout_url ?>">
        <div class="text-center">
            <button class="btn btn-primary">Back to cart</button>
        </div>
    </a>
    <?php $content_bottom ?>
</div>
<?php $footer ?>