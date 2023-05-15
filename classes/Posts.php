<?php

/**
 * Posts Class
 * 
 * Responsible for all function related to posts
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */

require_once ( CLASS_PATH . 'Settings.php');

class SAP_Posts {

	//Set Database variable
	private $db;
	//Set table name
	private $table_name;
	private $post_meta_table_name;
	private $settings;
	public $flash;
	public $common;

	public function __construct() {
		global $sap_db_connect,$sap_common;
		
		$this->db 		= $sap_db_connect;
		$this->flash 	= new Flash();
		$this->common 	= new Common();
		$this->table_name = 'sap_posts';

		$this->post_meta_table_name = 'sap_postmeta';

		$this->settings = new SAP_Settings();
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
		if ( sap_current_user_can('quick-post') ) {
			if (isset($_SESSION['user_details']) && !empty($_SESSION['user_details'])) {
				
				$template_path = $this->common->get_template_path('Posts' . DS . 'posts.php' );
				include_once( $template_path );
			} 
		}
		else {
			$this->common->redirect('login');
		}
	}

	/**
	 * Add new post
	 * 
	 * @package Social Auto Poster
	 * @since v1.0.0
	 */
	public function add_new_post() {

		if (isset($_SESSION['user_details']) && !empty($_SESSION['user_details'])) {

			if (!class_exists('SAP_Tumblr')) {
				require_once( CLASS_PATH . 'Social' . DS . 'tumblrConfig.php' );
			}
			
			$tumblr = new SAP_Tumblr();

			$template_path = $this->common->get_template_path('Posts' . DS . 'add.php' );
			include_once( $template_path );
		} 
		else {
			$this->common->redirect('login');
		}
	}

	/**
	 * Save posts to database
	 * 
	 * @todo : Set error message
	 * @since v1.0.0
	 */
	public function save_post() {

		//Check form submit request
		if (isset($_POST['form-submitted'])) {

			//Check Post Published Or Draft
			$uploads_folder      =  SAP_APP_PATH.'uploads/';
			$is_uploads_writable =  is_writable($uploads_folder);

			if(!$is_uploads_writable) {
				
				$this->sap_common->sap_script_logs('Please provide the write permission to the directory ( ' .$uploads_folder. ' )');
				$this->flash->setFlash('Please provide the write permission to the directory ( ' .$uploads_folder. ' )', 'error');
				header("Location:" . SAP_SITE_URL . "/add-new-post/");
						
			} else {

				$user_id = sap_get_current_user_id();

				$user_options = $this->settings->get_user_setting('sap_general_options', $user_id);

				$timezone = (!empty($user_options['timezone']) ) ? $user_options['timezone'] : ''; // user timezone

				//Update time zone based on user setting
				if (!empty($timezone)) { // set default timezone
					date_default_timezone_set($timezone);
				}
				
				//Call Upload class and upload media
				$fileUpload = new FileUploader(array());
				$uploadPath = $fileUpload->uploadFile('img');

				//Check media uploaded
				if (!is_int($uploadPath) || !is_numeric($uploadPath)) {
					$_POST['img'] = $uploadPath;
				} else {
					$_POST['img'] = '';
				}

				//Check post set scheduled
				if (!empty($_POST['sap-schedule-time']) && strtotime($_POST['sap-schedule-time']) > strtotime(date('Y-m-d H:i:s'))) {
					$_POST['status'] = 2;
				}

				$_POST['img'] = $uploadPath;
				$_POST['ip'] = $this->common->get_user_ip();

				$_POST['body'] = !empty($_POST['body']) ? $this->db->filter($this->db->clean($_POST['body'])) : '';

				$_POST['created_date'] = date('Y-m-d H:i:s');

				

				//Prepare data for store post in DB
				$prepare_data = array(
					'user_id'		=> $user_id,
					'body'			=> $_POST['body'],
					'img'			=> $_POST['img'],
					'ip_address'	=> $_POST['ip'],
					'status'		=> isset($_POST['status']) ? $_POST['status'] : 0,
					'created_date'	=> $_POST['created_date'],
				);

				if (isset($_POST['share_link'])) {
					$prepare_data['share_link'] = $_POST['share_link'];
				}

				if(isset($_POST['sap_reddit_post_type']) && !empty($_POST['sap_reddit_post_type']) && $_POST['sap_reddit_post_type']=='link'){

					if(empty($prepare_data['share_link'])){
						$this->flash->setFlash($this->sap_common->lang('reddit_link_error_msg'), 'error');
						header("Location:" . SAP_SITE_URL . "/add-new-post/" );
						exit;
					}
				}

				$prepare_data = $this->db->escape($prepare_data);

				
				if ($this->db->insert($this->table_name, $prepare_data)) {

					$_POST['id'] = $this->db->lastid();

					// update meta value for the post
					$this->save_post_meta();


					//Check Post Published Or Draft
					if (isset($_POST['status'])) {

						$this->sap_manage_wall_social_post($_POST['id'], $_POST['status'], $user_id);

						$this->flash->setFlash($this->sap_common->lang('new_content_published'), 'success');
					} else {
						$this->flash->setFlash($this->sap_common->lang('content_draft_save'), 'success');
					}

					header("Location:" . SAP_SITE_URL . "/posts/view/" . $_POST['id']);
					exit;
				} else {
					$this->flash->setFlash($this->sap_common->lang('saving_data_error_msg'), 'error');
				}

			}	
		}
	}

