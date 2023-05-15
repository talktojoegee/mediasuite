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
require_once('include/database.class.php');
require_once('include/functions.inc.php');

$task = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
$passed_step = isset($_SESSION['passed_step']) ? (int) $_SESSION['passed_step'] : 0;
$program_already_installed = false;
$focus_field = 'database_host';
$error_msg = '';

// handle previous steps
// -------------------------------------------------
if ($passed_step >= 2) {
          
    // OK
} else {
    header('location: index.php');
    exit;
}

// handle form submission
// -------------------------------------------------
if ($task == 'send') {
    $database_host      = isset($_POST['database_host']) ? prepare_input($_POST['database_host']) : 'localhost';
    $database_name      = isset($_POST['database_name']) ? prepare_input($_POST['database_name']) : '';
    $database_username  = isset($_POST['database_username']) ? prepare_input($_POST['database_username']) : '';
    $database_password  = isset($_POST['database_password']) ? $_POST['database_password'] : '';
    $database_prefix    = isset($_POST['database_prefix']) ? prepare_input($_POST['database_prefix']) : '';
    $install_type       = isset($_POST['install_type']) ? prepare_input($_POST['install_type']) : 'create';

    $db_pass_is_html = is_html($database_password);

    // validation here
    // -------------------------------------------------
    if ($database_host == '') {
        $focus_field = 'database_host';
        $error_msg = $sap_common->lang('install_database_host_error');
    } else if ($database_name == '') {
        $focus_field = 'database_name';
        $error_msg = $sap_common->lang('install_database_name_error');
    } else if ($database_username == '') {
        $focus_field = 'database_username';
        $error_msg = $sap_common->lang('install_database_username_error');
    } else {

        $arr = array();
        $db = Database::GetInstance($database_host, $database_name, $database_username, $database_password, SAP_DATABASE_TYPE, false, true);
        if ($db->Open()) {
            if (SAP_CHECK_DB_MINIMUM_VERSION && (version_compare($db->GetVersion(), SAP_DB_MINIMUM_VERSION, '<'))) {
                $alert_min_version_db = sprintf($sap_common->lang('install_db_version_check'),'_DB_VERSION_','_DB_','_DB_CURR_VERSION_');
                $alert_min_version_db = str_replace('_DB_VERSION_', '<b>' . SAP_DB_MINIMUM_VERSION . '</b>', $alert_min_version_db);
                $alert_min_version_db = str_replace('_DB_CURR_VERSION_', '<b>' . $db->GetVersion() . '</b>', $alert_min_version_db);
                $alert_min_version_db = str_replace('_DB_', '<b>' . $db->GetDbDriver() . '</b>', $alert_min_version_db);
                $error_msg = $alert_min_version_db;
            }
        } else {
            $error_text = $db->Error();
            $error_text = str_replace(array('"', "'"), '', $error_text);
            $error_text = str_replace(array("\n", "\t"), ' ', $error_text);
            $error_msg = $error_text;
        }

        if (empty($error_msg)) {
            $_SESSION['database_host'] = $database_host;
            $_SESSION['database_name'] = $database_name;
            $_SESSION['database_username'] = $database_username;
            $_SESSION['database_password'] = $database_password;
            $_SESSION['database_prefix'] = $database_prefix;

            // skip administrator settings				
            $_SESSION['passed_step'] = 3;
            header('location: administrator_account.php');
            exit;
        }
    }
} else {
    $database_host = isset($_SESSION['database_host']) ? $_SESSION['database_host'] : 'localhost';
    $database_name = isset($_SESSION['database_name']) ? $_SESSION['database_name'] : '';
    $database_username = isset($_SESSION['database_username']) ? $_SESSION['database_username'] : '';
    $database_password = isset($_SESSION['database_password']) ? $_SESSION['database_password'] : '';
    $database_prefix = isset($_SESSION['database_prefix']) ? $_SESSION['database_prefix'] : '';
}

