<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$passed_step = isset($_SESSION['passed_step']) ? (int) $_SESSION['passed_step'] : 0;

// handle previous steps
// -------------------------------------------------
if ($passed_step >= 5) {
    // OK
} else {
    header('location: index.php');
    exit;
}

$completed = false;
$error_mg = array();

if ($passed_step == 5) {

    $database_host      = isset($_SESSION['database_host']) ? prepare_input($_SESSION['database_host']) : '';
    $database_name      = isset($_SESSION['database_name']) ? prepare_input($_SESSION['database_name']) : '';
    $database_username  = isset($_SESSION['database_username']) ? prepare_input($_SESSION['database_username']) : '';
    $database_password  = isset($_SESSION['database_password']) ? $_SESSION['database_password'] : '';
    $database_prefix    = isset($_SESSION['database_prefix']) ? stripslashes($_SESSION['database_prefix']) : '';

    $admin_username     = isset($_SESSION['admin_username']) ? stripslashes($_SESSION['admin_username']) : '';
    $admin_password     = isset($_SESSION['admin_password']) ? stripslashes($_SESSION['admin_password']) : '';
    $admin_email        = isset($_SESSION['admin_email']) ? stripslashes($_SESSION['admin_email']) : '';
    $admin_first_name     = isset($_SESSION['admin_first_name']) ? stripslashes($_SESSION['admin_first_name']) : '';
    $admin_last_name        = isset($_SESSION['admin_last_name']) ? stripslashes($_SESSION['admin_last_name']) : '';

    $sql_dump_file = SAP_SQL_DUMP_FILE_CREATE;

    if (empty($database_host))
        $error_mg[] = $sap_common->lang('install_complete_installation_host_error');
    if (empty($database_name))
        $error_mg[] = $sap_common->lang('install_complete_installation_name_error');
    if (empty($database_username))
        $error_mg[] = $sap_common->lang('install_complete_installation_username_error');

    if (empty($error_mg)) {

        $db = Database::GetInstance($database_host, $database_name, $database_username, $database_password, SAP_DATABASE_TYPE, false, true);
        if ($db->Open()) {
            if (SAP_CHECK_DB_MINIMUM_VERSION && (version_compare($db->GetVersion(), SAP_DB_MINIMUM_VERSION, '<'))) {
                $alert_min_version_db = sprintf($sap_common->lang('install_complete_installation_db_version'),'{DB_VERSION}','{DB}','{DB_CURR_VERSION}');
                $alert_min_version_db = str_replace('{DB_VERSION}', '<b>' . SAP_DB_MINIMUM_VERSION . '</b>', $alert_min_version_db);
                $alert_min_version_db = str_replace('{DB_CURR_VERSION}', '<b>' . $db->GetVersion() . '</b>', $alert_min_version_db);
                $alert_min_version_db = str_replace('{DB}', '<b>' . $db->GetDbDriver() . '</b>', $alert_min_version_db);
                $error_mg[] = $alert_min_version_db;
            } else {
                // read sql dump file
                $sql_dump = file_get_contents($sql_dump_file);
                if ($sql_dump != '') {
                    if (false == ( $db_error = sap_db_install($sql_dump_file) )) {
                        $error_mg[] = 'SQL execution error! Please Turn debug mode On and check carefully a syntax of your SQL dump file.';
                    } else {
                        // write additional operations here, like setting up system preferences etc.
                        // ...
                        $completed = true;

                        session_destroy();

                        $sap_site_url = !empty($_SESSION['Site_URL'])? $_SESSION['Site_URL'] : $_SERVER['SERVER_NAME'];
						$sap_site_basepath = !empty($_SESSION['Site_BASEPATH'])? $_SESSION['Site_BASEPATH'] : '';

                        // now try to create file and write information
                        $config_file = file_get_contents(SAP_CONFIG_FILE_TEMPLATE);
                        $config_file = str_replace('<DB_HOST>', $database_host, $config_file);
                        $config_file = str_replace('<DB_NAME>', $database_name, $config_file);
                        $config_file = str_replace('<DB_USER>', $database_username, $config_file);
                        $config_file = str_replace('<DB_PASSWORD>', $database_password, $config_file);
                        $config_file = str_replace('<SITE_URL>', $sap_site_url, $config_file);
                        $config_file = str_replace('<DB_PREFIX>', $database_prefix, $config_file);
                        $config_file = str_replace('<SAP_ROUTES_PATH>', $sap_site_basepath, $config_file);

                        if (file_exists(SAP_CONFIG_FILE_PATH)) {
                        	chmod(SAP_CONFIG_FILE_PATH, 0777);
                        }
                        $f = fopen(SAP_CONFIG_FILE_PATH, 'w+');
                        if (!fwrite($f, $config_file) > 0) {
                            $error_mg[] = str_replace('{CONFIG_FILE_PATH}', SAP_CONFIG_FILE_PATH, 'Database was successfully created! Cannot open configuration file {CONFIG_FILE_PATH} to save info.');
                        }
                        fclose($f);
                    }
                } else {
                    $error_mg[] = str_replace('{SQL_DUMP_FILE}', $sql_dump_file, 'Could not read file <b>{SQL_DUMP_FILE}</b>! Please check if a file exists.');
                }
            }
        } else {
            $error_mg[] = str_replace('{ERROR}', '<br />Error: ' . $db->Error(), 'Database connecting error! Please check your connection parameters.{ERROR}<br />');
        }
    }
} else {
    $error_mg[] = $sap_common->lang('install_complete_installation_wrong_parameter');
}
$Site_url = $_SERVER['SCRIPT_URI'];
if( !empty( $Site_url )) {
    $Site_url = str_replace( '/index.php', '', $Site_url);
    $Site_url = str_replace( '/complete_installation.php', '', $Site_url);
    $Site_url = str_replace( '/install', '', $Site_url);
} else{
    $is_https = is_ssl();
    $protocol = ( $is_https == true ) ? 'https://' : 'http://';

    $currenthost = $protocol.str_replace($protocol, '',  $_SERVER['HTTP_HOST'] );
    $currentpath = preg_replace('@/+$@','',dirname($_SERVER['SCRIPT_NAME']));
    $currentpath = preg_replace('/\/wp.+/','',$currentpath);
    $Site_url = $currenthost.$currentpath;
    $Site_url = str_replace( '/index.php', '', $Site_url);
    $Site_url = str_replace( '/complete_installation.php', '', $Site_url);
    $Site_url = str_replace( '/install', '', $Site_url);
}
?>	
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="author" content="ApPHP Company - Advanced Power of PHP">
        <meta name="generator" content="ApPHP EasyInstaller">
        <title><?php echo $sap_common->lang('install_installation_complate'); ?></title>
        <link rel="icon" href="<?php echo $Site_url . '/assets/images/favicon.png'; ?>" type="image/png" sizes="32x32">
        <link rel="stylesheet" type="text/css" href="templates/css/styles.css" />	

        <script type="text/javascript" src="js/main.js"></script>
        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
    </head>
    <body>
        <div id="main">    
            <h1><?php echo $sap_common->lang('install_welcome_title'); ?></h1>
            <h2 class="sub-title"><?php echo $sap_common->lang('install_welcome_text'); ?></h2>

            <div id="content">
                <?php
                draw_side_navigation(6);
                ?>

                <div class="central-part">
                    <h2><?php echo $sap_common->lang('install_step_6_to_6'); ?>
                        <?php if (!$completed) { ?>
                            <?php echo $sap_common->lang('install_db_error'); ?>
                        <?php } else { ?>
                            <?php echo $sap_common->lang('install_db_completed'); ?>				
                    <?php } ?>
                    </h2>

                    <?php
                    if (!$completed) {
                        echo '<div class="alert alert-error">';
                        foreach ($error_mg as $msg) {
                            echo $msg . '<br>';
                        }
                        echo '</div>';
                    }
                    ?>

                    <table width="99%" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                            <?php if (!$completed) { ?>
                                <tr><td nowrap height="25px">&nbsp;</td></tr>
                                <tr>
                                    <td>	
                                        <a href="ready_to_install.php" class="form_button"><?php echo $sap_common->lang('install_back'); ?></a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="submit" class="form_button" onclick="javascript:location.reload();" value="Complete" />
                                    </td>
                                </tr>							
                            <?php } else { ?>
                                <tr><td>&nbsp;</td></tr>																		
                                <tr><td><h4><?php echo $sap_common->lang('install_installation_completed'); ?></h4></td></tr>
                                <tr>
                                    <td>
                                        <div class="alert alert-warning">
                                            <?php echo sprintf($sap_common->lang('install_security_msg'),'<b>','</b>'); ?></div>
                                        <br /><br />
                                        <a href="<?php echo !empty($Site_url)? $Site_url : '#'?>"><?php echo $sap_common->lang('install_proceed_to_login_page'); ?></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <br>								
                </div>
                <div class="clear"></div>
            </div>	
            <?php include_once('include/footer.inc.php'); ?>
        </div>
    </body>
</html>