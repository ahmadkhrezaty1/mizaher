<style>
	.blue{
		color: #2C9BB3 !important;
	}
</style>

<section class="section">
	<div class="section-header">
		<h1><i class="fab fa-wordpress"></i> <?php echo $page_title; ?></h1>
		<div class="section-header-breadcrumb">
			<div class="breadcrumb-item"><?php echo $this->lang->line("System"); ?></div>
			<div class="breadcrumb-item"><a href="<?php echo base_url('social_apps/settings'); ?>"><?php echo $this->lang->line("Social Apps"); ?></a></div>
			<div class="breadcrumb-item"><?php echo $page_title; ?></div>
		</div>
	</div>
	
	<div class="section-body">
		<div class="row">
			<div class="col-12">

				<?php if ($this->session->userdata('edit_wssh_error')): ?>
				<div class="alert alert-warning alert-dismissible show fade">
					<div class="alert-body">
						<button class="close" data-dismiss="alert">
							<span>×</span>
						</button>
						<?php echo $this->session->userdata('edit_wssh_error'); ?>
					</div>
				</div>
				<?php endif; ?>				

				<form action="<?php echo base_url("social_apps/edit_wordpress_settings_self_hosted/{$wp_settings['id']}"); ?>" method="POST">
					<div class="card">

						<?php if ($this->session->userdata('wp_sh_add_domain_name')): ?>
						<div class="alert alert-warning alert-dismissible show fade">
							<div class="alert-body">
								<button class="close" data-dismiss="alert">
									<span>×</span>
								</button>
								<?php echo $this->session->userdata('wp_sh_add_domain_name'); ?>
							</div>
						</div>
						<?php echo $this->session->unset_userdata('wp_sh_add_domain_name'); ?>
						<?php endif; ?>

						<div class="card-header"><h4 class="card-title"><i class="fas fa-info-circle"></i> <?php echo $this->lang->line("Edit App Details"); ?></h4></div>
						<div class="card-body">              
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="domain_name"><i class="fas fa-globe"></i> <?php echo $this->lang->line("Domain Name");?></label>
										<span data-toggle="tooltip" data-original-title="<?php echo $this->lang->line('Provide the domain name where your wordpress blog is installed.'); ?>"><i class="fas fa-info-circle"></i></span>
										<input id="domain_name" name="domain_name" value="<?php echo set_value('domain_name', $wp_settings['domain_name']); ?>" class="form-control" type="text" placeholder="http://yoursite.com">  
										<span class="red"><?php echo form_error('domain_name'); ?></span>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="user_key"><i class="fas fa-file-signature"></i> <?php echo $this->lang->line("User Key");?></label>
										<span data-toggle="tooltip" data-original-title="<?php echo $this->lang->line('User Key can be achieved from the Wordpress Self-hosted Authentication section of the Wordpress > Users > Your Profile page.'); ?>"><i class="fas fa-info-circle"></i></span>
										<input id="user_key" name="user_key" value="<?php echo set_value('user_key', $wp_settings['user_key']); ?>" class="form-control" type="text">  
										<span class="red"><?php echo form_error('user_key'); ?></span>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="authentication_key"><i class="fas fa-key"></i> <?php echo $this->lang->line("Authentication Key");?></label>
										<span data-toggle="tooltip" data-original-title="<?php echo $this->lang->line('Authentication Key needs to be put on the Wordpress Self-hosted Authentication section of the Wordpress > Users > Your Profile page.'); ?>"><i class="fas fa-info-circle"></i></span>
										<input id="authentication_key" name="authentication_key" value="<?php echo set_value('authentication_key', $wp_settings['authentication_key']); ?>" class="form-control" type="text">  
										<span class="red"><?php echo form_error('authentication_key'); ?></span>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label class="custom-switch mt-2">
									<input type="checkbox" name="status" value="1" class="custom-switch-input" <?php echo ('1' == $wp_settings['status']) ? 'checked' : ''; ?>>
									<span class="custom-switch-indicator"></span>
									<span class="custom-switch-description"><?php echo $this->lang->line('Active');?></span>
									<span class="red"><?php echo form_error('status'); ?></span>
								</label>
							</div>
						</div>

						<div class="card-footer bg-whitesmoke">
							<button class="btn btn-primary btn-lg" id="save-btn" type="submit"><i class="fas fa-save"></i> <?php echo $this->lang->line("Save");?></button>
							<button class="btn btn-secondary btn-lg float-right" onclick='goBack("social_apps/index")' type="button"><i class="fa fa-remove"></i>  <?php echo $this->lang->line("Cancel");?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

</section>