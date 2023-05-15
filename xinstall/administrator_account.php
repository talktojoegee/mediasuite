<?php
session_start();

if ( !defined('DS') )
    define('DS', DIRECTORY_SEPARATOR);

/* Absolute path to the Social Auto Poster directory. */
if ( !defined('SAP_APP_PATH') )
    define('SAP_APP_PATH', dirname(dirname(__FILE__)) . DS );

include ( SAP_APP_PATH . DS . 'classes' . DS . 'Common.php' );
global $sap_common;
$sap_common = new Common();


require_once('include/settings.inc.php');
require_once('include/functions.inc.php');

$task = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
$passed_step = isset($_SESSION['passed_step']) ? (int) $_SESSION['passed_step'] : 0;
$focus_field = 'admin_username';
$error_msg = '';

// handle previous steps
// -------------------------------------------------
if ($passed_step >= 3) {
    // OK
} else {
    header('location: index.php');
    exit;
}

// handle form submission
// -------------------------------------------------
if ($task == 'send') {

    $admin_password     = isset($_POST['admin_password']) ? prepare_input($_POST['admin_password']) : '';
    $admin_email        = isset($_POST['admin_email']) ? prepare_input($_POST['admin_email']) : '';
    $admin_first_name = isset($_POST['admin_first_name']) ? prepare_input($_POST['admin_first_name']) : '';
    $admin_last_name = isset($_POST['admin_last_name']) ? prepare_input($_POST['admin_last_name']) : '';

    // validation here
    // -------------------------------------------------
    if ($admin_first_name == '') {
        $focus_field = 'admin_first_name';
        $error_msg = $sap_common->lang('install_admin_fname');
    } else if ($admin_last_name == '') {
        $focus_field = 'admin_last_name';
        $error_msg = $sap_common->lang('install_admin_lname');
    } else if ($admin_email == '') {
        $focus_field = 'admin_email';
        $error_msg = $sap_common->lang('install_admin_email');
    } else if ($admin_password == '') {
        $focus_field = 'admin_password';
        
        $password = prepare_input($_POST["admin_password"]);
        if (strlen($password) <= 8) {
            $error_msg = $sap_common->lang('install_admin_password');
        } elseif ($admin_email ===  $admin_password) {
            $error_msg = $sap_common->lang('install_admin_ep_not_same');
        } else {
            $error_msg = $sap_common->lang('install_admin_password_not_empty');
        }
        
    } else if ($admin_email != '' && !is_email($admin_email)) {
        $focus_field = 'admin_email';
        $error_msg = $sap_common->lang('install_admin_email_formate');
    }  else {

        if (empty($error_msg)) {
            $_SESSION['admin_password'] = $admin_password;
            $_SESSION['admin_email']    = $admin_email;
            $_SESSION['admin_first_name'] = $admin_first_name;
            $_SESSION['admin_last_name'] = $admin_last_name;

            $_SESSION['passed_step'] = 4;
            header('location: ready_to_install.php');
            exit;
        }
    }
} else {
    $admin_email    = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : '';
    $admin_password = isset($_SESSION['admin_password']) ? $_SESSION['admin_password'] : '';
    
    $admin_first_name = isset($_SESSION['admin_first_name']) ? $_SESSION['admin_first_name'] : '';
    $admin_last_name = isset($_SESSION['admin_last_name']) ? $_SESSION['admin_last_name'] : '';
}
?>	

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="author" content="ApPHP Company - Advanced Power of PHP">
        <meta name="generator" content="ApPHP EasyInstaller">
        <title><?php echo $sap_common->lang('install_installation_admin_account'); ?></title>
        <link rel="icon" href="<?php echo $_SESSION['Site_URL'] . '/assets/images/favicon.png'; ?>" type="image/png" sizes="32x32">
        <link rel="stylesheet" type="text/css" href="templates/css/styles.css" />

        <script type="text/javascript" src="js/main.js"></script>
        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>	
        <script type="text/javascript" src="language/en/js/common.js"></script>		
    </head>
    <body onload="bodyOnLoad()">
        <div id="main">
            <h1><?php echo $sap_common->lang('install_welcome_title'); ?></h1>
            <h2 class="sub-title"><?php echo $sap_common->lang('install_welcome_text'); ?></h2>

            <div id="content">
                <?php
                draw_side_navigation(4);
                ?>
                <div class="central-part">
                    <h2><?php echo $sap_common->lang('install_step_4_to_6'); ?></h2>
                    <h3><?php echo $sap_common->lang('install_admin_access_info'); ?></h3>

                    <form action="administrator_account.php" method="post">
                        <input type="hidden" name="task" value="send" />
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />

                        <?php
                        if (!empty($error_msg)) {
                            echo '<div class="alert alert-error">' . $error_msg . '</div>';
                        }
                        ?>
                        <table width="100%" border="0" cellspacing="1" cellpadding="1">			
                            <tr>
                                <td colspan="3"><span class="star">*</span> <?php echo $sap_common->lang('install_required_fields'); ?></td>
                            </tr>
                            <tr><td nowrap height="10px" colspan="3"></td></tr>
	
                            <tr>
                                <td width="250px">&nbsp;<?php echo $sap_common->lang('first_name'); ?>&nbsp;<span class="star">*</span></td>
                                <td><input name="admin_first_name" id="admin_first_name" class="form_text" size="28" maxlength="125" value="<?php echo $admin_first_name; ?>" placeholder="<?php echo $sap_common->lang('first_name'); ?>" /></td>
                            </tr>
                            <tr>
                                <td width="250px">&nbsp;<?php echo $sap_common->lang('last_name'); ?>&nbsp;<span class="star">*</span></td>
                                <td><input name="admin_last_name" id="admin_last_name" class="form_text" size="28" maxlength="125" value="<?php echo $admin_last_name; ?>" placeholder="<?php echo $sap_common->lang('last_name'); ?>" /></td>
                            </tr>
                            
                            
                            <tr>
                                <td width="250px">&nbsp;<?php echo $sap_common->lang('admin_email'); ?>&nbsp;<span class="star">*</span></td>
                                <td><input name="admin_email" id="admin_email" class="form_text" size="28" maxlength="125" value="<?php echo $admin_email; ?>" onfocus="textboxOnFocus('notes_admin_email')" onblur="textboxOnBlur('notes_admin_email')" placeholder="<?php echo $sap_common->lang('admin_email'); ?>" /></td>
                                <td rowspan="6" valign="top">					
                                    <div id="notes_admin_email" class="notes_container">
                                        <?php echo sprintf($sap_common->lang('install_admin_email_help_text'),'<h4>','</h4>','<p>','</p>'); ?>
                                        
                                    </div>
                                    <div id="notes_admin_username" class="notes_container">
                                        <?php echo sprintf($sap_common->lang('install_admin_username_help_text'),'<h4>','</h4>','<p>','</p>'); ?>
                                        
                                    </div>
                                    <div id="notes_admin_password" class="notes_container">
                                        <?php echo sprintf($sap_common->lang('install_admin_password_help_text'),'<h4>','</h4>','<p>','</p>'); ?>

                                    </div>
                                    <img class="loading_img" src="images/ajax_loading.gif" alt="loading..." />
                                    <div id="notes_message" class="notes_container"></div>					
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;<?php echo $sap_common->lang('admin_password'); ?>&nbsp;<span class="star">*</span></td>
                                <td><input pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" name="admin_password" id="admin_password" class="form_text" type="password" size="28" maxlength="22" value="<?php echo $admin_password; ?>" onfocus="textboxOnFocus('notes_admin_password')" onblur="textboxOnBlur('notes_admin_password')" placeholder="<?php echo $sap_common->lang('admin_password'); ?>" /></td>
                            </tr>
                           				
                            <tr><td colspan="2" nowrap height="50px">&nbsp;</td></tr>
                            <tr>
                                <td colspan="2">
                                    <a href="database_settings.php" class="form_button" /><?php echo $sap_common->lang('install_back'); ?></a>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="submit" class="form_button" value="Continue" />
                                </td>
                            </tr>                        
                        </table>
                    </form>                        
                </div>
                <div class="clear"></div>
            </div>
            <?php include_once('include/footer.inc.php'); ?>        
        </div>

        <script type="text/javascript">
            'use strict';
            function bodyOnLoad() {
                setFocus("<?php echo $focus_field; ?>");
            }
        </script>
    </body>
</html>
