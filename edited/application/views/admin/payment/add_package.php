<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo base_url() ?>assets/css/froala_editor.min.css">
<link rel="stylesheet" href="<?php echo base_url() ?>assets/css/froala_style.min.css">
<link rel="stylesheet" href="<?php echo base_url() ?>assets/css/plugins/code_view.css">
<link rel="stylesheet" href="<?php echo base_url() ?>assets/css/plugins/image_manager.css">
<link rel="stylesheet" href="<?php echo base_url() ?>assets/css/plugins/image.css">
<link rel="stylesheet" href="<?php echo base_url() ?>assets/css/plugins/table.css">
<link rel="stylesheet" href="<?php echo base_url() ?>assets/css/plugins/video.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.css">

<section class="section section_custom">
  <div class="section-header">
    <h1><i class="fas fa-plus-circle"></i> <?php echo $page_title; ?></h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><?php echo $this->lang->line("Subscription"); ?></div>
      <div class="breadcrumb-item active"><a href="<?php echo base_url('payment/package_manager'); ?>"><?php echo $this->lang->line("Package Manager"); ?></a></div>
      <div class="breadcrumb-item"><?php echo $page_title; ?></div>
    </div>
  </div>

  <?php $this->load->view('admin/theme/message'); ?>

  <div class="row">
    <div class="col-12">
