<link rel="stylesheet" href="<?php echo SAP_SITE_URL . '/assets/css/jquery-ui.css' ?>" >
<?php
include SAP_APP_PATH . 'header.php';

include SAP_APP_PATH . 'sidebar.php';
global $sap_common;

$membership_status  = array(
	'1' => $sap_common->lang('active'),
	'0' => $sap_common->lang('pending'),	
);

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<section class="content-header">
		<h1><?php echo $sap_common->lang('add_membership'); ?><small></small></h1>
	</section>

	<section class="content">
		<?php
		echo $this->flash->renderFlash(); ?>

		<form class="add-membership-form" name="new-membership" id="add-membership" method="POST" enctype="multipart/form-data" action="<?php echo SAP_SITE_URL . '/membership/save/'; ?>">

			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title"><?php echo $sap_common->lang('membership_details'); ?></h3>
				</div>

				<div class="box-body">
					<div class="row sap-mt-1_5">
						<div class="col-md-6 form-group">
							<div class="row">
								<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('customer'); ?><span class="astric">*</span></label>
								<div class="col-sm-8 col-md-9">
									<select class="form-control" name="sap_customer">
										<option value=""><?php echo $sap_common->lang('select_customer'); ?></option>

										<?php
										$sap_plan  = isset( $_POST['sap_plan'] ) ? $_POST['sap_plan'] : '';
										$customers = $this->get_customer_without_membership();
					
										if( !empty($customers) ) {
											foreach ( $customers as $key => $customer ) { ?>
												<option value="<?php echo $customer->id ?>"><?php echo $customer->first_name.' '.$customer->last_name ?> </option>
										<?php } } ?>
									</select>
								</div>
							</div>
						</div>

						<div class="col-md-6 form-group">
							<div class="row">
								<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('membership_level'); ?><span class="astric">*</span></label>
								<div class="col-sm-8 col-md-9">
									<select class="form-control" name="sap_plan" id="sap_plan" >
										<option value=""><?php echo $sap_common->lang('select_membership_level'); ?></option>

										<?php
										$sap_plan = isset( $_POST['sap_plan'] ) ? $_POST['sap_plan'] : '';
										$plans 	  = $this->get_plans();
					
										if( !empty($plans) ) {
											foreach ( $plans as $key => $plan ) { ?>
												<option value="<?php echo $plan->id ?>" <?php if ($sap_plan == $plan->id) echo 'selected="selected"' ?> ><?php echo $plan->name ?> </option>
										<?php } } ?>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="row sap_plan">
						<div class="col-md-6 form-group">
							<div class="row">
								<label class="col-sm-4 col-md-3 control-label"><?php echo $sap_common->lang('status'); ?><span class="astric">*</span></label>
								<div class="col-sm-8 col-md-9">
									<select class="form-control" name="membership_status" id="membership_status">
									<option value=""><?php echo $sap_common->lang('select_membership_status'); ?></option>
									<?php
									foreach( $membership_status as $key => $stutus ){
										echo '<option value="'.$key.'" > '.$stutus.' </option>';
									}
									 ?>
									</select>
								</div>
							</div>
						</div>
						
						<div class="col-md-6 form-group">
							<div class="row">
								<label class="col-sm-4 col-md-3 control-label"><?php echo $sap_common->lang('never_expire'); ?></label>
								<div class="col-sm-8 col-md-9">
									<input type="checkbox" class="tgl tgl-ios" name="no_expiration" id="no_expiration" value="1" />
									<label class="tgl-btn float-right-cs-init" for="no_expiration"></label>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6 form-group">
							<div class="row">
								<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('start_date'); ?><span class="astric">*</span></label>
								<div class="col-sm-8 col-md-9">
									<input autocomplete="off" type="text" class="form-control" name="membership_start_date" id="membership_start_date" value="" placeholder="YYYY-MM-DD" />  
								</div>
							</div>
						</div>
						<div class="col-md-6 form-group">
							<div class="row">
								<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('expiration_date'); ?></label>
								<div class="col-sm-8 col-md-9">
									<input autocomplete="off" type="text" class="form-control" name="expiration_date" id="expiration_date" value="" placeholder="YYYY-MM-DD" />  
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('customer_id'); ?></label>
								<div class="col-sm-8 col-md-9">
									<input type="text" class="form-control" name="customer_id" id="customer_id" value=""  />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('subscription_id'); ?></label>
								<div class="col-sm-8 col-md-9">
									<input type="text" class="form-control" name="subscription_id" id="subscription_id" value="" />
								</div>
							</div>
						</div>
					</div>
					<div class="row sap-mt-1_5">
						<div class="col-md-6 form-group">
							<div class="row">
								<label class="col-sm-4 col-md-3 control-label"><?php echo $sap_common->lang('signup_auto_renew'); ?></label>
								<div class="col-sm-8 col-md-9">
									<input type="checkbox" class="tgl tgl-ios" name="auto_renew" id="auto_renew" value="1" />
									<label class="tgl-btn float-right-cs-init" for="auto_renew"></label>
								</div>


							</div>
						</div>

					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-info schedule-the-content auto-renew-note linkedin-multi-post-note">
								<i><?php echo $sap_common->lang('auto_renew_note'); ?></i>
							</div>
						</div>
					</div>
					<div class="sap-mt-1 col-md-12 form-group">
						<input type="hidden" name="form-submitted" value="1">
						<button type="submit" name="sap_add_membership_submit" class="btn btn-primary"><?php echo $sap_common->lang('add_membership'); ?></button>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="alert alert-info schedule-the-content linkedin-multi-post-note">
								<i><?php echo $sap_common->lang('membership_auto_renew_note'); ?></i>
							</div>  
						</div>
					</div>
				</div>
			</div>
		</form>

	</section>
</div>

<script src="<?php echo SAP_SITE_URL . '/assets/js/jquery.min.js' ?>" type="text/javascript"></script>
<?php include'footer.php'; ?>
<script src="<?php echo SAP_SITE_URL . '/assets/js/jquery-ui.js' ?>"></script>
<script src="<?php echo SAP_SITE_URL . '/assets/js/custom.js'; ?>"></script>

<script type="text/javascript">
	
	if($('#membership_start_date').length > 0){

		$( "#membership_start_date" ).datepicker({
			dateFormat: 'yy-mm-dd',
			
		  	changeMonth: true,
		  	changeYear: true,		  			  	
		  	onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() + 1);
                $("#expiration_date").datepicker("option", "minDate", dt);
            }
		});	
	}

	if($('#expiration_date').length > 0){
		$( "#expiration_date" ).datepicker({
			dateFormat: 'yy-mm-dd',
		  	changeMonth: true,
		  	changeYear: true,
		  	onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() - 1);
                $("#membership_start_date").datepicker("option", "maxDate", dt);
            }
		});	
	}



</script>