<?php

/**
 * Quick Posts Class
 * 
 * Responsible for all function related to Quick posts
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
class SAP_Shedule_Posts {

	//Set Database variable
	private $db;
	//Set table name
	private $quick_post_table_name;
	private $quick_post_meta_table_name;
	private $post_table_name;
	private $post_meta_table_name;
	public $flash;
	public $common;

	public function __construct() {
		global $sap_db_connect;
		$this->db = $sap_db_connect;

		//Assign table name
		$this->quick_post_table_name = 'sap_quick_posts';
		$this->quick_post_meta_table_name = 'sap_quick_postmeta';

		$this->post_table_name = 'sap_posts';
		$this->post_meta_table_name = 'sap_postmeta';
		$this->settings = new SAP_Settings(); // to fix timezone issue
	}

	/**
	 * Get schedule Posts
	 * 
	 * Handels list of schedule Posts
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function get_sheduled_post_ids() {

		$result = array();

		try {
			//Query for get schedule post only
			$query = "SELECT DISTINCT Qp.post_id, Qp.user_id FROM " . $this->quick_post_table_name . " Qp JOIN " . $this->quick_post_meta_table_name . " Qpm ON Qp.post_id = Qpm.post_id WHERE Qp.status='2'";

			$result['quick_posts'] = $this->db->get_results($query, true);
		} catch (Exception $e) {
			return $e->getMessage();
		}

		try {
			$query = "SELECT DISTINCT P.post_id, P.user_id FROM " . $this->post_table_name . " P JOIN " . $this->post_meta_table_name . " Pm ON P.post_id = Pm.post_id WHERE P.status='2'";
			$result['posts'] = $this->db->get_results($query, true);
		} catch (Exception $e) {
			return $e->getMessage();
		}

		//Return result
		return $result;
	}

	/**
	 * Handle Schedule Posting
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function handle_sheduled_posts() {

		$final_shedule_post = array();


		require_once( CLASS_PATH . 'Posts.php' );


		require_once( CLASS_PATH . 'Quick_Posts.php' );

		// Run cron every week and clear debug log
		if ( ! class_exists('SAP_Debug') ) {
			
			require_once ( CLASS_PATH . 'Debug.php');
			require_once ( CLASS_PATH . 'Settings.php');
			
			$common = new Common();
			$debug_log = new SAP_Debug();
			$settings_object = new SAP_Settings();

			$schedule_debug_clear = $settings_object->get_options('schedule_debug_clear');
			$day	= date( 'w' ); // return 0 to 6, 0 for Sunday, 6 for Saturday

			if( !empty($schedule_debug_clear) && $day == '1' ) {
				$today	= date( 'Y-m-d' );
				if( $today != $schedule_debug_clear && $today < $schedule_debug_clear ) {
					$debug_log->schedule_cleaner();
				}				
			}

		}

		$this->sap_post 	  = new SAP_Posts();
		$this->sap_quick_post = new SAP_Quick_Posts();

		$sheduled_post_ids    = $this->get_sheduled_post_ids();
	
		//schedule post for Quick posting
		if (!empty($sheduled_post_ids['quick_posts'])) {

			foreach ($sheduled_post_ids['quick_posts'] as $key => $value) {
				
				$user_options = $this->settings->get_user_setting('sap_general_options', $value['user_id']);

				$timezone = (!empty($user_options['timezone']) ) ? $user_options['timezone'] : ''; // user timezone

				//Update time zone based on user setting
				if ( !empty( $timezone ) ) { // set default timezone
				    date_default_timezone_set( $timezone );
				}

				$schedule_time = $this->sap_quick_post->get_post_meta($value['post_id'], 'sap_schedule_time');

		 	  if( $schedule_time < time() ) { // check post schedule time based on user time zone

					//Manage wall posting of Socials
					$this->sap_quick_post->sap_manage_wall_social_post($value['post_id'], 1, $value['user_id']);
				
					//Update Status after posting
					$status = array('status' => 1);
					$where = array('post_id' => $value['post_id']);
					$this->sap_quick_post->update_post($status, $where);
				}
			}
		}
	
		//schedule post for content posting
		if (!empty($sheduled_post_ids['posts'])) {

			foreach ($sheduled_post_ids['posts'] as $value) {

				$user_options = $this->settings->get_user_setting('sap_general_options', $value['user_id']);

				$timezone = (!empty($user_options['timezone']) ) ? $user_options['timezone'] : ''; // user timezone

				//Update time zone based on user setting
				if (!empty($timezone )) { // set default timezone
				    date_default_timezone_set($timezone);
				}

				$schedule_time = $this->sap_post->get_post_meta($value['post_id'], 'sap_schedule_time');
						
				if( $schedule_time < time() ) { // check post schedule time based on user time zone
					$this->sap_post->sap_manage_wall_shedule_social_post($value['post_id'], 1, $value['user_id']);

					//Update Status after posting
					$status = array('status' => 1);
					$where  = array('post_id' => $value['post_id']);
					$this->sap_post->update_posts($status, $where);
				}
			}
		}
	}

	
	/**
	 * Get all supported Networks
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function sap_get_supported_networks() {
		return array(
			'fb' => 'facebook',
			'tw' => 'twitter',
			'li' => 'linkedin',
			'tb' => 'tumblr',
			'reddit' => 'reddit',
			'blogger' => 'blogger'
		);
	}

}
