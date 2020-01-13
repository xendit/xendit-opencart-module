<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
            <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
    </ul>

    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-xendit" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary">
                    <i class="fa fa-save"></i>
                </button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default">
                    <i class="fa fa-reply"></i>
                </a>
            </div>
            <h1><?php echo $heading_title; ?></h1>
        </div>
    </div>

    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?> </h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="POST" enctype="multipart/form-data" id="form-xendit" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-xendit-status"> <?php echo $entry_status; ?> </label>
                        <div class="col-sm-10">
                            <select name="xendit_status" id="input-xendit-status" class="form-control">
                                <?php if ($xendit_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-xendit-mode"> <?php echo $entry_mode ?> </label>
                        <div class="col-sm-10">
                            <select name="xendit_environment" id="input-xendit-mode" class="form-control">
                                <?php if ($xendit_environment == 'live') { ?>
                                    <option value="live" selected="selected"><?php echo $text_live_mode ?></option>
                                    <option value="test"><?php echo $text_test_mode ?></option>
                                <?php } elseif ($xendit_environment == 'test') { ?>
                                    <option value="live"><?php echo  $text_live_mode ?></option>
                                    <option value="test" selected="selected"><?php echo $text_test_mode ?></option>
                                <?php } else { ?>
                                    <option value="live"><?php echo $text_live_mode ?></option>
                                    <option value="test"><?php echo $text_test_mode ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($xendit_environment == 'live') { ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-xendit-live-public-key"> <?php echo $entry_public_key; ?> </label>
                        <div class="col-sm-10">
                            <input
                                    type="password"
                                    name="xendit_live_public_key"
                                    value="<?php echo $xendit_live_public_key; ?>"
                                    placeholder="<?php echo $entry_public_key; ?>"
                                    id="input-xendit-live-public-key"
                                    class="form-control"
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-xendit-live-secret-key"> <?php echo $entry_secret_key; ?> </label>
                        <div class="col-sm-10">
                            <input
                                    type="password"
                                    name="xendit_live_secret_key"
                                    value="<?php echo $xendit_live_secret_key; ?>"
                                    placeholder="<?php echo $entry_secret_key; ?>"
                                    id="input-xendit-live-secret-key"
                                    class="form-control"
                            />
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-xendit-test-public-key"> <?php echo $entry_public_key; ?> </label>
                        <div class="col-sm-10">
                            <input
                                    type="password"
                                    name="xendit_test_public_key"
                                    value="<?php echo $xendit_test_public_key; ?>"
                                    placeholder="<?php echo $entry_public_key; ?>"
                                    id="input-xendit-test-public-key"
                                    class="form-control"
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-xendit-test-secret-key"> <?php echo $entry_secret_key; ?> </label>
                        <div class="col-sm-10">
                            <input
                                    type="password"
                                    name="xendit_test_secret_key"
                                    value="<?php echo $xendit_test_secret_key; ?>"
                                    placeholder="<?php echo $entry_secret_key; ?>"
                                    id="input-xendit-test-secret-key"
                                    class="form-control"
                            />
                        </div>
                    </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @media (min-width: 768px) {
        #button-register, #img_loading_register {
            position: relative;
            left: 5px;
        }
    }
</style>

<?php echo $footer; ?>