	/**
	 * View Posts
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function view_post() {
		if (isset($_SESSION['user_details']) && !empty($_SESSION['user_details'])) {
			if ( !class_exists('SAP_Tumblr') ) {
				require_once( CLASS_PATH . 'Social' . DS . 'tumblrConfig.php' );
			}

			$tumblr = new SAP_Tumblr();

			$template_path = $this->common->get_template_path('Posts' . DS . 'edit.php' );
			include_once( $template_path );
		} 
		else {
			$this->common->redirect('login');
		}
	}

	/**
	 * Update Post
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function update_post() {

		if (isset($_POST['form-updated'])) {

			$post_id = $_POST['id'];

			//Call Upload class and upload media
			$fileUpload = new FileUploader(array());

			if (!empty($_FILES['img'])) {
				$uploadPath = $fileUpload->uploadFile('img');
			}

			//Check media uploaded
			if (!is_int($uploadPath) || !is_numeric($uploadPath)) {
				$post_img = $uploadPath;
			} else {
				$post_img = $_POST['edit_image'];
			}

			$user_id = sap_get_current_user_id();

			$user_options = $this->settings->get_user_setting('sap_general_options', $user_id);

			$timezone = (!empty($user_options['timezone']) ) ? $user_options['timezone'] : ''; // user timezone

			//Update time zone based on user setting
			if (!empty($timezone)) { // set default timezone
			    date_default_timezone_set($timezone);
			}

			//Check post set scheduled
			if (!empty($_POST['sap-schedule-time']) && strtotime($_POST['sap-schedule-time']) > strtotime(date('Y-m-d H:i:s'))) {
				$_POST['status'] = 2;
			}

			if(isset($_POST['sap_reddit_post_type']) && !empty($_POST['sap_reddit_post_type']) && $_POST['sap_reddit_post_type']=='link'){

				if(empty($prepare_data['share_link'])){
					$this->flash->setFlash($this->sap_common->lang('reddit_link_error_msg'), 'error');
					header("Location:" . SAP_SITE_URL . "/posts/view/" . $_POST['id']);
					exit;
				}
			}

			$_POST['img'] 	= $post_img;
			$_POST['ip'] 	= $this->common->get_user_ip();
			$_POST['body'] 	= $this->db->filter($_POST['body']);
			$_POST['modified_date'] = date('Y-m-d H:i:s');

			//Prepare data for store post in DB
			$prepare_data = array(
				'body' => $_POST['body'],
				'img' => $_POST['img'],
				'status' => isset($_POST['status']) ? $_POST['status'] : 0,
				'modified_date' => $_POST['modified_date'],
			);

			if (isset($_POST['share_link'])) {
				$prepare_data['share_link'] = $_POST['share_link'];
			}

			$prepare_data = $this->db->escape($prepare_data);

			if ($this->db->update($this->table_name, $prepare_data, array('post_id' => $post_id))) {

				// update meta value for the post
				$this->save_post_meta();

				//Check Post Published Or Draft
				if (isset($_POST['status'])) {
					$this->sap_manage_wall_social_post($_POST['id'], $_POST['status'], $user_id);
				}

				header("Location:" . SAP_SITE_URL . "/posts/view/" . $_POST['id']);
				exit;
			} else {
				$this->flash->setFlash($this->sap_common->lang('saving_data_error_msg'), 'error');
			}
		}
	}

	/**
	 * Update Quick Post
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function update_posts($prepare_data, $where_data) {

		if (!empty($prepare_data) && !empty($where_data)) {
			//Run update query in db and return result
			return $this->db->update($this->table_name, $prepare_data, $where_data);
		}
	}

	/**
	 * Delete Posts
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function delete_post() {

		if (!empty($_REQUEST['post_id'])) {
			$result = array();
			$post_id = $_REQUEST['post_id'];
			$img_path='';
			$sap_img ='';

			$result = $this->db->get_results("SELECT * FROM " . $this->table_name . " where `post_id` = ".$post_id);
		
			$sap_img = $this->get_post_meta($post_id, '_sap_fb_post_image');
			
			if(isset($sap_img) && !empty($sap_img)){
				$sap_img = 	SAP_APP_PATH . 'uploads/'.$sap_img;
			}

			$img_meta = $result[0]->img;
			
			if( isset($img_meta) && !empty($img_meta) ){
		
				$img_path = SAP_APP_PATH . 'uploads/'. $result[0]->img;		
			}
		
			$conditions = array('post_id' => $post_id);
			$is_deleted = $this->db->delete($this->table_name, $conditions);
			$this->db->delete($this->post_meta_table_name, $conditions);

			if ($is_deleted) {
				$result = array('status' => '1');
				unlink($img_path);
				unlink($sap_img);
			} else {
				$result = array('status' => '0');
			}
			echo json_encode($result);
			die;
		}
	}

	/**
	 * Delete Multiple posts
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function delete_multiple_post() {

		if (!empty($_REQUEST['id'])) {
			$result = array();
			$post_id = $_REQUEST['id'];
			foreach ($post_id as $key => $value) {
				$conditions = array('post_id' => $value);
				$is_deleted = $this->db->delete($this->table_name, $conditions);
				$this->db->delete($this->post_meta_table_name, $conditions);
			}
			if ($is_deleted) {
				$result = array('status' => '1');
				$this->flash->setFlash($this->sap_common->lang('select_post_delete'), 'success');
			} else {
				$result = array('status' => '0');
			}
			echo json_encode($result);
			die;
		}
	}

	/**
	 * Get post settings
	 * 
	 * Handels list setting Option get
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function get_post($post_id, $object) {

		$result = array();
		if (isset($post_id) && !empty($post_id)) {
			try {
				$result = $this->db->get_row("SELECT * FROM " . $this->table_name . " where `post_id` = '{$post_id}'", $object);
			} catch (Exception $e) {
				return $e->getMessage();
			}
			//Return result
			return $result;
		}
	}

	/**
	 * Get all posts
	 * 
	 * Handels post listing
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function get_posts() {
		$result = array();
		try {
			$user_id = sap_get_current_user_id();

			$result = $this->db->get_results("SELECT * FROM " . $this->table_name . " WHERE `user_id` = {$user_id} ORDER BY `created_date` DESC");
			
		} catch (Exception $e) {
			return $e->getMessage();
		}

		//Return result
		return $result;
	}

	/**
	 * Update option settings
	 * 
	 * Handels to insert post meta
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function insert_post_meta($post_id, $meta_key, $meta_value) {
		if (!empty($post_id) && !empty($meta_key)) {
			$post_meta_data = array('post_id' => $post_id, 'meta_key' => $meta_key, 'meta_value' => is_array($meta_value) ? serialize($meta_value) : $meta_value);
			//Run query and insert option in db

			$post_meta_data = $this->db->escape($post_meta_data);
			$this->db->insert($this->post_meta_table_name, $post_meta_data);

			//Return inserted ID
			return $this->db->lastid();
		}
	}

	/**
	 * Update option settings
	 * 
	 * Handels to Update post meta
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function update_post_meta($post_id, $meta_key, $meta_value) {

		if (!empty($post_id) && !empty($meta_key)) {
			//Check option exist in Database
			$check_postmeta_exist = $this->db->num_rows("SELECT * FROM " . $this->post_meta_table_name . " WHERE post_id = '{$post_id}' AND meta_key = '{$meta_key}' ");

			//Exist database set update query another insert option
			if ($check_postmeta_exist) {

				//Prepare data for update
				$post_meta_data = array('post_id' => $post_id, 'meta_key' => $meta_key, 'meta_value' => is_array($meta_value) ? serialize($meta_value) : $this->db->filter($meta_value));
				$where_data = array('post_id' => $post_id, 'meta_key' => $meta_key);

				//Run update query in db and return result
				return $this->db->update($this->post_meta_table_name, $post_meta_data, $where_data);
			} else {

				//Prepare data for insert
				$post_meta_data = array('post_id' => $post_id, 'meta_key' => $meta_key, 'meta_value' => is_array($meta_value) ? serialize($meta_value) : $this->db->filter($meta_value));

				//Run query and insert option in db
				$this->db->insert($this->post_meta_table_name, $post_meta_data);

				//Return inserted ID
				return $this->db->lastid();
			}
		}
	}

	/**
	 * Delete option settings
	 * 
	 * Handels to delete post meta
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function delete_post_meta($post_id, $meta_key) {

		if (!empty($post_id) && !empty($meta_key)) {
			$post_meta_data = array('post_id' => $post_id, 'meta_key' => $meta_key);
			//Run database and Insert options in table
			$result = $this->db->delete($this->post_meta_table_name, $post_meta_data);

			//Return result
			return $result;
		}
	}

	/**
	 * Update option settings
	 * 
	 * Handels to get post meta
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function get_post_meta($post_id, $meta_key) {

		if (!empty($post_id) && !empty($meta_key)) {
			//Check option exist in Database
			$postmeta_data = $this->db->get_row("SELECT * FROM " . $this->post_meta_table_name . " WHERE post_id = '{$post_id}' AND meta_key = '{$meta_key}' ");

			if (isset($postmeta_data[3]) && $this->common->is_serialized($postmeta_data[3])) {
				$result = unserialize($postmeta_data[3]);
			} elseif (isset($postmeta_data[3]) && is_string($postmeta_data[3])) {
				$result = $postmeta_data[3];
			} else {
				$result = '';
			}
			return $result;
		}
	}

	/**
	 * Reset option settings
	 * 
	 * Handels to reset post status
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function reset_post_status() {

		if (!empty($_REQUEST['post_id']) && !empty($_REQUEST['meta_key'])) {

			$result = array();
			$post_id = $_REQUEST['post_id'];
			$meta_key = $_REQUEST['meta_key'];

			$is_deleted = $this->delete_post_meta($post_id, $meta_key);
			if ($is_deleted) {
				$is_display_schedule = $this->is_display_schedule($post_id);
				$result = array('status' => '1','is_display_schedule' => $is_display_schedule);
			} else {
				$result = array('status' => '0');
			}
			echo json_encode($result);
			die;
		}
	}

	/**
	 * Reset option settings
	 * 
	 * Handels to reset post status
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */ 
	public function is_display_schedule( $post_id ) {

		$_sap_fb_status = $this->get_post_meta($post_id,'_sap_fb_status'); 
		$_sap_tw_status = $this->get_post_meta($post_id,'_sap_tw_status'); 
		$_sap_li_status = $this->get_post_meta($post_id,'_sap_li_status'); 
		$_sap_tumblr_status = $this->get_post_meta($post_id,'_sap_tumblr_status'); 
		$_sap_pin_status = $this->get_post_meta($post_id,'_sap_pin_status');
		$_sap_gmb_status = $this->get_post_meta($post_id,'_sap_gmb_status');
		$_sap_reddit_status = $this->get_post_meta($post_id,'_sap_reddit_status');
		$_sap_blogger_status = $this->get_post_meta($post_id,'_sap_blogger_status');
		
		if (
			( 
				( !empty( $_sap_fb_status ) && $_sap_fb_status === '1' ) &&
				( !empty( $_sap_tw_status ) && $_sap_tw_status === '1' ) &&
				( !empty( $_sap_li_status ) && $_sap_li_status === '1' ) &&
				( !empty( $_sap_tumblr_status ) && $_sap_tumblr_status === '1' ) &&
				( !empty( $_sap_pin_status ) && $_sap_pin_status === '1' ) &&
				( !empty( $_sap_gmb_status ) && $_sap_gmb_status === '1' ) &&
				( !empty( $_sap_reddit_status ) && $_sap_reddit_status === '1' ) &&
				( !empty( $_sap_blogger_status ) && $_sap_blogger_status === '1' ) 

			)
			
		) {
			return "false";
		} else if(
				empty( $_sap_fb_status ) ||
				empty( $_sap_tw_status ) ||
				empty( $_sap_li_status ) ||
				empty( $_sap_tumblr_status ) ||
				empty( $_sap_pin_status ) ||
				empty( $_sap_gmb_status ) ||
				empty( $_sap_reddit_status ) ||
				empty( $_sap_blogger_status ) 
			){
			return "true";
		}else{
			return "true";
		}

	}

