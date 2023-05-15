<?php global $router, $match, $sap_common; ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo SAP_NAME; ?></title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="icon" href="<?php echo SAP_SITE_URL . '/assets/images/favicon.png'; ?>" type="image/png" sizes="32x32">
        <!-- Bootstrap 3.3.7 -->
        <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/bootstrap.min.css'; ?>">
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
                <img src="<?php echo SAP_SITE_URL .'/assets/images/logo_white.png'; ?>" class="mingle-logo" />
                <p><?php echo SAP_NAME; ?></p>
            </div>
            <!-- /.login-logo -->
            <div class="login-box-body">

                <?php 
                if(isset($_SESSION['flash']['messageStack']) && !empty($_SESSION['flash']['messageStack'])){
                    foreach ($_SESSION['flash']['messageStack'] as $key => $value){
                        if( isset($value['unique']) && !empty($value['unique']) ){
                            unset($_SESSION['flash']['messageStack'][$key]);
                        }
                    }
                }
                echo $this->flash->renderFlash();
                ?>
                <?php
            
                $user_email = $password = '';
                if ( isset( $_COOKIE['user_login'] ) ) {
                    $user_data = unserialize( $_COOKIE['user_login'] );
                    $user_email = $user_data['user_email'];
                    $password = base64_decode($user_data['password']);
                }
                $checked = ( !empty( $user_email ) && !empty( $password ) ) ? 'checked="checked"' : '';
                ?>
                <p class="login-box-msg">Sign in to start your session</p>
                <form action="<?php echo SAP_SITE_URL . '/login_user/'; ?>" method="post">
                    <div class="form-group has-feedback">
                        <input type="email" name="user_email" id="user_email" class="form-control" oninvalid="this.setCustomValidity('Please Enter valid email')" oninput="setCustomValidity('')" placeholder="<?php echo $sap_common->lang('email'); ?>" required="required" autofocus="" tabindex="1" value="<?php echo ( !empty( $user_email ) ) ? $user_email : ''; ?>">
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" name="user_password" oninvalid="this.setCustomValidity('please enter the password')" oninput="setCustomValidity('')" value="<?php echo ( !empty( $password ) ) ? $password : ''; ?>" class="form-control" placeholder="<?php echo $sap_common->lang('password'); ?>" required="" tabindex="2">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    
                    <div class="row">
                        <div class="col-xs-8">            
                        </div>
                        <!-- /.col -->
                        <div class="col-xs-4">
                            <button type="submit" class="btn btn-primary btn-block btn-flat"><?php echo $sap_common->lang('sign_in'); ?></button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
                <a href="<?php echo $router->generate('user_signup')?>"><?php echo $sap_common->lang('sign_up'); ?> | </a> 
                <a href="<?php echo $router->generate('forgot_password') ?>"><?php echo $sap_common->lang('forgot_password'); ?></a>
                
                <br>
            </div>
            <!-- /.login-box-body -->
        </div>
    </body>
    <!-- jQuery 3 -->
    <script src="<?php echo SAP_SITE_URL . '/assets/js/jquery.min.js'; ?>"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="<?php echo SAP_SITE_URL . '/assets/js/bootstrap.min.js'; ?>"></script>
</body>
</html>