<div class="card">
          <div class="card-body">
       <?php echo form_open_multipart(site_url('payment/add_package_action'),array('id'=>'form_transout', 'class' => 'form-horizontal')); ?>
        <div class="card">
          <div class="card-body">
             
            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="name"> <?php echo $this->lang->line("Package Name")?> *</label>
                  <input name="name" value="<?php echo set_value('name');?>"  class="form-control" type="text">
                  <span class="red"><?php echo form_error('name'); ?></span>
                </div>
              </div>
              <div class="col-6">
                <div class="form-group">
                  <label for="price"><?php echo $this->lang->line("Price")?> - <?php echo isset($payment_config[0]['currency']) ? $payment_config[0]['currency'] : 'USD'; ?> *</label>              
                  <input name="price" value="<?php echo set_value('price');?>"  class="form-control" type="text">
                  <span class="red"><?php echo form_error('price'); ?></span>               
                </div>
              </div>
            </div>         

             <div class="form-group">
               <label for="price"><?php echo $this->lang->line("Validity");?> *</label>              
                <div class="row">
                  <div class="col-6">
                    <input type="text" name="validity_amount" value="<?php echo set_value('validity_amount') ?>" class="form-control">
                  </div>
                  <div class="col-6">
                    <?php echo form_dropdown('validity_type', $validity_type, set_value('validity_type'), 'class="form-control select2"'); ?>
                  </div>
                </div>
               <span class="red"><?php echo form_error('validity_amount'); ?></span>
              
             </div>

             <div class="form-group">
               <label for="price"><?php echo $this->lang->line("Fastspring");?> *</label>              
                <div class="row">
                  <div class="col-12">
                    <input type="text" name="fastspring" value="<?php echo set_value('fastspring') ?>" class="form-control">
                  </div>
                </div>              
             </div>

             <div class="form-group">
                <div class="row">
                  <div class="col-6">
                    <label for="package_photo"><?php echo $this->lang->line("Package Photo");?> *</label>
                    <input type="file" name="package_photo" class="form-control">
                  </div>
                  <div class="col-6">
                    <label for="package_premium_photo"><?php echo $this->lang->line("Package Premium Photo");?> *</label>
                    <input type="file" name="package_premium_photo" class="form-control">
                  </div>
                </div>              
             </div>
             <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label for="description"> <?php echo $this->lang->line("Description")?> *</label>
                  <textarea id="textarea" name="description" value="<?php echo set_value('description');?>"  class="form-control" type="text"></textarea>
                  <span class="red"><?php echo form_error('description'); ?></span>
                </div>
              </div>
             </div>

             <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="premium_name"> <?php echo $this->lang->line("Premium Package Name")?> *</label>
                  <input name="premium_name" value="<?php echo set_value('premium_name');?>"  class="form-control" type="text">
                  <span class="red"><?php echo form_error('premium_name'); ?></span>
                </div>
              </div>
              <div class="col-6">
                <div class="form-group">
                  <label for="premium_price"><?php echo $this->lang->line("Premium Price")?> - <?php echo isset($payment_config[0]['currency']) ? $payment_config[0]['currency'] : 'USD'; ?> *</label>              
                  <input name="premium_price" value="<?php echo set_value('premium_price');?>"  class="form-control" type="number" min="0">
                  <span class="red"><?php echo form_error('premium_price'); ?></span>               
                </div>
              </div>
             </div>
             <div class="row">
              <div class="col-4">
                <div class="form-group">
                  <label for="premium_days"> <?php echo $this->lang->line("Premium Days")?> *</label>
                  <input name="premium_days" value="<?php echo set_value('premium_days');?>"  class="form-control" type="number" min="0">
                  <span class="red"><?php echo form_error('premium_days'); ?></span>
                </div>
              </div>
              <div class="col-4">
                <div class="form-group">
                  <label for="premium_users"> <?php echo $this->lang->line("Premium Users")?> *</label>
                  <input name="premium_users" value="<?php echo set_value('premium_users');?>"  class="form-control" type="number" min="0">
                  <span class="red"><?php echo form_error('premium_users'); ?></span>
                </div>
              </div>
              <div class="col-4">
                <div class="form-group">
                  <label for="premium_fastspring"> <?php echo $this->lang->line("Premium Fastspring")?> *</label>
                  <input name="premium_fastspring" value="<?php echo set_value('premium_fastspring');?>"  class="form-control" type="text">
                  <span class="red"><?php echo form_error('premium_fastspring'); ?></span>
                </div>
              </div>
            </div>    

             <div class="row">
               <div class="col-12 col-md-6">
                 <div class="form-group">
                   <label for="visible" ><i class="fas fa-hand-holding-usd"></i>  <?php echo $this->lang->line('Available to Purchase');?></label>
                     
                     <div class="form-group">
                       <?php 
                       $visible = set_value('visible');
                       if($visible == '') $visible='1';
                       ?>
                       <label class="custom-switch mt-2">
                         <input type="checkbox" name="visible" value="1" class="custom-switch-input"  <?php if($visible=='1') echo 'checked'; ?>>
                         <span class="custom-switch-indicator"></span>
                         <span class="custom-switch-description"><?php echo $this->lang->line('Yes');?></span>
                         <span class="red"><?php echo form_error('visible'); ?></span>
                       </label>
                     </div>
                 </div> 
               </div>

               <div class="col-12 col-md-6">
                 <div class="form-group" id="highlight_container">
                   <label for="highlight" ><i class="far fa-lightbulb"></i> <?php echo $this->lang->line('Highlighted Package');?></label>
                     
                     <div class="form-group">
                       <?php 
                       $highlight = set_value('highlight');
                       if($highlight == '') $highlight='0';
                       ?>
                       <label class="custom-switch mt-2">
                         <input type="checkbox" name="highlight" value="1" class="custom-switch-input"  <?php if($highlight=='1') echo 'checked'; ?>>
                         <span class="custom-switch-indicator"></span>
                         <span class="custom-switch-description"><?php echo $this->lang->line('Yes');?></span>
                         <span class="red"><?php echo form_error('highlight'); ?></span>
                       </label>
                     </div>
                 </div> 
               </div>
             </div>

             <div class="form-group">
               <label for=""><?php echo $this->lang->line("Modules")?> *</label>   
               <?php $mandatory_modules = array(65,199,200); ?>
               <div class="table-responsive">
                  <table class="table table-bordered">
                   <?php                  

                    echo "<tr>"; 
                        echo "<th class='info' width='20px'>"; 
                          echo $this->lang->line("#");         
                        echo "</th>";
                        echo "<th class='text-center info' width='20px'>"; 
                          echo '<input class="regular-checkbox" id="all_modules" type="checkbox"/><label for="all_modules"></label>';         
                        echo "</th>";                       
                        echo "<th class='info'>"; 
                          echo $this->lang->line("Module");         
                        echo "</th>";
                        echo "<th class='text-center info' colspan='2'>"; 
                          echo $this->lang->line("Usage Limit");         
                        echo "</th>";
                        echo "<th class='text-center info' colspan='2'>"; 
                          echo $this->lang->line("Bulk Limit");         
                        echo "</th>";
                     echo "</tr>"; 
                    
                    $SL=0;
                    foreach($modules as $module) 
                    {  
                     $SL++;
                     echo "<tr>"; 
                        echo "<td class='text-center'>".$SL."</td>";   
                        echo "<td class='text-center'>";?>
                           <input  name="modules[]" id="box<?php echo $SL;?>" class="modules regular-checkbox <?php if(in_array($module['id'], $mandatory_modules)) echo 'mandatory';?>" <?php if(in_array($module['id'], $mandatory_modules)) echo 'checked onclick="return false;"';?>  type="checkbox" value="<?php echo $module['id']; ?>"/> <?php

                            $style="style='cursor:pointer;'";
                            if(in_array($module['id'], $mandatory_modules)) $style = "style='border-color:#6777EF;cursor:pointer;' title='".$this->lang->line('This is a mandatory module and can not be unchecked.')."' data-toggle='tooltip'";

                           echo "<label for='box".$SL."' ".$style."></label>";                
                        echo "</td>";

                        echo "<td>".$module['module_name']."</td>";   

                        if($module["limit_enabled"]=='0')
                        {
                          $disabled=" readonly";
                          $limit=$this->lang->line("Unlimited");
                          $style='background:#ddd';
                        }
                        else
                        {
                            $disabled="";
                            $limit=$module['extra_text'];
                            $style='';
                        }


                        echo "<td align='center'>".$limit."</td><td align='center'><input type='number' ".$disabled." class='form-control' value='0' min='0' style='width:70px; ".$style."' name='monthly_".$module['id']."'></td>";
                      
                        if($module["bulk_limit_enabled"]=="0")
                        {
                          $disabled=" readonly";
                          $limit="";
                          $style='background:#ddd';

                        }
                        else
                        {
                            $disabled="";
                            $limit="";
                            $style='';
                        }
                        $xval=0;

                        echo "<td align='center'><input type='number' class='form-control' ".$disabled." value='".$xval."'  min='0' style='width:70px; ".$style."' name='bulk_".$module['id']."'></td>";
                      echo "</tr>";                 
                    }                
                    ?>            
                  </table> 
               </div>    
               <span class="red" ><?php echo "<br/><br/>".form_error('modules[]'); ?></span>
             </div>    
          </div>
          <div class="card-footer bg-whitesmoke">
            <button name="submit" type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> <?php echo $this->lang->line("Save");?></button>
            <button  type="button" class="btn btn-secondary btn-lg float-right" onclick='goBack("payment/package_manager",0)'><i class="fa fa-remove"></i> <?php echo $this->lang->line("Cancel");?></button>
          </div>
        </div>
      </form>  
    </div>
  </div>
</section>

<script type="text/javascript">
  $(document).ready(function() {
    $("#all_modules").change(function(){
      if ($(this).is(':checked')) 
      $(".modules:not(.mandatory)").prop("checked",true);
      else
      $(".modules:not(.mandatory)").prop("checked",false);
    });
  });
</script>



  <script type="text/javascript"
    src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.js"></script>
  <script type="text/javascript"
    src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/mode/xml/xml.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/froala_editor.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/align.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/code_beautifier.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/code_view.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/draggable.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/image.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/image_manager.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/link.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/lists.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/paragraph_format.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/paragraph_style.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/table.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/video.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/url.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url() ?>assets/js/plugins/entities.min.js"></script>

  <script>
    (function () {
      const editorInstance = new FroalaEditor('#textarea', {
        enter: FroalaEditor.ENTER_P,
        placeholderText: null,
        events: {
          initialized: function () {
            const editor = this
            this.el.closest('form').addEventListener('submit', function (e) {
              console.log(editor.$oel.val())
              e.preventDefault()
            })
          }
        }
      })
    })()
  </script>
