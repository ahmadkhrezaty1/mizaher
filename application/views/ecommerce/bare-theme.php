<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
		<title><?php echo isset($page_title) ? $page_title : $this->config->item('product_name');?></title>
		<link rel="shortcut icon" href="<?php echo (isset($favicon) && $favicon!="") ? $favicon : base_url('assets/img/favicon.png');?>">

		<!-- General CSS Files -->
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/fontawesome/css/all.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/fontawesome/css/v4-shims.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/chocolat/dist/css/chocolat.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/dropzonejs/dropzone.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/bootstrap-social/bootstrap-social.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/bootstrap-daterangepicker/daterangepicker.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/select2/dist/css/select2.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/jquery-selectric/selectric.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/bootstrap-timepicker/css/bootstrap-timepicker.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/datatables/datatables.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/datatables/Select-1.2.4/css/select.bootstrap4.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/ionicons/css/ionicons.min.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/modules/izitoast/css/iziToast.min.css">

		<!-- Template CSS -->
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/style.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/components.css">

		<!-- SlimScroll -->
		<link href="<?php echo base_url();?>plugins/perfect-scrollbar-1.4.0/css/perfect-scrollbar.css" rel="stylesheet">

		<!--Jquey Date Time Picker -->
		<link href="<?php echo base_url();?>plugins/datetimepickerjquery/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />

		<!--Emoji CSS-->
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>plugins/emoji/dist/emojionearea.min.css" media="screen">

		<!-- Custom -->
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css">


		<script type="text/javascript">
		  <?php 
		  if($this->session->userdata("is_mobile")=='1') echo 'var areWeUsingScroll = false;';
		  else echo 'var areWeUsingScroll = true;';
		  ;?>
		</script>

		<!-- General JS Scripts -->
		<script src="<?php echo base_url(); ?>assets/modules/jquery.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/popper.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/tooltip.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/bootstrap/js/bootstrap.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/moment.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/js/stisla.js"></script>

		<!-- JS Libraies -->
		<script src="<?php echo base_url(); ?>assets/modules/dropzonejs/min/dropzone.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/bootstrap-daterangepicker/daterangepicker.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/select2/dist/js/select2.full.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/jquery-selectric/jquery.selectric.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/datatables/datatables.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/datatables/Select-1.2.4/js/dataTables.select.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/sweetalert/sweetalert.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/izitoast/js/iziToast.min.js"></script>

		<!-- Slimscroll -->
		<script src="<?php echo base_url();?>plugins/perfect-scrollbar-1.4.0/dist/perfect-scrollbar.js"></script>

		<!--Jquery Date Time Picker -->
		<script type="text/javascript" src="<?php echo base_url();?>plugins/datetimepickerjquery/jquery.datetimepicker.js"></script>

		<!-- Emoji Library-->
		<script src="<?php echo base_url();?>plugins/emoji/dist/emojionearea.js" type="text/javascript"></script>

		<!-- Template JS File -->
		<script src="<?php echo base_url(); ?>assets/js/scripts.js"></script>
		<script src="<?php echo base_url(); ?>assets/modules/chocolat/dist/js/jquery.chocolat.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/js/custom.js"></script>

		<!-- Load Facebook Messenger SDK -->
		
		<script  type="text/javascript">



			var PSID; 

			(function(d, s, id){
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) {return;}
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/messenger.Extensions.js";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'Messenger'));


			window.extAsyncInit = function() {
				
				MessengerExtensions.getContext('<?php echo $fb_app_id; ?>', 
				  function success(thread_context){
				  	 PSID=thread_context.psid;
				  },
				  function error(err){
				   	console.log(err);
				  }
				);

			};



			
		</script>

	</head>

	<body>
	  <div id="app">
	    <div class="main-wrapper">
			<div class="container" style="margin-top: 30px">
				<?php 
					if(isset($body)) $this->load->view($body);
					else echo $output;
				?>
			</div>
		</div>
	  </div>
	</body>
</html>
