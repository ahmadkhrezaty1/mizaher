<?php

require_once("Home.php"); // including home controller

/**
* class admin_config
* @category controller
*/
class Member extends Home
{
    /**
    * load constructor method
    * @access public
    * @return void
    */
    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('logged_in')!= 1) {
            redirect('home/login_page', 'location');
        }
   }

    /**
    * load index method. redirect to config
    * @access public
    * @return void
    */
    public function index()
    {
        $this->edit_profile();
    }

    public function user_added_email_to_admin($data){
        if(!is_manager()){
            redirect('home/access_forbidden', 'location');
        }
        $user_name = $data['name'];
        $limit = '-';
        $package_name = get_package_name($data['package_id']);
        $expired_date = $data['expired_date'];
        $manager_name = $this->session->userdata('username');
        $subject = $manager_name." | Added New User [ Username : ". $user_name ." ]";
        $details_url = site_url()."admin/user_manager/";
        $message = "<p>Package Name: ".$package_name." Expiry Date:".$expired_date." ".$this->lang->line("To show all users you can go to<br>")."<a href='".$details_url."'>All Users</a>";
        if(get_limit()){
            $message .= '<br>And This Manager Can Added '.get_limit().' Users';
            $limit = get_limit();
        }
        $this->basic->insert_data('manager_logs',[
            'user_name' => $user_name,
            'manager_name' => $manager_name,
            'package_name' => $package_name,
            'expired_date' => $expired_date,
            'limit_users' => $limit,
            'method' => 'Add'
        ]);
        $message .= "</p>";
        $from = $this->session->userdata("user_login_email");
        $mask = $this->config->item("product_name");
        $html = 1;

        foreach(get_admins_email() as $admin){
            $to = $admin->email;
            $this->_mail_sender($from, $to, $subject, $message, $mask, $html);
        }

        $this->session->set_userdata('reg_success',1);
    }

    public function user_edited_email_to_admin($data){
        if(!is_manager()){
            redirect('home/access_forbidden', 'location');
        }
        $user_name = $data['name'];
        $limit = '-';
        $package_name = get_package_name($data['package_id']);
        $expired_date = $data['expired_date'];
        $manager_name = $this->session->userdata('username');
        $subject = $manager_name." | Edited User [ Username : ". $user_name ." ]";
        $details_url = site_url()."admin/user_manager/";
        $message = "<p>Package Name: ".$package_name." Expiry Date:".$expired_date." ".$this->lang->line("To show all users you can go to<br>")."<a href='".$details_url."'>All Users</a>";
        if(get_limit()){
            $message .= '<br>And This Manager Can Added '.get_limit().' Users';
            $limit = get_limit();
        }
        $this->basic->insert_data('manager_logs',[
            'user_name' => $user_name,
            'manager_name' => $manager_name,
            'package_name' => $package_name,
            'expired_date' => $expired_date,
            'limit_users' => $limit,
            'method' => 'Update'
        ]);
        $message .= "</p>";
        $from = $this->session->userdata("user_login_email");
        $mask = $this->config->item("product_name");
        $html = 1;

        foreach(get_admins_email() as $admin){
            $to = $admin->email;
            $this->_mail_sender($from, $to, $subject, $message, $mask, $html);
        }

        $this->session->set_userdata('reg_success',1);
    }

    function _mail_sender($from = '', $to = '', $subject = '', $message = '', $mask = "", $html = 1, $smtp = 1,$attachement="",$test_mail="")
    {
        if ($to!= '' && $subject!='' && $message!= '')
        {
            if($this->config->item('email_sending_option') == '') $email_sending_option = 'smtp';
            else $email_sending_option = $this->config->item('email_sending_option');

            if($test_mail == 1) $email_sending_option = 'smtp';

            $message=$message."<br/><br/>".$this->lang->line("The email was sent by"). ": ".$from;

            if($email_sending_option == 'smtp')
            {
                if ($smtp == '1') {
                    $where2 = array("where" => array('status' => '1','deleted' => '0'));
                    $email_config_details = $this->basic->get_data("email_config", $where2, $select = '', $join = '', $limit = '', $start = '', $group_by = '', $num_rows = 0);

                    if (count($email_config_details) == 0) {
                        $this->load->library('email');
                    } else {
                        foreach ($email_config_details as $send_info) {
                            $send_email = trim($send_info['email_address']);
                            $smtp_host = trim($send_info['smtp_host']);
                            $smtp_port = trim($send_info['smtp_port']);
                            $smtp_user = trim($send_info['smtp_user']);
                            $smtp_password = trim($send_info['smtp_password']);
                            $smtp_type = trim($send_info['smtp_type']);
                        }

                    /*****Email Sending Code ******/
                    $config = array(
                      'protocol' => 'smtp',
                      'smtp_host' => "{$smtp_host}",
                      'smtp_port' => "{$smtp_port}",
                      'smtp_user' => "{$smtp_user}", // change it to yours
                      'smtp_pass' => "{$smtp_password}", // change it to yours
                      'mailtype' => 'html',
                      'charset' => 'utf-8',
                      'newline' =>  "\r\n",
                      'set_crlf'=>"\r\n",
                      'smtp_timeout' => '30'
                     );
                    if($smtp_type != 'Default')
                        $config['smtp_crypto'] = $smtp_type;

                        $this->load->library('email', $config);
                    }
                } /*** End of If Smtp== 1 **/

                if (isset($send_email) && $send_email!= "") {
                    $from = $send_email;
                }
                $this->email->from($from, $mask);
                $this->email->to($to);
                $this->email->subject($subject);
                $this->email->message($message);
                if ($html == 1) {
                    $this->email->set_mailtype('html');
                }
                if ($attachement!="") {
                    $this->email->attach($attachement);
                }

                if ($this->email->send()) {
                    return true;
                } else {

                    if($test_mail==1) {
                        return $this->email->print_debugger();
                    } else {
                        return false;
                    }
                }                
            }

            if($email_sending_option == 'php_mail')
            {
                $from = get_domain_only(base_url());
                $from = "support@".$from;
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers .= "From: {$from}" . "\r\n";
                if(mail($to, $subject, $message, $headers))
                    return true;
                else
                    return false;
            }



        } else {
            return false;
        }
    }
 
    public function edit_profile()
    {      
        $data['body'] = "member/edit_profile";
        $data['page_title'] = $this->lang->line('user');
        $join = array('package'=>"users.package_id=package.id,left");
        $data["profile_info"]=$this->basic->get_data("users",array("where"=>array("users.id"=>$this->session->userdata("user_id"))),"users.*,package_name",$join);
        $data["time_zone_list"] = $this->_time_zone_list();
        $this->_viewcontroller($data);
    }

    public function user_manager()
    {
        if(!is_manager()){
            redirect('home/access_forbidden', 'location');
        }
        $data['body']='manager/user/user_list';
        $data['page_title']=$this->lang->line("User Manager");
        $this->_viewcontroller($data);  
    }

    public function user_manager_data()
    {           
        if(!is_manager()){
            redirect('home/access_forbidden', 'location');
        }
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'user_id','avatar','name', 'email','package_name', 'status', 'user_type','expired_date', 'actions', 'add_date','last_login_at','last_login_ip');
        $search_columns = array('name', 'email','mobile','add_date','expired_date','last_login_ip');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'user_id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where = array();
        if ($search_value != '') 
        {
            $or_where = array();
            foreach ($search_columns as $key => $value) 
            $or_where[$value.' LIKE '] = "%$search_value%";
            $where = array('or_where' => $or_where);
        }
            
        $table="users";
        $join = array('package'=>"package.id=users.package_id,left");
        $select= array("users.*","users.id as user_id","package.package_name");
        $info=$this->basic->get_manager_users($table,$where,$select,$join,$limit,$start,$order_by,$group_by='');
        $total_rows_array=$this->basic->count_row($table,$where,$count=$table.".id",$join,$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            $status = $info[$i]["status"];
            if($status=='1') $info[$i]["status"] = "<i title ='".$this->lang->line('Active')."'class='status-icon fas fa-toggle-on text-primary'></i>";
            else $info[$i]["status"] = "<i title ='".$this->lang->line('Inactive')."'class='status-icon fas fa-toggle-off gray'></i>";

            $last_login_at = $info[$i]["last_login_at"];
            if($last_login_at=='0000-00-00 00:00:00') $info[$i]["last_login_at"] = $this->lang->line("Never");
            else $info[$i]["last_login_at"] = date("jS M y H:i",strtotime($info[$i]["last_login_at"]));

            $expired_date = $info[$i]["expired_date"];
            if($expired_date=='0000-00-00 00:00:00' || $info[$i]["user_type"]=="Admin") $info[$i]["expired_date"] = "-";
            else $info[$i]["expired_date"] = date("jS M y",strtotime($info[$i]["expired_date"]));

            $info[$i]["add_date"] = date("jS M y",strtotime($info[$i]["add_date"]));

            if($info[$i]["package_name"]=="") $info[$i]["package_name"] = "-";
  
            $user_name = $info[$i]["name"];
            $user_id = $info[$i]["id"];
            $str="";   
            
            $str=$str."<a class='btn btn-circle btn-outline-warning' data-toggle='tooltip' title='".$this->lang->line('Edit')."' href='".$base_url.'member/edit_user/'.$info[$i]["user_id"]."'>".'<i class="fas fa-edit"></i>'."</a>";
             
            if($this->session->userdata('license_type') == 'double')
            $info[$i]["actions"] = "<div style='min-width:208px'>".$str."</div>";
            else $info[$i]["actions"] = "<div style='min-width:161px'>".$str."</div>";
            $info[$i]["actions"] .= "<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";;

            $logo=$info[$i]["brand_logo"];

            if($logo=="") $logo=base_url("assets/img/avatar/avatar-1.png");
            else $logo=base_url().'member/'.$logo;

            $info[$i]["avatar"] = "<img src='".$logo."' width='40px' height='40px' class='rounded-circle'>";

            if($info[$i]['user_type']=='Admin') $tie="-circle orange";
            else $tie="-noicon blue";

            $info[$i]['name'] = "<span data-toggle='tooltip' title='".$this->lang->line($info[$i]['user_type'])."'><i class='fas fa-user".$tie." text-warning'></i> ".$info[$i]['name']." </span><script> $('[data-toggle=\"tooltip\"]').tooltip();</script>";
                
            if($this->is_demo=='1')  $info[$i]["email"] ="******@*****.***";
            if($this->is_demo=='1')  $info[$i]["last_login_ip"] ="XXXXXXXXX";

            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="user_id");

        echo json_encode($data);
    }

    public function edit_user($id=0)
    {   
        if(!is_manager()){
            redirect('home/access_forbidden', 'location');
        }    
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if(is_admin($id)){
            redirect('member/user_manager');
        }
        $data['body']='manager/user/edit_user';     
        $data['page_title']=$this->lang->line('Edit User');     
        $packages=$this->basic->get_data('package',$where='',$select='',$join='',$limit='',$start='',$order_by='package_name asc');
        $xdata=$this->basic->get_data('users',array("where"=>array("id"=>$id)));
        if(!isset($xdata[0])) exit();

        $visible_packages = array();
        foreach ($packages as $package){
            if($package['visible'] == '1')
                $visible_packages[] = $package;
        }
        $data['packages'] = format_data_dropdown($visible_packages,"id","package_name",false);
        $data['xdata'] = $xdata[0];
        $this->_viewcontroller($data);
    }


    public function edit_user_action() 
    {
        if(!is_manager()){
            redirect('home/access_forbidden', 'location');
        }
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if($_SERVER['REQUEST_METHOD'] === 'GET') 
        redirect('home/access_forbidden','location');

        if($_POST)
        {
            $id = $this->input->post('id');
            $this->form_validation->set_rules('name', '<b>'.$this->lang->line("Full Name").'</b>', 'trim');
            $unique_email = "users.email.".$id; 
            $this->form_validation->set_rules('email', '<b>'.$this->lang->line("Email").'</b>', "trim|required|valid_email|is_unique[$unique_email]");      
            $this->form_validation->set_rules('mobile', '<b>'.$this->lang->line("Mobile").'</b>', 'trim');            
            $this->form_validation->set_rules('address', '<b>'.$this->lang->line("Address").'</b>', 'trim');      
            $this->form_validation->set_rules('user_type', '<b>'.$this->lang->line("User Type").'</b>', 'trim|required');      
            $this->form_validation->set_rules('status', '<b>'.$this->lang->line("Status").'</b>', 'trim');

            if($this->input->post("user_type")=="Member")     
            {
                if(!is_manager_3()){
                    $this->form_validation->set_rules('package_id', '<b>'.$this->lang->line("Package").'</b>', 'trim|required');      
                }
                if(!is_manager_2() and !is_manager_3()){
                    $this->form_validation->set_rules('expired_date', '<b>'.$this->lang->line("Expiry Date").'</b>', 'trim|required');
                }
            }
                
            if ($this->form_validation->run() == FALSE)
            {
                $this->edit_user($id); 
            }
            else
            {               
                $name=$this->input->post('name');
                $email=$this->input->post('email');
                $mobile=$this->input->post('mobile');                
                $address=$this->input->post('address');
                $user_type=$this->input->post('user_type');
                $manager_type=$this->input->post('manager_type');
                $status=$this->input->post('status');
                //$package_id=get_expired_date($id);get_package_id
                if(is_manager_2()){
                    //$package_id=    get_package_id($id);
                    $package_id=$this->input->post('package_id');
                    $expired_date = get_expired_date();
                }elseif(is_manager_3()){
                    //$package_id=    get_package_id($id);
                    $package_id=get_package_id($this->session->userdata('user_id'));
                    $days = get_package($package_id)->premium_days;
                    $expired_date = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s"). ' + '.$days.' days'));
                }
                else{
                    $package_id=$this->input->post('package_id');
                    $expired_date=$this->input->post('expired_date');
                }
                if($status=='') $status='0';
                if($manager_type == null) $manager_type ='';
                if($user_type == 'Admin') redirect('home/access_forbidden','location');
                                                       
                $data=array
                (
                    'name'=>$name,
                    'email'=>$email,
                    'mobile'=>$mobile,
                    'address'=>$address,
                    'user_type'=>$user_type,
                    'manager_type'=>$manager_type,
                    'status'=>$status
                );
                if($user_type=='Member')
                {
                    $data["package_id"] = $package_id;
                    $data["expired_date"] = $expired_date;
                }
                else
                {
                    $data["package_id"] = 0;
                    $data["expired_date"] = '';
                }
                
                $current_date = strtotime(date("Y-m-d"));
                $expired_date = strtotime($expired_date);
                if($expired_date > $current_date)
                    $data["bot_status"] = "1";
                else
                    $data["bot_status"] = "0";
                
                if($this->basic->update_data('users',array("id"=>$id),$data)) {
                    $this->user_edited_email_to_admin($data);
                    $this->session->set_flashdata('success_message',1);   
                }
                else $this->session->set_flashdata('error_message',1);     
                
                redirect('member/user_manager','location');                 
                
            }
        }   
    }

    public function add_user()
    {       
        if(!is_manager()){
            redirect('home/access_forbidden', 'location');
        }    
        if(is_limited()){
            $this->session->set_flashdata('limited', 1);
            redirect('member/user_manager','location');  
        }      
        $data['body']='manager/user/add_user';     
        $data['page_title']=$this->lang->line('Add User');     
        $packages=$this->basic->get_data('package',$where='',$select='',$join='',$limit='',$start='',$order_by='package_name asc');
        $visible_packages = array();
        foreach ($packages as $package){
            if($package['visible'] == '1')
                $visible_packages[] = $package;
        }
        $data['packages'] = format_data_dropdown($visible_packages,"id","package_name",false);
        //echo "<pre>"; var_dump($visible_packages); exit;
        $this->_viewcontroller($data);
    }


    public function add_user_action() 
    {
        if(!is_manager()){
            redirect('home/access_forbidden', 'location');
        }    
        if(is_limited()){
            $this->session->set_flashdata('error_message',1); 
            redirect('member/user_manager','location');  
        }   
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if($_SERVER['REQUEST_METHOD'] === 'GET') 
        redirect('home/access_forbidden','location');

        if($_POST)
        {
            $this->form_validation->set_rules('name', '<b>'.$this->lang->line("Full Name").'</b>', 'trim');      
            $this->form_validation->set_rules('email', '<b>'.$this->lang->line("Email").'</b>', 'trim|required|valid_email|is_unique[users.email]');      
            $this->form_validation->set_rules('mobile', '<b>'.$this->lang->line("Mobile").'</b>', 'trim');      
            $this->form_validation->set_rules('password', '<b>'.$this->lang->line("Password").'</b>', 'trim|required');      
            $this->form_validation->set_rules('confirm_password', '<b>'.$this->lang->line("Confirm Password").'</b>', 'trim|required|matches[password]');      
            $this->form_validation->set_rules('address', '<b>'.$this->lang->line("Address").'</b>', 'trim');      
            $this->form_validation->set_rules('user_type', '<b>'.$this->lang->line("User Type").'</b>', 'trim|required');      
            $this->form_validation->set_rules('status', '<b>'.$this->lang->line("Status").'</b>', 'trim');

            if($this->input->post("user_type")=="Member")     
            {
                if(!is_manager_3()){
                    $this->form_validation->set_rules('package_id', '<b>'.$this->lang->line("Package").'</b>', 'trim|required');      
                }
                if(!is_manager_2() and !is_manager_3()){
                    $this->form_validation->set_rules('expired_date', '<b>'.$this->lang->line("Expiry Date").'</b>', 'trim|required');
                }
            }
                
            if ($this->form_validation->run() == FALSE)
            {
                $this->add_user(); 
            }
            else
            {      
                $users = 0;         
                $name=$this->input->post('name');
                $email=$this->input->post('email');
                $mobile=$this->input->post('mobile');
                $password=md5($this->input->post('password'));
                $confirm_password=$this->input->post('confirm_password');
                $address=$this->input->post('address');
                $user_type=$this->input->post('user_type');
                $manager_type=$this->input->post('manager_type');
                $status=$this->input->post('status');
                $package_id=$this->input->post('package_id');
                //$expired_date=$this->input->post('expired_date');
                if(is_manager_2()){
                    //$package_id=    get_package_id($id);
                    $package_id=$this->input->post('package_id');
                    $expired_date = get_expired_date();
                }elseif(is_manager_3()){
                    //$package_id=    get_package_id($id);
                    $package_id=get_package_id($this->session->userdata('user_id'));
                    $days = get_package($package_id)->premium_days;
                    $expired_date = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s"). ' + '.$days.' days'));
                }
                else{
                    $package_id=$this->input->post('package_id');
                    $expired_date=$this->input->post('expired_date');
                }
                if($status=='') $status='0';
                if($manager_type == null) $manager_type ='';
                if($manager_type == 'Manager 2') $users =$this->input->post('users');
                //echo $manager_type . "  " . $users; exit;
                if($user_type == 'Admin') redirect('home/access_forbidden','location');
                                                       
                $data=array
                (
                    'name'=>$name,
                    'email'=>$email,
                    'mobile'=>$mobile,
                    'password'=>$password,
                    'address'=>$address,
                    'user_type'=>$user_type,
                    'manager_type'=>$manager_type,
                    'users'=>$users,
                    'status'=>$status,
                    'added_by'=>$this->session->userdata('user_id'),
                    'add_date' => date("Y-m-d H:i:s")
                );

                if($user_type=='Member')
                {
                    $data["package_id"] = $package_id;
                    $data["expired_date"] = $expired_date;
                }
                else
                {
                    $data["package_id"] = 0;
                    $data["expired_date"] = '';
                }

                
                if($this->basic->insert_data('users',$data)) {
                    $this->user_added_email_to_admin($data);
                    $this->session->set_flashdata('success_message',1);   
                }
                else $this->session->set_flashdata('error_message',1);     
                
                redirect('member/user_manager','location');                 
                
            }
        }   
    }

    public function edit_profile_action()
    {
        if($this->is_demo == '1' && $this->session->userdata('user_type') == 'Admin')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>Permission denied</h2>"; 
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            redirect('home/access_forbidden', 'location');
        }

        if ($_POST) 
        {
            // validation
            $this->form_validation->set_rules('name',                '<b>'.$this->lang->line("name").'</b>',             'trim|required');
            $this->form_validation->set_rules('email',               '<b>'.$this->lang->line("email").'</b>',            'trim|required|valid_email|callback_unique_email_check['.$this->session->userdata('user_id').']');
            $this->form_validation->set_rules('address',             '<b>'.$this->lang->line("address").'</b>',          'trim');
            $this->form_validation->set_rules('time_zone',             '<b>'.$this->lang->line("Time Zone").'</b>',          'trim');
            
            if ($this->form_validation->run() == false) 
            {
                return $this->edit_profile();
            } 
            else 
            {
                // assign
                $name=addslashes(strip_tags($this->input->post('name', true)));
                $email=addslashes(strip_tags($this->input->post('email', true)));
                $address=addslashes(strip_tags($this->input->post('address', true)));
                $time_zone=addslashes(strip_tags($this->input->post('time_zone', true)));
                $base_path=FCPATH . 'member';
                if(!file_exists($base_path)) mkdir($base_path,0755);

                $this->load->library('upload');

                $photo="";
                if ($_FILES['logo']['size'] != 0) {
                    $photo = $this->session->userdata("user_id").".png";
                    $config = array(
                        "allowed_types" => "png",
                        "upload_path" => $base_path,
                        "overwrite" => true,
                        "file_name" => $photo,
                        'max_size' => '200',
                        'max_width' => '500',
                        'max_height' => '500'
                        );
                    $this->upload->initialize($config);
                    $this->load->library('upload', $config);

                    if (!$this->upload->do_upload('logo')) {
                        $this->session->set_userdata('logo_error', $this->upload->display_errors());
                        return $this->edit_profile();
                    }
                }

                $update_data=array
                (
                    "name"=>$name,
                    "email"=>$email,
                    "address"=>$address,
                    "time_zone"=>$time_zone
                );

                if($photo!="") $update_data["brand_logo"] = $photo;
 
                $this->basic->update_data("users",array("id"=>$this->session->userdata("user_id")),$update_data);
                     
                $this->session->set_flashdata('success_message', 1);
                redirect('member/edit_profile', 'location');
            }
        }
    }

    function unique_email_check($str, $edited_id)
    {
        $email= strip_tags(trim($this->input->post('email',TRUE)));
        if($email==""){
            $s= $this->lang->line("required");
            $s=str_replace("<b>%s</b>","",$s);
            $s="<b>".$this->lang->line("email")."</b> ".$s;
            $this->form_validation->set_message('unique_email_check', $s);
            return FALSE;
        }
        
        if(!isset($edited_id) || !$edited_id)
            $where=array("email"=>$email);
        else        
            $where=array("email"=>$email,"id !="=>$edited_id);
        
        
        $is_unique=$this->basic->is_unique("users",$where,$select='');
        
        if (!$is_unique) {
            $s = $this->lang->line("is_unique");
            $s=str_replace("<b>%s</b>","",$s);
            $s="<b>".$this->lang->line("email")."</b> ".$s;
            $this->form_validation->set_message('unique_email_check', $s);
            return FALSE;
            }
                
        return TRUE;
    }

}
