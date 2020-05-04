<section class="section section_custom">
  <div class="section-header">
    <h1><i class="fas fa-edit"></i> <?php echo $page_title; ?></h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><?php echo $this->lang->line("Subscription"); ?></div>
      <div class="breadcrumb-item active"><a href="<?php echo base_url('payment/package_manager'); ?>"><?php echo $this->lang->line("Package Manager"); ?></a></div>
      <div class="breadcrumb-item"><?php echo $page_title; ?></div>
    </div>
  </div>

  <?php $this->load->view('admin/theme/message'); ?>

  <div class="row">
    <div class="col-12">

      <?php echo form_open_multipart(site_url('payment/edit_user_package_action'),array('id'=>'form_transout', 'class' => 'form-horizontal')); ?>
        
        <input name="id" value="<?php echo $id;?>"  class="form-control" type="hidden">              
        
        <div class="card">
       
          <div class="card-body">
             
             

             <div class="form-group">
               <label for=""><?php echo $this->lang->line("Modules")?> *</label>   
                <?php $mandatory_modules = array(65,199,200); ?>
               <div class="table-responsive">
                  <table class="table table-bordered">
                   <?php                  
                    $current_modules=array();
                    $current_modules=explode(',',$value[0]["module_ids"]); 
                    $monthly_limit=json_decode($value[0]["monthly_limit"],true);
                    $bulk_limit=json_decode($value[0]["bulk_limit"],true);

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
                        echo "<td class='text-center'>";
                        $check_module = '';
                        if(is_array($current_modules) && in_array($module['id'], $current_modules)) $check_module='checked';  ?>
                          <input  name="modules[]" id="box<?php echo $SL;?>" class="modules regular-checkbox <?php if(in_array($module['id'], $mandatory_modules)) echo 'mandatory';?>" <?php echo $check_module; ?> <?php if(in_array($module['id'], $mandatory_modules)) echo 'checked onclick="return false;"';?> type="checkbox" value="<?php echo $module['id']; ?>"/> <?php

                          $style="style='cursor:pointer;'";
                          if(in_array($module['id'], $mandatory_modules)) $style = "style='border-color:#6777EF;cursor:pointer;' title='".$this->lang->line('This is a mandatory module and can not be unchecked.')."' data-toggle='tooltip'";

                           echo "<label for='box".$SL."' ".$style."></label>";                
                        echo "</td>";

                        echo "<td>".$module['module_name']."</td>"; 

                        $xmonthly_val=0;
                        $xbulk_val=0;
                     
                        if(in_array($module["id"],$current_modules))
                        {
                          $xmonthly_val=$monthly_limit[$module["id"]];
                          $xbulk_val=$bulk_limit[$module["id"]];
                        }  

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


                        echo "<td align='center'>".$limit."</td><td align='center'><input type='number' ".$disabled." class='form-control' value='".$xmonthly_val."' min='0' style='width:70px; ".$style."' name='monthly_".$module['id']."'></td>";
                      
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
                        $xval=$xbulk_val;

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
            <button type="button" class="btn btn-warning btn-lg" onclick='goBack("payment/default_user_package/<?php echo $id ?>",0)'><i class="fa fa-remove"></i> <?php echo $this->lang->line("Default");?></button>
            <button  type="button" class="btn btn-secondary btn-lg float-right" onclick='goBack("admin/user_manager",0)'><i class="fa fa-remove"></i> <?php echo $this->lang->line("Cancel");?></button>
          </div>
        </div>
      </form>  
    </div>
  </div>
</section>

          


<script type="text/javascript">
  $(document).ready(function() {
    if($("#price_default").val()=="0") $("#hidden").hide();
    else $("#validity").show();
    $("#all_modules").change(function(){
      if ($(this).is(':checked')) 
      $(".modules:not(.mandatory)").prop("checked",true);
      else
      $(".modules:not(.mandatory)").prop("checked",false);
    });
    $("#price_default").change(function(){
      if($(this).val()=="0") $("#hidden").hide();
      else $("#hidden").show();
    });
  });
</script>

<style type="text/css" media="screen">
  table label{margin-top: 10px;}
</style>