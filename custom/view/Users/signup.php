<?php
 global $router, $match, $sap_common; 

$payment_gateway = array();
$payment_gateway = $this->setting->get_options('payment_gateway');
$stripe_label = $this->setting->get_options('stripe_label');
$default_payment_method = $this->setting->get_options('default_payment_method');
$stripe_test_mode = $this->setting->get_options('stripe_test_mode');

$plans = $this->get_plans();

$register_data = isset($_SESSION['register_data']) ? $_SESSION['register_data'] : array();

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
        <div class="signup-box">
            <div class="login-logo">
                <img src="<?php echo SAP_SITE_URL .'/assets/images/logo_white.png'; ?>" class="mingle-logo" />
                <p><?php echo SAP_NAME; ?></p>
            </div>


             <!-- /.login-logo -->
            <div class="signup-box-body">                
                <form class="add-member-form" name="new-member" id="add-member" method="POST" enctype="multipart/form-data" action="<?php echo SAP_SITE_URL . '/save_user/'; ?>" novalidate="novalidate">
                    <div class="box box-primary">

                        <div class="signup-error">
                            <?php echo $this->flash->renderFlash();  ?> 
                        </div>

                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $sap_common->lang('sign_up'); ?></h3>
                        </div>                       

                        <input type="hidden" name="sap_role" value="user">
                        <input type="hidden" name="sap_notify" value="yes">

                        <div class="box-body">
                            <div class="row sap-mt-1_5">
                                <div class="col-md-4 form-group">
                                    <div class="row">
                                        <label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('first_name'); ?><span class="astric">*</span></label>
                                        <div class="col-sm-8 col-md-9">
                                            <input type="text" class="form-control" name="sap_firstname" value="<?php echo isset( $register_data['sap_firstname'] )  ? $register_data['sap_firstname'] : '' ?>" id="sap_firstname"  placeholder="<?php echo $sap_common->lang('first_name'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <div class="row">
                                        <label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('last_name'); ?></label>
                                        <div class="col-sm-8 col-md-9">
                                            <input type="text" class="form-control" name="sap_lastname" id="sap_lastname" value="<?php echo isset( $register_data['sap_lastname'] )  ? $register_data['sap_lastname'] : '' ?>" placeholder="<?php echo $sap_common->lang('last_name'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <div class="row">
                                        <label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('email'); ?><span class="astric">*</span></label>
                                        <div class="col-sm-8 col-md-9">
                                            <input type="text" class="form-control" name="sap_email" id="sap_email" value="<?php echo isset( $register_data['sap_email'] )  ? $register_data['sap_email'] : '' ?>" placeholder="<?php echo $sap_common->lang('email'); ?>">
                                        </div>
                                    </div>
                                </div>  
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <div class="row">
                                        <label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('password'); ?><span class="astric">*</span></label>
                                        <div class="col-sm-8 col-md-9">
                                            <input type="password" class="form-control" name="sap_password" id="sap_password" value="" placeholder="<?php echo $sap_common->lang('password'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <div class="row">
                                        <label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('re_password'); ?><span class="astric">*</span></label>
                                        <div class="col-sm-8 col-md-9">
                                            <input type="password" class="form-control" name="sap_repassword" id="sap_repassword" value="" placeholder="<?php echo $sap_common->lang('re_password_plh'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php  if( !empty($plans)){
                                
                             ?>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <div class="row">
                                        <?php 
                                        if( count($plans) > 1 ){ ?>
                                            <div class="alert alert-success w-100">
                                                <h4 class="text-center text-uppercase"><?php echo $sap_common->lang('choose_membership'); ?></h4>
                                            </div>

                                    <?php } ?>
                                        <div class="col-sm-8 col-md-9">
                                        <?php 
                                        
                                        
                                        
                                        if( !empty($plans) ){

                                            
                                            foreach( $plans as $key => $plan ){

                                               
                                                $unlimited_class  = '';
                                                if( $plan->subscription_expiration_days == '' || $plan->subscription_expiration_days == '0'  ){
                                                    $unlimited_class = 'unlimited_plan';    
                                                }

                                                if($plan->price == 0 || $plan->price == ''){
                                                    $class = 'price_zero_cls';
                                                }
                                                else{
                                                    $class = 'price_not_zero_cls';
                                                }
                                            ?>
                                            <div class="form-check">
                                                  <input class="form-check-input plan <?php echo $class .' '. $unlimited_class ?>" type="radio" name="sap_plan" value="<?php echo $plan->id ?>" id="<?php echo $plan->id ?>" >
                                                  <label class="form-check-label" for="<?php echo $plan->id ?>">
                                                    <?php                                                     
                                                    $plan_price = 'Free';
                                                    
                                                    if( !empty( $plan->price)){
                                                        $plan_price = "$".$plan->price;
                                                    }

                                                    $subscription_expiration_days = 'Never';
                                                    if( !empty( $plan->subscription_expiration_days)){
                                                        $subscription_expiration_days = $plan->subscription_expiration_days .' Days';
                                                    }

                                                    echo $plan->name .' - ' . $plan_price .' - '. $subscription_expiration_days ?> 
                                                    
                                                  </label>
                                            </div>
                                        <?php } } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="plan_result">
                            </div>


                            <div class="row payment_method_cls">
                                <div class="col-md-12 form-group">
                                    <div class="row">
                                        <label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('choose_payment'); ?></label>
                                        <div class="col-sm-8 col-md-9 gateway_checkbox">
                                            <?php

                                            $stripe_label = !empty($stripe_label) ? $stripe_label : 'Stripe';

                                            if(!empty($payment_gateway)){

                                                $payment_gateway = explode(',',$payment_gateway);
                                                
                                                foreach($payment_gateway as $data){
                                                ?>
                                                <div class="form-check">
                                                      <input class="form-check-input payment-gateway" type="radio" name="gateway_type" value="<?php echo $data ?>" id="payment_<?php echo $data ?>" <?php if($data == $default_payment_method){ echo 'checked'; } ?>>
                                                      <label class="form-check-label" for="payment_<?php echo $data ?>">
                                                        <?php if($data == 'stripe'){ echo $stripe_label; }else{ echo ucfirst($data); } ?>
                                                      </label>
                                                </div>
                                                <?php 
                                                }
                                            }
                                            else{
                                                ?>
                                                <div class="alert alert-danger" role="alert">
                                                <?php echo $sap_common->lang('signup_payment_help_text'); ?>
                                                </div>
                                                <?php 
                                            }
                                            ?>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php 
                            $payment_gateway_array = !empty($payment_gateway) ? $payment_gateway : array();


                            if( in_array( 'stripe', $payment_gateway_array)){


                                if( $stripe_test_mode == 'yes'){  
                                ?>

                                <div class="stripe-payment-fields" style="display:<?php if('stripe' == $default_payment_method){ echo 'block'; }else{ echo 'none'; } ?>;" >
                                      <div class="row">
                                            <div class="col-xs-12 col-md-12">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                         <?php echo sprintf($sap_common->lang('signup_test_help_text'),'<h3 class="panel-title">','</h3>','<span>','</span>'); ?>
                                                    </div>

                                                <div class="panel-body">
                                                    <?php echo sprintf($sap_common->lang('signup_card_details'),'<p>','<b>','</b>','</p>','<p>','<b>','</b>','</p>','<p>','<b>','</b>','</p>','<p>','<a href="https://stripe.com/docs/testing#cards" target="_blank">','</a>','</p>'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>

                            <div class="stripe-payment-fields" style="display:<?php if('stripe' == $default_payment_method){ echo 'block'; }else{ echo 'none'; } ?>;" >
                                <input type="hidden" name="action" value="stripe">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-12">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><?php echo $sap_common->lang('signup_payment_details'); ?></h3>
                                                </div>

                                            <div class="panel-body">
                                                
                                                <div class="form-group">
                                                    <label for="card_number"><?php echo $sap_common->lang('signup_card_number'); ?><span class="astric">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="card-number form-control" size="20" id="card_number" value="<?php echo isset( $register_data['card_number'] )  ? $register_data['card_number'] : '' ?>" autocomplete="off" placeholder="<?php echo $sap_common->lang('signup_valid_card_number'); ?>"
                                                            required autofocus name="card_number" />
                                                        <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xs-7 col-md-7">
                                                        
                                                        <div class="form-group">
                                                            <label class="card_exp_label" for="card_exp_month"><?php echo $sap_common->lang('signup_expiry_date'); ?><span class="astric">*</span></label>
                                                            <div class="col-xs-6 col-lg-6 pl-ziro">
                                                                <input value="<?php echo isset( $register_data['card_exp_month'] )  ? $register_data['card_exp_month'] : '' ?>" type="text" class="card-expiry-month form-control" size="2" id="card_exp_month" name="card_exp_month" placeholder="<?php echo $sap_common->lang('signup_card_mm'); ?>" required />
                                                            </div>
                                                            <div class="col-xs-6 col-lg-6 ">
                                                                <input type="text" class="card-expiry-year form-control" id="card_exp_year" name="card_exp_year" value="<?php echo isset( $register_data['card_exp_year'] )  ? $register_data['card_exp_year'] : '' ?>"  size="4" placeholder="<?php echo $sap_common->lang('signup_card_yy'); ?>" required />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-5 col-md-5 pull-right">
                                                        <div class="form-group">
                                                            <label for="card_cvv"><?php echo $sap_common->lang('signup_card_cvv_num'); ?><span class="astric">*</span></label>
                                                            <input size="4" value="<?php echo isset( $register_data['card_cvv'] )  ? $register_data['card_cvv'] : '' ?>"   autocomplete="off" type="password" class="card-cvc form-control" id="card_cvv" placeholder="<?php echo $sap_common->lang('signup_card_cvv_plh'); ?>" name="card_cvv" required/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                           
                            <div class="row auto-renew-opt" style="display:<?php if('stripe' == $default_payment_method){ echo 'block'; }else{ echo 'none'; } ?>;">
                                <label class="col-sm-2 col-md-2"><?php echo $sap_common->lang('signup_auto_renew'); ?></label>
                                <div class="col-sm-8 col-md-9">                                    
                                    <input type="checkbox" class="tgl tgl-ios" name="auto_renew" id="auto_renew" value="1">
                                    <label class="tgl-btn float-right-cs-init" for="auto_renew"></label>
                                </div>
                               
                            </div>
                        <?php }
                        }
                        if( !empty($payment_gateway) ){ ?>
                            <div class="sap-mt-1 col-md-12 form-group">
                                <input type="hidden" name="form-submitted" value="1">
                                <button type="submit" name="sap_add_member_submit" class="btn btn-primary"><?php echo $sap_common->lang('signup_register_btn'); ?></button>
                            </div>
                            <a class="text-center login-link" href="<?php echo SAP_SITE_URL ?>"><?php echo $sap_common->lang('back_to_login_text'); ?></a>
                        </div>
                    <?php } ?>
                    </div>
                </form>
            </div>

            <?php
                unset($_SESSION['register_data']);
                unset($register_data);
            ?>
            <!-- /.signup-box-body -->
        </div>
    </body>
    <!-- jQuery 3 -->
    <script src="<?php echo SAP_SITE_URL . '/assets/js/jquery.min.js'; ?>"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="<?php echo SAP_SITE_URL . '/assets/js/bootstrap.min.js'; ?>"></script>
    <script src="<?php echo SAP_SITE_URL . '/assets/js/jQuery-validate/jquery.validate.js' ?>"></script>
    <script src="<?php echo SAP_SITE_URL . '/assets/js/mingle-login.js'; ?>"></script>
<?php
    $publish_key = 'pk_test_bkY4rAXSqiPVNOgXUmhDxpFy';
?>

<script type="text/javascript">
    var stripe_publishable_key = "<?php echo $publish_key;?>";    
    </script>
<script type="text/javascript" src="https://js.stripe.com/v1/"></script>
<script type="text/javascript" src="<?php echo SAP_SITE_URL .'/assets/js/stripe-processing.js' ?>"></script>
</body>
</html>