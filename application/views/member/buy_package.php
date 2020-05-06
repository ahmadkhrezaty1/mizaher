<section class="section">
  <div class="section-header">
    <h1><i class="fas fa-cart-plus"></i>باقات منصة ميلانا للمستخدمين</h1>
    <div class="section-header-button">
      <a href="<?php echo base_url('payment/transaction_log'); ?>" class="btn btn-primary"><i class="fas fa-history"></i> <?php echo $this->lang->line("Transaction Log"); ?></a>
      <a href="<?php echo base_url('payment/buy_premium_package'); ?>" class="btn btn-danger"><?php echo $this->lang->line("PREMIUM PACKAGES"); ?></a>
    </div>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item">باقات منصة ميلانا بروجكيت للمستخدمين</a></div>
																	   
    </div>
  </div>

  <div class="section-body">
    
    <div class="row">
      <?php 
      foreach($payment_package as $pack)
      {?>
    
        <div class="col-12 col-md-4 col-lg-4" style="-ms-flex: 0 0 25%;flex: 0 0 25%;max-width: 25%;">
          <div class="pricing <?php if($pack['highlight']=='1') echo 'pricing-highlight';?>">

            <div class="">
				  
										 
              <div class="pricing-price">
                <div><?php echo $curency_icon; ?></sup><?php echo $pack["price"]?></div>
                <div><?php echo $pack["validity"]?> <?php echo $this->lang->line("days"); ?></div>
              </div>

            <div class="">
              <div class="card" style="width: 17rem;">
                <?php
                  $src = base_url().'upload/package/default.jpg';
                  if(!empty($pack["package_photo"])){
                    $src = base_url().$pack["package_photo"];
                  }
                ?>
                <img style="width: 271px; height: 200px" src="<?php echo $src ?>">
                <div class="card-body"> 
                  <div class="pricing-title">
                    <?php echo $pack["package_name"]; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="pricing-details nicescroll" style="height: 180px;">
              <?php echo $pack["description"]; ?>
                
              </div>
            <!--
              <div class="pricing-details nicescroll" style="height: 180px;">
                <?php 
                $module_ids=$pack["module_ids"];
                $monthly_limit=json_decode($pack["monthly_limit"],true);
                $module_names_array=$this->basic->execute_query('SELECT module_name,id FROM modules WHERE FIND_IN_SET(id,"'.$module_ids.'") > 0  ORDER BY module_name ASC');

                foreach ($module_names_array as $row)
                {                              
                    $limit=0;
                    $limit=$monthly_limit[$row["id"]];
                    if($limit=="0") $limit2=$this->lang->line("unlimited");
                    else $limit2=$limit;
                    $limit2=" : ".$limit2;
                    echo '
                    <div class="pricing-item">
                      <div class="pricing-item-icon_x bg-light_x"><i class="fas fa-check"></i></div>
                      <div class="pricing-item-label">&nbsp;'.$this->lang->line($row["module_name"]).$limit2.'</div>
                    </div>';
                } ?>
                                
              </div>
            -->
            </div>
            <div class="pricing-cta">
              <a class="choose_package" href="#" data-fsc-item-path-value="<?php echo $pack['fastspring'];?>" data-fsc-item-path="<?php echo $pack['fastspring'];?>" data-fsc-action="Add, Checkout" data-id="<?php echo $pack['id'];?>"><?php echo $this->lang->line("Select Package"); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
      <?php 
      } ?>
    </div>
  </div>
</section>

<div class="modal fade" tabindex="-1" role="dialog" id="payment_modal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-cart-plus"></i> <?php echo $this->lang->line("Payment Options");?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="text-center" id="waiting" style="width: 100%;margin: 20px 0;"><i class="fas fa-spinner fa-spin blue" style="font-size:40px;"></i></div>
        <div id="button_place"></div>
        <br>
        <?php 
        if ($last_payment_method != '')
        { 
          
          $payment_type = ($has_reccuring == 'true') ? $this->lang->line('Recurring') : $this->lang->line('Manual');

          echo '<br><div class="alert alert-light alert-has-icon">
                  <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
                  <div class="alert-body">
                    <div class="alert-title">'.$this->lang->line("Last Payment").'</div>
                    '.$this->lang->line("Last Payment").' : '.$last_payment_method.' ('.$payment_type.')
                  </div>
                </div>';
        }?>
         <p>
           <button style="background:#FFC439;color:#000" class="btn btn-warning brn-lg" id="selected-package-id-fast" value="" data-fsc-item-path="gold-reply" data-fsc-item-path-value="gold-reply" data-fsc-action="Add, Checkout">Pay With Fastspring
           </button>
         </p>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <?php if ('yes' == $manual_payment): ?>
          <button type="button" id="manual-payment-button" class="btn btn-info"><?php echo $this->lang->line('Manual Payment'); ?></button>      
        <?php endif; ?>
        <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal"><i class="fa fa-remove"></i> <?php echo $this->lang->line("Close"); ?></button>
      </div>
    </div>
  </div>
