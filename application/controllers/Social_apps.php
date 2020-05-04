<?php

require_once("Home.php"); // loading home controller

/**
* @category controller
* class Admin
*/

class Social_apps extends Home
{
    
    public $is_wp_social_sharing_exist = false;

    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('logged_in')!= 1) {
            redirect('home/login', 'location');
        }

        $function_name=$this->uri->segment(2);
        $pinterest_action_array = array('pinterest_settings', 'pinterest_settings_data', 'add_pinterest_settings','edit_pinterest_settings', 'pinterest_settings_update_action', 'delete_app_pinterest', 'change_app_status_pinterest','pinterest_intermediate_account_import_page','wordpress_settings_self_hosted','add_wordpress_settings_self_hosted','edit_wordpress_settings_self_hosted','wordpress_settings_self_hosted_data','delete_wordpress_settings_self_hosted','wordpress_settings_self_hosted_load_categories');

        if(!in_array($function_name, $pinterest_action_array)) 
        {
            if ($this->session->userdata('user_type')== "Member" && $this->config->item("backup_mode")==0)
            redirect('home/login', 'location');        
        }        
        
        $this->load->helper('form');
        $this->load->library('upload');
        
        $this->upload_path = realpath(APPPATH . '../upload');
        set_time_limit(0);

        $this->important_feature();
        $this->periodic_check();

        $this->is_wp_social_sharing_exist = $this->wp_social_sharing_exist();
    }


    public function index()
    {
        $this->settings();
    }


    public function settings()
    {

        $data['page_title'] = $this->lang->line('Social Apps');

        $data['body'] = 'admin/social_apps/settings';
        $data['title'] = $this->lang->line('Social Apps');

        $this->_viewcontroller($data);
    }


    public function google_settings()
    {

        if ($this->session->userdata('user_type') != 'Admin')
        redirect('home/login_page', 'location');

        $google_settings = $this->basic->get_data('login_config');

        if (!isset($google_settings[0])) $google_settings = array();
        else $google_settings = $google_settings[0];

        if($this->is_demo == '1')
        {
            $google_settings['api_key'] = 'XXXXXXXXXXX';
            $google_settings['google_client_secret'] = 'XXXXXXXXXXX';
        }

        $data['google_settings'] = $google_settings;
        $data['page_title'] = $this->lang->line('Google App Settings');
        $data['title'] = $this->lang->line('Google App Settings');
        $data['body'] = 'admin/social_apps/google_settings';

        $this->_viewcontroller($data);
    }



    public function google_settings_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if ($this->session->userdata('user_type') != 'Admin')
        redirect('home/login_page', 'location');

        if (!isset($_POST)) exit;

        $this->form_validation->set_rules('google_client_id', $this->lang->line("Client ID"), 'trim|required');
        $this->form_validation->set_rules('google_client_secret', $this->lang->line("Client Secret"), 'trim|required');

        if ($this->form_validation->run() == FALSE) 
            $this->google_settings();
        else {

            $this->csrf_token_check();

            $insert_data['app_name'] = strip_tags($this->input->post('app_name',true));
            $insert_data['api_key'] = strip_tags($this->input->post('api_key',true));
            $insert_data['google_client_id'] = strip_tags($this->input->post('google_client_id',true));
            $insert_data['google_client_secret'] = strip_tags($this->input->post('google_client_secret',true));
            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            $insert_data['status'] = $status;

            $google_settings = $this->basic->get_data('login_config');

            if (count($google_settings) > 0 ) {

                $id = $google_settings[0]['id'];
                $this->basic->update_data('login_config', array('id' => $id), $insert_data);
            }
            else 
                $this->basic->insert_data('login_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/google_settings'),'location');
        }
    }



    protected function facebookTokenValidityCheck($input_token)
    {

        if($input_token=="") 
        return "<span class='badge badge-status text-danger'><i class='fas fa-times-circle red'></i> ".$this->lang->line('Invalid')."</span>";
        $this->load->library("fb_rx_login"); 
        
        if($this->config->item('developer_access') == '1')
        {
            $valid_or_invalid = $this->fb_rx_login->access_token_validity_check_for_user($input_token);
            
            if($valid_or_invalid)
                return "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Valid')."</span>";
            else
                return "<span class='badge badge-status text-danger'><i class='fa fa-clock-o red'></i> ".$this->lang->line('Expired')."</span>";
        }
        else
        {
            $url="https://graph.facebook.com/debug_token?input_token={$input_token}&access_token={$input_token}";
            $result= $this->fb_rx_login->run_curl_for_fb($url);
            $result = json_decode($result,true);
             
            if(isset($result["data"]["is_valid"]) && $result["data"]["is_valid"])
                return "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Valid')."</span>";
            else
                return "<span class='badge badge-status text-danger'><i class='fa fa-clock-o red'></i> ".$this->lang->line('Expired')."</span>"; 
        }

    }



    public function facebook_settings()
    {
        $data['page_title'] = $this->lang->line('Facebook App Settings');
        $data['title'] = $this->lang->line('Facebook App Settings');
        $data['body'] = 'admin/social_apps/facebook_app_settings';

        $this->_viewcontroller($data);
    }


    public function facebook_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'api_id', 'api_secret', 'status', 'token_validity', 'action');
        $search_columns = array('app_name', 'api_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="facebook_rx_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['api_secret'] = "XXXXXXXXXXX";

            $token_validity = $this->facebookTokenValidityCheck($value['user_access_token']);
            if($value['status'] == 1)
                $info[$i]['status'] = "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Active')."</span>";
            else
                $info[$i]['status'] = "<span class='badge badge-status text-danger'><i class='fa fa-check-circle red'></i> ".$this->lang->line('Inactive')."</span>";
            $info[$i]['token_validity'] = $token_validity;

            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_facebook_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='".base_url('social_apps/login_button/').$value['id']."' class='btn btn-outline-primary btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Login to validate your accesstoken.')."'><i class='fab fa-facebook-square'></i></a> <a href='#' csrf_token='".$this->session->userdata('csrf_token_session')."' csrf_token='".$this->session->userdata('csrf_token_session')."' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_facebook_settings()
    {
        $data['table_id'] = 0;
        $data['facebook_settings'] = array();
        $data['page_title'] = $this->lang->line('Facebook App Settings');
        $data['title'] = $this->lang->line('Facebook App Settings');
        $data['body'] = 'admin/social_apps/facebook_settings';

        $this->_viewcontroller($data);
    }


    public function edit_facebook_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $facebook_settings = $this->basic->get_data('facebook_rx_config',array("where"=>array("id"=>$table_id)));
        if (!isset($facebook_settings[0])) $facebook_settings = array();
        else $facebook_settings = $facebook_settings[0];
        $data['table_id'] = $table_id;
        $data['facebook_settings'] = $facebook_settings;
        $data['page_title'] = $this->lang->line('Facebook App Settings');
        $data['title'] = $this->lang->line('Facebook App Settings');
        $data['body'] = 'admin/social_apps/facebook_settings';

        $this->_viewcontroller($data);
    }




    public function facebook_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;



        $this->form_validation->set_rules('api_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('api_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_facebook_settings();
            else $this->edit_facebook_settings($table_id);
        }
        else
        {
            $this->csrf_token_check();
            $insert_data['app_name'] = strip_tags($this->input->post('app_name',true));
            $insert_data['api_id'] = strip_tags($this->input->post('api_id',true));
            $insert_data['api_secret'] = strip_tags($this->input->post('api_secret',true));
            $insert_data['user_id'] = $this->user_id;

            if($this->session->userdata('user_type') == 'Admin')
                $insert_data['use_by'] = 'everyone';
            else
                $insert_data['use_by'] = 'only_me';
            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('facebook_rx_config');

            if ($table_id != 0) {
                $this->basic->update_data('facebook_rx_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('facebook_rx_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/facebook_settings'),'location');
            
        }
    }


    public function login_button($id)
    {     
        $this->is_group_posting_exist=$this->group_posting_exist();
        $fb_config_info = $this->basic->get_data('facebook_rx_config',array('where'=>array('id'=>$id)));
        if(isset($fb_config_info[0]['developer_access']) && $fb_config_info[0]['developer_access'] == '1' && $this->session->userdata('user_type')=="Admin")
        {
            $url = "https://ac.getapptoken.com/home/get_secret_code_info";
            $config_id = $fb_config_info[0]['secret_code'];

            $json="secret_code={$config_id}";
     
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$json);
            curl_setopt($ch,CURLOPT_POST,1);
            // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
            curl_setopt($ch, CURLOPT_COOKIEJAR,'cookie.txt');  
            curl_setopt($ch, CURLOPT_COOKIEFILE,'cookie.txt');  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
            $st=curl_exec($ch);  
            $result=json_decode($st,TRUE);


            if(isset($result['error']))
            {
                $this->session->set_userdata('secret_code_error','Invalid secret code!');
                redirect('facebook_rx_config/index','location');                
                exit();
            }


            // collect data from our server and then insert it into faceboo_rx_config_table and then call library for user and page info
            $config_data = array(
                'api_id' => $result['api_id'],
                'api_secret' => $result['api_secret'],
                'facebook_id' => $result['fb_id'],
                'user_access_token' => $result['access_token']
            );
            $this->basic->update_data("facebook_rx_config",array('id'=>$id),$config_data);

            $data = array(
                'user_id' => $this->user_id,
                'facebook_rx_config_id' => $id,
                'access_token' => $result['access_token'],
                'name' => isset($result['name']) ? $result['name'] : "",
                'email' => isset($result['email']) ? $result['email'] : "",
                'fb_id' => $result['fb_id'],
                'add_date' => date('Y-m-d')
                );

            $where=array();
            $where['where'] = array('user_id'=>$this->user_id,'fb_id'=>$result['fb_id']);
            $exist_or_not = $this->basic->get_data('facebook_rx_fb_user_info',$where);

            if(empty($exist_or_not))
            {
                $this->basic->insert_data('facebook_rx_fb_user_info',$data);
                $facebook_table_id = $this->db->insert_id();
            }
            else
            {
                $facebook_table_id = $exist_or_not[0]['id'];
                $where = array('user_id'=>$this->user_id,'fb_id'=>$result['fb_id']);
                $this->basic->update_data('facebook_rx_fb_user_info',$where,$data);
            }

            $this->session->set_userdata("facebook_rx_fb_user_info",$facebook_table_id);


            $this->session->set_userdata("fb_rx_login_database_id",$id);
            $this->fb_rx_login->app_initialize($id);
            $page_list = $this->fb_rx_login->get_page_list($result['access_token']);            
            if(!empty($page_list))
            {
                foreach($page_list as $page)
                {
                    $user_id = $this->user_id;
                    $page_id = $page['id'];
                    $page_cover = '';
                    if(isset($page['cover']['source'])) $page_cover = $page['cover']['source'];
                    $page_profile = '';
                    if(isset($page['picture']['url'])) $page_profile = $page['picture']['url'];
                    $page_name = '';
                    if(isset($page['name'])) $page_name = $page['name'];
                    $page_username = '';
                    if(isset($page['username'])) $page_username = $page['username'];
                    $page_access_token = '';
                    if(isset($page['access_token'])) $page_access_token = $page['access_token'];
                    $page_email = '';
                    if(isset($page['emails'][0])) $page_email = $page['emails'][0];

                    $data = array(
                        'user_id' => $user_id,
                        'facebook_rx_fb_user_info_id' => $facebook_table_id,
                        'page_id' => $page_id,
                        'page_cover' => $page_cover,
                        'page_profile' => $page_profile,
                        'page_name' => $page_name,
                        'username' => $page_username,
                        'page_access_token' => $page_access_token,
                        'page_email' => $page_email,
                        'add_date' => date('Y-m-d')
                        );

                    $where=array();
                    $where['where'] = array('facebook_rx_fb_user_info_id'=>$facebook_table_id,'page_id'=>$page['id']);
                    $exist_or_not = $this->basic->get_data('facebook_rx_fb_page_info',$where);

                    if(empty($exist_or_not))
                    {
                        $this->basic->insert_data('facebook_rx_fb_page_info',$data);
                    }
                    else
                    {
                        $where = array('facebook_rx_fb_user_info_id'=>$facebook_table_id,'page_id'=>$page['id']);
                        $this->basic->update_data('facebook_rx_fb_page_info',$where,$data);
                    }

                }
            }

            $group_list = $this->fb_rx_login->get_group_list($result['access_token']);

            if(!empty($group_list))
            {
                foreach($group_list as $group)
                {
                    $user_id = $this->user_id;
                    $group_access_token = $result['access_token']; // group uses user access token
                    $group_id = $group['id'];
                    $group_cover = '';
                    if(isset($group['cover']['source'])) $group_cover = $group['cover']['source'];
                    $group_profile = '';
                    if(isset($group['picture']['url'])) $group_profile = $group['picture']['url'];
                    $group_name = '';
                    if(isset($group['name'])) $group_name = $group['name'];

                    $data = array(
                        'user_id' => $user_id,
                        'facebook_rx_fb_user_info_id' => $facebook_table_id,
                        'group_id' => $group_id,
                        'group_cover' => $group_cover,
                        'group_profile' => $group_profile,
                        'group_name' => $group_name,
                        'group_access_token' => $group_access_token,
                        'add_date' => date('Y-m-d')
                        );

                    $where=array();
                    $where['where'] = array('facebook_rx_fb_user_info_id'=>$facebook_table_id,'group_id'=>$group['id']);
                    $exist_or_not = $this->basic->get_data('facebook_rx_fb_group_info',$where);

                    if(empty($exist_or_not))
                    {
                        $this->basic->insert_data('facebook_rx_fb_group_info',$data);
                    }
                    else
                    {
                        $where = array('facebook_rx_fb_user_info_id'=>$facebook_table_id,'group_id'=>$page['id']);
                        $this->basic->update_data('facebook_rx_fb_group_info',$where,$data);
                    }
                }
            }
            $this->session->set_userdata('success_message', 'success');
            redirect('facebook_rx_account_import/index','location');
        }
        else
        {
            $this->session->set_userdata("fb_rx_login_database_id",$id);
            $this->load->library('fb_rx_login');
            $redirect_url = base_url()."home/redirect_rx_link";        
            $data['fb_login_button'] = $this->fb_rx_login->login_for_user_access_token($redirect_url);  

            $data['body'] = 'facebook_rx/admin_login';
            $data['page_title'] =  $this->lang->line("Admin login");
            $data['expired_or_not'] = $this->fb_rx_login->access_token_validity_check();
            $this->_viewcontroller($data);
        }
    }





    /**
     * Twitter section starts
     */
    public function twitter_settings()
    {
        $data['page_title'] = $this->lang->line('Twitter App Settings');
        $data['title'] = $this->lang->line('Twitter App Settings');
        $data['body'] = 'admin/social_apps/twitter_app_settings';

        $this->_viewcontroller($data);
    }

    public function twitter_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'consumer_id', 'consumer_secret', 'status', 'action');
        $search_columns = array('app_name', 'consumer_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="twitter_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_twitter_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' csrf_token='".$this->session->userdata('csrf_token_session')."' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_twitter_settings()
    {
        $data['table_id'] = 0;
        $data['twitter_settings'] = array();
        $data['page_title'] = $this->lang->line('Twitter App Settings');
        $data['title'] = $this->lang->line('Twitter App Settings');
        $data['body'] = 'admin/social_apps/twitter_settings';

        $this->_viewcontroller($data);
    }


    public function edit_twitter_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $twitter_settings = $this->basic->get_data('twitter_config',array("where"=>array("id"=>$table_id)));
        if (!isset($twitter_settings[0])) $twitter_settings = array();
        else $twitter_settings = $twitter_settings[0];
        $data['table_id'] = $table_id;
        $data['twitter_settings'] = $twitter_settings;
        $data['page_title'] = $this->lang->line('Twitter App Settings');
        $data['title'] = $this->lang->line('Twitter App Settings');
        $data['body'] = 'admin/social_apps/twitter_settings';

        $this->_viewcontroller($data);
    }


    public function twitter_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;


        $this->form_validation->set_rules('consumer_id', $this->lang->line("Consumer ID"), 'trim|required');
        $this->form_validation->set_rules('consumer_secret', $this->lang->line("Consumer Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_twitter_settings();
            else $this->edit_twitter_settings($table_id);
        }
        else {

            $this->csrf_token_check();

            $insert_data['app_name'] = strip_tags($this->input->post('app_name',true));
            $insert_data['consumer_id'] = strip_tags($this->input->post('consumer_id',true));
            $insert_data['consumer_secret'] = strip_tags($this->input->post('consumer_secret',true));
            $insert_data['user_id'] = $this->user_id;
            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('twitter_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('twitter_config');

            if ($table_id != 0) {
                $this->basic->update_data('twitter_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('twitter_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/twitter_settings'),'location');
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_twitter()
    {
        $this->ajax_check();
        $this->csrf_token_check();
        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('twitter_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));
    }


    public function change_app_status_twitter()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('twitter_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('twitter_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('twitter_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Twitter section ends */




    /**
     * Linkedin section starts
     */
    public function linkedin_settings()
    {
        $data['page_title'] = $this->lang->line('Linkedin App Settings');
        $data['title'] = $this->lang->line('Linkedin App Settings');
        $data['body'] = 'admin/social_apps/linkedin_app_settings';

        $this->_viewcontroller($data);
    }


    public function linkedin_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'client_id', 'client_secret', 'status', 'action');
        $search_columns = array('app_name', 'client_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="linkedin_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_linkedin_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' csrf_token='".$this->session->userdata('csrf_token_session')."' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_linkedin_settings()
    {
        $data['table_id'] = 0;
        $data['linkedin_settings'] = array();
        $data['page_title'] = $this->lang->line('Linkedin App Settings');
        $data['title'] = $this->lang->line('Linkedin App Settings');
        $data['body'] = 'admin/social_apps/linkedin_settings';

        $this->_viewcontroller($data);
    }


    public function edit_linkedin_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $linkedin_settings = $this->basic->get_data('linkedin_config',array("where"=>array("id"=>$table_id)));
        if (!isset($linkedin_settings[0])) $linkedin_settings = array();
        else $linkedin_settings = $linkedin_settings[0];
        $data['table_id'] = $table_id;
        $data['linkedin_settings'] = $linkedin_settings;
        $data['page_title'] = $this->lang->line('Linkedin App Settings');
        $data['title'] = $this->lang->line('Linkedin App Settings');
        $data['body'] = 'admin/social_apps/linkedin_settings';

        $this->_viewcontroller($data);
    }




    public function linkedin_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;



        $this->form_validation->set_rules('client_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('client_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_linkedin_settings();
            else $this->edit_linkedin_settings($table_id);
        }
        else {

            $this->csrf_token_check();

            $insert_data['app_name'] = strip_tags($this->input->post('app_name',true));
            $insert_data['client_id'] = strip_tags($this->input->post('client_id',true));
            $insert_data['client_secret'] = strip_tags($this->input->post('client_secret',true));
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('linkedin_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('linkedin_config');

            if ($table_id != 0) {
                $this->basic->update_data('linkedin_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('linkedin_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/linkedin_settings'),'location');
            
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_linkedin()
    {
        $this->ajax_check();
        $this->csrf_token_check();
        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('linkedin_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));

    }


    public function change_app_status_linkedin()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('linkedin_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('linkedin_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('linkedin_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Linkedin section ends */


    /**
     * Reddit section starts
     */
    public function reddit_settings()
    {
        $data['page_title'] = $this->lang->line('Reddit App Settings');
        $data['title'] = $this->lang->line('Reddit App Settings');
        $data['body'] = 'admin/social_apps/reddit_app_settings';

        $this->_viewcontroller($data);
    }


    public function reddit_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'client_id', 'client_secret', 'status', 'action');
        $search_columns = array('app_name', 'client_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="reddit_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_reddit_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' csrf_token='".$this->session->userdata('csrf_token_session')."' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_reddit_settings()
    {
        $data['table_id'] = 0;
        $data['reddit_settings'] = array();
        $data['page_title'] = $this->lang->line('Reddit App Settings');
        $data['title'] = $this->lang->line('Reddit App Settings');
        $data['body'] = 'admin/social_apps/reddit_settings';

        $this->_viewcontroller($data);
    }


    public function edit_reddit_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $reddit_settings = $this->basic->get_data('reddit_config',array("where"=>array("id"=>$table_id)));
        if (!isset($reddit_settings[0])) $reddit_settings = array();
        else $reddit_settings = $reddit_settings[0];
        $data['table_id'] = $table_id;
        $data['reddit_settings'] = $reddit_settings;
        $data['page_title'] = $this->lang->line('Reddit App Settings');
        $data['title'] = $this->lang->line('Reddit App Settings');
        $data['body'] = 'admin/social_apps/reddit_settings';

        $this->_viewcontroller($data);
    }


    public function reddit_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;



        $this->form_validation->set_rules('client_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('client_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_reddit_settings();
            else $this->edit_reddit_settings($table_id);
        }
        else {

            $this->csrf_token_check();

            $insert_data['app_name'] = strip_tags($this->input->post('app_name',true));
            $insert_data['client_id'] = strip_tags($this->input->post('client_id',true));
            $insert_data['client_secret'] = strip_tags($this->input->post('client_secret',true));
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('reddit_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('reddit_config');

            if ($table_id != 0) {
                $this->basic->update_data('reddit_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('reddit_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/reddit_settings'),'location');
            
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_reddit()
    {
        $this->ajax_check();
        $this->csrf_token_check();
        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('reddit_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));

    }


    public function change_app_status_reddit()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('reddit_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('reddit_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('reddit_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Reddit section ends */


    /**
     * Pinterest section starts
     */
    public function pinterest_settings()
    {
        $data['page_title'] = $this->lang->line('Pinterest App Settings');
        $data['title'] = $this->lang->line('Pinterest App Settings');
        $data['body'] = 'admin/social_apps/pinterest_app_settings';

        $this->_viewcontroller($data);
    }


    public function pinterest_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'client_id', 'client_secret', 'status', 'action');
        $search_columns = array('app_name', 'client_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="pinterest_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $login_button = base_url('social_apps/pinterest_intermediate_account_import_page/'.$info[$i]['id']);


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:171px'><a href='".base_url('social_apps/edit_pinterest_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' csrf_token='".$this->session->userdata('csrf_token_session')."' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a> <a href='". $login_button ."' class='btn btn-outline-info btn-circle ' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Import Account')."'><i class='fab fa-pinterest-square'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function pinterest_intermediate_account_import_page($app_id)
    {
        $this->session->set_userdata('pinterest_app_id', $app_id);

        $this->load->library('Pinterests');

        $this->pinterests->app_initialize($app_id);
        $redirect_url = base_url('comboposter/login_callback/pinterest');
        $login_url = $this->pinterests->login_button($redirect_url);

        redirect($login_url,'location');
    }


    public function add_pinterest_settings()
    {
        $data['table_id'] = 0;
        $data['pinterest_settings'] = array();
        $data['page_title'] = $this->lang->line('pinterest App Settings');
        $data['title'] = $this->lang->line('pinterest App Settings');
        $data['body'] = 'admin/social_apps/pinterest_settings';

        $this->_viewcontroller($data);
    }


    public function edit_pinterest_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $pinterest_settings = $this->basic->get_data('pinterest_config',array("where"=>array("id"=>$table_id)));
        if (!isset($pinterest_settings[0])) $pinterest_settings = array();
        else $pinterest_settings = $pinterest_settings[0];
        $data['table_id'] = $table_id;
        $data['pinterest_settings'] = $pinterest_settings;
        $data['page_title'] = $this->lang->line('Pinterest App Settings');
        $data['title'] = $this->lang->line('Pinterest App Settings');
        $data['body'] = 'admin/social_apps/pinterest_settings';

        $this->_viewcontroller($data);
    }




    public function pinterest_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;


        $this->form_validation->set_rules('client_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('client_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_pinterest_settings();
            else $this->edit_pinterest_settings($table_id);
        }
        else {

            $this->csrf_token_check();

            $insert_data['app_name'] = strip_tags($this->input->post('app_name',true));
            $insert_data['client_id'] = strip_tags($this->input->post('client_id',true));
            $insert_data['client_secret'] = strip_tags($this->input->post('client_secret',true));
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            // if ($status == '1') {
            //     $this->basic->update_data('pinterest_config', array('user_id' => $this->user_id), array('status' => '0'));
            // }

            $insert_data['status'] = $status;
            $facebook_settings = $this->basic->get_data('pinterest_config');

            if ($table_id != 0) {
                $this->basic->update_data('pinterest_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('pinterest_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/pinterest_settings'),'location');
            
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_pinterest()
    {
        $this->ajax_check();
        $this->csrf_token_check();
        $app_table_id = $this->input->post('app_table_id', true);

        $this->basic->delete_data('pinterest_users_info', array('pinterest_config_table_id' => $app_table_id, 'user_id' => $this->user_id));

        $this->basic->delete_data('pinterest_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));

    }


    public function change_app_status_pinterest()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('pinterest_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                // $this->basic->update_data('pinterest_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('pinterest_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }

    /* Pinterest section ends */
    

    /**
     * Wordpress section starts
     */
    public function wordpress_settings()
    {
        $data['page_title'] = $this->lang->line('Wordpress App Settings');
        $data['title'] = $this->lang->line('Wordpress App Settings');
        $data['body'] = 'admin/social_apps/wordpress_app_settings';

        $this->_viewcontroller($data);
    }


    public function wordpress_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'client_id', 'client_secret', 'status', 'action');
        $search_columns = array('app_name', 'client_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="wordpress_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_wordpress_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' csrf_token='".$this->session->userdata('csrf_token_session')."' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_wordpress_settings()
    {
        $data['table_id'] = 0;
        $data['wordpress_settings'] = array();
        $data['page_title'] = $this->lang->line('Wordpress App Settings');
        $data['title'] = $this->lang->line('Wordpress App Settings');
        $data['body'] = 'admin/social_apps/wordpress_settings';

        $this->_viewcontroller($data);
    }


    public function edit_wordpress_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $wordpress_settings = $this->basic->get_data('wordpress_config',array("where"=>array("id"=>$table_id)));
        if (!isset($wordpress_settings[0])) $wordpress_settings = array();
        else $wordpress_settings = $wordpress_settings[0];
        $data['table_id'] = $table_id;
        $data['wordpress_settings'] = $wordpress_settings;
        $data['page_title'] = $this->lang->line('Wordpress App Settings');
        $data['title'] = $this->lang->line('Wordpress App Settings');
        $data['body'] = 'admin/social_apps/wordpress_settings';

        $this->_viewcontroller($data);
    }


    public function wordpress_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;



        $this->form_validation->set_rules('client_id', $this->lang->line("App ID"), 'trim|required');
        $this->form_validation->set_rules('client_secret', $this->lang->line("App Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_wordpress_settings();
            else $this->edit_wordpress_settings($table_id);
        }
        else {

            $this->csrf_token_check();

            $insert_data['app_name'] = strip_tags($this->input->post('app_name',true));
            $insert_data['client_id'] = strip_tags($this->input->post('client_id',true));
            $insert_data['client_secret'] = strip_tags($this->input->post('client_secret',true));
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('wordpress_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('wordpress_config');

            if ($table_id != 0) {
                $this->basic->update_data('wordpress_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('wordpress_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/wordpress_settings'),'location');
            
        }
    }

    /**
     * Wordpress (Self-Hosted) section starts here
     */
    public function wordpress_settings_self_hosted()
    {
        $data['page_title'] = $this->lang->line('Wordpress settings (self-hosted)');
        $data['body'] = 'admin/social_apps/wordpress_app_settings_self_hosted';

        $this->_viewcontroller($data);
    }

    public function wordpress_settings_self_hosted_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array('id', 'domain_name', 'user_key', 'authentication_key');
        $search_columns = array('domain_name', 'user_key', 'authentication_key');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;

        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by = $sort . " " . $order;

        $where_custom = '';
        $where_custom="user_id = " . $this->user_id;

        if ($search_value != '') {
            foreach ($search_columns as $key => $value) {
                $temp[] = $value." LIKE "."'%$search_value%'";
            }

            $imp = implode(" OR ", $temp);
            $where_custom .= " AND (" . $imp . ") ";
        }


        $table="wordpress_config_self_hosted";
        $select = [
            'id',
            'domain_name',
            'user_key',
            'authentication_key',
            'posting_medium',
            'status',
        ];
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table, $where='', $select, $join='', $limit, $start, $order_by, $group_by='');

        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $length = count($info);
        for ($i = 0; $i < $length; $i++) {

            if (isset($info[$i]['status'])) {
                $status = ('1' == $info[$i]['status']) ? 'text-success' : 'text-danger';

                if ('1' == $info[$i]['status']) {
                    $info[$i]['status'] = '<span class="badge badge-status text-success green"><i class="fa fa-check-circle"></i> ' . $this->lang->line('Active') . '</span>';
                } elseif ('0' == $info[$i]['status']) {
                    $info[$i]['status'] = '<span class="badge badge-status text-danger red"><i class="fa fa-check-circle"></i> ' . $this->lang->line('Inactive') . '</span>';
                }
            }

            if (isset($info[$i]['created_at'])) {
                $info[$i]['created_at'] = date('jS M Y H:i', strtotime($info[$i]['created_at']));
            }

            if (!isset($info[$i]['actions'])) {

                // Prepares buttons
                $actions = '<div style="min-width: 120px;">';

                $actions .= '<a data-toggle="tooltip" title="' . $this->lang->line('Update your blog categories') . '" href="#" class="btn btn-circle btn-outline-primary update-categories" data-wp-app-id="' . $info[$i]['id'] .'"><i class="fa fa-sync-alt"></i></a>&nbsp;&nbsp;';

                $actions .= '<a data-toggle="tooltip" title="' . $this->lang->line('Edit Wordpress Site Settings') . '" href="' . base_url("social_apps/edit_wordpress_settings_self_hosted/{$info[$i]['id']}") . '" class="btn btn-circle btn-outline-warning"><i class="fa fa-edit"></i></a>';
                $actions .= '&nbsp;&nbsp;<a data-toggle="tooltip" title="' . $this->lang->line('Delete Wordpress Site Settings') . '" href="" class="btn btn-circle btn-outline-danger" id="delete-wssh-settings"  csrf_token="'.$this->session->userdata('csrf_token_session').'" data-site-id="'. $info[$i]['id'] . '"><i class="fas fa-trash-alt"></i></a>';
                $actions .= '</div>';

                $info[$i]['actions'] = $actions;
                $info[$i]["actions"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            }

        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = $info;

        echo json_encode($data);
    }

    public function add_wordpress_settings_self_hosted()
    {   
        if ($this->is_wp_social_sharing_exist) {
            $social_media_data = $this->wordpress_settings_get_social_media_data();

            $data['facebook_account_list'] = $social_media_data['facebook_account_list'];
            $data['twitter_account_list'] = $social_media_data['twitter_account_list'];
            $data['linkedin_account_list'] = $social_media_data['linkedin_account_list'];
            $data['pinterest_account_list'] = $social_media_data['pinterest_account_list'];
            $data['reddit_account_list'] = $social_media_data['reddit_account_list'];
            $data['subreddits'] = $this->subRedditList();

            $data['post_type'] = 'nothing';
            $data['post_action'] = 'add';
            $data['campaigns_social_media'] = 0;
        }


        $auth_key = $this->generate_authentication_key();
        $data['page_title'] = $this->lang->line('Add Wordpress Settings (Self-Hosted)');
        $data['body'] = 'admin/social_apps/wordpress_settings_self_hosted_add';
        $data['auth_key'] = $auth_key;

        if ($_POST) {
            // Sets validation rules
            $this->csrf_token_check();
            $this->form_validation->set_rules('campaign_name', $this->lang->line('Campaign name'), 'trim|required');
            $this->form_validation->set_rules('domain_name', $this->lang->line('Domain name'), 'trim|required');
            $this->form_validation->set_rules('user_key', $this->lang->line('User key'), 'trim|required');
            $this->form_validation->set_rules('authentication_key', $this->lang->line('Authentication key'), 'trim|required');

            if (false === $this->form_validation->run()) {
                return $this->_viewcontroller($data);
            }

            $domain_name = filter_var($this->input->post('domain_name',true), FILTER_SANITIZE_URL, FILTER_VALIDATE_URL);
            if (false == $domain_name) {
                $message = $this->lang->line('Please provide a valid domain name.');
                $this->session->set_userdata('add_wssh_error', $message);
                
                return $this->_viewcontroller($data);
            }            

            $campaign_name = filter_var($this->input->post('campaign_name',true), FILTER_SANITIZE_STRING);
            $user_key = filter_var($this->input->post('user_key',true), FILTER_SANITIZE_STRING);
            $authentication_key = filter_var($this->input->post('authentication_key'), FILTER_SANITIZE_STRING);
            $status = filter_var($this->input->post('status',true), FILTER_SANITIZE_STRING);
            $status = empty($status) ? '0' : '1';

            $posting_medium = null;
            if ($this->is_wp_social_sharing_exist && $status) {
                $posting_medium = $this->wordpress_settings_prepare_selected_social_media_data();
                if (! is_array($posting_medium) && is_string($posting_medium)) {
                    $this->session->set_userdata('add_wssh_error', $posting_medium);

                    return $this->_viewcontroller($data);
                }
            }

            $data = [
                'campaign_name' => $campaign_name,
                'domain_name' => $domain_name,
                'user_key' => $user_key,
                'authentication_key' => $authentication_key,
                'posting_medium' => isset($posting_medium['posting_medium']) && is_array($posting_medium['posting_medium']) 
                    ? json_encode($posting_medium['posting_medium'])
                    : null,
                'subreddits' => isset($posting_medium['subreddits']) && null != $posting_medium['subreddits']
                    ? $posting_medium['subreddits']
                    : null,
                'user_id' => $this->user_id,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->basic->insert_data('wordpress_config_self_hosted', $data);

            if ($this->db->affected_rows() > 0) {
            	$this->_insert_usage_log($module_id=109, $request=1);
                if (is_array($posting_medium) && $social_media_count = count($posting_medium) > 0) {
                    $this->_insert_usage_log($module_id=269, $request=$social_media_count); 
                } 

                redirect(base_url('social_apps/wordpress_settings_self_hosted'), 'location');
            }

            $message = $this->lang->line('Something went wrong while adding your wordpress site.');
            $this->session->set_userdata('add_wssh_error', $message);   
            return $this->_viewcontroller($data);
        }

        $this->_viewcontroller($data);
    }

    public function edit_wordpress_settings_self_hosted($id = null)
    {
        if (null === $id) {
            redirect('error_404', 'location');
            exit;
        }
        
        $where = [
            'where' => [
                'id' => (int) $id,
            ],
        ];
        $select = [
            'id',
            'user_id',
            'campaign_name',
            'domain_name',
            'user_key',
            'authentication_key',
            'posting_medium',
            'subreddits',
            'status',
        ];

        $result = $this->basic->get_data('wordpress_config_self_hosted', $where, $select, [], 1);

        if (1 != sizeof($result)) {
            redirect('error_404', 'location');
            exit;
        }

        if ('Member' == $this->session->userdata('user_type')) {
            if ($result[0]['user_id'] != $this->user_id) {
                redirect('error_404', 'location');
                exit;
            }
        }

        if ($this->is_wp_social_sharing_exist) {
            $posting_medium = isset($result[0]['posting_medium']) 
                ? json_decode($result[0]['posting_medium'], true) 
                : [];
            $subreddits = isset($result[0]['subreddits']) && null != $result[0]['subreddits'] 
                ? (string) $result[0]['subreddits'] 
                : '0';
            $social_media_data = $this->wordpress_settings_get_social_media_data();

            $data['facebook_account_list'] = $social_media_data['facebook_account_list'];
            $data['twitter_account_list'] = $social_media_data['twitter_account_list'];
            $data['linkedin_account_list'] = $social_media_data['linkedin_account_list'];
            $data['pinterest_account_list'] = $social_media_data['pinterest_account_list'];
            $data['reddit_account_list'] = $social_media_data['reddit_account_list'];

            $data['campaign_form_info'] = ['subreddits' => $subreddits];
            $data['subreddits'] = $this->subRedditList();

            $data['post_type'] = 'nothing';
            $data['post_action'] = 'edit';
            $data['campaigns_social_media'] = $posting_medium;
        }

        $data['page_title'] = $this->lang->line('Edit Wordpress Settings (Self-Hosted)');
        $data['body'] = 'admin/social_apps/wordpress_settings_self_hosted_edit';
        $data['wp_settings'] = isset($result[0]) ? $result[0] : [];

        if ($_POST) {
            // Sets validation rules
            $this->csrf_token_check();
            $this->form_validation->set_rules('campaign_name', $this->lang->line('Campaign name'), 'trim|required');
            $this->form_validation->set_rules('domain_name', $this->lang->line('Domain name'), 'trim|required');
            $this->form_validation->set_rules('user_key', $this->lang->line('Consumer name'), 'trim|required');
            $this->form_validation->set_rules('authentication_key', $this->lang->line('Client key'), 'trim|required');

            if (false === $this->form_validation->run()) {
                return $this->_viewcontroller($data);
            }

            $domain_name = filter_var($this->input->post('domain_name',true), FILTER_SANITIZE_URL, FILTER_VALIDATE_URL);
            if (false == $domain_name) {
              $message = $this->lang->line('Please provide a valid domain name.');
              $this->session->set_userdata('edit_wssh_error', $message);
              return $this->_viewcontroller($data); 
            }

            $campaign_name = filter_var($this->input->post('campaign_name',true), FILTER_SANITIZE_STRING);
            $user_key = filter_var($this->input->post('user_key',true), FILTER_SANITIZE_STRING);
            $authentication_key = filter_var($this->input->post('authentication_key'), FILTER_SANITIZE_STRING);
            $status = filter_var($this->input->post('status',true), FILTER_SANITIZE_STRING);
            $status = empty($status) ? '0' : '1';

            $posting_medium = null;
            if ($this->is_wp_social_sharing_exist && $status) {
                $posting_medium = $this->wordpress_settings_prepare_selected_social_media_data();
                if (! is_array($posting_medium) && is_string($posting_medium)) {
                    $this->session->set_userdata('edit_wssh_error', $posting_medium);

                    return $this->_viewcontroller($data);
                }
            }

            $data = [
                'campaign_name' => $campaign_name,
                'domain_name' => $domain_name,
                'user_key' => $user_key,
                'authentication_key' => $authentication_key,
                'posting_medium' => isset($posting_medium['posting_medium']) && is_array($posting_medium['posting_medium'])
                    ? json_encode($posting_medium['posting_medium'])
                    : null,
                'subreddits' => isset($posting_medium['subreddits']) && null != $posting_medium['subreddits']
                    ? $posting_medium['subreddits']
                    : null,    
                'user_id' => $this->user_id,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ('Admin' == $this->session->userdata['user_type']) {
                $where = [
                    'id' => (int) $id,
                ];
            } else {
                $where = [
                    'id' => (int) $id,
                    'user_id' => $this->user_id,
                ];
            }

            $this->basic->update_data('wordpress_config_self_hosted', $where, $data);

            if ($this->db->affected_rows() > 0) {
                $message = $this->lang->line('Your wordpress site settings have been updated successfully.');
                $this->session->set_userdata('edit_wssh_success', $message);                
                redirect(base_url('social_apps/wordpress_settings_self_hosted'), 'location');
            }

            $message = $this->lang->line('Something went wrong while adding your wordpress site.');
            $this->session->set_userdata('edit_wssh_error', $message);   
            return $this->_viewcontroller($data);
        }

        $this->_viewcontroller($data);
    }

    public function delete_wordpress_settings_self_hosted()
    {
        if (! $this->input->is_ajax_request()) {
            $message = $this->lang->line('Bad request.');
            echo json_encode(['error' => $message]);
            exit;
        }

        $this->csrf_token_check();


        $id = (int) $this->input->post('site_id');

        $select = [
            'id',
            'user_id',
        ];

        $result = $this->basic->get_data('wordpress_config_self_hosted', [ 'where' => ['id' => (int) $id]], $select, [], 1);

        if (1 != sizeof($result)) {
            $message = $this->lang->line('Bad request.');
            echo json_encode(['error' => $message]);
            exit;
        }

        if ('Member' == $this->session->userdata('user_type')) {
            if ($result[0]['user_id'] != $this->user_id) {
                $message = $this->lang->line('Bad request.');
                echo json_encode(['error' => $message]);
                exit;
            }
        }

        if ('Admin' == $this->session->userdata('user_type')) {
            $where = ['id' => $id];
        } else {
            $where = ['id' => $id, 'user_id' => $this->user_id];
        }

        if ($this->basic->delete_data('wordpress_config_self_hosted', $where)) {
        	$this->_delete_usage_log($module_id=109,$request=1);
            $message = $this->lang->line('Your wordpress site settings have been deleted successfully.');
            echo json_encode([
                'status' => 'ok',
                'message' => $message,
            ]);
            exit;
        }      

        $message = $this->lang->line('Bad request.');
        echo json_encode(['error' => $message]);
        exit;        
    }

    public function wordpress_settings_prepare_selected_social_media_data()
    {
        $facebook_pages = (array) $this->input->post('facebook_pages', true);
        $twitter_accounts = (array) $this->input->post('twitter_accounts', true);
        $linkedin_accounts = (array) $this->input->post('linkedin_accounts', true);
        $pinterest_accounts = (array) $this->input->post('pinterest_boards', true);
        $reddit_accounts = (array) $this->input->post('reddit_accounts', true);
        $subreddits = (string) $this->input->post('subreddits', true);

        if (count($facebook_pages) == 0 
            && count($twitter_accounts) == 0 
            && count($linkedin_accounts) == 0 
            && count($pinterest_accounts) == 0 
            && count($reddit_accounts) == 0
        ) {

            return $this->lang->line("Please make sure that at least one social media is selected.");
        }

        if (count($reddit_accounts) > 0 && ! in_array($subreddits, $this->subRedditList())) {
            return $this->lang->line("Please make sure that a subreddit is selected.");
        }

        $posting_medium = array();

        $merged_array = array_merge(
            $facebook_pages, 
            $twitter_accounts, 
            $linkedin_accounts, 
            $pinterest_accounts, 
            $reddit_accounts
        );

        $posting_medium = array_filter($merged_array);

        $subreddits = ($subreddits && count($reddit_accounts) > 0) ? $subreddits : null;

        return [
            'posting_medium' => $posting_medium,
            'subreddits' => $subreddits,
        ];
    }

    public function wordpress_settings_get_social_media_data() 
    {
        $data = [];

        // Prepares facebook user data
        $existing_accounts = $this->basic->get_data('facebook_rx_fb_user_info', array('where' => array('user_id' => $this->user_id)));

        $existing_account_info = [];
        if(!empty($existing_accounts)) {
            $i = 0;
            foreach($existing_accounts as $value) {

                $existing_account_info[$i]['fb_id'] = $value['fb_id'];
                $existing_account_info[$i]['userinfo_table_id'] = $value['id'];
                $existing_account_info[$i]['name'] = $value['name'];
                $existing_account_info[$i]['email'] = $value['email'];
                $existing_account_info[$i]['user_access_token'] = $value['access_token'];

                $where = array();
                $where['where'] = array(
                    'facebook_rx_fb_user_info_id' => $value['id']
                );
                $select = array(
                    'id', 'facebook_rx_fb_user_info_id', 'page_id', 'page_profile', 'page_name', 'username'
                );
                $page_count = $this->basic->get_data('facebook_rx_fb_page_info', $where, $select);
                $existing_account_info[$i]['page_list'] = $page_count;

                if(!empty($page_count)) {
                    $existing_account_info[$i]['total_pages'] = count($page_count);
                } else {
                    $existing_account_info[$i]['total_pages'] = 0;
                }

                $group_count = $this->basic->get_data('facebook_rx_fb_group_info', $where);
                $existing_account_info[$i]['group_list'] = $group_count;

                if(!empty($group_count)) {
                    $existing_account_info[$i]['total_groups'] = count($group_count);
                } else {
                    $existing_account_info[$i]['total_groups'] = 0;
                }
                $i++;
            }
        }


        // Prepares pinterest user data
        $pinterest_where['where'] = array('pinterest_users_info.user_id' => $this->user_id);
        $pinterest_join = array('pinterest_board_info' => 'pinterest_users_info.id=pinterest_board_info.pinterest_table_id,left');
        $pinterest_select = array('pinterest_users_info.id', 'user_name', 'board_name', 'pinterest_board_info.id as table_id', 'name', 'image');

        $pinterest_list = $this->basic->get_data('pinterest_users_info', $pinterest_where, $pinterest_select, $pinterest_join);

        $pinterest_info = array();
        if(!empty($pinterest_list)) {
            $i = 0;
            foreach($pinterest_list as $value) {

                $pinterest_info[$value['user_name']]['id'] = $value['id'];
                $pinterest_info[$value['user_name']]['user_name'] = $value['user_name'];
                $pinterest_info[$value['user_name']]['name'] = $value['name'];
                $pinterest_info[$value['user_name']]['image'] = $value['image'];
                $pinterest_info[$value['user_name']]['pinterest_info'][$i]['table_id'] = $value['table_id'];
                $pinterest_info[$value['user_name']]['pinterest_info'][$i]['board_name'] = $value['board_name'];
                $i++;
            }
        }

        // Prepares reddit user data
        $reddit_where = array(
            'where' => array(
                'user_id' => $this->user_id
            )
        );

        $reddit_select = array(
            'username', 'id', 'profile_pic', 'url'
        );

        $reddit_account_list = $this->basic->get_data('reddit_users_info',  $reddit_where, $reddit_select);
        $data['reddit_account_list'] = $reddit_account_list;
        $data['subreddits'] = $this->subRedditList();

        $data['facebook_account_list'] = $existing_account_info;
        $data['pinterest_account_list'] = $pinterest_info;

        $data['twitter_account_list'] = $this->basic->get_data('twitter_users_info', array('where' => array('user_id' => $this->user_id)));
        // $data['tumblr_account_list'] = $this->basic->get_data('tumblr_users_info', array('where' => array('user_id' => $this->user_id)));
        $data['linkedin_account_list'] = $this->basic->get_data('linkedin_users_info', array('where' => array('user_id' => $this->user_id)));
        $data['blogger_account_list'] = $this->basic->get_data('blogger_users_info', array('where' => array('user_id' => $this->user_id)));

        return $data;
    }

    public function subRedditList()
    {
         
        $subRedditListArr = array(
            "0" => "Please select a subreddit",
            "1200isplenty" => "1200isplenty",
            "AdventureTime" => "AdventureTime",
            "Android" => "Android",
            "Android" => "Android",
            "Animated" => "Animated",
            "Anxiety" => "Anxiety",
            "ArcherFX" => "ArcherFX",
            "ArtPorn" => "ArtPorn",
            "AskReddit" => "AskReddit",
            "AskScience" => "AskScience",
            "AskScience" => "AskScience",
            "AskSocialScience" => "AskSocialScience",
            "Baseball" => "Baseball",
            "BetterCallSaul" => "BetterCallSaul",
            "Bitcoin" => "Bitcoin",
            "Bitcoin" => "Bitcoin",
            "BobsBurgers" => "BobsBurgers",
            "Books" => "Books",
            "BreakingBad" => "BreakingBad",
            "Cheap_Meals" => "Cheap_Meals",
            "Classic4chan" => "Classic4chan",
            "DeepIntoYouTube" => "DeepIntoYouTube",
            "Discussion" => "Discussion",
            "DnD" => "DnD",
            "Doctor Who" => "Doctor Who",
            "DoesAnybodyElse" => "DoesAnybodyElse",
            "DunderMifflin" => "DunderMifflin",
            "EDC" => "EDC",
            "Economics" => "Economics",
            "Entrepreneur" => "Entrepreneur",
            "FlashTV" => "FlashTV",
            "FoodPorn" => "FoodPorn",
            "General" => "General",
            "General" => "General",
            "General" => "General",
            "General" => "General",
            "General" => "General",
            "HistoryPorn" => "HistoryPorn",
            "Hockey" => "Hockey",
            "HybridAnimals" => "HybridAnimals",
            "IASIP" => "IASIP",
            "Jokes" => "Jokes",
            "LetsNotMeet" => "LetsNotMeet",
            "LifeProTips" => "LifeProTips",
            "Lifestyle" => "Lifestyle",
            "Linux" => "Linux",
            "LiverpoolFC" => "LiverpoolFC",
            "Netflix" => "Netflix",
            "Occupation" => "Occupation",
            "Offensive_Wallpapers" => "Offensive_Wallpapers",
            "OutOfTheLoop" => "OutOfTheLoop",
            "PandR" => "PandR",
            "Pokemon" => "Pokemon",
            "Seinfeld" => "Seinfeld",
            "Sherlock" => "Sherlock",
            "Soccer" => "Soccer",
            "Sound" => "Sound",
            "SpacePorn" => "SpacePorn",
            "Television" => "Television",
            "TheSimpsons" => "TheSimpsons",
            "Tinder" => "Tinder",
            "TrueDetective" => "TrueDetective",
            "UniversityofReddit" => "UniversityofReddit",
            "YouShouldKnow" => "YouShouldKnow",
            "advice" => "advice",
            "amiugly" => "amiugly",
            "anime" => "anime",
            "apple" => "apple",
            "aquariums" => "aquariums",
            "askculinary" => "askculinary",
            "askengineers" => "askengineers",
            "askengineers" => "askengineers",
            "askhistorians" => "askhistorians",
            "askmen" => "askmen",
            "askphilosophy" => "askphilosophy",
            "askwomen" => "askwomen",
            "bannedfromclubpenguin" => "bannedfromclubpenguin",
            "batman" => "batman",
            "battlestations" => "battlestations",
            "bicycling" => "bicycling",
            "bigbrother" => "bigbrother",
            "biology" => "biology",
            "blackmirror" => "blackmirror",
            "blackpeoplegifs" => "blackpeoplegifs",
            "budgetfood" => "budgetfood",
            "business" => "business",
            "casualiama" => "casualiama",
            "celebs" => "celebs",
            "changemyview" => "changemyview",
            "changemyview" => "changemyview",
            "chelseafc" => "chelseafc",
            "chemicalreactiongifs" => "chemicalreactiongifs",
            "chemicalreactiongifs" => "chemicalreactiongifs",
            "chemistry" => "chemistry",
            "coding" => "coding",
            "college" => "college",
            "comics" => "comics",
            "community" => "community",
            "confession" => "confession",
            "cooking" => "cooking",
            "cosplay" => "cosplay",
            "cosplay" => "cosplay",
            "crazyideas" => "crazyideas",
            "cyberpunk" => "cyberpunk",
            "dbz" => "dbz",
            "depression" => "depression",
            "doctorwho" => "doctorwho",
            "education" => "education",
            "educationalgifs" => "educationalgifs",
            "engineering" => "engineering",
            "entertainment" => "entertainment",
            "environment" => "environment",
            "everymanshouldknow" => "everymanshouldknow",
            "facebookwins" => "facebookwins",
            "facepalm" => "facepalm",
            "facepalm" => "facepalm",
            "fantasy" => "fantasy",
            "fantasyfootball" => "fantasyfootball",
            "firefly" => "firefly",
            "fitmeals" => "fitmeals",
            "flexibility" => "flexibility",
            "food" => "food",
            "funny" => "funny",
            "futurama" => "futurama",
            "gadgets" => "gadgets",
            "gallifrey" => "gallifrey",
            "gamedev" => "gamedev",
            "gameofthrones" => "gameofthrones",
            "geek" => "geek",
            "gentlemanboners" => "gentlemanboners",
            "gifs" => "gifs",
            "girlsmirin" => "girlsmirin",
            "google" => "google",
            "gravityfalls" => "gravityfalls",
            "gunners" => "gunners",
            "hardbodies" => "hardbodies",
            "hardware" => "hardware",
            "harrypotter" => "harrypotter",
            "history" => "history",
            "hockey" => "hockey",
            "houseofcards" => "houseofcards",
            "howto" => "howto",
            "humor" => "humor",
            "investing" => "investing",
            "japanesegameshows" => "japanesegameshows",
            "keto" => "keto",
            "ketorecipes" => "ketorecipes",
            "languagelearning" => "languagelearning",
            "law" => "law",
            "learnprogramming" => "learnprogramming",
            "lectures" => "lectures",
            "lego" => "lego",
            "lifehacks" => "lifehacks",
            "linguistics" => "linguistics",
            "literature" => "literature",
            "loseit" => "loseit",
            "magicTCG" => "magicTCG",
            "marvelstudios" => "marvelstudios",
            "math" => "math",
            "mrrobot" => "mrrobot",
            "mylittlepony" => "mylittlepony",
            "naruto" => "naruto",
            "nasa" => "nasa",
            "nbastreams" => "nbastreams",
            "nosleep" => "nosleep",
            "nutrition" => "nutrition",
            "olympics" => "olympics",
            "onepunchman" => "onepunchman",
            "paleo" => "paleo",
            "patriots" => "patriots",
            "pettyrevenge" => "pettyrevenge",
            "photoshop" => "photoshop",
            "physics" => "physics",
            "pics" => "pics",
            "pizza" => "pizza",
            "podcasts" => "podcasts",
            "poetry" => "poetry",
            "pokemon" => "pokemon",
            "preppers" => "preppers",
            "prettygirls" => "prettygirls",
            "psychology" => "psychology",
            "python" => "python",
            "quotes" => "quotes",
            "rainmeter" => "rainmeter",
            "rateme" => "rateme",
            "reactiongifs" => "reactiongifs",
            "recipes" => "recipes",
            "reddevils" => "reddevils",
            "relationship_advice" => "relationship_advice",
            "rickandmorty" => "rickandmorty",
            "running" => "running",
            "samplesize" => "samplesize",
            "scifi" => "scifi",
            "screenwriting" => "screenwriting",
            "seinfeld" => "seinfeld",
            "self" => "self",
            "sex" => "sex",
            "shield" => "shield",
            "simpleliving" => "simpleliving",
            "soccer" => "soccer",
            "southpark" => "southpark",
            "stockmarket" => "stockmarket",
            "stocks" => "stocks",
            "talesfromtechsupport" => "talesfromtechsupport",
            "tattoos" => "tattoos",
            "teachers" => "teachers",
            "thewalkingdead" => "thewalkingdead",
            "thinspo" => "thinspo",
            "tinyhouses" => "tinyhouses",
            "todayilearned" => "todayilearned",
            "topgear" => "topgear",
            "tumblr" => "tumblr",
            "twinpeaks" => "twinpeaks",
            "twitch" => "twitch",
            "vandwellers" => "vandwellers",
            "vegan" => "vegan",
            "videos" => "videos",
            "wallpaper" => "wallpaper",
            "weathergifs" => "weathergifs",
            "westworld" => "westworld",
            "whitepeoplegifs" => "whitepeoplegifs",
            "wikileaks" => "wikileaks",
            "wikipedia" => "wikipedia",
            "woodworking" => "woodworking",
            "writing" => "writing",
            "wwe" => "wwe",
            "youtube" => "youtube",
            "youtubehaiku" => "youtubehaiku",
            "zombies" => "zombies"
        );

        $subRedditListArr = array_unique($subRedditListArr);

        return $subRedditListArr;
    }

    public function wordpress_settings_self_hosted_load_categories() 
    {
        $this->ajax_check();

        $wp_app_id = (string) $this->input->post('wp_app_id', true);

        if (! $wp_app_id) {
            echo json_encode([
                'status' => false,
                'message' => $this->lang->line('Unable to update categories'),
            ]);

            exit;
        }

        $where = [
            'where' => [
                'id' => $wp_app_id,
                'user_id' => $this->user_id,
                'status' => '1',
            ],
        ];

        $select = [
            'domain_name',
            'user_key',
            'authentication_key',
        ];

        $result = $this->basic->get_data('wordpress_config_self_hosted', $where, $select, '', 1);

        if (1 != count($result)) {
            echo json_encode([
                'status' => false,
                'message' => $this->lang->line('Unable to update categories'),
            ]);

            exit;
        }

        $app = $result[0];


        $data = [
            'domain_name' => $app['domain_name'],
            'user_key' => $app['user_key'],
            'authentication_key' => $app['authentication_key'],
            'category' => true,
        ];

        $this->load->library('wordpress_self_hosted');

        $response = '';

        try {
            $response = $this->wordpress_self_hosted->get_categories_wordpress_self_hosted($data);
        } catch(\Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $this->lang->line('Unable to update categories. Please check you blog url, user key, or authentication key'),
            ]);

            exit;
        }

        if ($response) {
            $this->basic->update_data(
                'wordpress_config_self_hosted', 
                ['id' => $wp_app_id, 'user_id' => $this->user_id], 
                ['blog_category' => $response]
            );

            if ($this->db->affected_rows() > 0) {
                echo json_encode([
                    'status' => true,
                    'message' => $this->lang->line('Your blog categories have been updated successfully'),
                ]);

                exit;
            }

            echo json_encode([
                'status' => true,
                'message' => $this->lang->line('Your blog categories are up-to-date'),
            ]);

            exit;
        } else {
            echo json_encode([
                'status' => false,
                'message' => $this->lang->line('Failed to pull categories from your blog'),
            ]);

            exit;
        }
    }   

    /**
     * Generates random key used for authentication key
     */
    private function generate_authentication_key() 
    {
        $random_string = $this->get_client_ip() . mt_rand() . mt_rand() . mt_rand() . microtime(true);
        return md5($random_string);
    }

    /**
     * Function to get the client IP address
     *
     * Credits goes to https://stackoverflow.com/a/15699240
     */
    private function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    } 

    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_wordpress()
    {
        $this->ajax_check();
        $this->csrf_token_check();
        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('wordpress_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));

    }


    public function change_app_status_wordpress()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('wordpress_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('wordpress_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('wordpress_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Wordpress section ends */


    /**
     * Tumblr section starts
     */
    public function tumblr_settings()
    {
        $data['page_title'] = $this->lang->line('Tumblr App Settings');
        $data['title'] = $this->lang->line('Tumblr App Settings');
        $data['body'] = 'admin/social_apps/tumblr_app_settings';

        $this->_viewcontroller($data);
    }


    public function tumblr_settings_data()
    {
        $this->ajax_check();
        $search_value = $_POST['search']['value'];
        $display_columns = array("#","CHECKBOX",'id', 'app_name', 'consumer_id', 'consumer_secret', 'status', 'action');
        $search_columns = array('app_name', 'consumer_id');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom="user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }


        $table="tumblr_config";
        $this->db->where($where_custom);
        $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
        $this->db->where($where_custom);
        $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join='',$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        $i=0;
        $base_url=base_url();
        foreach ($info as $key => $value) 
        {
            if($this->is_demo == '1')
            $info[$i]['client_secret'] = "XXXXXXXXXXX";

            if ($info[$i]['status'] == '1') {

                $info[$i]['status'] = '<span class="badge badge-status text-success"><i class="fa fa-check-circle green"></i> Active</span>';
            }
            else {

                $info[$i]['status'] = '<span class="badge badge-status text-danger"><i class="fa fa-check-circle red"></i> Inactive</span>';
            }


            $info[$i]['action'] = "";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<div style='min-width:130px'><a href='".base_url('social_apps/edit_tumblr_settings/').$value['id']."' class='btn btn-outline-warning btn-circle' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Edit APP Settings')."'><i class='fas fa-edit'></i></a> ";
            
            if($this->is_demo != '1')
            $info[$i]['action'] .= "<a href='#'  table_id='".$value['id']."' class='btn btn-outline-primary btn-circle change_state' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Change the state of this app.')."'><i class='fas fa-exchange-alt'></i></a> <a href='#' csrf_token='".$this->session->userdata('csrf_token_session')."' class='btn btn-outline-danger btn-circle delete_app' table_id='".$value['id']."' data-toggle='tooltip' data-placement='top' title='".$this->lang->line('Delete this APP')."'><i class='fas fa-trash-alt'></i></a></div>";

            $info[$i]["action"] .="<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
            $i++;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }


    public function add_tumblr_settings()
    {
        $data['table_id'] = 0;
        $data['tumblr_settings'] = array();
        $data['page_title'] = $this->lang->line('Tumblr App Settings');
        $data['title'] = $this->lang->line('Tumblr App Settings');
        $data['body'] = 'admin/social_apps/tumblr_settings';

        $this->_viewcontroller($data);
    }


    public function edit_tumblr_settings($table_id=0)
    {
        
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }
        if($table_id==0) exit;
        $tumblr_settings = $this->basic->get_data('tumblr_config',array("where"=>array("id"=>$table_id)));
        if (!isset($tumblr_settings[0])) $tumblr_settings = array();
        else $tumblr_settings = $tumblr_settings[0];
        $data['table_id'] = $table_id;
        $data['tumblr_settings'] = $tumblr_settings;
        $data['page_title'] = $this->lang->line('tumblr App Settings');
        $data['title'] = $this->lang->line('tumblr App Settings');
        $data['body'] = 'admin/social_apps/tumblr_settings';

        $this->_viewcontroller($data);
    }


    public function tumblr_settings_update_action()
    {
        if($this->is_demo == '1')
        {
            echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
            exit();
        }

        if (!isset($_POST)) exit;


        $this->form_validation->set_rules('consumer_id', $this->lang->line("Consumer ID"), 'trim|required');
        $this->form_validation->set_rules('consumer_secret', $this->lang->line("Consumer Secret"), 'trim|required');
        $table_id = $this->input->post('table_id',true);

        if ($this->form_validation->run() == FALSE) 
        {
            if($table_id == 0) $this->add_tumblr_settings();
            else $this->edit_tumblr_settings($table_id);
        }
        else {

            $this->csrf_token_check();

            $insert_data['app_name'] = strip_tags($this->input->post('app_name',true));
            $insert_data['consumer_id'] = strip_tags($this->input->post('consumer_id',true));
            $insert_data['consumer_secret'] = strip_tags($this->input->post('consumer_secret',true));
            $insert_data['user_id'] = $this->user_id;

            
            $status = $this->input->post('status');
            if($status=='') $status='0';
            else {
                $this->basic->update_data('tumblr_config', '', array('status' => '0'));
            }
            $insert_data['status'] = $status;

            $facebook_settings = $this->basic->get_data('tumblr_config');

            if ($table_id != 0) {
                $this->basic->update_data('tumblr_config', array('id' => $table_id,"user_id"=>$this->user_id), $insert_data);
            }
            else 
                $this->basic->insert_data('tumblr_config', $insert_data);

            $this->session->set_flashdata('success_message', '1');
            redirect(base_url('social_apps/tumblr_settings'),'location');
        }
    }


    /**
     *                  incomplete
     * complete it after importing user & campaign creating
     */
    public function delete_app_tumblr()
    {
        $this->ajax_check();
        $this->csrf_token_check();
        $app_table_id = $this->input->post('app_table_id', true);
        $this->basic->delete_data('tumblr_config', array('id' => $app_table_id, 'user_id' => $this->user_id));
        echo json_encode(array('status' => '1', 'message' => $this->lang->line("App has deleted successfully.")));
    }


    public function change_app_status_tumblr()
    {
        $this->ajax_check();

        $app_table_id = $this->input->post('app_table_id', true);
        $app_info = $this->basic->get_data('tumblr_config', array('where' => array('id' => $app_table_id, 'user_id' => $this->user_id)));

        if (count($app_info) > 0) {

            if ($app_info[0]['status'] == '0') { 
                $status_to_be_changed = '1';
                $this->basic->update_data('tumblr_config', '', array('status' => '0'));
            } else {
                $status_to_be_changed = '0';
            }

            $this->basic->update_data('tumblr_config', array('id' => $app_table_id, 'user_id' => $this->user_id), array('status' => $status_to_be_changed));

            echo json_encode(array('status' => '1', 'message' => $this->lang->line("App status changed successfully.")));exit;
        } else {
            echo json_encode(array('status' => '0', 'message' => $this->lang->line("Sorry, no information is found for this app.")));
        }
    }
    /* Tumblr section ends ffghfgh*/



}

