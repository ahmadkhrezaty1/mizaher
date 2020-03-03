<section class="section section_custom pt-1">
  <div class="section-header d-none">
    <h1><i class="fas fa-shopping-cart"></i> <?php echo $page_title;?></h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="<?php echo base_url('messenger_bot'); ?>"><?php echo $this->lang->line("Messenger Bot"); ?></a></div>
      <div class="breadcrumb-item"><a href="<?php echo base_url('ecommerce'); ?>"><?php echo $this->lang->line("E-commerce"); ?></a></div>
      <div class="breadcrumb-item"><?php echo $this->lang->line("Orders"); ?></div>
    </div>
  </div>

  <?php $this->load->view('admin/theme/message'); ?>

  <style type="text/css">
    @media (max-width: 575.98px) {
      #search_store_id{width: 75px;}
      #search_status{width: 80px;}
      #select2-search_store_id-container,#select2-search_status-container,#search_value{padding-left: 8px;padding-right: 5px;}
    }
  </style>

  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card no_shadow">
          <div class="card-body data-card p-0 pr-3">
            <div class="row">
              <div class="col-12 col-md-9">
                <?php
                
                $status_list[''] = $this->lang->line("Status");                
                echo 
                '<div class="input-group mb-3" id="searchbox">
                  <div class="input-group-prepend d-none">
                    '.form_dropdown('search_store_id',$store_list,$this->session->userdata("ecommerce_selected_store"),'class="form-control select2" id="search_store_id"').'
                  </div>
                  <div class="input-group-prepend">
                    '.form_dropdown('search_status',$status_list,'','class="form-control select2" id="search_status"').'
                  </div>
                  <input type="text" class="form-control" id="search_value" autofocus name="search_value" placeholder="'.$this->lang->line("Search...").'" style="max-width:300px;">
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="button" id="search_action"><i class="fas fa-search"></i> <span class="d-none d-sm-inline">'.$this->lang->line("Search").'</span></button>
                  </div>
                </div>'; ?>                                          
              </div>

              <div class="col-12 col-md-3">

            	<?php
			          echo $drop_menu ='<a href="javascript:;" id="search_date_range" class="btn btn-outline-primary btn-lg float-right icon-left btn-icon"><i class="fas fa-calendar"></i> '.$this->lang->line("Choose Date").'</a><input type="hidden" id="search_date_range_val">';
			        ?>

                                         
              </div>
            </div>

            <div class="table-responsive2">
                <input type="hidden" id="put_page_id">
                <table class="table table-bordered" id="mytable">
                  <thead>
                    <tr>
                      <th>#</th>      
                      <th style="vertical-align:middle;width:20px">
                          <input class="regular-checkbox" id="datatableSelectAllRows" type="checkbox"/><label for="datatableSelectAllRows"></label>        
                      </th>
                      <th><?php echo $this->lang->line("Subscriber ID")?></th>              
                      <th><?php echo $this->lang->line("Store")?></th>              
                      <th><?php echo $this->lang->line("Status")?></th>              
                      <th><?php echo $this->lang->line("Coupon")?></th>                   
                      <th><?php echo $this->lang->line("Amount")?></th>                   
                      <th><?php echo $this->lang->line("Method")?></th>                   
                      <th><?php echo $this->lang->line("Transaction ID")?></th>                   
                      <th><?php echo $this->lang->line("Invoice")?></th>                   
                      <th><?php echo $this->lang->line("Manual Payment")?></th>                                      
                      <th><?php echo $this->lang->line("Ordered at")?></th>                   
                      <th><?php echo $this->lang->line("Paid at")?></th>                  
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



