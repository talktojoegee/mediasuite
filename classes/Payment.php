<?php

/**
 * Payment Class function
 * 
 * A class contains common function to be used to throughout the System
 *
 * @package Social Auto Poster
 * @since 2.0.0
 */

class SAP_Payment {

	private $_table_users;
	private $_plan_table;
	private $_db;
	public $flash;
	public $common;		
	public $user;		
	public $plan;		
	public $settings;	
	public $membership;
	public $sap_common;

	public function __construct() {

		global $sap_common;
		
		$this->_db = new Sap_Database();
		$this->_plan_table = 'sap_plans';
		$this->_table_users = 'sap_users';
		$this->_table_membership = 'sap_membership';
		$this->_table_payment_history = 'sap_payment_history';
		$this->sap_common = $sap_common;

		$this->flash = new Flash();
		$this->common = new Common();	

		if( !class_exists('SAP_Users')){
			require_once CLASS_PATH.'/Users.php';
		}

		if( !class_exists('SAP_Plans')){
			require_once CLASS_PATH.'/Plans.php';
		}

		if( !class_exists('SAP_Membership')){
			require_once CLASS_PATH.'/Membership.php';
		}

		$this->plan = new SAP_Plans();		
		$this->settings 	= new SAP_Settings();		
		
	}

	/**
	 * Hendle to render  payment form
	 * 
	 * Handels render payment form
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function payment(){
		
		global $match;

		$user 	= sap_get_current_user();

		// Get login user data
		$login_user = $this->login_user_details($user['user_id']);

		$url 			= $_SERVER['REQUEST_URI'];
		$is_upgrade 	= strpos( $url,'upgrade');

		$upgrade = 'no';
		if( $is_upgrade != '' ){ 
			$upgrade = 'yes';
		}		
		
		if($upgrade == 'yes'){
			$plan_data  = $this->plan->get_upgrade_plans( $login_user->id );
		}
		else{	

			$plan_data  = $this->plan->get_plans();	
		}

		$plan_exits = '';
		if( !empty($plan_data)){
			$plan_exits = 'yes';
		}
		

		$membership_data = $this->_db->get_row('SELECT * FROM '.$this->_table_membership.' WHERE user_id = '.$user['user_id'] , true);



		$str_time = strtotime($membership_data->expiration_date);			

		if( (  $membership_data->membership_status == '1'  || $membership_data->membership_status == '3' )   && ( empty($str_time) || date('Y-m-d') < date('Y-m-d',$str_time ) )  && ( $is_upgrade == '' )  ){
		
			header("Location:" . SAP_SITE_URL);
			die();
		}	

		
		if($membership_data->membership_status == '0' && $match['name'] != 'back-payment-page' ){

			header( "Location:" . SAP_SITE_URL . "/payment/subscription/" );			
		}
		else{
			$template_path = $this->common->get_template_path('Payment' . DS . 'payment.php' );
			include_once( $template_path );
		}		
	}



	/**
	 * Hendle to render  subscription details page if user payment status is pending
	 * 
	 * Handels render payment form
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */

	public function subscription(){

		
		$user 	= sap_get_current_user();

		// Get login user data
		$login_user = $this->login_user_details($user['user_id']);


		$url 			= $_SERVER['REQUEST_URI'];
		$is_upgrade 	= strpos( $url,'upgrade');

		$membership_data = $this->_db->get_row('SELECT * FROM '.$this->_table_membership.' WHERE user_id = '.$user['user_id'] , true);

		$str_time = strtotime($membership_data->expiration_date);			

		if( (  $membership_data->membership_status == '1'  || $membership_data->membership_status == '3' )   && ( empty($str_time) || date('Y-m-d') < date('Y-m-d',$str_time ) )  && ( $is_upgrade == '' )  ){
		
			header("Location:" . SAP_SITE_URL);
			die();
		}


		$this->user = new SAP_Users();
		$template_path = $this->common->get_template_path('Payment' . DS . 'subscription.php' );
			include_once( $template_path );
	}