</div>

<?php if ('yes' == $manual_payment): ?>
<div class="modal fade" role="dialog" id="manual-payment-modal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-invoice-dollar"></i> <?php echo $this->lang->line("Manual payment");?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="container">

          <?php if (isset($manual_payment_instruction) && ! empty($manual_payment_instruction)): ?>
          <div class="row">
            <div class="col-lg-12 mb-4">
              <!-- Manual payment instruction -->
              <h6  class="display-6"><i class="far fa-lightbulb"></i> <?php echo $this->lang->line('Manual payment instructions'); ?></h6>
                  <?php echo $manual_payment_instruction; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Paid amount and currency -->
          <div class="row">
            <div class="col-lg-6 mb-4">
              <div class="form-group">
                <label for="paid-amount"><i class="fa fa-money-bill-alt"></i> <?php echo $this->lang->line('Paid Amount'); ?>:</label>
                <input type="number" name="paid-amount" id="paid-amount" class="form-control" min="1">
                <input type="hidden" id="selected-package-id">
              </div>
            </div>
            <div class="col-lg-6 mb-4">
              <div class="form-group">
                <label for="paid-currency"><i class="fa fa-coins"></i> <?php echo $this->lang->line('Currency'); ?></label>              
                <?php echo form_dropdown('paid-currency', $currency_list, $currency, ['id' => 'paid-currency', 'class' => 'form-control select2','style'=>'width:100%']); ?>
              </div>
            </div>
          </div>          
          
          <div class="row">
            <!-- Image upload - Dropzone -->
            <div class="col-lg-6">
              <div class="form-group">
                <label><i class="fa fa-paperclip"></i> <?php echo $this->lang->line('Attachment'); ?> <?php echo $this->lang->line('(Max 5MB)');?> </label>
                <div id="manual-payment-dropzone" class="dropzone mb-1">
                  <div class="dz-default dz-message">
                    <input class="form-control" name="uploaded-file" id="uploaded-file" type="hidden">
                    <span style="font-size: 20px;"><i class="fas fa-cloud-upload-alt" style="font-size: 35px;color: #6777ef;"></i> <?php echo $this->lang->line('Upload'); ?></span>
                  </div>
                </div>
                <span class="red">Allowed types: pdf, doc, txt, png, jpg and zip</span>
              </div>
            </div>

            <!-- Additional Info -->
            <div class="col-lg-6">
              <div class="form-group">
                <label for="paid-amount"><i class="fa fa-info-circle"></i> <?php echo $this->lang->line('Additional Info'); ?>:</label>
                &nbsp;
                <textarea name="additional-info" id="additional-info" class="form-control"></textarea>
              </div>
            </div>  
          </div>

        </div><!-- ends container -->
      </div><!-- ends modal-body -->

      <!-- Modal footer -->
      <div class="modal-footer bg-whitesmoke br">
        <button type="button" id="manual-payment-submit" class="btn btn-primary"><?php echo $this->lang->line('Submit'); ?></button>      
        <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal"><i class="fa fa-remove"></i> <?php echo $this->lang->line("Close"); ?></button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
  $(document).ready(function() {

    // Fixes multiple modal issues
    $('.modal').on("hidden.bs.modal", function (e) { 
      if ($('.modal:visible').length) { 
        $('body').addClass('modal-open');
      }
    });

    var base_url="<?php echo site_url();?>",
      payment_modal = $('#payment_modal');

    function get_payment_button(package) 
    {
						   
							  
			
		
					  
								 
												 
									
			
									
												 
			
               
                  
                  
      
    
            
                 
                         
                  
      
                  
                         
      
        
       
    }    

    $(document).on('click', ".choose_package", function(e) {
       e.preventDefault();           
       var package=$(this).attr('data-id');
       var fastspring=$(this).attr('data-fastspring');

       // Sets package id for manual payment
       $('#selected-package-id').val(package);
       $('#selected-package-id-fast').val(package);
       $('#selected-package-id-fast').attr('data-fsc-item-path', fastspring);
       $('#selected-package-id-fast').attr('data-fsc-item-path-value', fastspring);
       
       var has_reccuring = <?php echo $has_reccuring; ?>;
       if(has_reccuring)  
       {
        swal("<?php echo $this->lang->line('Subscription Message'); ?>", "<?php echo $this->lang->line('You have already a subscription enabled in paypal. If you want to use different paypal or different package, make sure to cancel your previous subscription from your paypal.');?>")
        .then((value) => {
          get_payment_button(package);            
        });
      }
      else get_payment_button(package);
    });

  });
</script>

