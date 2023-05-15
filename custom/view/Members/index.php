<?php
session_start();
include SAP_APP_PATH . 'header.php';

include SAP_APP_PATH . 'sidebar.php';

global $sap_common;
?>
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>
			<?php echo $sap_common->lang('customers'); ?>
			<div class='sap-delete'>
				<a href="<?php echo $router->generate('add_member'); ?>" class="btn btn-primary"><?php echo $sap_common->lang('add_new'); ?></a>
			</div>
		</h1>
	</section>

	<!-- Main content -->
	<?php
	$all_members = $this->get_members();
	?>

	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<?php echo $this->flash->renderFlash(); ?>
				<div class="box">
					
					<div class="box-body sap-custom-drop-down-wrap">
						<div class="filter-wrap">
							
							<?php if( !empty($all_members) ) { ?>
								
								<div class="delete-dropdown">
									<select id='searchByGender'>
										<option value=''><?php echo $sap_common->lang('bulk_action'); ?></option>
										<option value='delete'><?php echo $sap_common->lang('delete'); ?></option>
									</select>
									<button class="delete_bulk_plan btn btn-primary"><?php echo $sap_common->lang('apply'); ?></button>
								</div>
						<?php } ?>
						
						<div class="filter-dropdown">
							<select id='searchByStatus' name="searchByStatus">
								<option value=''><?php echo $sap_common->lang('select_status'); ?></option>
								<option value="1"><?php echo $sap_common->lang('active'); ?></option>
								<option value="0"><?php echo $sap_common->lang('in-active'); ?></option>
							</select>
							<button class="apply_filters btn btn-primary"><?php echo $sap_common->lang('filter'); ?></button>
						</div>
					</div>
						
						 
					<table id="list-members" class="display table table-bordered table-striped member-list">
						<thead>
							<tr>
								<th data-sortable="false" data-width="10px"><input type="checkbox" class="multipost-select-all" /></th>
								<th data-sortable="false"><?php echo $sap_common->lang('number'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('name'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('email'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('role'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('status'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('date'); ?></th>
								<th data-sortable="false"><?php echo $sap_common->lang('action'); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th data-sortable="false" data-width="10px"><input type="checkbox" class="multipost-select-all" /></th>
								<th data-sortable="false"><?php echo $sap_common->lang('number'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('name'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('email'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('role'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('status'); ?></th>
								<th data-sortable="true"><?php echo $sap_common->lang('date'); ?></th>
								<th data-sortable="false"><?php echo $sap_common->lang('action'); ?></th>
							</tr>
						</tfoot>
						<tbody>
							
						</tbody>
					</table>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- /.content -->

</div><!-- /.content-wrapper -->

<?php
include SAP_APP_PATH . 'footer.php'; ?>

<script type="text/javascript" class="init">
	'use strict';

	$(document).ready(function () {		

		var dtListUsers =  $('#list-members').DataTable({				
			"aLengthMenu": [[15,25, 50, 100], [15,25, 50, 100]],
			"pageLength": 15,			
			"responsive": true,
			"processing": true,
			"bLengthChange" : false,
			"order": [[7, "desc"]],
        	"serverSide": true,
        	'ajax': {
		       'url':'../members-ajax/',
		       'data': function(data){
		          // Read values
		          var searchByStatus = $('#searchByStatus').val();
		          data.searchByStatus = searchByStatus;
		       }
		    },
		    
		});

		$('body').on('click','.apply_filters',function(){
		    dtListUsers.draw();
		});

		$(document).on('click', '.delete_member', function () {
			
			var obj = $(this);
			var member_id = $(this).attr('aria-data-id');
			var success_message = '<?php echo $sap_common->lang('selected_customers_delete'); ?>';
			if ( confirm("<?php echo $sap_common->lang('delete_record_conform_msg'); ?>") ) {
				
				$.ajax({
					type: 'POST',
					url: '../member/delete/',
					data: {member_id: member_id},
					success: function (result) {

						var result = $.parseJSON(result);

						if ( result.status ) {
							$('#member_' + member_id).parent('td').parent('tr').remove();
							$('<div class="alert alert-success alert-dismissible" role="alert" bis_skin_checked="1">'+
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>'+ success_message +'</div>').insertBefore('.box');
						}
					}
				});
			}
		});

		$(document).on('click','.delete_bulk_plan', function(){
	        var selected_val = $('#searchByGender').val();
	        if(selected_val == '' ){
				alert("Please select bulk action!");
			}
	        if(selected_val == 'delete') {
	            var id = [];

	            $("input[name='member_id[]']:checked").each(function (i) {
	                id[i] = $(this).val();
	            });

	            if (id.length === 0) {
	                alert("<?php echo $sap_common->lang('select_checkbox_alert'); ?>");

	            } else if(confirm("<?php echo $sap_common->lang('delete_selected_records_conform_msg'); ?>")) {

	                $.ajax({
	                    url: '../member/delete_multiple/',
	                    method: 'POST',
	                    data: {id: id},
	                    success: function (result)
	                    {
	                     var result = jQuery.parseJSON(result);
	                     if (result.status) {
	                        location.reload();
	                    }
	                }
	            });

	            } else {
	                return false;
	            }
	        }
	    });
	});
</script>
