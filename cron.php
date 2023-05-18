<?php

if (!defined('CLASS_PATH')) {
    require_once ( 'mingle-config.php');
}

if (!class_exists('SAP_Settings')) {
    require_once ( CLASS_PATH . 'Settings.php');
}

// Include all constant
require_once ( SAP_APP_PATH . 'config' . DS . 'constant.php' );

//Get Setting Object and based on settings
$settings_object = new SAP_Settings();

$sap_general_options = $settings_object->get_options('sap_general_options');
$cron_run_time = $settings_object->get_options('cron_run_time');
$cron_membership_expire_time = $settings_object->get_options('cron_membership_expire_time');

//Update time zone based on setting
if (!empty($sap_general_options['timezone'])) {
    date_default_timezone_set($sap_general_options['timezone']);
}

if (!class_exists('SAP_Shedule_Posts')) {
    require_once( CLASS_PATH . 'Shedule_Posts.php' );
}

if (!class_exists('SAP_Mingle_Update')) {
    require_once( CLASS_PATH . 'Mingle_Update.php' );
}

if (!class_exists('SAP_Payment')) {
    require_once( CLASS_PATH . 'Payment.php' );
}

/**
 * Insert debug clear option into database with first time install
 * @var [type]
 */
$schedule_debug_clear = $settings_object->get_options('schedule_debug_clear');
if( isset($schedule_debug_clear) && empty($schedule_debug_clear) ){
    $today = date("Y-m-d");
    $settings_object->update_options('schedule_debug_clear', $today );
}

/**
 * Fire schedule post twice hourly
 */

if (empty($cron_run_time) || $cron_run_time <= time()) {

    //Check and publish schedule post
    $shedule_object = new SAP_Shedule_Posts();
    $shedule_posts = $shedule_object->handle_sheduled_posts();

    //Update time for next schedule
    $run_time = time() + 50;
    $settings_object->update_options('cron_run_time', $run_time);
}


/**
 * Fire expire membership twice dayly
 */

if (empty($cron_membership_expire_time) || $cron_membership_expire_time <= time()) {

    //Check and publish schedule post
    $payment_object = new SAP_Payment();
    $shedule_posts = $payment_object->cron_to_expire_membership();

    //Update time for next schedule
    $run_time = strtotime(date('y-m-d H:i a'). "+12 hours");
    $settings_object->update_options('cron_membership_expire_time', $run_time);
}

/*
if (empty($_SESSION['Update_version'])) {

    $updater_object = new SAP_Mingle_Update();
    $license_data = $updater_object->check_update();
    $_SESSION['Update_version'] = $license_data;
}
*/