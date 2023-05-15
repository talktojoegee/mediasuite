
<div class="tab-pane <?php echo ( $active_tab == "linkedin") ? "active" : '' ?>" id="linkedin">
    <!-- form start -->
    <form id="linkedin-settings" class="form-horizontal" method="POST" action="<?php echo SAP_SITE_URL . '/settings/save/'; ?>" enctype="multipart/form-data"> 
        <?php
        global $sap_common;
        //Get linkedin SAP options which stored
        $sap_linkedin_options   = $this->get_user_setting('sap_linkedin_options');
        $sap_linkedin_sess_data = $this->get_user_setting('sap_li_sess_data');

        $user_id=3;
        //Url shortner options
        $shortner_options = $common->sap_get_all_url_shortners();

        $li_profile_data = $linkedin->sap_li_get_profiles_data();
        // if Linkedin user id is not empty reset session data
        if (isset($_GET['li_reset_user']) && $_GET['li_reset_user'] == '1') {
            $linkedin->sap_li_reset_session();
        }
        ?>
        <div class="box box-primary">
            <div class="box-header sap-settings-box-header"><?php echo $sap_common->lang('li_general_setting'); ?></div>
            <div class="box-body">
                <div class="sap-box-inner">
                    <div class="form-group">
                        <label for="" class="col-sm-3 control-label"><?php echo $sap_common->lang('en_autopost_li'); ?></label>
                        <div class="tg-list-item col-sm-9">
                            <input class="tgl tgl-ios" name="sap_linkedin_options[enable_linkedin]" id="enable_linkedin" <?php echo!empty($sap_linkedin_options['enable_linkedin']) ? 'checked="checked"' : ''; ?> type="checkbox" value="1">
                            <label class="tgl-btn float-right-cs-init" for="enable_linkedin"></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <button type="submit" name="sap_linkedin_submit" class="btn btn-primary sap-linkedin-submit"><i class="fa fa-inbox"></i> <?php echo $sap_common->lang('save'); ?></button>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header sap-settings-box-header"><?php echo $sap_common->lang('li_api_setting'); ?> </div>
            <div class="box-body">
                <div class="sap-box-inner sap-api-linkedin-settings">
                    <div class="form-group">
                        <label for="app-setting" class="col-sm-3 control-label"><?php echo $sap_common->lang('li_application'); ?></label>
                        <div class="col-sm-9">

                            <?php echo sprintf($sap_common->lang('li_application_help_text'),'<span>','<a href="https://docs.wpwebelite.com/social-network-integration/linkedin/" target="_blank">','</a>','</span>'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="app-permission-setting" class="col-sm-3 control-label"><?php echo $sap_common->lang('allowing_permissinons'); ?></label>
                        <div class="col-sm-9">
                            <span><?php echo $sap_common->lang('li_allowing_permissinons_hrlp_text'); ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="fetch-company-profiles"><?php echo $sap_common->lang('en_company_page'); ?></label>
                        <div class="col-sm-9">
                            <div class="tg-list-item">
                                <input class="tgl tgl-ios" name="sap_linkedin_options[enable_company_pages]" id="enable_company_pages" <?php echo!empty($sap_linkedin_options['enable_company_pages']) ? 'checked="checked"' : ''; ?> type="checkbox" value="on">
                                <label class="tgl-btn float-right-cs-init" for="enable_company_pages"></label>
                            </div> 
                            <div class="alert alert-info organization-approved linkedin-multi-post-note">
                                <i>
                                    <?php echo sprintf($sap_common->lang('en_company_page_help_text'),'<strong>','</strong>'); ?></i>
                            </div>
                        </div>

                    </div>

                    <div class="form-group display_desktop">
                        <label class="col-sm-3"><?php echo $sap_common->lang('li_app_id_key'); ?> <span class="astric">*</span></label>
                        <label class="col-sm-3"><?php echo $sap_common->lang('li_app_secret'); ?><span class="astric">*</span></label>
                        <label class="col-sm-3"><?php echo $sap_common->lang('valid_redirect_url'); ?></label>
                        <label class="col-sm-3"><?php echo $sap_common->lang('allowing_permissinons'); ?></label>
                    </div>

                    <?php
                    $sap_linkedin_keys = empty($sap_linkedin_options['linkedin_keys']) ? array(0 => array('app_id' => '', 'app_secret' => '')) : $sap_linkedin_options['linkedin_keys'];
                    if (!empty($sap_linkedin_keys)) {
                        $i = 0;
                        foreach ($sap_linkedin_keys as $key => $value) {
                            
                            ?>
                            <div class="form-group display_mobile sap-linkedin-account-details" data-row-id="<?php echo $key; ?>">
                                <div class="col-sm-3">
                                    <label class="col-sm-12"><?php echo $sap_common->lang('li_app_id_key'); ?></label>
                                    <input class="form-control sap-linkedin-app-id" name="sap_linkedin_options[linkedin_keys][<?php echo $key; ?>][app_id]" value="<?php echo $value['app_id']; ?>" placeholder="<?php echo $sap_common->lang('li_app_id_key_plh_text'); ?>" type="text">
                                </div>
                                <div class="col-sm-3">
                                    <label class="col-sm-12"><?php echo $sap_common->lang('li_app_secret'); ?></label>
                                    <input class="form-control sap-linkedin-app-secret" name="sap_linkedin_options[linkedin_keys][<?php echo $key; ?>][app_secret]" value="<?php echo $value['app_secret']; ?>" placeholder="<?php echo $sap_common->lang('li_app_secret_plh_text'); ?>" type="text">
                                </div>
                                <?php
                                if (!empty($value['app_id'])) {
                                    $valid_auto_redirect_url = SAP_SITE_URL.'/settings/' . '?grant_li=true&li_app_id=' . $value['app_id'];
                                    ?>
                                    <div class="col-sm-3">
                                        <label class="col-sm-12"><?php echo $sap_common->lang('valid_redirect_url'); ?></label>
                                        <input class="form-control sap-oauth-url li-oauth-url" id="li-oauth-url-<?php print $value['app_id']; ?>" type="text" value="<?php echo $valid_auto_redirect_url; ?>" size="30" readonly/>
                                        <button type="button" data-inputID="#li-oauth-url-" data-appid="<?php print $value['app_id']; ?>" class="btn btn-primary copy-clipboard"><?php echo $sap_common->lang('copy'); ?></button>
                                    </div>
                                <?php } ?>
                                <div class="col-sm-3">
                                    <label class="col-sm-12"><?php echo $sap_common->lang('allowing_permissinons'); ?></label>
                                    <div class="sap-grant-reset-data">
                                        <?php
                                        if (!empty($value['app_id']) && !empty($value['app_secret']) && !empty($sap_linkedin_sess_data[$value['app_id']])) {
                                            echo '<p  class="sap-grant-msg">'.$sap_common->lang('allowing_permissinons_help_text').'</p>';
                                            ?>
                                            <a href="?li_reset_user=1&sap_li_app=<?php echo $value['app_id']; ?>"><?php echo $sap_common->lang('reset_user_session'); ?></a>
                                            <?php
                                        } elseif (!empty($value['app_id']) && !empty($value['app_secret'])) {
                                            echo '<p><a href="' . $linkedin->sap_get_li_login_url($value['app_id']) . '">'.$sap_common->lang('grant_permission').'</a></p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-12 remove-icon-linkedin">
                                    <div class="pull-right <?php echo ( $i == 0 ) ? 'sap-linkedin-main' : ''; ?>">
                                        <a href="javascript:void(0)" class="sap-linkedin-remove remove-tx-init"><i class="fa fa-close"></i></a>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $i++;
                        }
                    }
                    ?>

                    <div class="form-group">
                        <div class="pull-right add-more">
                            <button type="button" class="btn btn-primary sap-add-more-li-account"><i class="fa fa-plus"></i> <?php echo $sap_common->lang('add_more'); ?></button>
                        </div>
                    </div>

                </div>

            </div>

            <div class="box-footer">
                <div class="pull-right">
                    <button type="submit" name="sap_linkedin_submit" class="btn btn-primary sap-linkedin-submit"><i class="fa fa-inbox"></i> <?php echo $sap_common->lang('save'); ?></button>
                </div>
            </div>
        </div>


        <div class="box box-primary">
            <div class="box-header sap-settings-box-header"><?php echo $sap_common->lang('autopost_to_li'); ?></div>
            <div class="box-body">
                <div class="sap-box-inner sap-api-linkedin-settings">
                    <div class="form-group">
                        <label for="tw-post-users" class="col-sm-4 control-label"><?php echo $sap_common->lang('autopost_to_li_users'); ?> </label>
                        <div class="tg-list-item col-sm-6">
                            <select class="form-control sap_select" multiple="multiple" name="sap_linkedin_options[li_type_post_user][]">
                                <?php
                                $li_type_post_user = !empty($sap_linkedin_options['li_type_post_user']) ? ($sap_linkedin_options['li_type_post_user']) : array();
                                if (!empty($li_profile_data)) {
                                    foreach ($li_profile_data as $profile_id => $profile_name) {
                                        ?>                                       
                                        <option value="<?php echo $profile_id; ?>" <?php echo in_array($profile_id, $li_type_post_user) ? 'selected=selected' : ''; ?>><?php echo $profile_name; ?></option><?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label"> <?php echo $sap_common->lang('li_post_img'); ?></label>
                        <div class="col-sm-6 sap-linkedin-img-wrap <?php echo (!empty($sap_linkedin_options['linkedin_image'])) ? 'li-hide-uploader' : '';?>">
                            <?php
                            if (!empty($sap_linkedin_options['linkedin_image'])) {
                                ?>
                                <div class="linkedin-img-preview sap-img-preview">
                                    <img src="<?php echo SAP_IMG_URL . $sap_linkedin_options['linkedin_image']; ?>">
                                    <div class="cross-arrow">
                                        <a href="javascript:void(0)" data-upload_img=".sap-linkedin-img-wrap .file-input" data-preview=".linkedin-img-preview" title="Remove Tweet Image" class="sap-setting-remove-img remove-tx-init"><i class="fa fa-close"></i></a>
                                    </div> 
                                </div>
                            <?php }
                            ?>
                            <input id="sap_linkedin_img" name="linkedin_image" type="file" class="file file-loading <?php echo!empty($sap_linkedin_options['linkedin_image']) ? 'sap-hide' : ''; ?>" data-show-upload="false" data-show-caption="true" data-allowed-file-extensions='["png", "jpg","jpeg", "gif"]' tabindex="15">
                            <input type="hidden" class="uploaded_img" name="sap_linkedin_options[linkedin_image]" value="<?php echo!empty($sap_linkedin_options['linkedin_image']) ? $sap_linkedin_options['linkedin_image'] : ''; ?>" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label"><?php echo $sap_common->lang('url_shortener'); ?></label> 
                        <div class="col-sm-6">
                            <select class="sap_select sap-url-shortener-select" name="sap_linkedin_options[li_type_shortner_opt]">
                                <?php
                                $selected_url_type = !empty($sap_linkedin_options['li_type_shortner_opt']) ? $sap_linkedin_options['li_type_shortner_opt'] : '';
                                foreach ($shortner_options as $key => $value) {
                                    $selected = "";
                                    if (!empty($selected_url_type) && $selected_url_type == $key) {
                                        $selected = ' selected="selected"';
                                    }
                                    ?>
                                    <option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </div> 
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label"><?php echo $sap_common->lang('bit_access_token'); ?></label>                      
                        <div class="col-sm-6">
                            <input type="text" class="form-control bitly-token" name="sap_linkedin_options[li_bitly_access_token]" value="<?php echo!empty($sap_linkedin_options['li_bitly_access_token']) ? $sap_linkedin_options['li_bitly_access_token'] : ''; ?>" >     
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label"><?php echo $sap_common->lang('shorte_api_token'); ?></label>                      
                        <div class="col-sm-6">
                            <input type="text" class="form-control shorte-token" name="sap_linkedin_options[li_shortest_api_token]" value="<?php echo!empty($sap_linkedin_options['li_shortest_api_token']) ? $sap_linkedin_options['li_shortest_api_token'] : ''; ?>" >     
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" name="sap_linkedin_submit" class="btn btn-primary sap-linkedin-submit"><i class="fa fa-inbox"></i> <?php echo $sap_common->lang('save'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