	/**
	 * Login user details
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function login_user_details($user_id) {
		
		$result = array();
		
		if (isset($user_id) && !empty($user_id)) {

			try {
				$result = $this->_db->get_row("SELECT * FROM " . $this->_table_users . " where `id` = '{$user_id}'", true);
			} catch (Exception $e) {
				return $e->getMessage();
			}			
			return $result;
		}
	}

	/**	
	 * 
	 * Handels to make payment
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function make_payment( $payment_data = array()){

		$is_proration_active = (!defined('PRORATION_CREDITS') || PRORATION_CREDITS != FALSE ) ? true: false;

		// Get current user
		$user = sap_get_current_user();

		if(isset($_POST['is_upgrade']) && $_POST['is_upgrade'] != '' ){
			$payment_data = $_POST;	
		}

		// Get stripe settings
		$test_publishable_key 	= $this->settings->get_options('test_publishable_key');
		$test_secret_key 		= $this->settings->get_options('test_secret_key');

		$live_publishable_key 	= $this->settings->get_options('live_publishable_key');
		$live_secret_key 		= $this->settings->get_options('live_secret_key');

		$stripe_test_mode 		= $this->settings->get_options('stripe_test_mode');		


		// IF send box enabled
		if( $stripe_test_mode == 'yes' ) {			
			$publish_key	= $test_publishable_key;
			$secret_key		= $test_secret_key;
		}
		else {			
			$publish_key	= $live_publishable_key;
			$secret_key		= $live_secret_key;
		}

		$stripe_customer_id = '';

		// Get Payment data
		$user_id 	= isset($payment_data['user_id']) ? $payment_data['user_id'] : $_POST['user_id'];

		$plan_id 	= isset($payment_data['sap_plan']) ? $payment_data['sap_plan'] : '';
		$action 	= isset($payment_data['gateway_type']) ? $payment_data['gateway_type'] : $_POST['gateway_type'];

		$card_cvv 	= isset($payment_data['card_cvv']) ? $payment_data['card_cvv'] : $_POST['card_cvv'];
		$card_number 	= isset( $payment_data['card_number'] ) ? $payment_data['card_number'] : $_POST['card_number'];
		$card_exp_month = isset( $payment_data['card_exp_month'] ) ? $payment_data['card_exp_month'] : $_POST['card_exp_month'];
		$card_exp_year 	= isset( $payment_data['card_exp_year'] ) ? $payment_data['card_exp_year'] : $_POST['card_exp_year'];

		$upgrade = isset( $payment_data['is_upgrade'] ) ? $payment_data['is_upgrade'] : 'no';
		

		if( $action == 'stripe' ) {			

			// Get login user data
			$login_user = $this->login_user_details($user_id);

			// Get selected Plan data
			$plan_data  	= $this->plan->get_plan( $plan_id, true );
			$plan_diration 	= $plan_data->subscription_expiration_days;


			$plan_amount =  $plan_data->price;
			if( $upgrade == 'yes' &&  $is_proration_active == true ){
				$discount_amt  	= $this->plan_proration_credit();
				$plan_amount 	= $plan_data->price - $discount_amt;
			}

			$this->user = new SAP_Users();

			// Get user membership details
			$user_membership_detail = $this->user->get_user_subscription_details($user_id);

	
			if( $user_membership_detail ){
				$stripe_customer_id = $user_membership_detail->customer_id;
			}
			else{
				$stripe_customer_id = '';
			}
			
			// load the stripe libraries
			require_once( LIB_PATH . '/stripe/init.php');
			
			// attempt to charge the customer's card
			try {

				$stripe 	= new \Stripe\StripeClient($secret_key);
	
	
				//Create stripe customer if not exits
				if( empty( $stripe_customer_id )){

					$display_name = $login_user->first_name .' '. $login_user->last_name;

					// Create stripe customer if not exits
					$customer_data  = $stripe->customers->create(array(
					  	'name' 			=> $display_name,
					  	'email' 		=> $login_user->email,
					  	'description' 	=> $plan_data->name,
					));					
					$stripe_customer_id = $customer_data->id;
				}

				// Created customer payment method...
				$customer_payment_method = $stripe->paymentMethods->create( array(
				  	'type' => 'card',
				  	'card' => array(
				    	'number' 	=> $card_number,
				    	'exp_month' => $card_exp_month,
				    	'exp_year' 	=> $card_exp_year,
				    	'cvc' 		=> $card_cvv,
				  	),
				));

				//Payment method id
				$payment_method = $customer_payment_method->id;

				//Stripe attach payment method.
				$paymentMethods = $stripe->paymentMethods->attach(
					$payment_method,
					array(
						'customer' => $stripe_customer_id
					)
				);

				// Check if auto renew is check the create subscription charge
				// If auto renew enble and not empty expiration days
				if(isset($payment_data['auto_renew']) && !empty($payment_data['auto_renew']) && !empty( $plan_data->subscription_expiration_days ) ){

					// Check if stripe product empty then create new product
					if(empty($plan_data->stripe_product_id) || empty($plan_data->stripe_subscription_id)){
						
						//Create product 
						$product = $stripe->products->create([
						  	'name' => $plan_data->name
						]);
						
						$stripe_price = $stripe->prices->create([
						  	'unit_amount' 	=> $plan_amount * 100,						  	
						  	'currency' 		=> 'usd',
						  	'recurring' 	=> ['interval' => 'day', 'interval_count' => $plan_data->subscription_expiration_days],
						  	'product' 		=> $product->id
						]);					
						
						$stripe_product_id = $product->id;
						$stripe_product_price_id = $stripe_price->id;	

						$update_plan_data = array(
							'stripe_subscription_id' => $stripe_product_price_id,
							'stripe_product_id'		 => $stripe_product_id
						);

						//Update Product & price id in plan 
						$update_plan_data = $this->_db->escape($update_plan_data);
						$update_plan = $this->_db->update($this->_plan_table, $update_plan_data,array('id' => $plan_data->id));
					}
					else{							
						$stripe_product_id = $plan_data->stripe_product_id;
						$stripe_product_price_id = $plan_data->stripe_subscription_id;
					}
					
					
					// Create subscription charge					
					$charge = $stripe->subscriptions->create(
						array(
				  			'customer' => $stripe_customer_id,
				  			'items' => array(
				    			array(
				    				'price' => $stripe_product_price_id
				    			),
				  			),
				  			'default_payment_method' => $payment_method
						)
					);

					if( isset( $discount_amt ) && !empty( $discount_amt ) ){
						
						$manage_customer_balance = $stripe->customers->createBalanceTransaction(
							$stripe_customer_id,
							array(
						  		'amount' 	=> $discount_amt * 100,
						  		'currency' 		=> 'usd'
							)
						);						
					}
				}
				else{ // Create charge if auto renew is disabled				


					$strip_token = $stripe->tokens->create([
					  'card' => [
					   	'number' 	=> trim($card_number),
				    	'exp_month' => $card_exp_month,
				    	'exp_year' 	=> $card_exp_year,
				    	'cvc' 		=> $card_cvv,
					  ],
					]);
					
					$strip_source = $stripe->customers->createSource(
					    $stripe_customer_id,
					    ['source' => $strip_token->id]
					);
					
					//Create one time charge
					/*$charge = $stripe->charges->create([*/
	
