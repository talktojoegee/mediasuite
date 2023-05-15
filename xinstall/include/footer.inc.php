<div id="footer">
    <div>
        <?php echo sprintf($sap_common->lang('install_footer'),'<strong>','&copy',date('Y'),'<a href="https://www.wpwebelite.com" target="_blank">','</a>','</strong>'); ?>
        <div class="pull-right hidden-xs float-right">
            <b><?php echo $sap_common->lang('install_footer_version'); ?></b> 
            <?php
            echo SAP_VERSION;
            ?>
        </div>
    </div>
</div>