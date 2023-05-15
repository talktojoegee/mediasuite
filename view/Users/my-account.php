<?php
global $sap_common;
//Update user profile
if (!empty($_POST['sap_user_update_submit'])) {
    
}
$email = $user_details['email'];
$id = $user_details['id'];

$sap_my_account_tab = isset( $_SESSION['sap_my_account_tab'] ) ? $_SESSION['sap_my_account_tab'] : '';

include 'header.php';
include 'sidebar.php';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?php echo $sap_common->lang('my_account'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content sap-my-account">
        <?php echo $this->flash->renderFlash(); ?>
        <div class="box">
               <div class="box-body">
                  <form id="my_account" action="<?php echo SAP_SITE_URL . '/my-account/update'; ?>" class="form-horizontal" enctype="multipart/form-data" method="POST">
                     <div id="errorMessage" class="help-block help-block-error"></div>
                     <input type="hidden" name="sap_user_id"  class="form-control" id="sap_user_id" value="<?php echo $id; ?>">
                     <div class="form-group">
                        <label for="sap_user_fname" class="col-sm-2 control-label"><?php echo $sap_common->lang('first_name'); ?></label>
                        <div class="col-sm-10">
                           <input type="text" name="sap_user_fname"  class="form-control" id="sap_user_fname" placeholder="<?php echo $sap_common->lang('first_name_plh'); ?>" value="<?php echo $user_details['first_name']; ?>">
                        </div>
                     </div>
                     <div class="form-group">
                        <label for="sap_user_lname" class="col-sm-2 control-label"><?php echo $sap_common->lang('last_name'); ?></label>
                        <div class="col-sm-10">
                           <input type="text" name="sap_user_lname"  class="form-control" id="sap_user_lname" placeholder="<?php echo $sap_common->lang('last_name_plh'); ?>" value="<?php echo $user_details['last_name']; ?>">
                        </div>
                     </div>
                     <div class="form-group">
                        <label for="sap_user_email" class="col-sm-2 control-label"><?php echo $sap_common->lang('email'); ?><span class="color-red">*</span></label>
                        <div class="col-sm-10">
                           <input type="email" name="sap_user_email" class="form-control" id="sap_user_email" placeholder="<?php echo $sap_common->lang('email'); ?>" value="<?php echo $email; ?>" autocomplete="off">
                        </div>
                     </div>
                     <div class="form-group">
                        <label for="sap_user_password" class="col-sm-2 control-label"><?php echo $sap_common->lang('password'); ?></label>
                        <div class="col-sm-10">
                           <input type="password" name="sap_user_password" class="form-control" id="sap_user_password" placeholder="<?php echo $sap_common->lang('password'); ?>" autocomplete="off">
                        </div>
                     </div>
                     <div class="form-group">
                        <label for="sap_user_repassword" class="col-sm-2 control-label"><?php echo $sap_common->lang('re_password'); ?></label>
                        <div class="col-sm-10">
                           <input type="password" name="sap_user_repassword" class="form-control" id="sap_user_repassword" placeholder="<?php echo $sap_common->lang('re_password_plh'); ?>">
                        </div>
                     </div>
                     <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                           <button type="submit" name="sap_user_update_submit" class="btn btn-primary"><?php echo $sap_common->lang('submit'); ?></button>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
            <!-- Info boxes -->      
    </section>
</div>
<?php include'footer.php'; ?>