<?php if ('yes' == $manual_payment): ?>
<script>
  <?php if(!empty($this->session->flashdata('not_limited'))){ ?>
    alert('<?php echo $this->lang->line("You Must add all users to your previous package") ?>');
  <?php } ?>
  $(document).ready(function() {

    $(document).on('click', '#manual-payment-button', function() {
      $('#payment_modal').modal('toggle');
      $('#manual-payment-modal').modal();
    });

    // Uploads files
    var uploaded_file = $('#uploaded-file');
    Dropzone.autoDiscover = false;
    $("#manual-payment-dropzone").dropzone({ 
      url: '<?php echo base_url('payment/manual_payment_upload_file'); ?>',
      maxFilesize:5,
      uploadMultiple:false,
      paramName:"file",
      createImageThumbnails:true,
      acceptedFiles: ".pdf,.doc,.txt,.png,.jpg,.jpeg,.zip",
      maxFiles:1,
      addRemoveLinks:true,
      success:function(file, response) {
        var data = JSON.parse(response);

        // Shows error message
        if (data.error) {
          swal({
            icon: 'error',
            text: data.error,
            title: '<?php echo $this->lang->line('Error!'); ?>'
          });
          return;
        }

        if (data.filename) {
          $(uploaded_file).val(data.filename);
        }
      },
      removedfile: function(file) {
        var filename = $(uploaded_file).val();
        delete_uploaded_file(filename);
      },
    });

    // Handles form submit
    $(document).on('click', '#manual-payment-submit', function() {
      
      // Reference to the current el
      var that = this;

      // Shows spinner
      $(that).addClass('disabled btn-progress');

      var data = {
        paid_amount: $('#paid-amount').val(),
        paid_currency: $('#paid-currency').val(),
        package_id: $('#selected-package-id').val(),
        additional_info: $('#additional-info').val(),
      };

      $.ajax({
        type: 'POST',
        dataType: 'JSON',
        url: '<?php echo base_url('payment/manual_payment'); ?>',
        data: data,
        success: function(response) {
          if (response.success) {
            // Hides spinner
            $(that).removeClass('disabled btn-progress');

            // Empties form values
            empty_form_values();
            $('#selected-package-id').val('');  

            // Shows success message
            swal({
              icon: 'success',
              title: '<?php echo $this->lang->line('Success!'); ?>',
              text: response.success,
            });

            // Hides modal
            $('#manual-payment-modal').modal('hide');
          }

          // Shows error message
          if (response.error) {
            // Hides spinner
            $(that).removeClass('disabled btn-progress');

            swal({
              icon: 'error',
              title: '<?php echo $this->lang->line('Error!'); ?>',
              text: response.error,
            });
          }
        },
        error: function(xhr, status, error) {
          $(that).removeClass('disabled btn-progress');
        },
      });
    });

    $('#manual-payment-modal').on('hidden.bs.modal', function (e) {
      var filename = $(uploaded_file).val();
      delete_uploaded_file(filename);
      $('#selected-package-id').val(''); 
    });

    function delete_uploaded_file(filename) {
      if('' !== filename) {     
        $.ajax({
          type: 'POST',
          dataType: 'JSON',
          data: { filename },
          url: '<?php echo base_url('payment/manual_payment_delete_file'); ?>',
          success: function(data) {
            $('#uploaded-file').val('');
          }
        });
      }

      // Empties form values
      empty_form_values();     
    }

    // Empties form values
    function empty_form_values() {
      $('#paid-amount').val(''),
      $('.dz-preview').remove();
      $('#additional-info').val(''),
      $('#paid-currency').prop("selectedIndex", 0);
      $('#manual-payment-dropzone').removeClass('dz-started dz-max-files-reached');

      // Clears added file
      Dropzone.forElement('#manual-payment-dropzone').removeAllFiles(true);
    }

  });
</script>
<!-- //////////////   Test Mode ////////////// -->
<!--
<script
    id="fsc-api"
    src="https://d1f8f9xcsvx3ha.cloudfront.net/sbl/0.8.2/fastspring-builder.min.js"
    type="text/javascript"
    data-popup-closed="onFSPopupClosed"
    data-storefront="milana.test.onfastspring.com/popup-milana">
</script>
-->
<!-- //////////////   Milana Mode ////////////// -->

<script
    id="fsc-api"
    src="https://d1f8f9xcsvx3ha.cloudfront.net/sbl/0.8.2/fastspring-builder.min.js"
    type="text/javascript"
    data-popup-closed="onFSPopupClosed"
    data-storefront="milana.onfastspring.com/popup-milana">
</script>

<script>
    function onFSPopupClosed(orderReference) {
      if (orderReference)
      {
           var packageId = $('#selected-package-id').val();
           console.log(orderReference.id);
           fastspring.builder.reset();
           window.location.replace("<?php echo base_url() ?>payment/fastspring_pay/" + orderReference.id + "/" + packageId);
      } else {
           console.log("no order ID");
    }
    }
</script>
<?php endif; ?> 