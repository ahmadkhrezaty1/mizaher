<?php include("application/views/sms_email_manager/email/email_section_global_js.php"); ?>

<section class="section section_custom">
    <div class="section-header">
        <h1><i class="fas fa-history"></i> <?php echo $page_title; ?></h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="<?php echo base_url("messenger_bot_broadcast"); ?>"><?php echo $this->lang->line("SMS/Email Broadcasting"); ?></a></div>
            <div class="breadcrumb-item"><?php echo $page_title; ?></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body data-card">
                        <div class="row">
                            <div class="col-12">
                                <a href="javascript:;" id="email_log_date_range" class="btn btn-primary btn-lg icon-left btn-icon float-right"><i class="fas fa-calendar"></i> <?php echo $this->lang->line("Choose Date");?></a><input type="hidden" id="email_log_date_range_val">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive2">
                                    <table class="table table-bordered" id="mytable_email_logs">
                                        <thead>
                                            <tr>
                                                <th><?php echo $this->lang->line('#'); ?></th>
                                                <th><?php echo $this->lang->line('id'); ?></th>
                                                <th><?php echo $this->lang->line("Email API"); ?></th>
                                                <th><?php echo $this->lang->line("Send To"); ?></th>
                                                <th><?php echo $this->lang->line("Details"); ?></th>
                                                <th><?php echo $this->lang->line('Sent At'); ?></th>
                                                <th><?php echo $this->lang->line("Response"); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>            
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<div class="modal fade" id="see_email_message">
    <div class="modal-dialog" style="min-width:50%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title section-title"><i class="far fa-envelope"></i> <?php echo $this->lang->line("Email Details") ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" id="message_body"></div>
        </div>
    </div>
</div>
<style>
    #outer{text-align:justify;width:100% !important;} 
    .wizard-steps .wizard-step:before{content:none;}
    .wizard-steps .wizard-step{padding: 30px 35px;margin: 0 20px;}
    .wizard-step{border:1px dotted #eaeaea !important;padding:30px;}
    .wizard-steps {
        display: flex;
        margin: 0 -10px;
        counter-reset: wizard-counter;
    }

    .image {
      display: block;
      width: 100%;
      height: auto;
    }

    .overlay {
      position: absolute;
      bottom: 100%;
      left: 0;
      right: 0;
      background-color: #f5f5f5;
      overflow: hidden;
      width: 70%;
      margin-left: 40px;
      height:0;
      transition: .5s ease;
      cursor: pointer;
    }

    .container:hover .overlay {
        bottom: 20px;
        height: 88%;
    }

    .text {
      /*color: white;*/
      font-size: 20px;
      position: absolute;
      top: 50%;
      left: 50%;
      -webkit-transform: translate(-50%, -50%);
      -ms-transform: translate(-50%, -50%);
      transform: translate(-50%, -50%);
      text-align: center;
    }
</style>