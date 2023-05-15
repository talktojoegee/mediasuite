<?php
include SAP_APP_PATH . 'header.php';

include SAP_APP_PATH . 'sidebar.php';
global $sap_common;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<section class="content-header">
		<h1><?php echo $sap_common->lang('add_customer'); ?><small></small></h1>
	</section>

	<section class="content">
		<?php
		echo $this->flash->renderFlash(); ?>

		<form class="add-member-form" name="new-member" id="add-member" method="POST" enctype="multipart/form-data" action="<?php echo SAP_SITE_URL . '/member/save/'; ?>">

			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title"><?php echo $sap_common->lang('customer_details'); ?></h3>
				</div>

				<div class="box-body">
					<div class="row sap-mt-1_5">
						<div class="col-md-6 form-group"><div class="row">
							<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('first_name'); ?><span class="astric">*</span></label>
							<div class="col-sm-8 col-md-9">
								<input type="text" class="form-control" name="sap_firstname" id="sap_firstname" value="<?php echo ( !empty($_POST['sap_firstname']) ? $_POST['sap_firstname'] : '' ); ?>" placeholder="<?php echo $sap_common->lang('first_name'); ?>" />
							</div>
						</div></div>
						<div class="col-md-6 form-group"><div class="row">
							<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('last_name'); ?></label>
							<div class="col-sm-8 col-md-9">
								<input type="text" class="form-control" name="sap_lastname" id="sap_lastname" value="<?php echo ( !empty($_POST['sap_lastname']) ? $_POST['sap_lastname'] : '' ); ?>" placeholder="<?php echo $sap_common->lang('last_name'); ?>" />
							</div>
						</div></div>
					</div>

					<div class="row">
						<div class="col-md-6 form-group"><div class="row">
							<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('email'); ?><span class="astric">*</span></label>
							<div class="col-sm-8 col-md-9">
								<input type="text" class="form-control" name="sap_email" id="sap_email" value="<?php echo ( !empty($_POST['sap_email']) ? $_POST['sap_email'] : '' ); ?>" placeholder="<?php echo $sap_common->lang('email'); ?>" />
							</div>
						</div></div>
						<div class="col-md-6 form-group"><div class="row">
							<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('role'); ?></label>
							<div class="col-sm-8 col-md-9">
								<?php
								$role = isset( $_POST['sap_role'] ) ? $_POST['sap_role'] : ''; ?>

								<select name="sap_role" class="form-control sap_role">
									<option value="user" <?php if( 'user' == $role ) echo 'selected="selected"'; ?>><?php echo $sap_common->lang('user'); ?></option>
									<option value="superadmin" <?php if( 'superadmin' == $role ) echo 'selected="selected"'; ?>><?php echo $sap_common->lang('admin'); ?></option>
								</select>
							</div>
						</div></div>
					</div>

					<div class="row">
						<div class="col-md-6 form-group"><div class="row">
							<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('password'); ?><span class="astric">*</span></label>
							<div class="col-sm-8 col-md-9">
								<input type="password" class="form-control" name="sap_password" id="sap_password" value="" placeholder="<?php echo $sap_common->lang('password'); ?>" />
							</div>
						</div></div>
						<div class="col-md-6 form-group"><div class="row">
							<label class="col-sm-4 col-md-3"><?php echo $sap_common->lang('re_password'); ?><span class="astric">*</span></label>
							<div class="col-sm-8 col-md-9">
								<input type="password" class="form-control" name="sap_repassword" id="sap_repassword" value="" placeholder="<?php echo $sap_common->lang('re_password_plh'); ?>" />
							</div>
						</div></div>
					</div>
					

					<div class="row sap_plan">
						<div class="col-md-6 form-group">
							<div class="form-group"><div class="row">
								<label class="col-sm-4 col-md-3 control-label"><?php echo $sap_common->lang('notify_user'); ?></label>
								<div class="col-sm-8 col-md-9">
									
										<label class="auth-option">
											<input type="checkbox" name="sap_notify" id="sap_notify" value="yes" class="tgl tgl-ios" />
											<label class="tgl-btn float-right-cs-init" style="float:left;" for="sap_notify"></label>
											<span style="float: right; padding: 3px 0 0 9px;"><?php echo $sap_common->lang('notify_user_checkbox'); ?></span>
										</label>
									
								</div>
							</div>
						</div></div>
						<div class="col-md-6 form-group">
							<div class="row">
								<label class="col-sm-4 col-md-3 control-label"><?php echo $sap_common->lang('status'); ?></label>
								<div class="col-sm-8 col-md-9">
									<input type="checkbox" class="tgl tgl-ios" name="sap_status" id="sap_status" value="1" />
									<label class="tgl-btn float-right-cs-init" for="sap_status"></label>
								</div>
							</div>
						</div>
						 

					</div>

					<div class="sap-mt-1 col-md-12 form-group">
						<input type="hidden" name="form-submitted" value="1">
						<button type="submit" name="sap_add_member_submit" class="btn btn-primary"><?php echo $sap_common->lang('add_customer'); ?></button>
					</div>
				</div>
			</div>
		</form>

	</section>
</div>

<script src="<?php echo SAP_SITE_URL . '/assets/js/jquery.min.js' ?>" type="text/javascript"></script>
<script src="<?php echo SAP_SITE_URL . '/assets/js/custom.js'; ?>"></script>
<?php
include'footer.php';
?>