<?php

/**
 * Script Update Class
 * 
 * Responsible for all updates and license check
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
require_once ( CLASS_PATH . 'Settings.php');

class SAP_Mingle_Update {

    //Set Database variable
    private $db;
    private $settings;
    public $flash;
    public $common;
    public $sap_common;

    public function __construct() {

        global $sap_db_connect, $sap_common;
        
        $this->db = $sap_db_connect;
        $this->flash = new Flash();
        $this->common = new Common();
        $this->settings = new SAP_Settings();
        $this->tmp_dir = dirname(dirname(__FILE__)) . DS . 'tmp-sap-upgrade';
        $this->root_dir = dirname(dirname(__FILE__));
        $this->sap_common = $sap_common;
    }

    /**
     * Listing page of Posts
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function index() {

        //Includes Html files for Posts list
        if ( !sap_current_user_can('mingle-update') ) {

            if (isset($_SESSION['user_details']) && !empty($_SESSION['user_details'])) {

                $template_path = $this->common->get_template_path('Update' . DS . 'index.php' );
                include_once( $template_path );
            } 
        }
        else {
            $this->common->redirect('login');
        }
    }

    /**
     * Check Update Available
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function check_update() {

        $ch = curl_init();
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );

        $URL = SAP_UPDATER_URL . '?type=check_update';

        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        //Return the latest version data
        return $response;
    }

    /**
     * Version Updating
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function version_updating() {

        $response = array();

        $license_data = $this->get_license_data();

        if (empty($license_data['license_key']) && empty($license_data['license_email'])) {
            $response['error'] = $this->sap_common->lang('update_email_key_error');
            echo json_encode($response);
            exit;
        }

        if (empty($license_data['license_email'])) {
            $response['error'] = $this->sap_common->lang('update_email_address_error');
        }

        if (empty($license_data['license_key'])) {
            $response['error'] = $this->sap_common->lang('update_key_error');
        }

        //If error return it here
        if (!empty($response['error'])) {
            echo json_encode($response);
            exit;
        }

        $license_data = $this->get_license_data();


        $response = $this->updating_process($license_data);

        if (!empty($response->download_url)) {

            $newUpdate = file_get_contents($response->download_url);

            if (!is_dir($this->tmp_dir))
                mkdir($this->tmp_dir);

            $dlHandler = fopen($this->tmp_dir . '/mingle.zip', 'w');

            if (!fwrite($dlHandler, $newUpdate)) {
                echo json_encode(array('error' => $this->sap_common->lang('update_not_save')));
                exit;
            }
            
            fclose($dlHandler);
            if (isset($_SESSION['Update_version']) && !empty($_SESSION['Update_version'])) {
                $this->settings->update_options('sap_version', $_SESSION['Update_version']);
                unset($_SESSION['Update_version']);
            }
            echo json_encode(array('success' => $this->sap_common->lang('update_version_success')));
            exit;
        } elseif (!empty($response->error)) {
            echo json_encode($response);
            exit;
        } else {
            echo json_encode(array('error' => $this->sap_common->lang('update_fail')));
            exit;
        }
    }

    /**
     * Compress Version
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function version_compress() {

        $backup = realpath($this->tmp_dir . '/mingle.zip');

        $zip = new ZipArchive();
        $result = $zip->open($backup);

        $entries = array();
        for ($idx = 0; $idx < $zip->numFiles; $idx++) {

            if ($zip->getNameIndex($idx) == 'mingle/mingle-config.php' || $zip->getNameIndex($idx) == 'mingle/uploads/') {
                continue;
            }
            $entries[] = $zip->getNameIndex($idx);
        }

        $zip->extractTo($this->root_dir, $entries);

        $zip->close();

        $this->delete_files($this->tmp_dir);
        echo json_encode(array('success' => $this->sap_common->lang('update_script_up_to_date')));
    }

    /**
     * Version Compress
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function version_compress1() {

        $zipHandle = zip_open($this->tmp_dir . '/mingle.zip');

        while ($file = zip_read($zipHandle)) {

            $thisFileName = zip_entry_name($file);
            $thisFileDir = dirname($thisFileName);

            if ($thisFileName == 'mingle/mingle-config.php' || $thisFileDir == 'mingle/uploads') {
                continue;
            }

            //Continue if its not a file
            if (substr($thisFileName, -1, 1) == '/')
                continue;

            $dir_root = "$this->root_dir/$thisFileDir";

            //Make the directory if we need to...
            if (!is_dir($dir_root) && !is_file($dir_root)) {
                @mkdir($dir_root);
            }

            //Overwrite the file
            if (!is_dir($this->root_dir . '/' . $thisFileName)) {

                $contents = zip_entry_read($file, zip_entry_filesize($file));
                $contents = str_replace("\r\n", "\n", $contents);
                $updateThis = '';

                //If we need to run commands, then do it.
                if ($thisFileName == 'upgrade.php') {

                    $upgradeExec = fopen('upgrade.php', 'w');
                    fwrite($upgradeExec, $contents);
                    fclose($upgradeExec);
                    include ('upgrade.php');
                    unlink('upgrade.php');
                    echo' EXECUTED</li>';
                } else {

                    $updateThis = @fopen($this->root_dir . '/' . $thisFileName, 'w');
                    @fwrite($updateThis, $contents);
                    @fclose($updateThis);
                    unset($contents);
                }
            }
        }
        echo json_encode(array('success' => $this->sap_common->lang('update_script_up_to_date')));
    }

    /**
     * Update Process
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    protected function updating_process($license_data) {

        $fileds = array('email' => $license_data['license_email'], 'license_key' => $license_data['license_key']);
        $postData = '';

        foreach ($license_data as $k => $v) {
            $postData .= $k . '=' . $v . '&';
        }

        $postData = rtrim($postData, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, SAP_UPDATER_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, $postData);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        $result = json_decode($response);

        return $result;
    }

    /**
     * Save posts to database
     * 
     * @todo : Set error message
     * @since v1.0.0
     */
    public function save_process() {

        //Check form submit request
        if (isset($_POST['sap_register_license']) && !empty($_POST['sap_license_key']) && !empty($_POST['sap_license_email'])) {

            $sap_license_key = $_POST['sap_license_key'];
            $sap_license_email = $_POST['sap_license_email'];

            $update_options = $this->settings->update_options('sap_license_data', array('license_key' => $sap_license_key, 'license_email' => $sap_license_email));
            //Check response for DB Update
            if (!empty($update_options)) {
                $this->flash->setFlash($this->sap_common->lang('license_key_email_update'), 'success');
            } else {
                $this->flash->setFlash($this->sap_common->lang('error_saving_license_data'), 'error');
            }
        } else {
            $this->flash->setFlash($this->sap_common->lang('required_data_blank'), 'error');
        }

        $this->common->redirect('mingle_update');
        exit();
    }

    /**
     * License Data
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function get_license_data() {
        $license_options = $this->settings->get_options('sap_license_data');
        if (!empty($license_options)) {
            return $license_options;
        }
        return array();
    }

    /**
     * Delete Files
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function delete_files($target) {
        if (is_dir($target)) {

            $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file) {
                $this->delete_files($file);
            }

            if (is_dir) {
                rmdir($target);
            }
        } elseif (is_file($target)) {
            unlink($target);
        }
    }

}