<script>

	var base_url="<?php echo site_url(); ?>";
	
	$('#search_date_range').daterangepicker({
	  ranges: {
	    '<?php echo $this->lang->line("Last 30 Days");?>': [moment().subtract(29, 'days'), moment()],
	    '<?php echo $this->lang->line("This Month");?>'  : [moment().startOf('month'), moment().endOf('month')],
	    '<?php echo $this->lang->line("Last Month");?>'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	  },
	  startDate: moment().subtract(29, 'days'),
	  endDate  : moment()
	}, function (start, end) {
	  $('#search_date_range_val').val(start.format('YYYY-M-D') + '|' + end.format('YYYY-M-D')).change();
	});

	var perscroll;
	var table1 = '';
	table1 = $("#mytable").DataTable({
	  serverSide: true,
	  processing:true,
	  bFilter: false,
	  order: [[ 11, "desc" ]],
	  pageLength: 10,
	  ajax: {
	      url: base_url+'ecommerce/order_list_data',
	      type: 'POST',
	      data: function ( d )
	      {
	          d.search_store_id = $('#search_store_id').val();
            d.search_status = $('#search_status').val();
	          d.search_value = $('#search_value').val();
	          d.search_date_range = $('#search_date_range_val').val();
	      }
	  },
	  language: 
	  {
	    url: "<?php echo base_url('assets/modules/datatables/language/'.$this->language.'.json'); ?>"
	  },
	  dom: '<"top"f>rt<"bottom"lip><"clear">',
	  columnDefs: [
	    {
	        targets: [1],
	        visible: false
	    },
	    {
	        targets: [2,4,8,9,10,11,12],
	        className: 'text-center'
	    },
      {
          targets: [5,6],
          className: 'text-right'
      },
	    {
	        targets: [4,9,10],
	        sortable: false
	    }
	  ],
	  fnInitComplete:function(){  // when initialization is completed then apply scroll plugin
	         if(areWeUsingScroll)
	         {
	           if (perscroll) perscroll.destroy();
	           perscroll = new PerfectScrollbar('#mytable_wrapper .dataTables_scrollBody');
	         }
	     },
	     scrollX: 'auto',
	     fnDrawCallback: function( oSettings ) { //on paginition page 2,3.. often scroll shown, so reset it and assign it again 
	         if(areWeUsingScroll)
	         { 
	           if (perscroll) perscroll.destroy();
	           perscroll = new PerfectScrollbar('#mytable_wrapper .dataTables_scrollBody');
	         }
	     }
	});


	$("document").ready(function(){	   

	    $(document).on('change', '#search_store_id', function(e) {
	        table1.draw();
	    });

      $(document).on('change', '#search_status', function(e) {
          table1.draw();
      });

	    $(document).on('change', '#search_date_range_val', function(e) {
        	e.preventDefault();
        	table1.draw();
      });

    	$(document).on('keypress', '#search_value', function(e) {
      	if(e.which == 13) $("#search_action").click();
    	});

    	$(document).on('click', '#search_action', function(event) {
      	event.preventDefault(); 
      	table1.draw();
    	});

    	var Doyouwanttodeletethisrecordfromdatabase = "<?php echo $this->lang->line('Do you really want to change this order status?'); ?>";
    	$(document).on('change','.payment_status',function(e){
          var table_id = $(this).attr('data-id');
          var payment_status = $(this).val();
    	    swal({
    	        title: '<?php echo $this->lang->line("Change Order Status"); ?>',
    	        text: Doyouwanttodeletethisrecordfromdatabase,
    	        icon: 'warning',
    	        buttons: true,
    	        dangerMode: true,
    	    })
    	    .then((willDelete) => {
    	        if (willDelete) 
    	        {
    	            $.ajax({
    	                context: this,
    	                type:'POST' ,
    	                dataType:'JSON',
    	                url:"<?php echo base_url('ecommerce/change_payment_status')?>",
    	                data:{table_id:table_id,payment_status:payment_status},
    	                success:function(response)
    	                { 
    	                    if(response.status == '1')
    	                    {
    	                      iziToast.success({title: '',message: response.message,position: 'bottomRight',timeout: 3000});
    	                    }
                          else
    	                    {
    	                      iziToast.error({title: '',message: response.message,position: 'bottomRight',timeout: 3000});
    	                    }
    	                    table1.draw();
    	                }
    	            });
    	        } 
    	    });
    	});

      $(document).on('click', '#mp-download-file', function(e) {
        e.preventDefault();

        // Makes reference 
        var that = this;

        // Starts spinner
        $(that).removeClass('btn-outline-info');
        $(that).addClass('btn-info disabled btn-progress');

        // Grabs ID
        var file = $(this).data('id');

        // Requests for file
        $.ajax({
          type: 'POST',
          data: { file },
          dataType: 'JSON',
          url: '<?php echo base_url('ecommerce/manual_payment_download_file') ?>',
          success: function(res) {
            // Stops spinner
            $(that).removeClass('btn-info disabled btn-progress');
            $(that).addClass('btn-outline-info');

            // Shows error if something goes wrong
            if (res.error) {
              swal({
                icon: 'error',
                text: res.error,
                title: '<?php echo $this->lang->line('Error!'); ?>',
              });
              return;
            }

            // If everything goes well, requests for downloading the file
            if (res.status && 'ok' === res.status) {
              window.location = '<?php echo base_url('ecommerce/manual_payment_download_file'); ?>';
            }
          },
          error: function(xhr, status, error) {
            // Stops spinner
            $(that).removeClass('btn-info disabled btn-progress');
            $(that).addClass('btn-outline-info');

            // Shows internal errors
            swal({
              icon: 'error',
              text: error,
              title: '<?php echo $this->lang->line('Error!'); ?>',
            });
          }
        });
      });


      $(document).on('click', '.additional_info', function() { 
        $(this).addClass('btn-progress');       
        var cart_id = $(this).attr('data-id');
        $.ajax({
            context: this,
            type:'POST' ,
            url:"<?php echo base_url('ecommerce/addtional_info_modal_content')?>",
            data:{cart_id:cart_id},
            success:function(response)
            { 
              $('.additional_info').removeClass('btn-progress'); 
              $('#manual-payment-modal .modal-body').html(response);
              $('#manual-payment-modal').modal();
            }
        });
      });


	});

</script>




<div class="modal fade" tabindex="-1" role="dialog" id="manual-payment-modal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-invoice-dollar"></i> <?php echo $this->lang->line("Manual Payment Information");?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer bg-whitesmoke br"> 
        <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal"><i class="fa fa-remove"></i> <?php echo $this->lang->line("Close"); ?></button>
      </div>
    </div>
  </div>
</div>
