<section class="section section_custom">
	<div class="section-header">
		<h1><i class="fab fa-wordpress"></i> <?php echo $this->lang->line("Wordpress Settings (Self-Hosted)"); ?></h1>
		<div class="section-header-button">
	     	<a class="btn btn-primary" href="<?= base_url('social_apps/add_wordpress_settings_self_hosted') ?>">
	        <i class="fas fa-plus-circle"></i> <?php echo $this->lang->line('Add New Site'); ?></a>
	    </div>

	    <div class="section-header-breadcrumb">
	      <div class="breadcrumb-item active">
	      	<?php echo $this->lang->line('System'); ?>
	      </div>
	      <div class="breadcrumb-item">
	      	<a href="<?php echo base_url('social_apps/index'); ?>"><?php echo $this->lang->line("Social Apps"); ?></a>
	      </div>
	      <div class="breadcrumb-item active"><?php echo $page_title; ?></div>
	    </div>

	</div>
	<div class="section-body">
		<div class="row">
			<div class="col-12">

				<?php if ($this->session->userdata('edit_wssh_success')): ?>
				<div class="alert alert-success alert-dismissible show fade">
					<div class="alert-body text-center">
						<button class="close" data-dismiss="alert">
							<span>×</span>
						</button>
						<?php echo $this->session->userdata('edit_wssh_success'); ?>
					</div>
				</div>
				<?php $this->session->unset_userdata('edit_wssh_success'); ?>
				<?php endif; ?>

				<div class="card">
					<div class="card-header d-flex justify-content-end">
						<a class="btn btn-primary" href="<?php echo base_url('assets/wordpress-self-hosted/wp-self-hosted-authentication.zip'); ?>"><i class="fa fa-download"></i> <?php echo $this->lang->line('Download API Plugin'); ?></a>
					</div>
					<div class="card-body data-card">
						<div class="table-responsive">
							<table id="wssh-datatable" class="table table-bordered" style="width:100%">
						        <thead>
						            <tr>
						                <th>#</th>
						                <th><?php echo $this->lang->line('Domain Name'); ?></th>
						                <th><?php echo $this->lang->line('User Key'); ?></th>
						                <th><?php echo $this->lang->line('Authentication Key'); ?></th>
						                <th><?php echo $this->lang->line('Status'); ?></th>
						                <th><?php echo $this->lang->line('Actions'); ?></th>
						            </tr>
						        </thead>
						    </table>
						</div>	
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<style>
	.card {box-shadow: none !important;}
	.data-div {margin-left: 45px;}
	.margin-top {margin-top: 30px;}
	.flex-column .nav-item .nav-link.active
	{
	  background: #fff !important;
	  color: #3516df !important;
	  border: 1px solid #988be1 !important;
	}

	.flex-column .nav-item .nav-link .form_id, .flex-column .nav-item .nav-link .insert_date
	{
	  color: #608683 !important;
	  font-size: 12px !important;
	  padding: 0 !important;
	  margin: 0 !important;
	}
	.waiting {height: 100%;width:100%;display: table;}
    .waiting i{font-size:60px;display: table-cell; vertical-align: middle;padding:30px 0;}
</style>

<script>
	$(document).ready(function() {
		var wssh_table = $('#wssh-datatable').DataTable({
	      	processing: true,
	      	serverSide: true,
			order: [[ 0, "desc" ]],
			pageLength: 10,	        
	        ajax: {
	        	url: '<?= base_url('social_apps/wordpress_settings_self_hosted_data') ?>',
	        	type: 'POST',
	        	dataSrc: function (json) {
	                $(".table-responsive").niceScroll();
	                return json.data;
	            },
	        },
	        columns: [
			    {data: 'id'},
			    {data: 'domain_name'},
			    {data: 'user_key'},
			    {data: 'authentication_key'},
			    {data: 'status'},
			    {data: 'actions'}
			],
			language: {
        		url: "<?= base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
  			},
      		columnDefs: [
				{ 
					'sortable': false, 
					'targets': [2,3,4,5]
				},
				{
				    targets: [0,1,2,3,4,5],
				    className: 'text-center'
				}
			],
			dom: '<"top"f>rt<"bottom"lip><"clear">',
		});

		// Attempts to delete wordpress site's settings
		$(document).on('click', '#delete-wssh-settings', function(e) {
			e.preventDefault()

			// Grabs site ID
			var site_id = $(this).data('site-id');

			swal({
				title: '<?php ('Are you sure?'); ?>',
				text: '<?php echo $this->lang->line('Once deleted, you will not be able to recover this wordpress site\'s settings!'); ?>',
				icon: 'warning',
				buttons: true,
				dangerMode: true,
			}).then((yes) => {
				if (yes) {
					$.ajax({
						type: 'POST',
						url: '<?php echo base_url('social_apps/delete_wordpress_settings_self_hosted') ?>',
						dataType: 'JSON',
						data: { site_id },
						success: function(res) {
							if (res) {
								if ('ok' == res.status) {
									// Reloads datatable
									wssh_table.ajax.reload()

									// Displays success message
									iziToast.success({title: '',message: res.message,position: 'bottomRight'});
								} else if (true === res.error) {
									// Displays error message
									iziToast.error({title: '',message: res.message, position: 'bottomRight'});
								}	
							}
						},
						error: function(xhr, status, error) {
							// Displays error message
							iziToast.error({title: '',message: error,position: 'bottomRight'});	
						}
					})
				} else {
					return
				}
			});
		});
	});
</script>