					$status='';
					$charge = $stripe->paymentIntents->create([
					  'amount' 		=> $plan_amount * 100,
					  'currency' 	=> 'usd',
					  'description' => 'Create charge for '.$plan_data->name,
					  'customer' 	=> $stripe_customer_id,
					  'confirm'     => true,
					  'payment_method_types' =>['card' ],
                   	  'payment_method' => $payment_method,
                   	  
                      'confirmation_method' => 'automatic',
					  'return_url'	=> SAP_SITE_URL.'/get_stripe_data/?user_id='.$user_id
					]);	
					
					if( !empty($charge->id) )	{
						$status = $charge->status;
					}
						
				}


				if($charge->id != '' && $upgrade == 'yes'){
					$this->membership = new SAP_Membership();
					$cancel_membership_user_data['user_id'] = $user_id;
					$this->membership->cancle_user_membership($cancel_membership_user_data);
				}	



				// get expiration days of the subscription
				$expiration_days = !empty($plan_data->subscription_expiration_days)? $plan_data->subscription_expiration_days :0;

				//completed
				$expiration_date = '';
				if( !empty( $expiration_days ) ){
					$current_date 	 	= date('Y-m-d H:i:s');
					$expiration_date 	= date('Y-m-d', strtotime($current_date. ' + '.$expiration_days.' day'));
				}
						
				$membership_status 	= ( $status == 'succeeded' && $charge->amount_received > 0 ) ? '1' : '0';

				$subscription_id = $charge->id;
				$user = sap_get_current_user();

				if( $user ){
					$customer_name = $this->_db->filter($user['first_name']) .' '. $this->_db->filter($user['last_name']);
					$customer_email = $this->_db->filter($user['user_email']);
				}
				else{
					$customer_name = $this->_db->filter($payment_data['sap_firstname']) .' '. $this->_db->filter($payment_data['sap_lastname']);
					
					$customer_email = $this->_db->filter($payment_data['sap_email']);
				}
				

				// IF plan upgrade
				if( $upgrade == 'yes' ){
					
					$old_plan = $this->plan->get_plan($user_membership_detail->plan_id , true); 

					$update_data = array(
						'plan_id'           => $payment_data['sap_plan'],
						'membership_status' => $membership_status,
						'customer_id' 	    => $stripe_customer_id,
						'recurring'			=> isset($payment_data['auto_renew']) ? '1' : '0',
						'expiration_date'   => $expiration_date,
						'gateway'   		=> $action,
						'previous_plan'     => $old_plan->name,
						'membership_duration_days'   => $plan_diration,
						'subscription_id'     => $subscription_id,
						'created_date'      => date('Y-m-d H:i:s'),
						'modified_date'     => date('Y-m-d H:i:s'),
						'upgrade_date'     	=> date('Y-m-d H:i:s')
					);					

					//Insert subscription or charge detail into the membership table
					$update_data = $this->_db->escape( $update_data );
					$result = $this->_db->update($this->_table_membership, $update_data, array('user_id' => $user_id));
					$membership_id = $user_membership_detail->id;

				}
				else{

					/*
					*  Check if user details not empty then get user membership details 
					*  else create new membership 
					*/
					if( $user ){

						$customer_name = $user['first_name'] .' '.$user['last_name'];

						//Prepare Membership date
						$update_data = array(
							'user_id'           => $user_id,
							'plan_id'           => $payment_data['sap_plan'],
							'customer_id' 	    => $stripe_customer_id,
							'customer_name'     => $customer_name,
							'membership_status' => $membership_status,
							'gateway'   		=> $action,
							'membership_duration_days'	=> $plan_diration,
							'subscription_id'     => $subscription_id,
							'recurring'			=> isset($payment_data['auto_renew']) ? '1' : '0',
							'expiration_date'   => $expiration_date,
							'created_date'      => date('Y-m-d H:i:s'),
							'modified_date'     => date('Y-m-d H:i:s'),
						);


						$this->membership 		= new SAP_Membership();
						$user_membership_data 	=  $this->membership->get_membership_by_user_id($user_id,true );						

						// Check user membership details if exits then update membership else create new membership
						if( !empty( $user_membership_data ) ){

							$update_data = $this->_db->escape( $update_data );
							$result 	 = $this->_db->update($this->_table_membership, $update_data, array('user_id' => $user_id));	
							$membership_id = $user_membership_data->id;
						}
						else{
							$update_data 	= $this->_db->escape( $update_data );
							$result 	 	= $this->_db->insert($this->_table_membership, $update_data);
							$membership_id 	= $this->_db->lastid();
						}
						//Insert subscription or charge detail into the membership table
					}
					else{
					
						$customer_name = $payment_data['sap_firstname'].' '.$payment_data['sap_lastname'];
						$customer_email = $payment_data['sap_email'];

						$update_data = array(
							'user_id'           => $user_id,
							'plan_id'           => $payment_data['sap_plan'],
							'customer_id' 	    => $stripe_customer_id,
							'customer_name'     => $customer_name,
							'membership_status' => $membership_status,
							'gateway'   		=> $action,
							'membership_duration_days'	=> $plan_diration,
							'subscription_id'   => $subscription_id,
							'recurring'			=> isset($payment_data['auto_renew']) ? '1' : '0',
							'expiration_date'   => $expiration_date,
							'created_date'      => date('Y-m-d H:i:s'),
							'modified_date'     => date('Y-m-d H:i:s'),
							'membership_created_date' => date('Y-m-d H:i:s'),
						);

						//Insert subscription or charge detail into the membership table
						$update_data = $this->_db->escape( $update_data );
						$result = $this->_db->insert($this->_table_membership, $update_data, $conditions);						

						$membership_id = $this->_db->lastid();
					}
				}

				if(!empty($membership_id)){

					$insert_payment = array(
						'user_id' 		=> $user_id,
						'membership_id' => $membership_id,
						'plan_id' 		=> $payment_data['sap_plan'],
						'customer_id' 	=> $stripe_customer_id,
						'customer_name' => $customer_name,
						'customer_email' => $customer_email,
						'payment_date' 	=> date('Y-m-d H:i:s'),
						'amount' 		=> $plan_amount,
						'type' 			=> '1',
						'gateway' 		=> 'stripe',
						'payment_status'=> ( $status == 'succeeded' && $charge->amount_received > 0 ) ? '1' : '0',
						'transaction_id'=> $charge->id,
						'transaction_data' => serialize($charge),
						'created_date' 	=> date('Y-m-d H:i:s'),
						'modified_date' 	=> date('Y-m-d H:i:s'),
					);
						
					$insert_payment = $this->_db->escape( $insert_payment );
					$result 		= $this->_db->insert($this->_table_payment_history, $insert_payment);
				}

				return $charge;
			}
			catch (Exception $e) {
				// redirect on failed payment
				$this->flash->setFlash($e->getMessage(), 'error');
			}
		}
	}

	/**	
	 * 
	 * Handels to update user data after payment successfull
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function update_user_strip_data( $user_id, $update_data ){
		$conditions = array('id' => $user_id);
		$result 	= $this->_db->update($this->_table_users, $update_data, $conditions);
	}

	
	/**	
	 * Stripe webhook call after invoice generate 
	 *
	 * Handels update user subscrition  
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function subscription_webhook(){

		$email 	= new Sap_Email();		

		$body 		= @file_get_contents('php://input');
		$event_json = json_decode($body,true);
		$charge     = serialize($event_json);
		$billing_reason = isset($event_json['data']['object']['billing_reason'])?$event_json['data']['object']['billing_reason']:'';
		
		// Get stripe event type
		$event_type = isset($event_json['type']) ? $event_json['type'] : '';		


		// check event type for refunded
		if( $event_type == 'charge.refunded'){				

			// Check for stripe customer id
			if( isset($event_json['data']['object']['customer']) && !empty($event_json['data']['object']['customer'])){

				//Stripe customer id
				$customer_id = $event_json['data']['object']['customer'];
				

				//Get customer details
				$cust_plan_result = $this->_db->get_row( "SELECT u.*,m.plan_id FROM sap_users as u INNER JOIN sap_membership as m ON u.id = m.user_id WHERE m.customer_id  = '{$customer_id}'",'ARRAY_A' );				
				
				//Get customer plan details
				$plan_data  = $this->plan->get_plan( $cust_plan_result->plan_id, true );
				$this->user = new SAP_Users();

				//Get customer memebership details
				$membership_data = $this->user->get_user_subscription_details($cust_plan_result->id);

				if( !empty( $cust_plan_result ) ){

					// Update membership status to expired
					$update_data = array(
						'membership_status' => '2'
					);

					//Update membership details
					$result = $this->_db->update($this->_table_membership, $update_data, array('user_id' => $cust_plan_result->id));

					if( $result ){

						$sql = 'SELECT id FROM '.$this->_table_payment_history.' WHERE user_id = "'.$cust_plan_result->id.'" AND customer_id ="'.$customer_id.'"';
						
						$memebership_payments = $this->_db->get_results( $sql );
						
						if(!empty( $memebership_payments )){

							foreach ($memebership_payments as $key => $payments) {

								$payment_history_update_data['payment_status'] = '3';

								$result = $this->_db->update($this->_table_payment_history, $payment_history_update_data, array('id' => $payments->id));
							}
						}
					}
				}
			}
		}

		if(  $billing_reason == 'subscription_cycle'){ // Uncomment for live mode

			$payment_status = isset($event_json['data']['object']['status']) ? $event_json['data']['object']['status']: '';

			
			if( $event_type != 'invoice.payment_succeeded' ){ // fail payment

				if( isset($event_json['data']['object']['lines']['data']) && !empty($event_json['data']['object']['lines']['data'])){				

					$customer_id = isset( $event_json['data']['object']['customer'] ) ? $event_json['data']['object']['customer'] :'';
					

					$cust_plan_result = $this->_db->get_row( "SELECT u.*,m.plan_id FROM sap_users as u INNER JOIN sap_membership as m ON u.id = m.user_id WHERE m.customer_id  = '{$customer_id}'",'ARRAY_A' );

					$plan_data  = $this->plan->get_plan( $cust_plan_result->plan_id, true );
					$this->user = new SAP_Users();

					$membership_data = $this->user->get_user_subscription_details($cust_plan_result->id);

					if( !empty( $cust_plan_result ) ){
						
						foreach ($event_json['data']['object']['lines']['data'] as $key => $subscription) {

							$price_id 	= $subscription['price']['id'];

							$expiration_days = !empty($plan_data->subscription_expiration_days)? $plan_data->subscription_expiration_days :0;
							
							$update_data = array(
								'membership_status' => '0'								
							);

							//Insert subscription or charge detail into the membership table
							$result = $this->_db->update($this->_table_membership, $update_data, array('user_id' => $cust_plan_result->id));

							if($result){
								
								$insert_payment = array(
									'user_id' 		=> $cust_plan_result->id,
									'membership_id' => $membership_data->id,
									'plan_id' 		=> $membership_data->plan_id,
									'customer_id' 	=> $membership_data->customer_id,
									'customer_name' => $cust_plan_result->first_name .' '.$cust_plan_result->last_name,
									'customer_email' => $cust_plan_result->email,
									'payment_date' 	=> date('Y-m-d H:i:s'),
									'amount' 		=> $plan_data->price,
									'type' 			=> '1',
									'gateway' 		=> 'stripe',
									'payment_status'=> '3',
									'transaction_id'=> $event_json['data']['object']['charge'],
									'transaction_data' => serialize($charge),
									'created_date' 	=> date('Y-m-d H:i:s'),
									'modified_date' 	=> date('Y-m-d H:i:s'),
								);
								$insert_payment = $this->_db->escape( $insert_payment );
								$result_payment = $this->_db->insert($this->_table_payment_history, $insert_payment);
							}
						}
					}
				}
			}
			else{

				if( isset($event_json['data']['object']['lines']['data']) && !empty($event_json['data']['object']['lines']['data'])){
				

				$customer_id = isset( $event_json['data']['object']['customer'] ) ? $event_json['data']['object']['customer'] :'';

				$cust_plan_result = $this->_db->get_row( "SELECT u.*,m.plan_id FROM sap_users as u INNER JOIN sap_membership as m ON u.id = m.user_id WHERE m.customer_id  = '{$customer_id}'",'ARRAY_A' );

				$plan_data  = $this->plan->get_plan( $cust_plan_result->plan_id, true );

				$this->user = new SAP_Users();
				
				$membership_data = $this->user->get_user_subscription_details($cust_plan_result->id);

				$smtp_setting = $this->settings->get_options('sap_smtp_setting');
				
				if( !empty( $cust_plan_result ) ){
					
					foreach ($event_json['data']['object']['lines']['data'] as $key => $subscription) {

						$price_id 	= $subscription['price']['id'];

						$expiration_days = !empty($plan_data->subscription_expiration_days)? $plan_data->subscription_expiration_days :0;

						$current_date 	 = date('Y-m-d H:i:s');
						$expiration_date = date('Y-m-d', strtotime($current_date. ' + '.$expiration_days.' day'));

						$renewal_email_subject = $this->settings->get_options('renewal_email_subject');
						$renewal_email_content = $this->settings->get_options('renewal_email_content');

						ob_start();

						$template_path = $this->common->get_template_path('Payment' . DS . 'subscription-renew-email-template.php' );
						include_once( $template_path );

						$message = ob_get_clean();

						if( isset($smtp_setting['enable'] ) && $smtp_setting['enable'] == 'yes' ){
							$email = new Sap_Email();
							$email->send($cust_plan_result->email,$renewal_email_subject, $message);
						}
						else{

							$headers = "MIME-Version: 1.0\r\n";
							$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
							
							$email->send($cust_plan_result->email,$renewal_email_subject, $message,$headers);
						}
						
						$update_data = array(
							'membership_status' => '1',
							'expiration_date'   => $expiration_date,
							'renew_date'   		=> date('Y-m-d H:i:s'),
						);

						//Insert subscription or charge detail into the membership table
						$result = $this->_db->update($this->_table_membership, $update_data, array('user_id' => $cust_plan_result->id));

						if($result){
							
							$insert_payment = array(
								'user_id' 		=> $cust_plan_result->id,
								'membership_id' => $membership_data->id,
								'plan_id' 		=> $membership_data->plan_id,
								'customer_id' 	=> $membership_data->customer_id,
								'customer_name' => $cust_plan_result->first_name .' '.$cust_plan_result->last_name,
								'customer_email' => $cust_plan_result->email,
								'payment_date' 	=> date('Y-m-d H:i:s'),
								'amount' 		=> $plan_data->price,
								'type' 			=> '1',
								'gateway' 		=> 'stripe',
								'payment_status'=> '1',
								'transaction_id'=> $event_json['data']['object']['charge'],
								'transaction_data' => serialize($charge),
								'created_date' 	=> date('Y-m-d H:i:s'),
								'modified_date' 	=> date('Y-m-d H:i:s'),
							);
							$insert_payment = $this->_db->escape( $insert_payment );
							$result_payment = $this->_db->insert($this->_table_payment_history, $insert_payment);
						}
					}
				}
			}

			}			
		}
	}


	/**	
	 * Render plan details with price title and description
	 *	 
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function plan_details(){
		
		$plan_id = isset( $_POST['plan_id'] ) ? $_POST['plan_id'] : '';
		
		$plan_data  = $this->plan->get_plan( $plan_id,true );
		$networks 	= unserialize($plan_data->networks);

		
		$expiration_date = 'Never';
		if( !empty( $plan_data->subscription_expiration_days ) ){
			$expiration_date = sap_format_date(get_date_after_x_date( '',$plan_data->subscription_expiration_days ));	
		}
		?>

		<div class="plan-preview-container">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th><?php echo $this->sap_common->lang('membership') ?></th>
						<th><?php echo $this->sap_common->lang('amount') ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo $plan_data->name?></td>
						<td>₦<?php echo  number_format($plan_data->price,2) ?></td>
					</tr>
				</tbody>
				<tfoot>
					<?php if( !empty($networks ) ){ ?>
						<tr class="rcp-total allow-network ">
							<th><?php echo $this->sap_common->lang('allowed_networks') ?></th>
							<td>
								<ul>
								<?php 
								$li_content = '';
							foreach ($networks as $key => $network) {
	        					$li_content .= '<li>'.sap_get_networks_label($network).'</li> ,';
	        				}
	        				echo rtrim($li_content,", ");
							?></ul></td>
						</tr>
					<?php } ?>
					<tr class="rcp-total">
						<th><?php echo $this->sap_common->lang('total') ?></th>
						<td>₦<?php echo  number_format($plan_data->price,2) ?></td>
					</tr>
					
					<tr>
						<th><?php echo $this->sap_common->lang('next_renewal_due') ?></th>
						<td><?php 					
						echo $expiration_date ?></td>
					</tr>			
				</tfoot>
			</table>
		</div>
		<?php
		echo ob_get_clean();
		die();
	}




	/**	
	 * Re payment from inner payment page
	 *	 
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function re_payment(){		

		$error = false;
		
		// check the plan is empty
		if ( empty( trim( $_POST['sap_plan'] ) ) ) {
			$error = true;
			$this->flash->setFlash($this->sap_common->lang('select_plan'), 'error' );
		}

		$plan_id 		 = isset( $_POST['sap_plan'] ) ? $_POST['sap_plan'] :''; 

		// get selected plan details
		$plan_data  	 = $this->plan->get_plan( $plan_id, true );			

		$plan_price =  0;
		if( !empty( $plan_data ) && isset( $plan_data->price ) ){
			$plan_price =  $plan_data->price;
		}		
		
		// Check the payment gateway is empty
		if ( empty( $_POST['gateway_type'] ) && !empty( $plan_price ) ) {
			$error = true;
			$this->flash->setFlash($this->sap_common->lang('select_payment_method'), 'error' );
		}		

		$upgrade 	= isset( $_POST['is_upgrade'] ) ? $_POST['is_upgrade'] : 'no';

		// Check if no error
		if( $error ) {

			if( isset($_POST['is_upgrade'] ) ){
				header( "Location:" . SAP_SITE_URL . "/payment/upgrade/" );
				exit;
			}
			else{
				header( "Location:" . SAP_SITE_URL . "/payment/payment/" );
				exit;
			}
		}


		if( empty( $_POST['gateway_type'] )){
			$_POST['gateway_type'] = 'manual';
		}

		$user_id 		= sap_get_current_user_id();
		$this->user = new SAP_Users();


		$gateway 		= 'manual';

		$transaction_id = '';
		$type 			= '0';
		$previous_plan  = '';

		$user_membership_detail = $this->user->get_user_subscription_details($user_id);
		
		if( $upgrade == 'yes' ){
			$old_plan = $this->plan->get_plan($user_membership_detail->plan_id , true); 			
			$type = '2';
			$previous_plan = $old_plan->name;
		}
		
		if( isset( $_POST['gateway_type'] ) && !empty( $_POST['gateway_type'] ) && $_POST['gateway_type'] == 'stripe'){

			$gateway = 'stripe';
			$auto_renew = isset( $_POST['auto_renew'] ) ? $_POST['auto_renew'] : '0';			

			// Stripe response
			$strie_payment_result = $stripe = $this->make_payment( $_POST );


			// Get transaction id from stripe response
			$transaction_id = $stripe->id;

			$user_id = $_POST['user_id'];
			$this->membership = new SAP_Membership();
			
			// Get user membership details
			$membership_data = $this->membership->get_membership_by_user_id( $user_id,true );

			// Get login user details
			$login_user 	= $this->login_user_details($user_id);


			$first_name = $login_user->first_name;
			$last_name 	= $login_user->last_name;
			$customer_email = $login_user->email;

			// Get plan expiration date
			$current_date 	 = date('Y-m-d H:i:s');			

			$expiration_date = '';
			if( !empty( $plan_data->subscription_expiration_days ) ){
				$expiration_date = date('Y-m-d', strtotime($current_date. ' + '.$plan_data->subscription_expiration_days.' day'));			
			}

			if( !empty($membership_data) ){

				$update_data['recurring'] 			= $auto_renew;
				$update_data['expiration_date'] 	= $expiration_date;
				$update_data['membership_status'] 	= '1';
				$update_data['plan_id'] 			= $plan_id;
				$update_data['previous_plan']     	= $previous_plan;
				
				

				$where['id'] 	= $membership_data->id;
				$update_data 	= $this->_db->escape($update_data);
				$membership_id 	= $this->_db->update($this->_table_membership, $update_data, $where );
			}

			if( !empty( $membership_id ) ){

				if( !isset($_POST['is_upgrade'] ) ){
					$insert_payment = array(
						'user_id' 		=> $user_id,
						'membership_id' => $membership_id,
						'plan_id' 		=> $plan_id,
						'customer_id' 	=> '',
						'customer_name' => $first_name .' '.$last_name,
						'customer_email' => $customer_email,
						'payment_date' 	=> date('Y-m-d H:i:s'),
						'amount' 		=> $plan_data->price,
						'type' 			=> $type,
						'gateway' 		=> $gateway,
						'payment_status'=> '1',
						'transaction_id'=> $transaction_id,
						'created_date' 	=> date('Y-m-d H:i:s'),
					);

					$insert_payment = $this->_db->escape($insert_payment);
					$result = $this->_db->insert($this->_table_payment_history, $insert_payment);

				}
			}
		}

		if(isset($_POST['gateway_type']) && !empty($_POST['gateway_type']) && $_POST['gateway_type'] == 'paypal'){
		}

		if(isset($_POST['gateway_type']) && !empty($_POST['gateway_type']) && $_POST['gateway_type'] == 'manual'){

			$this->make_manual_payment( $_POST );
		}


		if(  isset( $_POST['gateway_type'] ) && !empty( $_POST['gateway_type'] ) && $_POST['gateway_type'] == 'stripe' && empty($strie_payment_result) ){

			header( "Location:" . SAP_SITE_URL.'/payment/');
			die();

		}

		if( $upgrade == 'yes' ){
			header( "Location:" . SAP_SITE_URL.'/upgrade-thank-you/'.$user_id );
			exit;
		}
		else{
			header( "Location:" . SAP_SITE_URL.'/thank-you/'.$user_id );
			exit;
		}
	}

	/**	
	 * Create membership or update membership and create payment histry
	 *	 
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function make_manual_payment( $post_data = array() ){

		$update_data 	= array();

		$user_id 		= sap_get_current_user_id();
		
		if( !empty( $user_id) ){
			
			$login_user 	= $this->login_user_details($user_id);
			$first_name = $login_user->first_name;
			$last_name 	= $login_user->last_name;
			$customer_email 	= $login_user->email;
		}
		else{
			$first_name = $post_data['sap_firstname'];
			$last_name 	= $post_data['sap_lastname'];
			$customer_email = $post_data['sap_email'];
			$user_id 	= $post_data['user_id'];
		}
		
		$plan_id =  isset( $post_data['sap_plan'] ) ? $post_data['sap_plan'] : '';

		$this->membership = new SAP_Membership();
		$membership_data = $this->membership->get_membership_by_user_id( $user_id,true );
		$plan_data  	 = $this->plan->get_plan( $_POST['sap_plan'], true );
		

		$expiration_days = !empty($plan_data->subscription_expiration_days)? $plan_data->subscription_expiration_days :0;

		$this->user = new SAP_Users();

		$upgrade 		= isset( $_POST['is_upgrade'] ) ? $_POST['is_upgrade'] : 'no';
		$type 			= '1';
		$previous_plan  = '';

		$user_membership_detail = $this->user->get_user_subscription_details($user_id);		
		
		$plan_amount = $plan_data->price;

		if( $upgrade == 'yes' ){
			$old_plan = $this->plan->get_plan($user_membership_detail->plan_id , true); 			
			$type = '2';
			$previous_plan = $old_plan->name;

			$discount_amt  = $this->plan_proration_credit();
			$plan_amount = $plan_data->price - $discount_amt;
		}
		

		//completed
		$current_date 	 = date('Y-m-d H:i:s');

		$expiration_date = '';

		if( !empty( $expiration_days ) ){
			$expiration_date = date('Y-m-d', strtotime($current_date. ' + '.$expiration_days.' day'));
		}

		if( !empty( $membership_data ) ){ // IF user membership exits the update membership data	

			$update_data['recurring'] 			= '0';
			$update_data['expiration_date'] 	= $expiration_date;
			$update_data['membership_status'] 	= '0';
			$update_data['plan_id'] 			= $plan_id;
			$update_data['previous_plan']     	= $previous_plan;
			$update_data['upgrade_date']     	= date('Y-m-d H:i:s');
			$update_data['gateway']     		= 'manual';
			$update_data['membership_duration_days']	= $expiration_days;
			
			$where['id'] = $membership_data->id;
			
			$this->_db->update($this->_table_membership, $update_data, $where );
			$membership_id = $membership_data->id;
			
		}
		else{ // if user membership data empty the add new membership

			$insert_data = array(
				'user_id'           => $user_id,
				'plan_id'           => $plan_id,
				'customer_id' 	    => '',
				'customer_name'     => $first_name .' '.$last_name,
				'membership_status' => '0',
				'recurring'			=> '0',
				'expiration_date'   => $expiration_date,
				'created_date'      => date('Y-m-d H:i:s'),
				'modified_date'     => date('Y-m-d H:i:s'),
				'membership_created_date'     => date('Y-m-d H:i:s'),
				'gateway'     => 'manual',
				'membership_duration_days'     => $expiration_days,
			);

			//$conditions = array('id' => $user_id);
			$insert_data 	= $this->_db->escape($insert_data);
			$result 		= $this->_db->insert($this->_table_membership, $insert_data);

			$membership_id 	= $this->_db->lastid();			
		}

		// Added payment histry
		if(!empty($membership_id)){

			$insert_payment = array(
				'user_id' 		=> $user_id,
				'membership_id' => $membership_id,
				'plan_id' 		=> $_POST['sap_plan'],
				'customer_id' 	=> '',
				'customer_name' => $first_name .' '.$last_name,
				'customer_email'=> $customer_email,
				'payment_date' 	=> date('Y-m-d H:i:s'),
				'amount' 		=> $plan_amount,
				'type' 			=> $type,
				'gateway' 		=> 'manual',
				'payment_status'=> '1',
				'transaction_id'=> '',
				'transaction_data' => '',
				'created_date' 	=> date('Y-m-d H:i:s'),
				'modified_date' 	=> date('Y-m-d H:i:s'),
			);
			
			$insert_payment = $this->_db->escape($insert_payment);
			$result = $this->_db->insert($this->_table_payment_history, $insert_payment);			
		}

	}



	/**	
	 * Cron to expire membership
	 *	 
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function cron_to_expire_membership(){
		
		$expired_membership_email_subject 	= $this->settings->get_options('expired_membership_email_subject');
		
		$expired_membership_email_content 	= $this->settings->get_options('expired_membership_email_content');
		
		$current_date = date('Y-m-d');

		$end_date = date('Y-m-d', strtotime($current_date) );

		$query =  "SELECT m.* ,p.name as ".$this->_plan_table." FROM ".$this->_table_membership." as m INNER JOIN sap_plans as p ON  m.plan_id = p.id WHERE expiration_date  <= '{$current_date}' AND membership_status != '2'";

		$result = $this->_db->get_results( $query );

		if( !empty( $result ) ){
			
			foreach($result as $membership ){				
				
				if( !empty( $membership->expiration_date ) ){

					if( !empty( $expired_membership_email_content ) && !empty( $expired_membership_email_subject ) ){

						$user_data = sap_get_users_by_id($membership->user_id);

						$email = new Sap_Email();					

						$template_path = $this->common->get_template_path('Membership' . DS . 'expire-membership-notification-temp.php' );
						include_once( $template_path );						
						$message = ob_get_clean();

						$smtp_setting = $this->settings->get_options('sap_smtp_setting');

						if( isset($smtp_setting['enable'] ) && $smtp_setting['enable'] == 'yes' ){
							$email->send($user_data->email, $expired_membership_email_subject, $message);
						}
						else{
							$headers = "MIME-Version: 1.0\r\n";
							$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";							
							mail($user_data->email,$expired_membership_email_subject, $message,$headers);
						}
					}

					
					$update_membership_data = array(
						'membership_status' => '2'
					);					

					$this->_db->update($this->_table_membership, $update_membership_data,array('id' => $membership->id));
				}
			}
		}
	}


	/**	
	 * Get plan proration credit
	 *	 
	 * 
	 * @package Social Auto Poster
	 * @since 2.0.0
	 */
	public function plan_proration_credit(){

		$is_proration_active = (!defined('PRORATION_CREDITS') || PRORATION_CREDITS != FALSE ) ? true: false;


		if( !$is_proration_active ){
			return false;
		}

		$this->user = new SAP_Users();

		$user_id 		= sap_get_current_user_id();
		$login_user 	= $this->login_user_details($user_id);
		$user_membership_detail = $this->user->get_user_subscription_details($user_id);

		$plan_price 	 = $user_membership_detail->price;
		$expiration_days = $user_membership_detail->subscription_expiration_days;
		$plan_id 		 = $user_membership_detail->plan_id;
		$user_id 		 = $user_membership_detail->user_id;
		$created_date 	 = $user_membership_detail->membership_created_date;
		
		$upgrade_plan_id = isset( $_POST['plan_id'] ) ? $_POST['plan_id'] : 0;	

		$now 		= time(); // or your date as well
		$your_date 	= strtotime($created_date);
		$datediff 	= $now - $your_date;
		$duration   = $user_membership_detail->membership_duration_days;
		
		if( empty($duration) ) {
			$duration = $expiration_days;
		}

		$total_used_days = round($datediff / (60 * 60 * 24)) + 1;
		$discount_amt 	 = $plan_price;

		if(empty( $user_membership_detail->expiration_date )) {
			return false;
		}


		if( $total_used_days > 1 ){
		
			$discount_amt = ( $plan_price /  $expiration_days ) * $total_used_days;
			$discount_amt = $plan_price - $discount_amt;
			
		}

		if( isset($_POST['is_ajax'])){

			$up_plan_data = $this->plan->get_plan($upgrade_plan_id,true);
			
			ob_start();
			?>
				<br>
				<p>
				<?php echo sprintf($this->common->lang('membership_upgrede_message'), "$".round($discount_amt,2)) ?>
				</p>



				<table class="table">
				  	<thead>
				    	<tr>
					      <th scope="col"><?php echo $this->common->lang('membership') ?></th>
					      <th scope="col"><?php echo $this->common->lang('amount') ?></th>
				    	</tr>
				  	</thead>
				  	<tbody>
					    <tr>
					      	<td><?php echo $up_plan_data->name ?></td>
					      	<td>₦<?php echo number_format(round($up_plan_data->price,2)) ?></td>
					    </tr>
					    <tr>
					      	<td><?php echo $this->common->lang('proration_credit') ?></td>
					      	<td> - ₦<?php echo number_format(round($discount_amt,2)) ?></td>
					    </tr>

					    <tr>
					      	<td><?php echo $this->common->lang('next_renewal_due') ?></td>
					      	<td>
					      	<?php
					      		if( !empty( $up_plan_data->subscription_expiration_days ) ){
					      			echo sap_get_membership_expiration_date(get_date_after_x_date('',$up_plan_data->subscription_expiration_days));
					      		}
					      		else{
					      			echo sap_get_membership_expiration_date('');
					      		}
					      	?>
					      	</td>
					    </tr>
				    </tbody>
				    <tfoot>
					    <tr>
					      	<th><?php echo $this->common->lang('total_price') ?></th>
					      	<th>₦<?php echo number_format(round($up_plan_data->price - $discount_amt,2)) ; ?></th>
					    </tr>
				  	</tfoot>
			</table>
			<?php

			$content = ob_get_clean();

			echo $content;
		}
		else{
			return $discount_amt;
		}
	}
}