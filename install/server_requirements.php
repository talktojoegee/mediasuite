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
$program_already_installed = false;

// handle previous installation
// -------------------------------------------------
if (file_exists(SAP_CONFIG_FILE_PATH)) {
    $program_already_installed = true;
}

// handle previous steps
// -------------------------------------------------
if ($passed_step >= 1) {
    // OK
} else {
    header('location: index.php');
    exit;
}

// handle form submission
// -------------------------------------------------
if ($task == 'send') {
    $_SESSION['passed_step'] = 2;
    header('location: database_settings.php');
    exit;
}

ob_start();
if (function_exists('phpinfo'))
    @phpinfo(-1);
$phpinfo = array('phpinfo' => array());
if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
    foreach ($matches as $match) {
        $array_keys = array_keys($phpinfo);
        $end_array_keys = end($array_keys);
        if (strlen($match[1])) {
            $phpinfo[$match[1]] = array();
        } else if (isset($match[3])) {
            $phpinfo[$end_array_keys][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
        } else {
            $phpinfo[$end_array_keys][] = $match[2];
        }
    }

$is_error = false;
$error_mg = array();

// php version check
if (SAP_CHECK_PHP_MINIMUM_VERSION && (version_compare(phpversion(), SAP_PHP_MINIMUM_VERSION, '<'))) {
    $is_error = true;
    $alert_min_version_php = sprintf($sap_common->lang('install_php_version_check'),'{PHP_VERSION}','{PHP_CURR_VERSION}');

    $alert_min_version_php = str_replace('{PHP_VERSION}', SAP_PHP_MINIMUM_VERSION, $alert_min_version_php);
    $alert_min_version_php = str_replace('{PHP_CURR_VERSION}', phpversion(), $alert_min_version_php);
    $error_mg[] = $alert_min_version_php;
}

$php_core_index = ((version_compare(phpversion(), '5.3.0', '<'))) ? 'PHP Core' : 'Core';
// [0] requred
// [1] title
// [2] condition
// [3] true value
// [4] false value
// [5] error message
$validations = array(
    // Getting system info
    'divider_system_info' => array(
        'title' => 'Getting system info',
        'description' => ''
    ),
    'phpversion' => array(
        true, 'php version', function_exists('phpversion'), phpversion(), 'Unknown'
    ),
    'system' => array(
        false, 'system', isset($phpinfo['phpinfo']['System']),
        (isset($phpinfo['phpinfo']['System']) ? $phpinfo['phpinfo']['System'] : ''),
        'disabled'
    ),
    'architecture' => array(
        false, 'system architecture', (isset($phpinfo['phpinfo']['Architecture'])),
        (isset($phpinfo['phpinfo']['Architecture']) ? $phpinfo['phpinfo']['Architecture'] : ''),
        'disabled'
    ),
    'build_date' => array(
        false, 'build date', isset($phpinfo['phpinfo']['Build Date']),
        (isset($phpinfo['phpinfo']['Build Date']) ? $phpinfo['phpinfo']['Build Date'] : ''),
        'disabled'
    ),
    'server_api' => array(
        false, 'server api', isset($phpinfo['phpinfo']['Server API']),
        ( isset($phpinfo['phpinfo']['Server API']) ? $phpinfo['phpinfo']['Server API'] : '' ),
        'Unknown'
    ),
    // Required php settings
    'divider_php_settings' => array('title' => 'Required PHP settings'),
    'database_extension' => array(
        false, 'database extension' . ' (pdo_' . SAP_DATABASE_TYPE . ')',
        extension_loaded('pdo_' . SAP_DATABASE_TYPE),
        'enabled', 'disabled'
    ),
    'short_open_tag' => array(
        false, 'short open tag',
        (isset($phpinfo[$php_core_index]) && $phpinfo[$php_core_index]['short_open_tag'][0] == 'On'),
        'on', 'off'
    ),
    'session_support' => array(
        false, 'session support',
        (isset($phpinfo['session']['Session Support']) && $phpinfo['session']['Session Support'] == 'enabled'),
        'enabled', 'disabled'
    ),
);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="author" content="WPWEB">
        <title><?php echo $sap_common->lang('install_installation_guide_server_req'); ?></title>
        <link rel="stylesheet" type="text/css" href="templates/css/styles.css" />	
        <link rel="icon" href="<?php echo $_SESSION['Site_URL'] . '/assets/images/favicon.png'; ?>" type="image/png" sizes="32x32">
        <script type="text/javascript" src="js/main.js"></script>
        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
    </head>
    <body>
        <div id="main">
            <h1><?php echo $sap_common->lang('install_welcome_title'); ?></h1>
            <h2 class="sub-title"><?php echo $sap_common->lang('install_welcome_text'); ?></h2>

            <div id="content">		
                <?php
                    draw_side_navigation(2);
                ?>
                <div class="central-part">
                    <h2><?php echo $sap_common->lang('install_step_2_to_6'); ?></h2>

                    <form action="server_requirements.php" method="post">
                        <input type="hidden" name="task" value="send" />
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                        <?php
                        $content = '';
                        foreach ($validations as $key => $val) {

                            $content .= '<tr>';

                            if (preg_match('/divider\_/i', $key)) {

                                if ($val['title'] != '') {
                                    $content .= '<td colspan="2">';
                                    $content .= '<h3>' . $val['title'] . '</h3>';
                                    if (!empty($val['description']))
                                        $content .= '<p>' . $val['description'] . '</p>';
                                    $content .= '</td>';
                                } else {
                                    $content .= '<td colspan="2" nowrap height="9px"></td>';
                                }
                            } else {
                                $content .= '<td>&#8226; ' . $val[1] . ': <i>' . (($val[2]) ? '<span class="found">' . $val[3] . '</span>' : '<span class="disabled">' . $val[4] . '</span>') . '</i></td>';
                                if ($val[0] == true && !$val[2]) {
                                    $is_error = true;
                                    $error_mg[$key] = isset($val[5]) ? $val[5] : str_ireplace('{SETTINGS_NAME}', '<b>' . $key . '</b>', 'This installation requires {SETTINGS_NAME} settings turned on/installed.');
                                    $content .= '<td><span class="failed">'.$sap_common->lang('install_failed').'!</span></td>';
                                } else {
                                    $content .= '<td><span class="passed">'.$sap_common->lang('install_passed').'</span></td>';
                                }
                            }
                            $content .= '</tr>' . "\n";
                        }
                        ?>

                        <?php
                        if ($is_error) {
                            echo '<div class="alert alert-error">';
                            foreach ($error_mg as $msg) {
                                echo $msg . '<br>';
                            }
                            echo '</div>';
                        }                       
                        ?>
                        <table width="99%" cellspacing="2" cellpadding="0" border="0">
                            <tbody>
                                <?php echo $content; ?>
                            </tbody>
                        </table>

                        <div class="buttons-wrapper">
                            <a href="index.php" class="form_button" /><?php echo $sap_common->lang('install_back'); ?></a>
                            <?php if (!$is_error) { ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="submit" class="form_button" name="btnSubmit" value="<?php echo $sap_common->lang('install_continue'); ?>" />
                            <?php } ?>
                        </div>
                    </form>
                </div>					
                <div class="clear"></div>
            </div>
            <?php include_once('include/footer.inc.php'); ?>
        </div>
    </body>
</html>