// handle previous installation
// -------------------------------------------------
if (file_exists(SAP_CONFIG_FILE_PATH)) {
    $program_already_installed = true;
    include_once(SAP_CONFIG_FILE_PATH);
    if (defined('DB_PREFIX'))
        $database_prefix = DB_PREFIX;
} ?>	

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="author" content="WPWEB">
        <title><?php echo $sap_common->lang('install_installation_guide_db'); ?></title>

        <link rel="stylesheet" type="text/css" href="templates/css/styles.css" />
        <link rel="icon" href="<?php echo $_SESSION['Site_URL'] . '/assets/images/favicon.png'; ?>" type="image/png" sizes="32x32">
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
                    draw_side_navigation(3);
                ?>
                <div class="central-part">
                    <h2><?php echo $sap_common->lang('install_step_3_to_6'); ?></h2>
                    <h3><?php echo $sap_common->lang('install_database_import'); ?></h3>

                    <form action="database_settings.php" method="post">
                        <input type="hidden" name="task" value="send" />
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                        <?php
                        if (!empty($error_msg)) {
                            echo '<div class="alert alert-error">' . $error_msg . '</div>';
                        }
                        ?>

                        <table width="99%" border="0" cellspacing="1" cellpadding="1">
                            <tr>
                                <td colspan="3"><span class="star">*</span> <?php echo $sap_common->lang('install_required_fields'); ?></td>
                            </tr>
                            <tr><td nowrap height="10px" colspan="3"></td></tr>
                            <tr>
                                <td width="250px" nowrap>&nbsp;<?php echo $sap_common->lang('install_database_host'); ?>: <span class="star">*</span></td>
                                <td>
                                    <input type="text" class="form_text" name="database_host" id="database_host" size="30" value="<?php echo $database_host; ?>" placeholder="<?php echo $sap_common->lang('localhost'); ?>" onfocus="textboxOnFocus('notes_host')" onblur="textboxOnBlur('notes_host')" />
                                </td>
                                <td rowspan="7" valign="top">					
                                    <div id="notes_host" class="notes_container">
                                        <?php echo sprintf($sap_common->lang('install_database_host_help_text'),'<h4>','</h4>','<p>','</p>'); ?>
                                    </div>						
                                    <div id="notes_db_name" class="notes_container">
                                        <?php echo sprintf($sap_common->lang('install_database_name_help_text'),'<h4>','</h4>','<p>','</p>'); ?>
                                    </div>
                                    <div id="notes_db_user" class="notes_container">
                                        <?php echo sprintf($sap_common->lang('install_database_username_help_text'),'<h4>','</h4>','<p>','</p>'); ?>
                                    </div>
                                    <div id="notes_db_password" class="notes_container">
                                        <?php echo sprintf($sap_common->lang('install_database_password_help_text'),'<h4>','</h4>','<p>','</p>'); ?>
                                    </div>
                                    <div id="notes_db_prefix" class="notes_container">
                                        <?php echo sprintf($sap_common->lang('install_database_prefix_help_text'),'<h4>','</h4>','<p>','</p>'); ?>
                                    </div>
                                    <img class="loading_img" src="images/ajax_loading.gif" alt="loading..." />
                                    <div id="notes_message" class="notes_container"></div>					
                                </td>
                            </tr>
                            <tr>
                                <td nowrap>&nbsp;<?php echo $sap_common->lang('install_database_name'); ?>: <span class="star">*</span></td>
                                <td>
                                    <input type="text" class="form_text" name="database_name" id="database_name" size="30" autocomplete="off" value="<?php echo $database_name; ?>" placeholder="<?php echo $sap_common->lang('install_database_name'); ?>" onfocus="textboxOnFocus('notes_db_name')" onblur="textboxOnBlur('notes_db_name')" />
                                </td>
                            </tr>
                            <tr>
                                <td nowrap>&nbsp;<?php echo $sap_common->lang('install_database_username'); ?>: <span class="star">*</span></td>
                                <td>
                                    <input type="text" class="form_text" name="database_username" id="database_username" size="30" autocomplete="off" value="<?php echo $database_username; ?>" placeholder="<?php echo $sap_common->lang('install_database_username'); ?>" onfocus="textboxOnFocus('notes_db_user')" onblur="textboxOnBlur('notes_db_user')" />
                                </td>
                            </tr>
                            <tr>
                                <td nowrap>&nbsp;<?php echo $sap_common->lang('install_database_password'); ?>:</td>
                                <td>
                                    <input type="password" class="form_text" name="database_password" id="database_password" size="30" value="<?php echo $database_password; ?>" autocomplete="off" placeholder="<?php echo $sap_common->lang('install_database_password'); ?>" onfocus="textboxOnFocus('notes_db_password')" onblur="textboxOnBlur('notes_db_password')" />
                                    <input type="hidden" name="database_prefix" size="12" maxlength="12" value="<?php echo $database_prefix; ?>" autocomplete="off" />
                                </td>
                            </tr>			
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <input type="button" class="form_button" title="Test Connection" onclick="testDatabaseConnection()" value="<?php echo $sap_common->lang('install_db_test_connection'); ?>" />
                                </td>
                            </tr>
                            <tr><td nowrap height="10px" colspan="3"></td></tr>
                            <tr>
                                <td colspan="2">
                                    <a href="server_requirements.php" class="form_button" /><?php echo $sap_common->lang('install_back'); ?></a>
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

 