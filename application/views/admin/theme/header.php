<?php if ($this->config->item('header_sticky_alert')!="") : ?>
        <?php if($this->session->userdata('close_offer')!='yes' && $this->uri->segment(1)!='admin' && $this->uri->segment(1)!='user' && $this->uri->segment(1)!='cart') : ?>
        <div class="alert alert-danger text-center header_sticky_alert" role="alert" style="direction: rtl;font-size: 16px;text-align: right !important;">
            <?php echo $header_sticky_alert =  str_replace(array("#PRODUCT_PERCENT#","#BUNDLE_PERCENT#"), array($this->config->item("product_offer_percent"),$this->config->item("bundle_offer_percent")), htmlspecialchars_decode($this->config->item('header_sticky_alert'))); ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>    
    <script>

        $(document.body).on('click', '.closeoffer', function(event) {
            event.preventDefault();
            $(".header_sticky_alert").hide();
            var offerurl = "<?php echo base_url('home/close_offer');?>";            
            $.ajax({
                   type:'POST' ,
                   url: offerurl,    
                   success:function(response)
                   { 
                   }
                });
        });



</script>

<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
  <form class="form-inline mr-auto">
    <ul class="navbar-nav mr-3">
      <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg" id="collapse_me_plz"><i class="fas fa-bars"></i></a></li>
    </ul>
  </form>
  <ul class="navbar-nav navbar-right">

    <?php include(FCPATH.'application/views/admin/theme/notification.php'); ?>
    <?php include(FCPATH.'application/views/admin/theme/usermenu.php'); ?>  
    
  </ul>
</nav>
