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

// handle previous steps
// -------------------------------------------------
if ($passed_step >= 4) {
    // OK
} else {
    header('location: index.php');
    exit;
}

// handle form submission
// -------------------------------------------------
if ($task == 'send') {
    $_SESSION['passed_step'] = 5;
    header('location: complete_installation.php');
    exit;
}
?>	

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="author" content="WPWEB">
        <title><?php echo $sap_common->lang('install_installation_rti'); ?></title>

        <link rel="stylesheet" type="text/css" href="templates/css/styles.css" />	
        <link rel="icon" href="<?php echo $_SESSION['Site_URL'] . '/assets/images/favicon.png'; ?>" type="image/png" sizes="32x32">
        <script type="text/javascript" src="js/main.js"></script>
        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>	
        <script type="text/javascript" src="language/en/js/common.js"></script>			
    </head>
    <body>
        <div id="main">
            <h1><?php echo $sap_common->lang('install_welcome_title'); ?></h1>
            <h2 class="sub-title"><?php echo $sap_common->lang('install_welcome_text'); ?></h2>

            <div id="content">
                <?php
                    draw_side_navigation(5);
                ?>
                <div class="central-part">
                    <h2><?php echo $sap_common->lang('install_step_5_to_6'); ?></h2>

                    <p><?php echo $sap_common->lang('install_step_5_help_text'); ?></p>			

                    <form method="post" action="ready_to_install.php">
                        <input type="hidden" name="task" value="send" />
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />

                        <table width="100%" border="0" cellspacing="1" cellpadding="1">
                            <tr><td nowrap height="10px" colspan="3"></td></tr>
                            <tr><td colspan="2" nowrap height="20px">&nbsp;</td></tr>
                            <tr>
                                <td colspan="2">
                                    <a href="administrator_account.php" class="form_button" /><?php echo $sap_common->lang('install_back'); ?></a>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="submit" class="form_button ready_to_install" value="<?php echo $sap_common->lang('install'); ?>" />
                                </td>
                            </tr>                        
                        </table>
                    </form>
                </div>
                <div class="clear"></div>
            </div>	
            <?php include_once('include/footer.inc.php'); ?>
        </div>
    </body>
</html>