	/**
	 * Save post meta
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function save_post_meta() {

		//Call Upload class and upload media
		$meta_file_Upload = new FileUploader(array());


		//Preapare Data 
		if (!empty($_POST['status']) && $_POST['status'] == 2 && !empty($_POST['sap-schedule-time'])) {
			$this->update_post_meta($_POST['id'], 'sap_schedule_time', strtotime($_POST['sap-schedule-time']));
		}

		//Upload custom image if exist
		if (!empty($_FILES['sap_facebbok_post_img']['name'])) {
			$_POST['sap_facebbok_post_img'] = $meta_file_Upload->uploadFile('sap_facebbok_post_img');
		}

		$fb_prefix = '_sap_fb_post_';

		$prepare_fb_post_meta = array(
			"msg" => !empty($_POST['sap_facebook']['message']) ? ($_POST['sap_facebook']['message']) : '',
			"accounts" => !empty($_POST['sap_facebook']['accounts']) ? ($_POST['sap_facebook']['accounts']) : '',
			"image" => !empty($_POST['sap_facebbok_post_img']) ? ($_POST['sap_facebbok_post_img']) : '',
		);

		if ( !empty($_POST['sap_facebook']['type']) ){
			$prepare_fb_post_meta['type'] = $_POST['sap_facebook']['type'];
		}

		if (!empty($prepare_fb_post_meta)) {
			foreach ($prepare_fb_post_meta as $meta_key => $meta_value) {
				$this->update_post_meta($_POST['id'], $fb_prefix . $meta_key, $meta_value);
			}
		}

		//Prepare Data for Instagram
		$insta_prefix = '_sap_instagram_post_';
		//Prepare Data for GMB
		if (!empty($_FILES['sap_instagram_post_img']['name'])) {
			$_POST['sap_instagram_post_img'] = $meta_file_Upload->uploadFile('sap_instagram_post_img');
		}	

		$insta_post_meta = array(
			"msg" => !empty($_POST['sap_instagram']['message']) ? ($_POST['sap_instagram']['message']) : '',
			"accounts" => !empty($_POST['sap_instagram']['accounts']) ? ($_POST['sap_instagram']['accounts']) : '',
			"image" => !empty($_POST['sap_instagram_post_img']) ? ($_POST['sap_instagram_post_img']) : '',
		);

		if (!empty($insta_post_meta)) {
			foreach ($insta_post_meta as $meta_key => $meta_value) {
				
				$this->update_post_meta($_POST['id'], $insta_prefix . $meta_key, $meta_value);
			}
			
		}


		//Prepare Data for Reddit
		$reddit_prefix = '_sap_reddit_post_';
		
		if (!empty($_FILES['sap_reddit_post_img']['name'])) {
			$_POST['sap_reddit_post_img'] = $meta_file_Upload->uploadFile('sap_reddit_post_img');
		}	

		$reddit_post_meta = array(
			"type" => !empty($_POST['sap_reddit_post_type']) ? ($_POST['sap_reddit_post_type']) : '',
			"msg" => !empty($_POST['sap_reddit_msg']) ? ($_POST['sap_reddit_msg']) : '',
			"accounts" => !empty($_POST['sap_reddit_user_id']) ? ($_POST['sap_reddit_user_id']) : '',
			"img" => !empty($_POST['sap_reddit_post_img']) ? ($_POST['sap_reddit_post_img']) : '',
		);

		if (!empty($reddit_post_meta)) {
			foreach ($reddit_post_meta as $meta_key => $meta_value) {
				
				$this->update_post_meta($_POST['id'], $reddit_prefix . $meta_key, $meta_value);
			}
			
		}


		//Prepare Data for Blogger
		$blogger_prefix = '_sap_blogger_post_';
		
		if ( !empty( $_FILES['sap_blogger_post_img']['name'] ) ) {
			$_POST['sap_blogger_post_img'] = $meta_file_Upload->uploadFile('sap_blogger_post_img');
		}	

		$blogger_post_meta = array(
			"title" => !empty( $_POST['sap_blogger_title'] ) ? ( $_POST['sap_blogger_title'] ) : '',
			"accounts" => !empty( $_POST['sap_blogger_user_id'] ) ? ( $_POST['sap_blogger_user_id'] ) : '',
			"img" => !empty( $_POST['sap_blogger_post_img'] ) ? ( $_POST['sap_blogger_post_img'] ) : '',
			"url" => !empty( $_POST['sap_blogger_url'] ) ? ( $_POST['sap_blogger_url'] ) : '',
		);

		if ( !empty( $blogger_post_meta ) ) {
			foreach ( $blogger_post_meta as $meta_key => $meta_value ) {
				$this->update_post_meta( $_POST['id'], $blogger_prefix . $meta_key, $meta_value );
			}
			
		}


		//Prepare Data for GMB
		if (!empty($_FILES['sap_gmb_post_img']['name'])) {
			$_POST['sap_gmb_post_img'] = $meta_file_Upload->uploadFile('sap_gmb_post_img');
		}   

		$gmb_prefix = '_sap_gmb_post_';
		$prepare_gmb_post_meta = array(
			"msg" => !empty($_POST['sap_gmb']['message']) ? ($_POST['sap_gmb']['message']) : '',
			"link" => !empty($_POST['sap_gmb_custom_link']) ? ($_POST['sap_gmb_custom_link']) : '',
			"accounts" => !empty($_POST['sap_gmb']['accounts']) ? ($_POST['sap_gmb']['accounts']) : '',
			"image" => !empty($_POST['sap_gmb_post_img']) ? ($_POST['sap_gmb_post_img']) : '',
			'button_type' => !empty($_POST['sap_gmb']['gmb_button_type']) ? ($_POST['sap_gmb']['gmb_button_type']) : ''
		);

		if (!empty($prepare_gmb_post_meta)) {
			foreach ($prepare_gmb_post_meta as $meta_key => $meta_value) {
				
				$this->update_post_meta($_POST['id'], $gmb_prefix . $meta_key, $meta_value);
			}
			
		}

		
		//Preapare Data of Twitter custom
		$tw_prefix = '_sap_tw_';

		$prepare_tw_post_meta = array(
			"msg" => !empty($_POST['sap_twitter_msg']) ? $_POST['sap_twitter_msg'] : '',
			"template" => '',
			"accounts" => !empty($_POST['sap_twitter_user_id']) ? ($_POST['sap_twitter_user_id']) : '',
		);

		if (!empty($_FILES['sap_tweet_img']['name'])) {
			$prepare_tw_post_meta["image"] = $meta_file_Upload->uploadFile('sap_tweet_img');
		} else {
			$prepare_tw_post_meta["image"] = !empty($_POST['sap_tweet_img']) ? $_POST['sap_tweet_img'] : '';
		}

		if (!empty($prepare_tw_post_meta)) {
			foreach ($prepare_tw_post_meta as $meta_key => $meta_value) {
				$this->update_post_meta($_POST['id'], $tw_prefix . $meta_key, $meta_value);
			}
		}

		//Preapare Data of Linkedin custom
		$li_prefix = '_sap_li_post_';

		$prepare_li_post_meta = array(
			"title" => !empty($_POST['sap_linkedin_custom_title']) ? ($_POST['sap_linkedin_custom_title']) : '',
			"desc" => !empty($_POST['sap_linkedin_custom_description']) ? ($_POST['sap_linkedin_custom_description']) : '',
			"link" => !empty($_POST['sap_linkedin_custom_link']) ? ($_POST['sap_linkedin_custom_link']) : '',
			"profile" => !empty($_POST['sap_linkedin_user_id']) ? (implode(',', $_POST['sap_linkedin_user_id'])) : ''
		);

		//Upload custom image if exist
		if (!empty($_FILES['sap_linkedin_post_img']['name'])) {
			$prepare_li_post_meta['image'] = $meta_file_Upload->uploadFile('sap_linkedin_post_img');
		} else {
			$prepare_li_post_meta["image"] = !empty($_POST['sap_linkedin_post_img']) ? $_POST['sap_linkedin_post_img'] : '';
		}

		if (!empty($prepare_li_post_meta)) {
			foreach ($prepare_li_post_meta as $meta_key => $meta_value) {
				$this->update_post_meta($_POST['id'], $li_prefix . $meta_key, $meta_value);
			}
		}

		//Preapare Data of Tumblr custom
		$tb_prefix = '_sap_tumblr_post_';

		$prepare_tb_post_meta = array(
			"type" => !empty($_POST['sap_tumblr_posting_type']) ? ($_POST['sap_tumblr_posting_type']) : '',
			"link" => !empty($_POST['sap_tumblr_custom_link']) ? ($_POST['sap_tumblr_custom_link']) : '',
			"desc" => !empty($_POST['sap_tumblr_custom_description']) ? ($_POST['sap_tumblr_custom_description']) : '',
			"profile" => !empty($_POST['sap_tumblr_user_id']) ? (implode(',', $_POST['sap_tumblr_user_id'])) : ''
		);

		//Upload custom image if exist
		if (!empty($_FILES['sap_tumblr_post_img']['name'])) {
			$prepare_tb_post_meta['img'] = $meta_file_Upload->uploadFile('sap_tumblr_post_img');
		} else {
			$prepare_tb_post_meta["img"] = !empty($_POST['sap_tumblr_post_img']) ? $_POST['sap_tumblr_post_img'] : '';
		}

		if (!empty($prepare_tb_post_meta)) {
			foreach ($prepare_tb_post_meta as $meta_key => $meta_value) {
				$this->update_post_meta($_POST['id'], $tb_prefix . $meta_key, $meta_value);
			}
		}


		//Preapare Data of Pinterest custom
		//Upload custom image if exist
		 if( !empty( $_FILES['sap_pinterest_post_img']['name'] ) ) {
		  $_POST['sap_pinterest_post_img'] = $meta_file_Upload->uploadFile('sap_pinterest_post_img');
		  }

		  $pin_prefix = '_sap_pin_post_';

		  $prepare_pin_post_meta = array(
		  "msg"       => !empty($_POST['sap_pinterest']['message']) ? ($_POST['sap_pinterest']['message']) : '',
		  "accounts"    => !empty($_POST['sap_pinterest']['accounts']) ? ($_POST['sap_pinterest']['accounts']) : '',
		  "image"       => !empty($_POST['sap_pinterest_post_img']) ? ($_POST['sap_pinterest_post_img']) : '',
		  );

		  if (!empty($prepare_pin_post_meta)) {
		  foreach ($prepare_pin_post_meta as $meta_key => $meta_value) {
		  $this->update_post_meta($_POST['id'], $pin_prefix . $meta_key, $meta_value);
		  }
		  } 
	}

	/**
	 * Handle all Socail posts
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function sap_manage_wall_social_post($post_id, $scheduled = false, $user_id = '') {

		

		// If current user then take accesible netwroks from session
		$networks = sap_get_users_networks_by_id( $user_id );
				
		$networks = unserialize($networks->networks);
		
		//Call Upload class and upload media
		$fileUpload = new FileUploader(array());

		//Get general options of Facebook;
		$sap_facebook_options = array();

		if( in_array('facebook', $networks) ) {

			$sap_facebook_options = $this->settings->get_user_setting('sap_facebook_options', $user_id);

			$sap_facebook_options = !empty($sap_facebook_options) ? $sap_facebook_options : array();

		}

		//Get general options of Twiiter;
		$sap_twitter_options = array();
		if( in_array('twitter', $networks) ) {
			$sap_twitter_options = $this->settings->get_user_setting('sap_twitter_options', $user_id);
			$sap_twitter_options = !empty($sap_twitter_options) ? $sap_twitter_options : array();
		}

		//Get general options of linkedin;
		$sap_linkedin_options = array();
		if( in_array('linkedin', $networks) ) {
			$sap_linkedin_options = $this->settings->get_user_setting('sap_linkedin_options', $user_id);
			$sap_linkedin_options = !empty($sap_linkedin_options) ? $sap_linkedin_options : array();
		}

		//Get general options of Tumblr;
		$sap_tumblr_options = array();
		if( in_array('tumblr', $networks) ) {
			$sap_tumblr_options = $this->settings->get_user_setting('sap_tumblr_options', $user_id);
			$sap_tumblr_options = !empty($sap_tumblr_options) ? $sap_tumblr_options : array();
		}

		//Get general options of Google My Business
		$sap_gmb_options = array();
		if( in_array('gmb', $networks) ) {
			$sap_gmb_options = $this->settings->get_user_setting('sap_google_business_options', $user_id);
			$sap_gmb_options = !empty($sap_gmb_options) ? $sap_gmb_options : array();
		}

		$sap_pinterest_options = array();
		if( in_array('pinterest', $networks) ) {
			$sap_pinterest_options = $this->settings->get_user_setting('sap_pinterest_options', $user_id);
			$sap_pinterest_options = !empty($sap_pinterest_options) ? $sap_pinterest_options : array();
		}

		$sap_instagram_options = array();
		if( in_array('instagram', $networks) ) {
			$sap_instagram_options = $this->settings->get_user_setting('sap_instagram_options', $user_id);
			$sap_instagram_options = !empty($sap_instagram_options) ? $sap_instagram_options : array();
		}

		//Get Redit options
		$sap_reddit_options = array();
		
		if ( in_array('reddit', $networks) ) {
			$sap_reddit_options = $this->settings->get_user_setting('sap_reddit_options', $user_id);
			
			$sap_reddit_options = !empty($sap_reddit_options) ? $sap_reddit_options : array();
		}

		//Get Blogger options
		$sap_blogger_options = array();
		
		if ( in_array( 'blogger', $networks ) ) {
			$sap_blogger_options = $this->settings->get_user_setting('sap_blogger_options');
			
			$sap_blogger_options = !empty( $sap_blogger_options ) ? $sap_blogger_options : array();
		}


		//Check post first time inserting or updating...
		$sap_fb_status = $this->get_post_meta($post_id, '_sap_fb_status');
		//Check facebook enable
		if (!empty($sap_facebook_options['enable_facebook']) && empty($sap_fb_status) && ( isset($_POST['form-submitted']) || isset($_POST['form-updated']) )) {

			//Check schedule enable
			if (!empty($_POST['status']) && $_POST['status'] == 2 && $scheduled == 2) {
				$this->update_post_meta($post_id, '_sap_fb_status', 2);
				$this->flash->setFlash($this->sap_common->lang('content_scheduled_fb'), 'success');
			} else {

				if (!class_exists('SAP_Facebook')) {
					require_once ( CLASS_PATH . 'Social' . DS . 'fbConfig.php');
				}

				$facebook = new SAP_Facebook($user_id);
				$prefix = '_sap_fb_post_';
				

				$fb_result = $facebook->sap_fb_post_to_userwall($post_id);

				if (!empty($fb_result)) {
					$this->update_post_meta($post_id, '_sap_fb_status', '1');
				}
			}
		}

		//Check post first time inserting or updating...
		//Check Reddit enable
		$sap_reddit_status = $this->get_post_meta($post_id, '_sap_reddit_status');
			
		if (!empty($sap_reddit_options['enable_reddit']) && empty($sap_reddit_status) && ( isset($_POST['form-submitted']) || isset($_POST['form-updated']) )) {

			//Check schedule enable
			if (!empty($_POST['status']) && $_POST['status'] == 2 && $scheduled == 2) {
				$this->update_post_meta($post_id, '_sap_reddit_status', 2);
				$this->flash->setFlash($this->sap_common->lang('content_scheduled_reddit'), 'success');
			}else {

				if (!class_exists('SAP_Reddit')) {
					require_once ( CLASS_PATH . 'Social' . DS . 'redditConfig.php');
				}

				$reddit = new SAP_Reddit($user_id);
				$prefix = '_sap_reddit_post_';
	
				$reddit_result = $reddit->sap_reddit_post_to_userwall($post_id);
				
			
				if (!empty($reddit_result)) {
					$this->update_post_meta($post_id, '_sap_reddit_status', '1');
				}
			}
		}

		//Check Blogger enable
		$sap_blogger_status = $this->get_post_meta( $post_id, '_sap_blogger_status' );
			
		if ( !empty( $sap_blogger_options['enable_blogger'] ) && empty( $sap_blogger_status ) && ( isset( $_POST['form-submitted'] ) || isset( $_POST['form-updated'] ) ) ) {

			//Check schedule enable
			if ( !empty( $_POST['status'] ) && $_POST['status'] == 2 && $scheduled == 2 ) {
				$this->update_post_meta( $post_id, '_sap_blogger_status', 2 );
				$this->flash->setFlash( $this->sap_common->lang('content_scheduled_blogger'), 'success');
			}else {

				if ( !class_exists('SAP_Blogger') ) {
					require_once ( CLASS_PATH . 'Social' . DS . 'bloggerConfig.php' );
				}

				$blogger = new SAP_Blogger($user_id);
				$prefix = '_sap_blogger_post_';
	
				$blogger_result = $blogger->sap_blogger_post_to_userwall( $post_id );
			
				if ( !empty( $blogger_result ) ) {
					$this->update_post_meta( $post_id, '_sap_blogger_status', '1' );
				}
			}
		}


		$sap_tw_status = $this->get_post_meta($post_id, '_sap_tw_status');
		//Check twitter enable
		if (!empty($sap_twitter_options['enable_twitter']) && empty($sap_tw_status) && ( isset($_POST['form-submitted']) || isset($_POST['form-updated']) )) {

			//Check schedule enable
			if (!empty($_POST['status']) && $_POST['status'] == 2 && $scheduled == 2) {
				$this->update_post_meta($post_id, '_sap_tw_status', 2);
				$this->flash->setFlash($this->sap_common->lang('content_scheduled_twitter'), 'success');
			} else {
				//Upload custom image if exist
				if (!class_exists('SAP_Twitter')) {
					require_once ( CLASS_PATH . 'Social' . DS . 'twitterConfig.php');
				}

				$this->twposting = new SAP_Twitter($user_id);
				
				$prefix = '_sap_tw_';

				$tw_result = $this->twposting->sap_post_to_twitter($post_id);

				if (!empty($tw_result)) {
					$this->update_post_meta($post_id, '_sap_tw_status', '1');
				}
			}
		}

		$sap_li_status = $this->get_post_meta($post_id, '_sap_li_status');
		//Check Linkedin enable
		if (!empty($sap_linkedin_options['enable_linkedin']) && empty($sap_li_status) && ( isset($_POST['form-submitted']) || isset($_POST['form-updated']) )) {

			//Check schedule enable
			if (!empty($_POST['status']) && $_POST['status'] == 2 && $scheduled == 2) {
				$this->update_post_meta($post_id, '_sap_li_status', 2);
				$this->flash->setFlash($this->sap_common->lang('content_scheduled_li'), 'success');
			} else {
				if (!class_exists('SAP_Linkedin')) {
					require_once( CLASS_PATH . 'Social' . DS . 'liConfig.php' );
				}

				$linkedin = new SAP_Linkedin($user_id);
				$prefix = '_sap_li_post_';

				$li_result = $linkedin->sap_post_to_linkedin($post_id);

				if (!empty($li_result['success'])) {
					$this->update_post_meta($post_id, '_sap_li_status', '1');
				}
			}
		}

		$sap_tu_status = $this->get_post_meta($post_id, '_sap_tumblr_status');
		//Check Tumblr enable

		if (!empty($sap_tumblr_options['enable_tumblr']) && empty($sap_tu_status) && ( isset($_POST['form-submitted']) || isset($_POST['form-updated']) )) {

			//Check schedule enable
			if (!empty($_POST['status']) && $_POST['status'] == 2 && $scheduled == 2) {
				$this->update_post_meta($post_id, '_sap_tumblr_status', 2);
				$this->flash->setFlash($this->sap_common->lang('content_scheduled_tumb'), 'success');
			} else {
				
				if (!class_exists('SAP_Tumblr')) {
					require_once( CLASS_PATH . 'Social' . DS . 'tumblrConfig.php' );
				}

				$tumblr = new SAP_Tumblr($user_id);
				$prefix = '_sap_tumblr_post_';

				$tm_result = $tumblr->sap_post_to_tumblr($post_id);
				if ( (isset($tm_result) && $tm_result['status'] == 'published') || ( $tm_result['status'] == 'transcoding' && $tm_result['posting_type'] == 'video' ) ) {
					$this->update_post_meta($post_id, '_sap_tumblr_status', '1');
				}
			}
		}

		//Intagram posting method  
		$sap_instagram_status = $this->get_post_meta($post_id, '_sap_instagram_status');
		if (!empty($sap_instagram_options['enable_instagram']) && empty($sap_instagram_status) && ( isset($_POST['form-submitted']) || isset($_POST['form-updated']) )) {
				
			if (!empty($_POST['status']) && $_POST['status'] == 2 && $scheduled == 2) {
				$this->update_post_meta($post_id, '_sap_instagram_status', 2);
				$this->flash->setFlash($this->sap_common->lang('content_scheduled_instagram'), 'success');
			} else {

				if (!class_exists('SAP_Instagram')) {
					require_once ( CLASS_PATH . 'Social' . DS . 'instaConfig.php');
				}

				$instagram = new SAP_Instagram($user_id);
				$prefix = '_sap_instagram_post';

				$result = $instagram->sap_instagram_post_to_userwall($post_id);

				if (isset($result) && $result) {
					$this->update_post_meta($post_id, '_sap_instagram_status', '1');
				}
			}
		}

		////Check post first time inserting or updating... Google My Business
		$sap_gmb_status = $this->get_post_meta($post_id, '_sap_gmb_status');
		if (!empty($sap_gmb_options['enable_google_business']) && empty($sap_gmb_status) && ( isset($_POST['form-submitted']) || isset($_POST['form-updated']) )) {
				
			if (!empty($_POST['status']) && $_POST['status'] == 2 && $scheduled == 2) {
				$this->update_post_meta($post_id, '_sap_gmb_status', 2);
				$this->flash->setFlash($this->sap_common->lang('content_scheduled_gmb'), 'success');
			} else {

				if (!class_exists('SAP_Gmb')) {
					require_once ( CLASS_PATH . 'Social' . DS . 'gmbConfig.php');
				}

				$google_business = new SAP_Gmb($user_id);
				$prefix = '_sap_gmb_post';

				$gmb_result = $google_business->sap_send_post_to_gmb($post_id);

				if (isset($gmb_result['success']) && $gmb_result['success'] == '1') {
					$this->update_post_meta($post_id, '_sap_gmb_status', '1');
				}
			}
		}

		//Check post first time inserting or updating...
		$sap_pin_status      = $this->get_post_meta($post_id, '_sap_pin_status');

		//Check pinterest enable
		if( !empty($sap_pinterest_options['enable_pinterest']) && empty($sap_pin_status) && ( isset( $_POST['form-submitted'] ) || isset($_POST['form-updated']) ) ){

			//Check schedule enable
			if( !empty( $_POST['status'] ) && $_POST['status'] == 2 && $scheduled == 2){
				$this->update_post_meta( $post_id, '_sap_pin_status', 2);
				$this->flash->setFlash( $this->sap_common->lang('content_scheduled_pit'), 'success' );
			}else{

				if (!class_exists('SAP_Pinterest')) {
					require_once ( CLASS_PATH . 'Social' . DS . 'pinConfig.php');
				}

				$pinterest = new SAP_Pinterest($user_id);
				$prefix = '_sap_pin_post_';

				$pin_result = $pinterest->sap_pin_post_to_userwall( $post_id );

				if($pin_result) {
					$this->update_post_meta( $post_id, '_sap_pin_status', '1');
				}
			}
		} 
	}

	/**
	 * Short the Content As Per Character Limit
	 * 
	 * Handles to return short content as per character 
	 * limit
	 * 
	 * @package Social Auto Poster
	 * */
	public function sap_limit_character($content, $charlength = 140) {

		$excerpt = '';
		$charlength++;

		//check content length is greater then character length
		if (strlen($content) > $charlength) {

			$subex = substr($content, 0, $charlength - 5);
			$exwords = explode(' ', $subex);
			$excut = - ( strlen($exwords[count($exwords) - 1]) );

			if ($excut < 0) {
				$excerpt = substr($subex, 0, $excut);
			} else {
				$excerpt = $subex;
			}
		} else {
			$excerpt = $content;
		}

		//return short content
		return $excerpt;
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
			'blogger' => 'blogger',
		);
	}

	/**
	 * Handle all schedule Social posts
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function sap_manage_wall_shedule_social_post($post_id, $scheduled = false, $user_id = '') {
 	
 	
		//Get general options of Facebook;
		$sap_facebook_options = $this->settings->get_user_setting('sap_facebook_options', $user_id);
		$sap_facebook_options = !empty($sap_facebook_options) ? $sap_facebook_options : array();

		//Get general options of Twiiter;
		$sap_twitter_options = $this->settings->get_user_setting('sap_twitter_options', $user_id);
		$sap_twitter_options = !empty($sap_twitter_options) ? $sap_twitter_options : array();

		//Get general options of linkedin;
		$sap_linkedin_options = $this->settings->get_user_setting('sap_linkedin_options', $user_id);
		$sap_linkedin_options = !empty($sap_linkedin_options) ? $sap_linkedin_options : array();

		//Get general options of Tumblr;
		$sap_tumblr_options = $this->settings->get_user_setting('sap_tumblr_options', $user_id);
		$sap_tumblr_options = !empty($sap_tumblr_options) ? $sap_tumblr_options : array();

		//Get general options of Google My Business
		$sap_gmb_options = $this->settings->get_user_setting('sap_google_business_options', $user_id);
		$sap_gmb_options = !empty($sap_gmb_options) ? $sap_gmb_options : array();

		//Get general options of Pinterest
		$sap_pinterest_options = $this->settings->get_user_setting('sap_pinterest_options', $user_id);
		$sap_pinterest_options = !empty($sap_pinterest_options) ? $sap_pinterest_options : array();

		$sap_instagram_options = $this->settings->get_user_setting('sap_instagram_options', $user_id);
		$sap_instagram_options = !empty($sap_instagram_options) ? $sap_instagram_options : array();

		//Get general options of Reddit
		$sap_reddit_options = $this->settings->get_user_setting('sap_reddit_options', $user_id);
		$sap_reddit_options = !empty($sap_reddit_options) ? $sap_reddit_options : array();

		//Get general options of Blogger
		$sap_blogger_options = $this->settings->get_user_setting('sap_blogger_options', $user_id);
		$sap_blogger_options = !empty($sap_blogger_options) ? $sap_blogger_options : array();

		//Check post first time inserting or updating...
		$sap_fb_status = $this->get_post_meta($post_id, '_sap_fb_status');

		//Check schedule enable
		if (!empty($sap_facebook_options['enable_facebook']) && !empty($sap_fb_status) && $sap_fb_status == 2 && $scheduled == 1) {

			if (!class_exists('SAP_Facebook')) {
				require_once ( CLASS_PATH . 'Social' . DS . 'fbConfig.php');
			}

			$facebook = new SAP_Facebook($user_id);
			$fb_result = $facebook->sap_fb_post_to_userwall($post_id);

			if (!empty($fb_result)) {
				$this->update_post_meta($post_id, '_sap_fb_status', '1');
			}
		}
		

		$sap_tw_status = $this->get_post_meta($post_id, '_sap_tw_status');

		//Check schedule enable
		if (!empty($sap_twitter_options['enable_twitter']) && !empty($sap_tw_status) && $sap_tw_status == 2 && $scheduled == 1) {

			//Upload custom image if exist
			if (!class_exists('SAP_Twitter')) {
				require_once ( CLASS_PATH . 'Social' . DS . 'twitterConfig.php');
			}

			$this->twposting = new SAP_Twitter($user_id);
			$tw_result = $this->twposting->sap_post_to_twitter($post_id);
			if (!empty($tw_result)) {
				$this->update_post_meta($post_id, '_sap_tw_status', '1');
			}
		}

		$sap_li_status = $this->get_post_meta($post_id, '_sap_li_status');

		//Check schedule enable
		if (!empty($sap_linkedin_options['enable_linkedin']) && !empty($sap_li_status) && $sap_li_status == 2 && $scheduled == 1) {

			if (!class_exists('SAP_Linkedin')) {
				require_once( CLASS_PATH . 'Social' . DS . 'liConfig.php' );
			}

			$linkedin = new SAP_Linkedin($user_id);

			$li_result = $linkedin->sap_post_to_linkedin($post_id);
			if (!empty($li_result['success'])) {
				$this->update_post_meta($post_id, '_sap_li_status', '1');
			}
		}

		$sap_tu_status = $this->get_post_meta($post_id, '_sap_tumblr_status');
		//Check schedule enable
		if (!empty($sap_tumblr_options['enable_tumblr']) && !empty($sap_tu_status) && $sap_tu_status == 2 && $scheduled == 1) {

			if (!class_exists('SAP_Tumblr')) {
				require_once( CLASS_PATH . 'Social' . DS . 'tumblrConfig.php' );
			}
			

			$tumblr = new SAP_Tumblr($user_id);

			$tm_result = $tumblr->sap_post_to_tumblr($post_id);
			if (isset($tm_result) && $tm_result == '201') {
				$this->update_post_meta($post_id, '_sap_tumblr_status', '1');
			}
		}

		//Code to handle schedule posts for Google My Business
		$sap_gmb_status = $this->get_post_meta($post_id, '_sap_gmb_status');
		if (!empty($sap_gmb_options['enable_google_business']) && !empty($sap_gmb_status) && $sap_gmb_status == 2 && $scheduled == 1) {

			if (!class_exists('SAP_Gmb')) {

				require_once (CLASS_PATH . 'Social' . DS . 'gmbConfig.php');
			}

			$google_business = new SAP_Gmb($user_id);
			$gmb_result = $google_business->sap_send_post_to_gmb($post_id);

			if (isset($gmb_result['success']) && $gmb_result['success'] == '1') {

				$this->update_post_meta($post_id, '_sap_gmb_status', '1');
			}
		}

		//Code to handle schedule posts for Pinterest Cookie Method
		$sap_pin_status = $this->get_post_meta($post_id, '_sap_pin_status');
		if (!empty($sap_pinterest_options['enable_pinterest']) && !empty($sap_pin_status) && $sap_pin_status == 2 && $scheduled == 1) {

			if (!class_exists('SAP_Pinterest')) {

				require_once (CLASS_PATH . 'Social' . DS . 'pinConfig.php');
			}

			$pinterest = new SAP_Pinterest($user_id);
			$pin_result = $pinterest->sap_pin_post_to_userwall( $post_id );

			if ($pin_result) {
				$this->update_post_meta($post_id, '_sap_pin_status', '1');
			}
		}

		//Code to handle schedule posts for Instagram method
		$sap_instagram_status = $this->get_post_meta($post_id, '_sap_instagram_status');
		if (!empty($sap_instagram_options['enable_instagram']) && !empty($sap_instagram_status) && $sap_instagram_status == 2 && $scheduled == 1) {
			if (!class_exists('SAP_Instagram')) {
				require_once (CLASS_PATH . 'Social' . DS . 'instaConfig.php');
			}

			$instagram = new SAP_Instagram($user_id);
			$insta_result = $instagram->sap_instagram_post_to_userwall( $post_id );

			if ($insta_result) {
				$this->update_post_meta($post_id, '_sap_instagram_status', '1');
			}
		}

		//Code to handle schedule posts for Reddit method
		
		$sap_reddit_status = $this->get_post_meta($post_id, '_sap_reddit_status');
			
		if (!empty($sap_reddit_options['enable_reddit']) && !empty($sap_reddit_status) && $sap_reddit_status == 2 && $scheduled == 1) {

			//Check schedule enable
			
			if (!class_exists('SAP_Reddit')) {
				require_once ( CLASS_PATH . 'Social' . DS . 'redditConfig.php');
			}

			$reddit = new SAP_Reddit($user_id);
			$prefix = '_sap_reddit_post_';

			$reddit_result = $reddit->sap_reddit_post_to_userwall($post_id);

			if (!empty($reddit_result)) {
				$this->update_post_meta($post_id, '_sap_reddit_status', '1');
			}
			
		}


		//Code to handle schedule posts for Blogger method
		$sap_blogger_status = $this->get_post_meta( $post_id, '_sap_blogger_status' );
			
		if ( !empty( $sap_blogger_options['enable_blogger'] ) && !empty( $sap_blogger_status ) && $sap_blogger_status == 2 && $scheduled == 1 ) {

			//Check schedule enable
			
			if ( !class_exists( 'SAP_Blogger' ) ) {
				require_once ( CLASS_PATH . 'Social' . DS . 'bloggerConfig.php' );
			}

			$blogger = new SAP_Blogger($user_id);
			$prefix = '_sap_blogger_post_';

			$blogger_result = $blogger->sap_blogger_post_to_userwall( $post_id );

			if ( !empty( $blogger_result ) ) {
				$this->update_post_meta( $post_id, '_sap_blogger_status', '1' );
			}
			
		}

	}

}
