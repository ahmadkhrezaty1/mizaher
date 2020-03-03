<div id="put_script"></div>
<section class="section">
	<div class="section-header d-none">
		<h1><i class="fa fa-plus-circle"></i> <?php echo $page_title; ?></h1>
		<div class="section-header-breadcrumb">
		  <div class="breadcrumb-item"><a href="<?php echo base_url('messenger_bot'); ?>"><?php echo $this->lang->line("Messenger Bot"); ?></a></div>
		  <div class="breadcrumb-item"><a href="<?php echo base_url('ecommerce'); ?>"><?php echo $this->lang->line("E-commerce"); ?></a></div>
		  <div class="breadcrumb-item"><?php echo $page_title; ?></div>
		</div>
	</div>

	<div class="section-body">
		<form action="#" enctype="multipart/form-data" id="plugin_form">
			<div class="row">
				<div class="col-12 col-lg-6">
					<div class="card main_card no_shadow">
						<div class="card-header p-0 mb-3" style="border:none;min-height: 0;"><h4><i class="fas fa-store"></i> <?php echo $this->lang->line("Store Information"); ?></h4></div>		
						<div class="card-body p-0">
							<div class="row">

							  <div class="form-group col-12 col-md-6">
							    <label>
							       <?php echo $this->lang->line("Select page"); ?> *
							       <a href="#" data-placement="top" data-toggle="popover" data-trigger="focus" title="<?php echo $this->lang->line("Select page") ?>" data-content='<?php echo $this->lang->line("Select your Facebook page for which you want to create the store.") ?>'><i class='fas fa-info-circle'></i> </a>
							    </label>
							    <?php $page_info['']= $this->lang->line("select page"); ?>
							    <?php echo form_dropdown('page', $page_info,'', 'class="form-control select2" id="page" style="width:100%;"' ); ?>                   
							  </div>

							   <div class="form-group col-12 col-md-6">
							    <label>
							      <?php echo $this->lang->line("Store name"); ?> *
							    </label>
							    <input type="text" name="store_name" id="store_name" class="form-control">                      
							  </div>

							  <div class="form-group col-12 col-md-6">
							    <label>
							      <?php echo $this->lang->line("Email"); ?> *
							    </label>
							    <input type="email" name="store_email" id="store_email" class="form-control">                      
							  </div>

							  <div class="form-group col-12 col-md-6">
							    <label>
							      <?php echo $this->lang->line("Mobile/phone"); ?>
							    </label>
							    <input type="text" name="store_phone" id="store_phone" class="form-control">                      
							  </div>

							  <div class="form-group col-12 col-md-4">
							    <label>
							      <?php echo $this->lang->line("Country"); ?> *
							    </label>
							    <?php 
							    $country_names[''] = $this->lang->line("Select");
							    echo form_dropdown('store_country', $country_names,'', 'class="form-control select2" id="store_country" style="width:100%;"' ); 
							    ?>
							  </div>

							  <div class="form-group col-12 col-md-4">
							    <label>
							      <?php echo $this->lang->line("State"); ?> *
							    </label>
							    <input type="text" name="store_state" id="store_state" class="form-control">                      
							  </div>

							  <div class="form-group col-12 col-md-4">
							    <label>
							      <?php echo $this->lang->line("City"); ?> *
							    </label>
							    <input type="text" name="store_city" id="store_city" class="form-control">                      
							  </div>

							  <div class="form-group col-12 col-md-6">
							    <label>
							      <?php echo $this->lang->line("Street address"); ?> *
							    </label>
							    <input type="text" name="store_address" id="store_address" class="form-control">                  
							  </div>

							  <div class="form-group col-12 col-md-6">
							    <label>
							      <?php echo $this->lang->line("Postal code"); ?> *
							    </label>
							    <input type="text" name="store_zip" id="store_zip" class="form-control">                      
							  </div>

							  <div class="form-group col-12 col-md-6">
							    <label>
							      <?php echo $this->lang->line("Tax"); ?> % 
							    </label>
							    <div class="input-group mb-2">
		                            <input type="number" name="tax_percentage" id="tax_percentage" class="form-control" value="0" min="0" max="100">  
		                            <div class="input-group-append">
		                              <div class="input-group-text">%</div>
		                            </div>
		                        </div>                    
							  </div>

							  <div class="form-group col-12 col-md-6">
							    <label>
							      <?php echo $this->lang->line("Shipping fee"); ?>
							    </label>
							    <div class="input-group mb-2">
		                            <input type="number" name="shipping_charge" id="shipping_charge" class="form-control" value="0" min="0">  
		                            <div class="input-group-append">
		                              <div class="input-group-text">
		                              	<?php 
		                              		$currency = isset($get_ecommerce_config['currency']) ? $get_ecommerce_config['currency'] : "USD";
		                              		$currency_icon = isset($currency_icons[$currency]) ? $currency_icons[$currency] : "$";
		                              		echo $currency_icon; 
		                              	?>		                              		
		                              	</div>
		                            </div>
		                        </div>							                        
							  </div>	 

							  <div class="form-group col-12 col-md-6">
								<label>
									<?php echo $this->lang->line("PayPal checkout"); ?> *
								</label>
								<div class="row">
									<div class="col-12">
										<div class="selectgroup w-100">
											<label class="selectgroup-item">
												<input type="radio" name="paypal_enabled" value="1" class="selectgroup-input">
												<span class="selectgroup-button"> <?php echo $this->lang->line("Yes") ?></span>
											</label>
											<label class="selectgroup-item">
												<input type="radio" name="paypal_enabled" value="0" class="selectgroup-input" checked>
												<span class="selectgroup-button"> <?php echo $this->lang->line("No") ?></span>
											</label>
										</div>
									</div>
								</div>
							  </div>

							  <div class="form-group col-12 col-md-6">
								<label>
									<?php echo $this->lang->line("Stripe checkout"); ?> *
								</label>
								<div class="row">
									<div class="col-12">
										<div class="selectgroup w-100">
											<label class="selectgroup-item">
												<input type="radio" name="stripe_enabled" value="1" class="selectgroup-input">
												<span class="selectgroup-button"> <?php echo $this->lang->line("Yes") ?></span>
											</label>
											<label class="selectgroup-item">
												<input type="radio" name="stripe_enabled" value="0" class="selectgroup-input" checked>
												<span class="selectgroup-button"> <?php echo $this->lang->line("No") ?></span>
											</label>
										</div>
									</div>
								</div>
							  </div>

							  <div class="form-group col-12 col-md-6">
								<label>
									<?php echo $this->lang->line("Manual checkout"); ?> *
								</label>
								<div class="row">
									<div class="col-12">
										<div class="selectgroup w-100">
											<label class="selectgroup-item">
												<input type="radio" name="manual_enabled" value="1" class="selectgroup-input">
												<span class="selectgroup-button"> <?php echo $this->lang->line("Yes") ?></span>
											</label>
											<label class="selectgroup-item">
												<input type="radio" name="manual_enabled" value="0" class="selectgroup-input" checked>
												<span class="selectgroup-button"> <?php echo $this->lang->line("No") ?></span>
											</label>
										</div>
									</div>
								</div>
							  </div>

							  <div class="form-group col-12 col-md-6">
								<label>
									<?php echo $this->lang->line("Cash on delivery"); ?> *
								</label>
								<div class="row">
									<div class="col-12">
										<div class="selectgroup w-100">
											<label class="selectgroup-item">
												<input type="radio" name="cod_enabled" value="1" class="selectgroup-input" checked>
												<span class="selectgroup-button"> <?php echo $this->lang->line("Yes") ?></span>
											</label>
											<label class="selectgroup-item">
												<input type="radio" name="cod_enabled" value="0" class="selectgroup-input">
												<span class="selectgroup-button"> <?php echo $this->lang->line("No") ?></span>
											</label>
										</div>
									</div>
								</div>
							  </div>

							  <div class="col-12 col-md-6">
							    <div class="form-group">
							      <label><?php echo $this->lang->line('Logo'); ?> 
							       <a href="#" data-placement="top" data-toggle="popover" data-trigger="focus" title="<?php echo $this->lang->line("Logo"); ?>" data-content="<?php echo $this->lang->line("Maximum: 1MB, Format: JPG/PNG, Preferred ratio: 16:9"); ?>"><i class='fa fa-info-circle'></i> </a>
							      </label>
							      <div id="store-logo-dropzone" class="dropzone mb-1">
							        <div class="dz-default dz-message">
							          <input class="form-control" name="store_logo" id="store_logo" type="hidden">
							          <span style="font-size: 20px;"><i class="fas fa-cloud-upload-alt" title='<?php echo $this->lang->line("Upload"); ?>' data-toggle="tooltip" style="font-size: 35px;color: #6777ef;"></i> </span>
							        </div>
							      </div>
							      <span class="red"></span>
							    </div>
							  </div>

							  <div class="col-12 col-md-6">
							    <div class="form-group">
							      <label><?php echo $this->lang->line('Favicon'); ?> 
							       <a href="#" data-placement="top" data-toggle="popover" data-trigger="focus" title="<?php echo $this->lang->line("Favicon"); ?>" data-content="<?php echo $this->lang->line("Maximum: 1MB, Format: JPG/PNG, Preference: Square image / 100x100 px"); ?>"><i class='fa fa-info-circle'></i> </a>
							      </label>
							      <div id="store-favicon-dropzone" class="dropzone mb-1">
							        <div class="dz-default dz-message">
							          <input class="form-control" name="store_favicon" id="store_favicon" type="hidden">
							          <span style="font-size: 20px;"><i class="fas fa-cloud-upload-alt" title='<?php echo $this->lang->line("Upload"); ?>' data-toggle="tooltip" style="font-size: 35px;color: #6777ef;"></i> </span>
							        </div>
							      </div>
							      <span class="red"></span>
							    </div>
							  </div>


				  			  <div class="form-group col-12 col-md-8 d-none">
				  			    <label>
				  			      <?php echo $this->lang->line("Select label"); ?>
				  			       <a href="#" data-placement="top" data-toggle="popover" data-trigger="focus" title="<?php echo $this->lang->line("Select label") ?>" data-content='<?php echo $this->lang->line("Will assign to this label after successful checkout.") ?> <?php echo $this->lang->line("You must select page to fill this list with data."); ?>'><i class='fa fa-info-circle'></i> </a>
				  			    </label>
				  			    <?php echo form_dropdown('label_ids[]',array(), '','style="height:45px;overflow:hidden;width:100%;" multiple="multiple" class="form-control select2" id="label_ids"'); ?>
				  			  </div>

				                <div class="col-12 col-md-4">
				                  <div class="form-group">
				                    <label for="status" > <?php echo $this->lang->line('Status');?> *</label><br>
				                    <label class="custom-switch mt-2">
				                      <input type="checkbox" name="status" value="1" class="custom-switch-input" checked>
				                      <span class="custom-switch-indicator"></span>
				                      <span class="custom-switch-description"><?php echo $this->lang->line('Online');?></span>
				                      <span class="red"><?php echo form_error('status'); ?></span>
				                    </label>
				                  </div>
				                </div>	

							</div>
						</div>
						
					</div>
				</div>

				<div class="col-12 col-lg-6">
					<div class="card main_card no_shadow">
						<div class="card-header p-0 mb-3" style="border:none;min-height: 0;"><h4 class="full_width"><i class="fas fa-check-circle"></i> <?php echo $this->lang->line("Confirmation Message"); ?> <a id="variables" class="float-right text-warning pointer"><i class="fas fa-circle"></i> <?php echo  $this->lang->line("Variables"); ?></a></h4> </div>				
						<div class="card-body p-0">
							<ul class="nav nav-tabs" id="sequence_tab" role="tablist">

							  <li class="nav-item">
							    <a class="nav-link active" id="messenger_link" data-toggle="tab" href="#messenger_tab" role="tab" aria-controls="profile" aria-selected="false"><?php echo  $this->lang->line("Messenger"); ?></a>
							  </li>

							  <?php if($this->session->userdata('user_type') == 'Admin' || in_array(264,$this->module_access)) : ?>
							  <li class="nav-item">
							    <a class="nav-link" id="sms_link" data-toggle="tab" href="#sms_tab" role="tab" aria-controls="profile" aria-selected="false"><?php echo  $this->lang->line("SMS"); ?></a>
							  </li>
							  <?php endif; ?>

							  <?php 
							  if($this->basic->is_exist("modules",array("id"=>263))) :
								  if($this->session->userdata('user_type') == 'Admin' || in_array(263,$this->module_access)) : ?>
								  <li class="nav-item">
								    <a class="nav-link" id="email_link" data-toggle="tab" href="#email_tab" role="tab" aria-controls="profile" aria-selected="false"><?php echo  $this->lang->line("Email"); ?></a>
								  </li>
								  <?php endif; ?>
							  <?php endif; ?>

							</ul>
							<div class="tab-content tab-bordered">

							  <div class="tab-pane fade show active" id="messenger_tab" role="tabpanel" aria-labelledby="messenger_link">
							   
							   <div class="row">				                
				                 <div class="col-12 col-sm-12 col-md-6 col-lg-7">
				                 	<?php echo $this->lang->line("Messenger Content"); ?>
				                 	<div class="tab-content" id="MsgReminderTabContent">
					                 	 <?php
					                 	 for($i=1; $i <=$how_many_reminder; $i++)
				                       	 { ?>
					                         <div class="reminder_badge_warpper tab-pane fade  d-none <?php if($i==1) echo 'active show';?>" id="msg_reminder<?php echo $i;?>" role="tabpanel" aria-labelledby="msg_reminder_link>">
						                         <span class="reminder_badge" data-toggle="tooltip" title="<?php echo $this->lang->line('Messenger Reminder').' #'.$i; ?>"><i class="fas fa-bell"></i> <?php echo $i;?></span>											
						                         <div class="reminder_block">
						                         	<span class="block1">
						                         		<textarea autofocus data-toggle="tooltip" title="<?php echo $this->lang->line('Intro message will be displayed here, click to edit text. Clean text if you do not want this.'); ?>"  name="msg_reminder_text[]" id="msg_reminder_text<?php echo $i;?>">Hi {{last_name}}, have you forgot something special?</textarea>
						                         	</span>			                         	
						                         	<span class="block2 ">
						                         		<img data-toggle="tooltip" title="<?php echo $this->lang->line('Item preview will be displayed here as carousel.'); ?>" src="<?php echo base_url('assets/img/products/product-6.jpg') ?>">
						                         	</span>
						                         	<span class="block3">
						                         		<h6 data-toggle="tooltip" title="<?php echo $this->lang->line('Item name will be displayed here.'); ?>"><?php echo "Cart item title"; ?></h6>
						                         		<span data-toggle="tooltip" title="<?php echo $this->lang->line('Item quantity & price will be displayed here.'); ?>" class="text-muted"><?php echo "Quantity & price"; ?> </span>	                         	
						                         		<span class="text-muted website" data-toggle="tooltip" title="<?php echo $this->lang->line('Store name will be displayed here.'); ?>">Store Name</span>			                         	
						                         		<p>
						                         		<input data-toggle="tooltip" title="<?php echo $this->lang->line('Item link will be embedded here, click to edit button name.'); ?>" value="View Details" class="btn btn-block bg-white" name="msg_reminder_btn_details[]" id="msg_reminder_btn_details<?php echo $i;?>"/>
						                         		</p>
						                         	</span>
						                         	<span class="block4">
						                         		<textarea data-toggle="tooltip" title="<?php echo $this->lang->line('Additonal information like coupon can be displayed here, click to edit text.'); ?>" name="msg_reminder_text_checkout[]" id="msg_reminder_text_checkout<?php echo $i;?>">Stock limited, complete your order before it is out of stock.</textarea>	                         	
						                         		<p>
						                         		<input data-toggle="tooltip" title="<?php echo $this->lang->line('Checkout link will be embedded here, click to edit button name.'); ?>" value="Checkout Now" class="btn btn-block bg-white" name="msg_reminder_btn_checkout[]" id="msg_reminder_btn_checkout<?php echo $i;?>"/>
						                         		</p>
						                         	</span>
						                         </div>
						                     </div>
					                     <?php 
				                       	 } 
				                       	 ?>
				                       	 
				                       	 <div class="reminder_badge_warpper tab-pane fade show active" id="msg_checkout" role="tabpanel" aria-labelledby="msg_checkout_link>">
				                       	 	<span class="reminder_badge" data-toggle="tooltip" title="<?php echo $this->lang->line('Messenger Checkout'); ?>"><i class="fas fa-shopping-bag"></i></span>							
					                         <div class="reminder_block">
					                         	<span class="block1">
					                         		<textarea autofocus data-toggle="tooltip" title="<?php echo $this->lang->line('Intro message will be displayed here, click to edit text. Clean text if you do not want this.'); ?>"  name="msg_checkout_text" id="msg_checkout_text">Congratulations {{last_name}}!&#13;&#10;Thanks for shopping from our store. You made the right choice. If you need any information, just leave us a message here.</textarea>
					                         	</span>	
					                         	<span class="block5">

					                         		<ul class="list-group list-group-flush">
													  <li class="list-group-item"><span class="text-muted">Order confirmation</span></li>

													  <li class="list-group-item">
													  	<div class="media">
													  	  <img class="align-self-start mr-3" src="<?php echo base_url('assets/img/products/product-6.jpg') ?>">
													  	  <div class="media-body">
													  	    <h6 class="mt-0">Cart item title</h6>
													  	    <p class="text-muted">Price : XX</p>
													  	    <p class="text-muted">Qty : XX</p>
													  	  </div>
													  	</div>
													  </li>

													  <li class="list-group-item payment_info">
													  	<p class="text-muted">Paid with</p>
													  	<h6>Payment method</h6>
													  	<br>
													  	<p class="text-muted">Deliver to</p>
													  	<h6>Delivery address...</h6>
													  </li>

													  <li class="list-group-item">
													  	<span class="text-muted float-left">Total</span>
													  	<b class="float-right">$xx.xx</b>
													  </li>
													</ul>
					                         	</span>
					                         	<span class="block4">
					                         		<textarea data-toggle="tooltip" title="<?php echo $this->lang->line('Additonal information about next purchase like coupon can be displayed here, click to edit text.'); ?>" name="msg_reminder_text_checkout_next" id="msg_reminder_text_checkout_next">You can see your order history and status here.</textarea>		                         	
					                         		<p>
					                         		<input data-toggle="tooltip" title="<?php echo $this->lang->line('Buyer order page link will be embedded here, click to edit button name. Clean text if you do not want this.'); ?>" value="MY ORDERS" class="btn btn-block bg-white" name="msg_checkout_btn_website" id="msg_checkout_btn_website"/>
					                         		</p>
					                         	</span>
					                         </div>
					                     </div>

				                    </div>
				                 </div>

         		                 <div class="col-12 col-sm-12 col-md-6 col-lg-5">
         	                       <ul class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
         	                       	 <?php 
         	                       	 for($i=1; $i <=$how_many_reminder; $i++)
         	                       	 { ?>			                       	  	
         		                       	 <li class="nav-item d-none">
                                  			<a href="#msg_reminder<?php echo $i;?>"  id="msg_reminder_link<?php echo $i;?>" class="nav-link <?php if($i==1) echo 'active'; ?>" data-toggle="pill" role="tab" aria-controls="msg_reminder<?php echo $i;?>" aria-selected="true"><i class="fas fa-bell"></i> <?php echo $this->lang->line("Messenger Reminder");?> #<?php echo $i;?></a> 
                                  		
                                  			<?php 
                                  			$tmp_name = 'msg_reminder_time[]';
                                  			$tmp_id = 'msg_reminder_time_'.$i;
                                  			$tmp_select = ($i==1) ? '1' : '';
                                  			echo form_dropdown($tmp_name, $hours, $tmp_select,'id="'.$tmp_id.'" class="form-control" style="width: 100% !important;"'); 
                                  			?>
         		                         </li>
         	                       	 <?php 
         	                       	 } 
         	                       	 ?>
         	                         <li class="nav-item">		                         			
         	                         	<a href="#msg_checkout"  id="msg_checkout_link" class="nav-link nav_cart active" data-toggle="pill" role="tab" aria-controls="msg_checkout" aria-selected="true"><i class="fas fa-shopping-bag"></i> <?php echo $this->lang->line("Checkout Messenger");?></a> 
         	                         </li>
         	                       </ul>
         		                 </div>
				               </div>

							  </div>


							  <?php if($this->session->userdata('user_type') == 'Admin' || in_array(264,$this->module_access)) : ?>
							  <div class="tab-pane fade" id="sms_tab" role="tabpanel" aria-labelledby="sms_link">
							  	<div class="row">				                 
				                 <div class="col-12 col-sm-12 col-md-7">
				                 	<?php echo $this->lang->line("SMS Content"); ?>
				                 	<div class="tab-content" id="SmsReminderTabContent">
					                 	 <?php
					                 	 for($i=1; $i <=$how_many_reminder; $i++)
				                       	 { ?>
					                         <div class="reminder_badge_warpper tab-pane fade d-none <?php if($i==1) echo 'active show';?>" id="sms_reminder<?php echo $i;?>" role="tabpanel" aria-labelledby="sms_reminder_link>">
						                         <span class="reminder_badge" data-toggle="tooltip" title="<?php echo $this->lang->line('SMS Reminder').' #'.$i; ?>"><i class="fas fa-bell"></i> <?php echo $i;?></span>									
						                         <div class="reminder_block">
						                         	<span class="block4">
						                         		<textarea data-toggle="tooltip" title="<?php echo $this->lang->line('SMS content goes here.');?>" name="sms_reminder_text_checkout[]" id="sms_reminder_text_checkout<?php echo $i;?>">Hi, have you forgot something special? Stock limited, complete your order before it is out of stock : {{order_url}}</textarea>
						                         	</span>
						                         </div>
						                     </div>
					                     <?php 
				                       	 } 
				                       	 ?>
				                       	 
				                       	 <div class="reminder_badge_warpper tab-pane fade active show" id="sms_checkout" role="tabpanel" aria-labelledby="sms_checkout_link>">	
				                       	 	<span class="reminder_badge" data-toggle="tooltip" title="<?php echo $this->lang->line('SMS Checkout'); ?>"><i class="fas fa-shopping-bag"></i></span>										
					                         <div class="reminder_block">
					                         	<span class="block4">
					                         		<textarea data-toggle="tooltip" title="<?php echo $this->lang->line('SMS content goes here.'); ?>" name="sms_reminder_text_checkout_next" id="sms_reminder_text_checkout_next">Congratulations, thanks for shopping from our store. You made the right choice.</textarea>
					                         	</span>
					                         </div>
					                     </div>

				                    </div>

				                    <br>
				                    <div class="form-group">
				                    	<div class="label"><?php echo $this->lang->line("SMS Sender"); ?></div>
				                    	<?php echo form_dropdown('sms_api_id', $sms_option, '','class="form-control select2" id="sms_api_id" style="width:100%"'); ?>
				                    </div>
				                 </div>

         		                 <div class="col-12 col-sm-12 col-md-5">
         	                       <ul class="nav flex-column nav-pills " id="v-pills-tab" role="tablist" aria-orientation="vertical">
         	                       	 <?php 
         	                       	 for($i=1; $i <=$how_many_reminder; $i++)
         	                       	 { ?>			                       	  	
         		                       	 <li class="nav-item d-none">
                                  			<a href="#sms_reminder<?php echo $i;?>"  id="sms_reminder_link<?php echo $i;?>" class="nav-link <?php if($i==1) echo 'active'; ?>" data-toggle="pill" role="tab" aria-controls="sms_reminder<?php echo $i;?>" aria-selected="true"><i class="fas fa-bell"></i> <?php echo $this->lang->line("SMS Reminder");?> #<?php echo $i;?></a> 
                                  		
                                  			<?php 
                                  			$tmp_name = 'sms_reminder_time[]';
                                  			$tmp_id = 'sms_reminder_time_'.$i;
                                  			$tmp_select = '';
                                  			echo form_dropdown($tmp_name, $hours, $tmp_select,'id="'.$tmp_id.'" class="form-control" style="width: 100% !important;"'); 
                                  			?>
         		                         </li>
         	                       	 <?php 
         	                       	 } 
         	                       	 ?>
         	                         <li class="nav-item">		                         			
         	                         	<a href="#sms_checkout"  id="sms_checkout_link" class="nav-link nav_cart active" data-toggle="pill" role="tab" aria-controls="msg_checkout" aria-selected="true"><i class="fas fa-shopping-bag"></i> <?php echo $this->lang->line("Checkout SMS");?></a> 
         	                         </li>
         	                       </ul>
         		                 </div>
				                </div>
							  </div>
							  <?php endif; ?>



							  <?php if($this->session->userdata('user_type') == 'Admin' || in_array(263,$this->module_access)) : ?>
							  <div class="tab-pane fade" id="email_tab" role="tabpanel" aria-labelledby="email_link">
							    <div class="row">							      
							      <div class="col-12 col-sm-12 col-md-7">
							       	<?php echo $this->lang->line("Email Content"); ?>
							       	<div class="tab-content" id="EmailReminderTabContent">
							           	 <?php
							           	 for($i=1; $i <=$how_many_reminder; $i++)
							             	 { ?>
							                   <div class="reminder_badge_warpper tab-pane fade d-none <?php if($i==1) echo 'active show';?> " style="border:none;padding: 0" id="email_reminder<?php echo $i;?>" role="tabpanel" aria-labelledby="email_reminder_link>">
							                       <span class="reminder_badge" data-toggle="tooltip" title="<?php echo $this->lang->line('Email Reminder').' #'.$i; ?>"><i class="fas fa-bell"></i> <?php echo $i;?></span>
							                       <textarea class="visualeditor" data-toggle="tooltip" title="<?php echo $this->lang->line('Email content goes here.');?>" name="email_reminder_text_checkout[]" id="email_reminder_text_checkout<?php echo $i;?>">Hi {{last_name}},<br>Have you forgot something special? Stock limited, complete your order before it is out of stock : <a href="{{order_url}}" target="_blank">Checkout here</a></a><br>Happy shopping :)</textarea>							                       	
							                   </div>
							               	 <?php 
							             	 } 
							             	 ?>
							             	 
							             	<div class="reminder_badge_warpper tab-pane fade active show" style="border:none;padding: 0" id="email_checkout" role="tabpanel" aria-labelledby="email_checkout_link>">	
							             	 	<span class="reminder_badge" data-toggle="tooltip" title="<?php echo $this->lang->line('Email Checkout'); ?>"><i class="fas fa-shopping-bag"></i></span>										
							                	<textarea class="visualeditor"  data-toggle="tooltip" title="<?php echo $this->lang->line('Email content goes here.'); ?>" name="email_reminder_text_checkout_next" id="email_reminder_text_checkout_next">Congratulations {{last_name}}!<br>Thanks for shopping from our store. If you need any information, just leave us a message here.<br>You can see your order history and status here : {{my_orders_url}}<br>Have a nice day :)</textarea>
							                </div>

							          </div>
				                      <div class="form-group">
				                    	<div class="label"><?php echo $this->lang->line("Email Sender"); ?></div>
				                    	<?php echo form_dropdown('email_api_id', $email_option, '','class="form-control select2" id="email_api_id" style="width:100%"'); ?>
				                      </div>
				                      <div class="form-group">
				                    	<div class="label"><?php echo $this->lang->line("Email Subject"); ?></div>
				                    	<input name="email_subject" id="email_subject" class="form-control" value="<?php echo $this->lang->line('Order Update'); ?>">
				                      </div>
							       </div>

							       <div class="col-12 col-sm-12 col-md-5">
							         <ul class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
							         	 <?php 
							         	 for($i=1; $i <=$how_many_reminder; $i++)
							         	 { ?>			                       	  	
							             	 <li class="nav-item d-none">
							       			<a href="#email_reminder<?php echo $i;?>"  id="email_reminder_link<?php echo $i;?>" class="nav-link <?php if($i==1) echo 'active'; ?>" data-toggle="pill" role="tab" aria-controls="email_reminder<?php echo $i;?>" aria-selected="true"><i class="fas fa-bell"></i> <?php echo $this->lang->line("Email Reminder");?> #<?php echo $i;?></a> 
							       		
							       			<?php 
							       			$tmp_name = 'email_reminder_time[]';
							       			$tmp_id = 'email_reminder_time_'.$i;
							       			$tmp_select = '';
							       			echo form_dropdown($tmp_name, $hours, $tmp_select,'id="'.$tmp_id.'" class="form-control" style="width: 100% !important;"'); 
							       			?>
							               </li>
							         	 <?php 
							         	 } 
							         	 ?>
							           <li class="nav-item">		                         			
							           	<a href="#email_checkout"  id="email_checkout_link" class="nav-link nav_cart active" data-toggle="pill" role="tab" aria-controls="msg_checkout" aria-selected="true"><i class="fas fa-shopping-bag"></i> <?php echo $this->lang->line("Checkout Email");?></a> 
							           </li>
							         </ul>
							       </div>

							    </div>
							  </div>
							  <?php endif; ?>

							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<div class="card no_shadow">
						<div class="card-footer p-0">  
							<button class="btn btn-lg btn-primary" id="get_button" name="get_button" type="button"><i class="fas fa-plus-circle"></i> <?php echo $this->lang->line("Create Store");?></button>
							<button class="btn btn-lg btn-light float-right" onclick="ecommerceGoBack()" type="button"><i class="fas fa-times"></i> <?php echo $this->lang->line("Cancel");?></button>
					    </div>
					</div>
				</div>
			</div>

		</form>
	</div>
</section>




<script>
	var base_url="<?php echo site_url(); ?>";
	var action_url = base_url+"ecommerce/add_store_action";
	var success_title = '<?php echo $this->lang->line("Store Created"); ?>';
	$("document").ready(function()	{
		
		$(document).on('blur','#store_name',function(event){
			event.preventDefault();
			var ref=$(this).val();
			$("#email_subject").val(ref+" | <?php echo $this->lang->line('Cart Update'); ?>");

		});

		$(document).on('change','#page',function(event){
			event.preventDefault();

			var page_id=$(this).val();			 
			  $.ajax({
			  type:'POST' ,
			  url: base_url+"ecommerce/get_template_label_dropdown",
			  data: {page_id:page_id},
			  dataType : 'JSON',
			  success:function(response){
			    // $("#template_id").html(response.template_option);
			    $("#label_ids").html(response.label_option);
			    $("#put_script").html(response.script);
			  }

			});
		});
		
		$(document).on('click','#get_button',function(e){
			get_button();
		});


	});
</script>

<?php include(APPPATH.'views/ecommerce/store_style.php'); ?>
<?php include(APPPATH.'views/ecommerce/store_js.php'); ?>