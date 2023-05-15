<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Flash
 *
 * @author PC10
 */
class Flash {

    private $_messageStack;
    public $message;

    public function __construct() {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = array('messageStack' => array());
        }

    }

    public function setFlash($message, $class, $icon = '',$unique = false) {
        $this->_messageStack = array('message' => $message, 'class' => $class, 'icon' => $icon);
        if(isset($unique) && $unique == true){
            $this->_messageStack['unique'] = '1';
        }
        $_SESSION['flash']['messageStack'][] = $this->_messageStack;
    }

    public function renderFlash() {
		$render = '';

       $uploads_folder      =  SAP_APP_PATH.'uploads/';
       $is_uploads_writable =  is_writable($uploads_folder);
       
        if( !$is_uploads_writable && $_SESSION['user_details']['role'] == 'superadmin' ) {
            $this->setFlash('Please provide the write permission to the directory ( ' .$uploads_folder. ' )','error change-uploads-permission');
        }

        if ( file_exists( SAP_APP_PATH . 'install' ) ) {
            if (isset($_SESSION['user_details']) && !empty($_SESSION['user_details'])) {
                $this->setFlash('For security reasons, please remove /install folder from your server!', 'error remove-install-folder'); 
            }              
        }  

        if (!empty($_SESSION['flash']['messageStack'])) {

        	foreach ( $_SESSION['flash']['messageStack'] as $key => $value ) {

	            $render .= '<div class="alert alert-' . $_SESSION['flash']['messageStack'][$key]['class'] . ' alert-dismissible" role="alert">
	  							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	  							' . $_SESSION['flash']['messageStack'][$key]['message'] . '
	  						</div>';
        	}
        	unset($_SESSION['flash']['messageStack']);
        } else {
            $render = "";
        }
        
        return $render;
    }

}
