<?php
global $sap_common;
include SAP_APP_PATH . 'header.php';

include SAP_APP_PATH . 'sidebar.php';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo $sap_common->lang('register_license_update'); ?></h1>
    </section>

    <section class="content sap-update-wrap">
        <div class="row">
            <div class="col-xs-12">
                <?php
                echo $this->flash->renderFlash();
                $license_data = $this->get_license_data();
                ?>
                <div class="box">                    
                    <form id="sap_license_form" action="<?php echo SAP_SITE_URL . '/mingle-update/save_process/'; ?>" class="form-horizontal" enctype="multipart/form-data" method="POST">
                        <div class="box-body">

                            <div class="col-lg-2">
                                <div class="row">
                                    <div class="col-lg-10">
                                        <div class="form-group sap-share-link-parent" >
                                            <span><strong><?php echo $sap_common->lang('current_version'); ?></strong> <?php echo SAP_VERSION; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="col-lg-10">
                                        <div class="form-group sap-license-key-wrap" >
                                            <input class="form-control sap-license-email" placeholder="<?php echo $sap_common->lang('update_enter_email_plh'); ?>" name="sap_license_email" value="<?php echo!empty($license_data['license_email']) ? $license_data['license_email'] : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="col-lg-10">
                                        <div class="form-group sap-license-key-wrap" >
                                            <input class="form-control sap-license-key" placeholder="<?php echo $sap_common->lang('update_enter_license_plh'); ?>" name="sap_license_key" value="<?php echo!empty($license_data['license_key']) ? $license_data['license_key'] : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-2 pull-right">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group sap-license-btn-wrap" >
                                            <button type="submit" name="sap_register_license" class="sap_register_license btn btn-success pull-left"> <?php echo $sap_common->lang('register_license'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php if (!empty($license_data)) { ?>
                    <div class="box">                    
                        <div class="box-body">
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-lg-10">
                                        <div class="form-group sap-share-link-parent" >
                                            <?php
                                            if (!empty($_SESSION['Update_version']) && $_SESSION['Update_version'] > SAP_VERSION) {
                                                echo '<span><strong>There is a new version of Mingle - ' . SAP_NAME . ' available. <a href="https://www.wpwebelite.com/changelogs/mingle-sap-php-script/changelog.txt" target="_blank">View version ' . $_SESSION['Update_version'] . ' details</a> or Click on below button for update now.</strong> </span>';
                                            } else {
                                                echo '<span><strong>You have the latest version of Mingle - ' . SAP_NAME . ' </strong> </span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="sap-response">
                                            <div class="alert alert-dismissible obj-hide" role="alert"></div>
                                        </div>
                                        <?php if (!empty($_SESSION['Update_version']) && $_SESSION['Update_version'] > SAP_VERSION) { ?>
                                            <div class="form-group sap-check-for-update-wrap" >
                                                <button type="button" name="sap-check-for-update" class="sap-check-for-update btn btn-success pull-left"><?php echo $sap_common->lang('update_loader'); ?></button>
                                                <div class="update_loader"><img src="<?php echo SAP_SITE_URL.'/assets/images/ajax-loader.gif'; ?>" alt="Update"></div>
                                            </div>
                                        </div>
                                    <?php }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

        </div>
    </section>

</div>

<?php include SAP_APP_PATH . 'footer.php'; ?>
