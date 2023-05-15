<?php
/**
 * The base configuration for Social Auto Poster
 */
global $router, $match;
ob_start();
session_start();

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for Script */
define('SAP_DB_NAME', '<DB_NAME>');

/** MySQL database username */
define('SAP_DB_USER', '<DB_USER>');

/** MySQL database password */
define('SAP_DB_PASS', '<DB_PASSWORD>');

/** MySQL hostname */
define('SAP_DB_HOST', '<DB_HOST>');

/**
 * For developers: debugging mode.
 */
define( 'SAP_DEBUG', false );
define( 'SAP_DEBUG_DISPLAY', true );

/* Directory Seperator */
if ( !defined('DS') )
    define('DS', DIRECTORY_SEPARATOR);

/* Absolute path to the Social Auto Poster directory. */
if ( !defined('SAP_APP_PATH') )
	define('SAP_APP_PATH', dirname(__FILE__) . DS );


//Site Url
define('SAP_SITE_URL', '<SITE_URL>');
define('SAP_IMG_URL', SAP_SITE_URL.'/uploads/');
define('CLASS_PATH', SAP_APP_PATH . 'classes' . DIRECTORY_SEPARATOR);
define('LIB_PATH', SAP_APP_PATH . 'Lib' . DIRECTORY_SEPARATOR);
define('CLASS_PREFIX', 'SAP_');

define('SAP_BASEPATH', '/<SAP_ROUTES_PATH>');
if ( !defined('SAP_LOG_DIR') )
    define('SAP_LOG_DIR', SAP_APP_PATH . 'mingle-script-logs/');

if ( !defined('SAP_NAME') )
    define('SAP_NAME', 'Social Auto Poster');
define('SAP_UPDATER_URL', 'https://updater.wpwebelite.com/Updates/SAPSCRIPT/license-info.php');


/** Sets up Social Auto Poster vars and included files. */
require_once(SAP_APP_PATH . 'mingle-settings.php');
