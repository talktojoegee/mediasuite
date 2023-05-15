<?php global $router, $match,$sap_common; ?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo $sap_common->lang('SAP_NAME'); ?></title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.7 -->
        <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/bootstrap.min.css'; ?>">
        <link rel="icon" href="<?php echo SAP_SITE_URL . '/assets/images/favicon.png'; ?>" type="image/png" sizes="32x32">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/font-awesome.min.css'; ?>">
        <!-- Theme style -->
        <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/mingle-social-auto-poster.min.css'; ?>">
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->
        <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/_all-skins.min.css'; ?>">
        <!-- Login Page CSS -->
        <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/mingle-login.css'; ?>">
        <!-- Google Font -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    </head>
    <body class="hold-transition login-page">
        <!-- login -->
        <div class="login-box">
            <div class="login-logo">
                <img src="<?php echo SAP_SITE_URL .'/assets/images/Mingle-Logo.svg'; ?>" class="mingle-logo" />
                <p><?php echo $sap_common->lang('SAP_NAME'); ?></p>
            </div>
            <!-- /.login-logo -->
            <div class="login-box-body forgot-password">
                <?php
                if (isset($_SESSION['flash']['messageStack']) && !empty($_SESSION['flash']['messageStack'])) {
                    foreach ($_SESSION['flash']['messageStack'] as $key => $value) {
                        if (isset($value['unique']) && !empty($value['unique'])) {
                            unset($_SESSION['flash']['messageStack'][$key]);
                        }
                    }
                }
                echo $this->flash->renderFlash();
                ?>
                <p class="login-box-msg"><?php $sap_common->e_lang('reset_your_password');?></p>
                <form action="<?php echo SAP_SITE_URL . '/forgot-password-process/'; ?>" method="post">
                    <span><?php echo $sap_common->lang('reset_password_help_text'); ?></span>
                    <div class="form-group has-feedback">
                        <input type="email" name="user_email" id="user_email" class="form-control" placeholder="<?php echo $sap_common->lang('email'); ?>" required="" autofocus="" tabindex="1">
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="row">
                        <!-- /.col -->
                        <div class="col-xs-6">
                            <button type="submit" class="btn btn-primary btn-block btn-flat"><?php echo $sap_common->lang('reset_btn_text'); ?></button>
                        </div>

                        <!-- /.col -->
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <a class="back-to-login" href="<?php echo $router->generate('login') ?>"><?php echo $sap_common->lang('back_to_login_text'); ?></a><br>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
    <!-- jQuery 3 -->
    <script src="<?php echo SAP_SITE_URL . '/assets/js/jquery.min.js'; ?>"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="<?php echo SAP_SITE_URL . '/assets/js/bootstrap.min.js'; ?>"></script>
</body>
</html>