<?php
global $router, $match, $sap_common; 

$payment_gateway = $this->setting->get_options('payment_gateway');
$stripe_label    = $this->setting->get_options('stripe_label');
$default_payment_method = $this->setting->get_options('default_payment_method');

?>
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
        
        <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/style.css'; ?>">
        
        <!-- Google Font -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

        <script>
            var SAP_SITE_URL = "<?php echo SAP_SITE_URL; ?>";
        </script>
    </head>
    <body class="hold-transition login-page">
        <!-- login -->
        <div class="thank-you-box">
            <div class="login-logo">
                <img src="<?php echo SAP_SITE_URL .'/assets/images/Mingle-Logo.svg'; ?>" class="mingle-logo" />
            </div>
            <?php echo $this->flash->renderFlash(); ?>            
            <!-- /.login-logo -->
            <div class="signup-box-body">                
               <div class="box box-primary">
                    <div class="box-header text-center">
                        <h2 class="box-title"><?php echo $sap_common->lang('subscription_details'); ?></h2>
                    </div>                        
                    <div class="box-body">
                    	<div id="plan_result">
                            <table class="table table-striped table-bordered">  
                                <tbody>
                                    <tr>
                                        <th scope="row"><?php echo $sap_common->lang('customer_name'); ?></th>
                                        <td><?php echo $subscription_details->customer_name ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php echo $sap_common->lang('membership_level'); ?></th>
                                        <td><?php echo $subscription_details->name ?></td>
                                    </tr>
                                    <tr>
                                      <th scope="row"><?php echo $sap_common->lang('allowed_network'); ?></th>
                                      <td>
                                        <?php
                                            $networks = unserialize($subscription_details->networks);
                                            if( !empty( $networks ) ){
                                                foreach ($networks as $key => $network) {
                                                    $li_content .= sap_get_networks_label($network).', ';
                                                }
                                                echo rtrim($li_content,", ");  
                                            }
                                        ?>
                                         </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php echo $sap_common->lang('membership_status'); ?></th>
                                        <td><?php echo get_membership_status_label($subscription_details->membership_status) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php echo $sap_common->lang(''); ?>Recurring</th>
                                        <td><?php 
                                            if( $subscription_details->recurring == '1'){
                                                echo $sap_common->lang('yes');
                                            }else{
                                                echo $sap_common->lang('no');
                                            }
                                        ?>
                                            
                                        </td>                                 
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php echo $sap_common->lang('expiration_date'); ?></th>
                                        <td><?php echo sap_get_membership_expiration_date( $subscription_details->expiration_date) ?></td>
                                    </tr>                                    
                                  </tbody>
                                </table>                          
                                </div>
                            </div>
                            <div class="login-btn-wrap text-center box-footer">
		                        <a class="text-center btn btn-primary" href="<?php echo SAP_SITE_URL?>"><?php echo $sap_common->lang('continue_login'); ?></a>
		                    </div>
                    </div>
                </div>
            </div>
            <!-- /.signup-box-body -->
        </div>
    </body>
    <!-- jQuery 3 -->
    <script src="<?php echo SAP_SITE_URL . '/assets/js/jquery.min.js'; ?>"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="<?php echo SAP_SITE_URL . '/assets/js/bootstrap.min.js'; ?>"></script>
    <script src="<?php echo SAP_SITE_URL . '/assets/js/jQuery-validate/jquery.validate.js' ?>"></script>
    <script src="<?php echo SAP_SITE_URL . '/assets/js/mingle-login.js'; ?>"></script>


<script type="text/javascript" src="https://js.stripe.com/v1/"></script>
<script type="text/javascript" src="<?php echo SAP_SITE_URL .'/assets/js/stripe-processing.js' ?>"></script>
</body>
</html>