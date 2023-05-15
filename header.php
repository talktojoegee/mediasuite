<?php global $router, $match; 
$user_id = sap_get_current_user_id();
if( !empty( $user_id ) && isset( $this->settings ) && !empty( $this->settings ) ){
    $user_options = $this->settings->get_user_setting('sap_general_options', $user_id);

    $timezone = (!empty($user_options['timezone']) ) ? $user_options['timezone'] : ''; // user timezone

    //Update time zone based on user setting
    if (!empty($timezone)) { // set default timezone
        date_default_timezone_set($timezone);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Mingle - <?php echo SAP_NAME; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="icon" href="<?php echo SAP_SITE_URL . '/assets/images/favicon.png'; ?>" type="image/png" sizes="32x32">

    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/bootstrap.min.css'; ?>">
    <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/bootstrap-datetimepicker.css'; ?>">
    <?php
    if ($match['name'] == 'report' ) {?>
        <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/bootstrap-datepicker.min.css'; ?>">
        <?php 
    } ?>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/font-awesome.min.css'; ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/mingle-social-auto-poster.min.css'; ?>">
    
    <?php
    if ($match['name'] == 'addpost' || $match['name'] == 'viewpost' ||  $match['name'] == 'settings' || $match['name'] == 'quick_posts' || $match['name'] == 'quick_posts_with_id') {
        echo '<link rel="stylesheet" href="'.SAP_SITE_URL.'/assets/css/select2.min.css">';
        echo '<link rel="stylesheet" href="'.SAP_SITE_URL.'/assets/css/fileinput.css">';
    } ?>
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
       <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/_all-skins.min.css'; ?>">
       <!-- iCheck -->
       <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/flat/blue.css'; ?>">
       <!-- bootstrap wysihtml5 - text editor -->

       <!-- style -->
       <link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/style.css'; ?>">
       <!-- Google Font -->
       <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

       <!-- dataTable Start -->
       <link rel="stylesheet" type="text/css" href="<?php echo SAP_SITE_URL . '/assets/dataTables/css/dataTables.bootstrap.min.css'; ?>">
       <!-- dataTable End -->
       <?php
       $settings_object      = new SAP_Settings();
       $sap_general_options = $settings_object->get_options('sap_general_options');
       if( !empty($sap_general_options['timezone']) ){
           echo '<script> var SapTimeZone = "'.$sap_general_options['timezone'].'"; </script>';
       }else {
           echo '<script> var SapTimeZone = "'.date_default_timezone_get().'"; </script>';
       } ?>

       <script>
        var SAP_SITE_URL = "<?php echo SAP_SITE_URL; ?>";
    </script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php sap_check_user_payment_status() ?>
        <header class="main-header">
            <div class="pull-left image logo-class">
              <img src="<?php echo SAP_SITE_URL.'/assets/images/wp-logo-white.svg'; ?>" alt="User Image">
          </div>
          <!-- Logo -->
          <a href="<?php echo SAP_SITE_URL; ?>/posts/" class="logo sap-logo-text">
            <span class="logo-mini"><b>SAP</b></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><?php echo SAP_NAME; ?></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <?php
            $user_details = isset( $_SESSION['user_details'] ) ? $_SESSION['user_details'] : array(); ?>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="" class="nav-link dropdown-toggle" data-toggle="dropdown" >
                            <i class="fa fa-user-circle"></i>
                            <span class="hidden-xs"><?php echo $user_details['first_name'] . ' ' . $user_details['last_name']; ?></span>
                            <i class="fa fa-angle-down m-l-1"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                          <li class="dropdown-item"><a href="<?php echo SAP_SITE_URL . '/my-account/'; ?>"><i class="fa fa-users"></i>My Account</a></li>
                          <li class="dropdown-item"><a href="<?php echo SAP_SITE_URL . '/logout/'; ?>"><i class="fa fa-sign-out"></i>Signout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>