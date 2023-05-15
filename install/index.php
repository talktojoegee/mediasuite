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
$program_already_installed = false;

// handle previous installation
// -------------------------------------------------
if (file_exists(SAP_CONFIG_FILE_PATH)) {
    $program_already_installed = true;
}

 $Site_url = $_SERVER['SCRIPT_URI'];
 if( !empty( $Site_url )) {
     $Site_url = str_replace( '/index.php', '', $Site_url);
     $Site_url = str_replace( '/install', '', $Site_url);
 } else{
    $is_https = is_ssl();
    $protocol = ( $is_https == true ) ? 'https://' : 'http://';

    $currenthost = $protocol.str_replace($protocol, '',  $_SERVER['HTTP_HOST'] );
    $currentpath = preg_replace('@/+$@','',dirname($_SERVER['SCRIPT_NAME']));
    $currentpath = preg_replace('/\/wp.+/','',$currentpath);
    $Site_url = $currenthost.$currentpath;
    
    $Site_url = str_replace( '/index.php', '', $Site_url);
    $Site_url = str_replace( '/install', '', $Site_url);
 }
 
 $_SESSION['Site_URL'] = $Site_url;

 $Site_base_path = !empty($_SERVER['SCRIPT_URL'] ) ? $_SERVER['SCRIPT_URL'] : $_SERVER['SCRIPT_NAME'];
 
 $Site_base_path = str_replace( '/index.php', '', $Site_base_path);
 $Site_base_path = str_replace( '/install', '', $Site_base_path);
 $Site_base_path = str_replace( '/', '', $Site_base_path);
 
 $_SESSION['Site_BASEPATH'] = $Site_base_path;

// handle form submission
// -------------------------------------------------
if ($task == 'send') {
    $_SESSION['passed_step'] = 1;
    header('location: server_requirements.php');
    exit;
} else if ($task == 'start_over') {
    $_SESSION['passed_step'] = 0;    
    @unlink(SAP_CONFIG_FILE_PATH);
    session_destroy();
    // *** set new token
    $_SESSION['token'] = md5(uniqid(rand(), true));
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="author" content="WPWEB">
        <title><?php echo $sap_common->lang('install_installation_guide_start'); ?></title>
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
                    draw_side_navigation(1);
                ?>
                <div class="central-part">
                    <form action="index.php" method="post">
                        <input type="hidden" name="task" value="send" />
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />

                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tbody>
                                <tr>
                                    <td>
                                    	<?php
                                    	if( file_exists(dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR .'mingle-config.php') ){
                                    	?>
                                    	<div class="alert alert-warning"><b><?php echo $sap_common->lang('install_installation_already_complate_text'); ?></b></div>
                                    	<?php 
                                    	} ?>

                                        <?php echo sprintf($sap_common->lang('install_welcome_description'),'<h2>','</h2>','<p>','</p>','<ol>','<li>','</li>','<li>','</li>','<li>','</li>','<li>','</li>','</ol>','<br/>','<p>','</p>'); ?>

                                    </td>
                                </tr>
                                <tr><td nowrap="nowrap" height="30px"></td></tr>
                                <tr>
                                    <td>
                                        <input type="submit" class="form_button" name="btnSubmit" id="button_start" title="<?php echo $sap_common->lang('install_lets_go'); ?>" value="<?php echo $sap_common->lang('install_lets_go'); ?>" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>		
                </div>
                <div class="clear"></div>
            </div>
            <?php include_once('include/footer.inc.php'); ?>
        </div>
    </body>
</html>