<?php

session_start();

require_once('../include/settings.inc.php');
require_once('../include/database.class.php');
require_once('../include/functions.inc.php');

$action = isset($_POST['action']) ? prepare_input($_POST['action']) : '';
$database_host = isset($_POST['db_host']) ? prepare_input($_POST['db_host']) : '';
$database_name = isset($_POST['db_name']) ? prepare_input($_POST['db_name']) : '';
$database_username = isset($_POST['db_username']) ? prepare_input($_POST['db_username']) : '';
$database_password = isset($_POST['db_password']) ? $_POST['db_password'] : '';

$db_pass_is_html = is_html($database_password);

$arr = array();

$arr[] = '"status": "0"';
$arr[] = '"db_connection_status": "0"';
$arr[] = '"db_version": ""';
$arr[] = '"db_error": ""';

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
header('Content-Type: application/json');

if ($action == 'sap_test_db_connection') {

    $arr[] = '"status": "1"';

    $error = false;

    if (empty($database_host)) {
        $error = true;
        $arr[] = '"db_error": "Database host cannot be empty! Please re-enter."';
    } else if (empty($database_name)) {
        $error = true;
        $arr[] = '"db_error": "Database name cannot be empty! Please re-enter."';
    } else if (empty($database_username)) {
        $error = true;
        $arr[] = '"db_error": "Database username cannot be empty! Please re-enter."';
    } else if ($db_pass_is_html) {
        $error = true;
        $arr[] = '"db_pass": "Database password contains html."';
    }

    if (!$error) {
        $db = Database::GetInstance($database_host, $database_name, $database_username, $database_password, SAP_DATABASE_TYPE, false, true);
        if ($db->Open()) {
            if (SAP_CHECK_DB_MINIMUM_VERSION && (version_compare($db->GetVersion(), SAP_DB_MINIMUM_VERSION, '<'))) {
                $alert_min_version_db = sprintf($sap_common->lang('install_php_version_check'),'_DB_VERSION_','_DB_','_DB_CURR_VERSION_');
                $alert_min_version_db = str_replace('_DB_VERSION_', '<b>' . SAP_DB_MINIMUM_VERSION . '</b>', $alert_min_version_db);
                $alert_min_version_db = str_replace('_DB_CURR_VERSION_', '<b>' . $db->GetVersion() . '</b>', $alert_min_version_db);
                $alert_min_version_db = str_replace('_DB_', '<b>' . $db->GetDbDriver() . '</b>', $alert_min_version_db);
                $arr[] = '"db_version": "' . SAP_DATABASE_TYPE . ' ' . $db->GetVersion() . '"';
                $arr[] = '"db_error": "' . $alert_min_version_db . '"';
            } else {
                $arr[] = '"db_connection_status": "1"';
                $arr[] = '"db_version": "' . SAP_DATABASE_TYPE . ' ' . $db->GetVersion() . '"';
            }
        } else {
            $error_text = $db->Error();
            $error_text = str_replace(array('"', "'"), '', $error_text);
            $error_text = str_replace(array("\n", "\t"), ' ', $error_text);
            $arr[] = '"db_error": "' . $error_text . '"';
        }
    }else{
        $arr[] = '"status": "0"';
    }
} else {
    // wrong parameters passed!
    $arr[] = '"status": "0"';
}

echo '{';
echo implode(',', $arr);
echo '}';
