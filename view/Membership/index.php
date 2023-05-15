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
			<?php echo $sap_common->lang('membership'); ?>
			
			<div class='sap-delete'>
				<a href="<?php echo $router->generate('add_membership'); ?>" class="btn btn-primary"><?php echo $sap_common->lang('add_new'); ?></a>
			</div>
		</h1>
	</section>

	<!-- Main content -->
	<?php
	$all_members = $this->get_membership();
	?>

	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<?php echo $this->flash->renderFlash(); ?>
				<div class="box">
					
					<div class="box-body sap-custom-drop-down-wrap">
						<div class="filter-wrap">
							<div class="filter-dropdown">
								<select id='searchByStatus' name="searchByStatus">
									<option value=''><?php echo $sap_common->lang('select_status'); ?></option>
									<option value="1"><?php echo $sap_common->lang('active'); ?></option>
									<option value="0"><?php echo $sap_common->lang('pending'); ?></option>
									<option value="2"><?php echo $sap_common->lang('expired'); ?></option>
									<option value="3"><?php echo $sap_common->lang('cancelled'); ?></option>
								</select>
								<?php if(!empty($allplan)){ ?>
								<select id='searchByPlan' name="searchByPlan">
									<option value=''><?php echo $sap_common->lang('select_membership_level'); ?></option>
									<?php 
									foreach($allplan as $plan){
										echo '<option value="'.$plan->name.'">'.$plan->name.'</option>';	
									}
									?>							
								</select>
								<?php } ?>
								<button class="apply_filters btn btn-primary"><?php echo $sap_common->lang('filter'); ?></button>				
							</div>
						</div>
					</div>
						 <div class="datatable-overly"> 
						<table id="list-members" class="display table table-bordered table-striped member-list">
							<thead>
								<tr>
									
									<th data-sortable="false"><?php echo $sap_common->lang('number'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('membership_level'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('customer_name'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('customer_id'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('status'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('recurring'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('expiration'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('created_date'); ?></th>
									<th data-sortable="false"><?php echo $sap_common->lang('action'); ?></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									
									<th data-sortable="false"><?php echo $sap_common->lang('number'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('membership_level'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('customer_name'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('customer_id'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('status'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('recurring'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('expiration'); ?></th>
									<th data-sortable="true"><?php echo $sap_common->lang('created_date'); ?></th>
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
			"oLanguage": {
				"sEmptyTable": "No membership found."
			},
			"aLengthMenu": [[15,25, 50, 100], [15,25, 50, 100]],
			"pageLength": 15,
			"bLengthChange" : false,
			"order": [[8, "desc"]],
			"responsive": true,
			"processing": true,
        	"serverSide": true,        	
		    "columnDefs": [	         
	            {"targets": 0, "name": "membership_level", 'searchable': true, 'orderable': false},
	            {"targets": 1, "name": "customer_name", 'searchable': true, 'orderable': true},
	            {"targets": 2, "name": "customer_id", 'searchable': true, 'orderable': true},
	            {"targets": 4, "name": "status", 'searchable': true, 'orderable': true},
	            {"targets": 5, "name": "recurring", 'searchable': false, 'orderable': true},
	            {"targets": 6, "name": "expiration", 'searchable': false, 'orderable': true},
	            {"targets": 7, "name": "created_date", 'searchable': false, 'orderable': true},
	            {"targets": 8, "name": "action", 'searchable': false, 'orderable': false},
	        ],
	      
        	'ajax': {
		       'url':'../membership-ajax/',
		       'data': function(data){
		          // Read values
		          var searchByStatus = $('#searchByStatus').val();
		          data.searchByStatus = searchByStatus;

		          var searchByPlan = $('#searchByPlan').val();
		          data.searchByPlan = searchByPlan;
		       }
		    },
		    
		} );


		$('body').on('click','.apply_filters',function(){
		    dtListUsers.draw();
		  });		
	});
</script>
