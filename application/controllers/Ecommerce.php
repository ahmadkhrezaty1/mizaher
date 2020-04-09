<?php
require_once("application/controllers/Home.php"); // loading home controller

class Ecommerce extends Home
{    
  public $currency_icon;
  public function __construct()
  {
      parent::__construct();
      $this->load->helpers(array('ecommerce_helper'));

      $function_name=$this->uri->segment(2);
      // $public_functions = array("order","product","store","cart","update_cart_item","update_cart","apply_coupon","proceed_checkout","paypal_action","paypal_action_main","stripe_action","cod_action","manual_action","my_orders","my_orders_data","addtional_info_modal_content","manual_payment_download_file","handle_attachment","manual_payment_display_attachment");
      $private_functions = array("","index","store_list","copy_url","order_list","change_payment_status","order_list_data","reminder_send_status_data","reminder_response","add_store","add_store_action","edit_store","edit_store_action","product_list","product_list_data","delete_store","add_product","add_product_action","edit_product","edit_product_action","delete_product","payment_accounts","payment_accounts_action","attribute_list","attribute_list_data","ajax_create_new_attribute","ajax_get_attribute_update_info","ajax_update_attribute","delete_attribute","category_list","category_list_data","ajax_create_new_category","ajax_get_category_update_info","ajax_update_category","delete_category","coupon_list","coupon_list_data","add_coupon","add_coupon_action","edit_coupon","edit_coupon_action","delete_coupon","upload_product_thumb","delete_product_thumb","upload_store_logo","delete_store_logo","upload_store_favicon","delete_store_favicon");
      if(in_array($function_name, $private_functions)) 
      {
        if($this->session->userdata('logged_in')!= 1) redirect('home/login', 'location');
        if($this->session->userdata('user_type') != 'Admin' && !in_array(268,$this->module_access)) redirect('home/login', 'location');        
        $this->member_validity();
      }
      $this->currency_icon = $this->currency_icon();
  }

  public function index()
  {
      $this->store_list();
  }

  public function store_list()
  {
      $data['body'] = 'ecommerce/store_list';
      $data['page_title'] = $this->lang->line("Ecommerce Store");
      $data['data_days'] = $data_days = 30;        

      $store_data=$this->basic->get_data("ecommerce_store",array("where"=>array("ecommerce_store.user_id"=>$this->user_id)),'ecommerce_store.*,page_name,page_profile,facebook_rx_fb_page_info.page_id as fb_page_id',array('facebook_rx_fb_page_info'=>"facebook_rx_fb_page_info.id=ecommerce_store.page_id,left"),'',$start=NULL,$order_by="store_name ASC");
      
      $default_store_id  = isset($store_data[0]['id']) ? $store_data[0]['id'] : "";
   
     
      $store_id = $this->input->post('store_id');
      if($store_id =="" && $this->session->userdata("ecommerce_selected_store")=="")  $store_id = $default_store_id;

      if($store_id!="") $this->session->set_userdata("ecommerce_selected_store",$store_id);

      $store_id = $this->session->userdata("ecommerce_selected_store");

      $current_store_data = $this->get_current_store_data();
      $default_store_name = isset($current_store_data['store_name']) ? $current_store_data['store_name'] : "";
      $default_store_unique_id = isset($current_store_data['store_unique_id']) ? $current_store_data['store_unique_id'] : "";
      $default_store_title_display = !empty($default_store_unique_id) ? "<a target='_BLANK' href='".base_url("ecommerce/store/".$default_store_unique_id)."'>".$default_store_name."</a>" : "";
      $this->session->set_userdata("ecommerce_selected_store_title_display",$default_store_title_display);
      $this->session->set_userdata("ecommerce_selected_store_title",$default_store_name);

      $from_date = $this->input->post('from_date');
      $to_date = $this->input->post('to_date');
      $currency = $this->input->post('currency');

      if($to_date=='') $to_date = date("Y-m-d");
      if($from_date=='') $from_date = date("Y-m-d",strtotime("$to_date - ".$data_days." days"));

      $ecommerce_config = $this->get_ecommerce_config();
      if($this->input->post('from_date')=="") $from_date=$from_date." 00:00:00";
      if($this->input->post('to_date')=="") $to_date=$to_date." 23:59:59";
      if($this->input->post('currency')=="") $currency= isset($ecommerce_config['currency']) ? $ecommerce_config['currency'] : "USD";

      $this->session->set_userdata("ecommerce_from_date",$from_date);
      $this->session->set_userdata("ecommerce_to_date",$to_date);
      $this->session->set_userdata("ecommerce_currency",$currency);


      $where_simple2=array();
      $where_simple2['ecommerce_cart.currency'] = $currency;
      $where_simple2['ecommerce_cart.store_id'] = $store_id;
      $where_simple2['ecommerce_cart.user_id'] = $this->user_id;
      $where_simple2['ecommerce_cart.updated_at >='] = $from_date;
      $where_simple2['ecommerce_cart.updated_at <='] = $to_date;
      $where2  = array('where'=>$where_simple2);
      $select2 = array("ecommerce_cart.*","first_name","last_name","full_name","profile_pic","email","image_path");  
      $table2 = "ecommerce_cart";
      $join2 = array('messenger_bot_subscriber'=>"messenger_bot_subscriber.subscribe_id=ecommerce_cart.subscriber_id,left");
      $cart_data = $this->basic->get_data($table2,$where2,$select2,$join2,$limit2='',$start2='',$order_by2='ecommerce_cart.updated_at desc');
       
      $i=0;
      if(isset($store_data[$i]))
      {
        $store_data[$i]["page_name"] = "<a data-toggle='tooltip' data-original-title='".$this->lang->line('Visit Page')."' target='_BLANK' href='https://facebook.com/".$store_data[$i]["fb_page_id"]."'>".$store_data[$i]["page_name"]."</a>";

        $store_data[$i]['created_at'] = date('jS F y', strtotime($store_data[$i]['created_at']));
      }       

      $data["store_data"] = $store_data;

      $data["cart_data"] = $cart_data;
      // $data['country_names'] = $this->get_country_names();
      $data['currency_icons'] = $this->currency_icon();
      $data['product_list'] = $this->get_product_list_array($store_id);
      $data['top_products'] = $this->basic->get_data("ecommerce_cart_item",array("where"=>array("store_id"=>$store_id,"updated_at >="=>$from_date,"updated_at <="=>$to_date)),"sum(quantity) as sales_count,product_id",$join='',$limit='10',$start=NULL,$order_by='sales_count desc',$group_by='product_id');
      $data['ecommerce_config'] = $this->get_ecommerce_config();
      $data['currecny_list_all'] = $this->currecny_list_all();
      $data['ecommerce_config'] = $ecommerce_config;
      $this->_viewcontroller($data);
  }

  public function copy_url($store_id=0)
  {
    $data['product_list'] = $this->get_product_list_array($store_id);    
    $data['category_list'] = $this->get_category_list();
    $data['current_store_data'] = $this->get_current_store_data();
    $data['body'] = "ecommerce/copy_url";
    $data['iframe'] = "1";
    $this->_viewcontroller($data);
  }

  public function store($store_unique_id=0)
  {
    if($store_unique_id==0) exit();
    $subscriber_id = $this->input->get("subscriber_id",true); // if loaded via webview then we will get this
    $sort_by = $this->input->post("sort_by",true);      
    $search = $this->input->post("search",true);      
    $category_id = $this->input->post("category_id",true);

    $this->session->set_userdata('search_search',$search);
    $this->session->set_userdata('search_sort_by',$sort_by);
    $this->session->set_userdata('search_category_id',$category_id);

    if($subscriber_id=="") // means it's being loaded inside xerochat admin panel
    {
      if($this->session->userdata('logged_in')!= 1)
      {
        echo '<br/><h1 style="text-align:center">'.$this->lang->line("Access Forbidden.").'</h1>';
        exit();
      }
    }

    $where_simple = array("ecommerce_store.store_unique_id"=>$store_unique_id,"ecommerce_store.status"=>'1');
    if($subscriber_id=="") $where_simple['ecommerce_store.user_id'] = $this->user_id;
    $where = array('where'=>$where_simple);
    $store_data = $this->basic->get_data("ecommerce_store",$where);

    if(!isset($store_data[0]))
    {
      echo '<br/><h1 style="text-align:center">'.$this->lang->line("Store not found.").'</h1>';
      exit();
    }
    $store_id = $store_data[0]['id'];
    $user_id = $store_data[0]['user_id'];

    $fb_app_id = $this->get_app_id();
    $data = array('body'=>"ecommerce/store_single","page_title"=>$store_data[0]['store_name']." | ".$this->lang->line("Products"),"fb_app_id"=>$fb_app_id,"favicon"=>base_url('upload/ecommerce/'.$store_data[0]['store_favicon']));

    $order_by = "product_name ASC";
    $default_where = array();
    if($sort_by=="discount") $default_where['sell_price !='] = '0';
    else $order_by = $sort_by;

    if($search!='') $default_where['product_name'] = $search;
    if($category_id!='') $default_where['category_id'] = $category_id;

    $data["store_data"] = $store_data[0];
    $data["product_list"] = $this->get_product_list_array($store_id,$default_where,$order_by);
    $data["category_list"] = $this->get_category_list($store_id);
    $data["attribute_list"] = $this->get_attribute_list($store_id);
    // $data['country_names'] = $this->get_country_names();
    $data['currency_icons'] = $this->currency_icon();
    $data['ecommerce_config'] = $this->get_ecommerce_config($user_id);
    $data["sort_dropdown"] = 
    array
    (
      "product_name ASC"=>$this->lang->line("Sort"),
      "discount"=>$this->lang->line("Discount"),
      "visit_count DESC"=>$this->lang->line("Popular"),
      "sales_count DESC"=>$this->lang->line("Top Sale"),
      "original_price DESC"=>$this->lang->line("High Price"),
      "original_price ASC"=>$this->lang->line("Low Price")
    );
    $data['current_cart'] = $this->get_current_cart($subscriber_id,$store_id); 
    $this->load->view('ecommerce/bare-theme', $data);
  }

  public function product($product_id=0)
  {
    if($product_id==0) exit();
    $subscriber_id = $this->input->get("subscriber_id",true); // if loaded via webview then we will get this
    
    if($subscriber_id=="") // means it's being loaded inside xerochat admin panel
    {
      if($this->session->userdata('logged_in')!= 1)
      {
        echo '<br/><h1 style="text-align:center">'.$this->lang->line("Access Forbidden.").'</h1>';
        exit();
      }
    }

    $where_simple = array("ecommerce_product.id"=>$product_id,"ecommerce_product.status"=>"1","ecommerce_store.status"=>"1");
    if($subscriber_id=="") $where_simple['ecommerce_product.user_id'] = $this->user_id;
    $where = array('where'=>$where_simple);
    $join = array('ecommerce_store'=>"ecommerce_product.store_id=ecommerce_store.id,left");  
    $select = array("ecommerce_product.*","store_name","store_unique_id","store_logo","store_favicon");   
    $product_data = $this->basic->get_data("ecommerce_product",$where,$select,$join);

    if(!isset($product_data[0]))
    {
      echo '<br/><h1 style="text-align:center">'.$this->lang->line("Product not found.").'</h1>';
      exit();
    }

    $update_visit_count_sql = "UPDATE ecommerce_product SET visit_count=visit_count+1 WHERE id=".$product_id;
    $this->basic->execute_complex_query($update_visit_count_sql);

    $user_id = isset($product_data[0]["user_id"]) ? $product_data[0]["user_id"] : 0;
    $fb_app_id = $this->get_app_id();
    $data = array('body'=>"ecommerce/product_single","page_title"=>$product_data[0]['store_name']." | ".$product_data[0]['product_name'],"fb_app_id"=>$fb_app_id,"favicon"=>base_url('upload/ecommerce/'.$product_data[0]['store_favicon']));
   
    $data["product_data"] = $product_data[0];      
    $data["category_list"] = $this->get_category_list($product_data[0]["store_id"]);
    $data["attribute_list"] = $this->get_attribute_list($product_data[0]["store_id"],true);
    $data['currency_icons'] = $this->currency_icon();
    $data['ecommerce_config'] = $this->get_ecommerce_config($user_id);  
    $data['current_cart'] = $this->get_current_cart($subscriber_id,$product_data[0]['store_id']);  
    $this->load->view('ecommerce/bare-theme', $data);
  }

  public function cart($id=0)
  {      
    $subscriber_id = $this->input->get("subscriber_id",true);      

    if($subscriber_id=="")
    {
      if($this->session->userdata('logged_in')!= 1)
      {
        echo '<div class="alert alert-danger text-center">'.$this->lang->line("Access Forbidden").'</div>';
        exit();
      }
    }

    $this->update_cart($id,$subscriber_id);

    $select2 = array("ecommerce_cart.*","first_name","last_name","full_name","profile_pic","email","image_path","phone_number","user_location","store_name","store_email","store_favicon","store_phone","store_logo","store_address","store_zip","store_country","store_state","store_unique_id");  
    $join2 = array('messenger_bot_subscriber'=>"messenger_bot_subscriber.subscribe_id=ecommerce_cart.subscriber_id,left",'ecommerce_store'=>"ecommerce_store.id=ecommerce_cart.store_id,left");
    $where_simple2 = array("ecommerce_cart.id"=>$id,"action_type !="=>"checkout");
    if($subscriber_id!="") $where_simple2['ecommerce_cart.subscriber_id'] = $subscriber_id;
    else $where_simple2['ecommerce_cart.user_id'] = $this->user_id;
    $where2 = array('where'=>$where_simple2);
    $webhook_data = $this->basic->get_data("ecommerce_cart",$where2,$select2,$join2);

    if(!isset($webhook_data[0]))
    {
      $not_found = $this->lang->line("Order data not found.");
      echo '<br/><h1 style="text-align:center">'.$not_found.'</h1>';
      exit();
    }
    $webhook_data_final = $webhook_data[0];      

    $join = array('ecommerce_product'=>"ecommerce_product.id=ecommerce_cart_item.product_id,left");

    $fb_app_id = $this->get_app_id();

    $data['webhook_data_final'] = $webhook_data_final;
    $data['currency_list'] = $this->currecny_list_all();
    $data['country_names'] = $this->get_country_names();
    $data['currency_icons'] = $this->currency_icon();
    $data['product_list'] = $this->basic->get_data("ecommerce_cart_item",array('where'=>array("cart_id"=>$id)),array("ecommerce_cart_item.*","product_name","thumbnail","taxable"),$join);      
    $data['fb_app_id'] = $fb_app_id;
    $data['favicon'] = base_url('upload/ecommerce/'.$webhook_data_final['store_favicon']);
    $data['page_title'] = $webhook_data_final['store_name']." | ".$this->lang->line("Checkout");
    $data['body'] = "ecommerce/cart";
    $data['subscriber_id'] = $subscriber_id;

    $this->load->view('ecommerce/bare-theme', $data);      
    
  }

  public function order($id=0) // if $id passed means not ajax, it's loading view
  {
    $is_ajax = $this->input->post('is_ajax',true);
    if($id==0) $id = $this->input->post('webhook_id',true);
    $subscriber_id = "";

    if($is_ajax=='1') // ajax call | means it's being loaded inside xerochat admin panel
    {
      $order_title = $this->lang->line("Order");
      $this->ajax_check();
      if($this->session->userdata('logged_in')!= 1)
      {
        echo '<div class="alert alert-danger text-center">'.$this->lang->line("Access Forbidden").'</div>';
        exit();
      }
    }
    else // view load
    {
      $order_title = $this->lang->line("Invoice");
      $subscriber_id = $this->input->get("subscriber_id",true); // if loaded via webview then we will get this
    }

    if($subscriber_id=="") // means it's being loaded inside xerochat admin panel
    {
      if($this->session->userdata('logged_in')!= 1)
      {
        echo '<div class="alert alert-danger text-center">'.$this->lang->line("Access Forbidden").'</div>';
        exit();
      }
    }

    $select2 = array("ecommerce_cart.*","first_name","last_name","full_name","profile_pic","user_location","email","image_path","phone_number","store_name","store_email","store_favicon","store_phone","store_logo","store_address","store_zip","store_city","store_country","store_state","store_unique_id");  
    $join2 = array('messenger_bot_subscriber'=>"messenger_bot_subscriber.subscribe_id=ecommerce_cart.subscriber_id,left",'ecommerce_store'=>"ecommerce_store.id=ecommerce_cart.store_id,left");
    $where_simple2 = array("ecommerce_cart.id"=>$id);
    if($subscriber_id!="") $where_simple2['ecommerce_cart.subscriber_id'] = $subscriber_id;
    else $where_simple2['ecommerce_cart.user_id'] = $this->user_id;
    $where2 = array('where'=>$where_simple2);
    $webhook_data = $this->basic->get_data("ecommerce_cart",$where2,$select2,$join2);

    if(!isset($webhook_data[0]))
    {
      $not_found = $this->lang->line("Order data not found.");
      if($is_ajax=='1') echo '<div class="alert alert-danger text-center">'.$not_found.'</div>';
      else echo '<br/><h1 style="text-align:center">'.$not_found.'</h1>';
      exit();
    }
    $webhook_data_final = $webhook_data[0];
    $country_names = $this->get_country_names();
    $currency_icons = $this->currency_icon();

    $join = array('ecommerce_product'=>"ecommerce_product.id=ecommerce_cart_item.product_id,left");
    $product_list = $this->basic->get_data("ecommerce_cart_item",array('where'=>array("cart_id"=>$id)),array("ecommerce_cart_item.*","product_name","thumbnail","taxable"),$join);

    $order_date = date("jS M,Y",strtotime($webhook_data_final['updated_at']));      
    $wc_first_name = $webhook_data_final['first_name'];
    $wc_last_name = $webhook_data_final['last_name'];
    $wc_buyer_bill = ($wc_first_name=='') ? $webhook_data_final['full_name'] : $wc_first_name." ".$wc_last_name;
    $confirmation_response = json_decode($webhook_data_final['confirmation_response'],true);
    $currency = $webhook_data_final['currency'];
    $currency_icon = isset($currency_icons[$currency])?$currency_icons[$currency]:'$';
    $wc_email_bill = $webhook_data_final['email'];
    $wc_phone_bill = $webhook_data_final['phone_number'];
    $shipping_cost = $webhook_data_final["shipping"];
    $total_tax = $webhook_data_final["tax"];     
    $checkout_amount  = $webhook_data_final['payment_amount'];
    $coupon_code = $webhook_data_final['coupon_code'];
    $coupon_type = $webhook_data_final['coupon_type'];
    $coupon_amount =  $webhook_data_final['discount'];
    $subtotal =  $webhook_data_final['subtotal'];
    $payment_status = $webhook_data_final['status'];
    
    $payment_method =  $webhook_data_final['payment_method'];
    if($payment_method!='') $payment_method =  $payment_method." ".$webhook_data_final['card_ending'];

    if($payment_status=='pending') $payment_status_badge = "<span class='badge badge-dark text-danger'><i class='fas fa-spinner'></i> ".$this->lang->line("Pending")."</span>";
    else if($payment_status=='approved') $payment_status_badge = "<span class='badge badge-light text-primary'><i class='fas fa-thumbs-up'></i> ".$this->lang->line("Approved")."</span>";
    else if($payment_status=='rejected') $payment_status_badge = "<span class='badge badge-danger'><i class='fas fa-thumbs-down'></i> ".$this->lang->line("Rejected")."</span>";
    else if($payment_status=='shipped') $payment_status_badge = "<span class='badge badge-info'><i class='fas fa-truck'></i> ".$this->lang->line("Shipped")."</span>";
    else if($payment_status=='delivered') $payment_status_badge = "<span class='badge badge-info'><i class='fas fa-truck-loading'></i> ".$this->lang->line("Delivered")."</span>";
    else if($payment_status=='completed') $payment_status_badge = "<span class='badge badge-success'><i class='fas fa-check-circle'></i> ".$this->lang->line("Completed")."</span>";
    
    $order_no =  $webhook_data_final['id'];
    $order_url =  base_url("ecommerce/order/".$order_no);
    
    $buyer_country = isset($country_names[$webhook_data_final["buyer_country"]]) ? ucwords(strtolower($country_names[$webhook_data_final["buyer_country"]])) : $webhook_data_final["buyer_country"];
    $store_country = isset($country_names[$webhook_data_final["store_country"]]) ? ucwords(strtolower($country_names[$webhook_data_final["store_country"]])) : $webhook_data_final["store_country"];
    $buyer_address = $webhook_data_final["buyer_address"]."<br>".$webhook_data_final["buyer_city"]."<br>".$webhook_data_final["buyer_state"]." ".$webhook_data_final["buyer_zip"]."<br>".$buyer_country;
    $store_name = $webhook_data_final['store_name'];
    $store_address = $webhook_data_final["store_address"]."<br>".$webhook_data_final["store_city"]."<br>".$webhook_data_final["store_state"]." ".$webhook_data_final["store_zip"]."<br>".$store_country;
    $store_phone = $webhook_data_final["store_phone"];
    $store_email = $webhook_data_final["store_email"];
    $subscriber_id_database = $webhook_data_final["subscriber_id"];
    $store_unique_id = $webhook_data_final["store_unique_id"];

    $table_bordered = ($is_ajax=='1') ? '' : 'table-bordered';
    $table_data ='
    <div class="table-responsive">
    <table class="table table-striped table-hover table-md '.$table_bordered.'">
      <tbody>
      <tr>
        <th data-width="40">#</th>
        <th class="text-center">'.$this->lang->line("Thumbnail").'</th>
        <th>'.$this->lang->line("Item").'</th>
        <th class="text-center">'.$this->lang->line("Unit Price").'</th>
        <th class="text-center">'.$this->lang->line("Quantity").'</th>
        <th class="text-right">'.$this->lang->line("Price").'</th>
      </tr>';
    $i=0;
    $subtotal_count = 0;
    foreach ($product_list as $key => $value) 
    {        
      $title = isset($value['product_name']) ? $value['product_name'] : "";
      $quantity = isset($value['quantity']) ? $value['quantity'] : 1;
      $price = isset($value['unit_price']) ? $value['unit_price'] : 0;
      $item_total = $price*$quantity;
      $subtotal_count+=$item_total;
      $item_total = $this->two_decimal_place($item_total); 
      $price =  $this->two_decimal_place($price); 
      $image_url = (isset($value['thumbnail']) && !empty($value['thumbnail'])) ? base_url('upload/ecommerce/'.$value['thumbnail']) : base_url('assets/img/example-image.jpg');        
      $permalink = base_url("ecommerce/product/".$value['product_id']);
      $attribute_info = (is_array(json_decode($value["attribute_info"],true))) ? json_decode($value["attribute_info"],true) : array();

      $attribute_query_string_array = array();
      $attribute_query_string = "";
      foreach ($attribute_info as $key2 => $value2) 
      {
        $attribute_query_string_array[]="option".$key2."=".urlencode($value2);
      }
      $attribute_query_string = implode("&", $attribute_query_string_array);
      if(!empty($attribute_query_string_array)) $attribute_query_string = "&quantity=".$quantity."&".$attribute_query_string;

      $attribute_print = "";
      if(!empty($attribute_info))$attribute_print = "<br><small>".implode(', ', array_values($attribute_info)). "</small>";
      if($subscriber_id!='') $permalink.="?subscriber_id=".$subscriber_id.$attribute_query_string;

      $i++;
      $off = $value["coupon_info"];
  		if($off!="") $off.=" ".$this->lang->line("OFF");
        $table_data .='
        <tr>
          <td data-width="40">'.$i.'</td>
          <td class="text-center" width="100px;"><a href="'.$permalink.'"><img src="'.$image_url.'" style="width:80px; height:80px;" class="rounded"></a></td>
          <td><a href="'.$permalink.'">'.$title.'</a> <span class="text-warning"> '.$off."</span>".$attribute_print.'</td>
          <td class="text-center">'.$currency_icon.$price.'</td>
          <td class="text-center">'.$quantity.'</td>
          <td class="text-right">'.$currency_icon.$item_total.'</td>
        </tr>';
    }
    $table_data .= '</tbody></table></div>';        

    if($coupon_code=="") $coupon_info = "";
    else $coupon_info = '<div class="section-title">'.$this->lang->line("Coupon Code").'</div><p class="section-lead">'.$coupon_code.'</p>';

    $coupon_info2 = "";
    if($coupon_code!='' && $coupon_type=="fixed cart")
    $coupon_info2 = 
    '<div class="invoice-detail-item">
      <div class="invoice-detail-name">'.$this->lang->line("Discount").'</div>
      <div class="invoice-detail-value">-'.$currency_icon.$this->two_decimal_place($coupon_amount).'</div>
    </div>';

    $tax_info = "";
    if($total_tax>0)
    $tax_info = 
    '<div class="invoice-detail-item">
        <div class="invoice-detail-name">'.$this->lang->line("Tax").'</div>
        <div class="invoice-detail-value">'.$currency_icon.$this->two_decimal_place($total_tax).'</div>
    </div>';

    $shipping_info = "";
    if($shipping_cost>0)
    $shipping_info = 
    '<div class="invoice-detail-item">
        <div class="invoice-detail-name">'.$this->lang->line("Delivery Fee").'</div>
        <div class="invoice-detail-value">'.$currency_icon.$this->two_decimal_place($shipping_cost).'</div>
    </div>';


    // $coupon_code." (".$currency_icon.$coupon_amount.")";      

    if($webhook_data_final['action_type']!='checkout') $subtotal = $subtotal_count;
    $subtotal = $this->two_decimal_place($subtotal);
    $checkout_amount = $this->two_decimal_place($checkout_amount);
    $coupon_amount = $this->two_decimal_place($coupon_amount);

    if($subscriber_id=='')
    {
      $wc_buyer_bill_formatted = '<a href="'.base_url('subscriber_manager/bot_subscribers/'.$subscriber_id_database).'">'.$wc_buyer_bill.'</a>';
      $store_name_formatted = '<a href="'.base_url('ecommerce/store/'.$store_unique_id).'">'.$store_name.'</a>';
      $store_image = ($webhook_data_final['store_logo']!='' && $is_ajax!='1') ? '<div class="col-lg-12 text-center"><a href="'.base_url('ecommerce/store/'.$store_unique_id).'"><img src="'.base_url("upload/ecommerce/".$webhook_data_final['store_logo']).'" style="height:50px"></a><hr></div>':'';
    }
    else 
    {
      $wc_buyer_bill_formatted = $wc_buyer_bill;
      $store_name_formatted = '<a href="'.base_url('ecommerce/store/'.$store_unique_id."?subscriber_id=".$subscriber_id).'">'.$store_name.'</a>';
      $store_image = ($webhook_data_final['store_logo']!='' && $is_ajax!='1') ? '<div class="col-lg-12 text-center"><a href="'.base_url('ecommerce/store/'.$store_unique_id."?subscriber_id=".$subscriber_id).'"><img src="'.base_url("upload/ecommerce/".$webhook_data_final['store_logo']).'" style="height:50px"></a><hr></div>':'';
    }

    if($is_ajax=='1') $order_details = '<h4>'.$order_title.' #<a href="'.$order_url.'">'.$order_no.'</a></h4>';
    else $order_details = '<h4>'.$order_title.' #'.$order_no.'</h4>';


    $output = "";
    $after_checkout_details ="";
    $payment_method_deatils = 
    '<div class="section-title">'.$this->lang->line("Payment Status").'</div>
     <p class="section-lead">'.$payment_method.' '.$payment_status_badge.'</p>
     ';
    $coupon_details = '<div class="col-7">'.$payment_method_deatils.'</div>';
    if($webhook_data_final['action_type']=='checkout')
    {
      $after_checkout_details = 
      $coupon_info2.$shipping_info.$tax_info.'
      <hr class="mt-2 mb-2">
      <div class="invoice-detail-item">
        <div class="invoice-detail-name">'.$this->lang->line("Total").'</div>
        <div class="invoice-detail-value invoice-detail-value-lg">'.$currency_icon.$checkout_amount.'</div>
      </div>';

      $coupon_details =
      '<div class="col-7">
        '.$coupon_info.'
        '.$payment_method_deatils.'
        <div class="section-title">'.$this->lang->line("Deliver to").'</div>
        <p class="section-lead ml-0">
        	'.$webhook_data_final['buyer_first_name']." ".$webhook_data_final['buyer_last_name']."<br>".$buyer_address."<br>".$webhook_data_final['buyer_email']."<br>".$webhook_data_final['buyer_mobile']."<br>".'
        </p>  
      </div>';
    }
    $padding = ($is_ajax=='1') ? "padding:40px" : "padding:40px 25px";

    $user_loc = "";
    $tmp = json_decode($webhook_data_final['user_location'],true);
    if(is_array($tmp)) 
    {
      $user_country = isset($tmp['country']) ? $tmp['country'] : "";
      $country_name = isset($country_names[$user_country]) ? ucwords(strtolower($country_names[$user_country])) : $user_country;
      $tmp["country"] = $country_name;
      if(isset($tmp["state"]) && isset($tmp["zip"])) 
      {
        $tmp["state"] = $tmp["state"]." ".$tmp["zip"];
        unset($tmp["zip"]);
      }
      $user_loc = implode('<br>', $tmp);
    }      

    $pay_message = "";
    if($subscriber_id!="")
    {
  	  $payment_action = $this->input->get("action",true);
  	  if($payment_action!="")
  	  {
  	  	if($payment_action=="success")
  	  	{
  	  		$invoice_link = base_url("ecommerce/order/".$order_no."?subscriber_id=".$subscriber_id);
  	  		$message = "<i class='fas fa-check-circle'></i> ".$this->lang->line('Your payment has been received successfully and order will be processed soon. It may take few seconds to change your payment status depending on PayPal request. You can see your order status from this page')." : <br><a href='".$invoice_link."'>".$invoice_link."</a>";
  	  		$this->session->set_userdata('payment_status','1');
        	$this->session->set_userdata('payment_status_message',$message);
  	  	}
  	  	else if($payment_action=="cancel")
  	  	{
  	  		$message = "<i class='fas fa-times-circle'></i> ".$this->lang->line('Payment was failed to process and the cart has been cancelled.');
  	  		$this->session->set_userdata('payment_status','0');
        	$this->session->set_userdata('payment_status_message',$message);
  	  	}
  	  }
    	  
	  	if($this->session->userdata('payment_status')=='1')
		  $pay_message = "<div class='alert alert-success text-center'>".$this->session->userdata('payment_status_message')."</div>";
		  else if($this->session->userdata('payment_status')=='0')
		  $pay_message = "<div class='alert alert-danger text-center'>".$this->session->userdata('payment_status_message')."</div>";
		    
		  $this->session->unset_userdata('payment_status');
		  $this->session->unset_userdata('payment_status_message');  	      	  
    }

    $hide_order = '';
    $no_order = '';
    if(count($product_list)==0)
    {
      $hide_order='d-none';
      $no_order = '
      <div class="col-12">
        <div class="empty-state">
          <img class="img-fluid" style="height: 300px" src="'.base_url('assets/img/drawkit/drawkit-full-stack-man-colour.svg').'" alt="image">
           <h2 class="mt-0">'.$this->lang->line("Cart is empty").'</h2>
           <p class="lead">'.$this->lang->line("There is no product added to cart.").'</p>
        </div>
      </div>
      ';
    }

    $output .= 
    '
      <section class="section">
        '.$pay_message.'
        <div class="section-body">
          <div class="invoice" style="border:1px solid #dee2e6;'.$padding.'">
            <div class="invoice-print">
              <div class="row">
                '.$store_image.'
                '.$no_order.'
                <div class="col-lg-12 '.$hide_order.'">
                  <div class="invoice-title">
                    '.$order_details.'
                    <div class="invoice-number" style="margin-top:-35px;">'.$order_date.'</div>
                  </div>
                  <br>
                  <div class="row">
                    <div class="col-6">
                      <address>
                        <strong>'.$this->lang->line("Bill to").':</strong><br><br>
                        '.$wc_buyer_bill_formatted.'
                        <br>                        
                        '.$user_loc.'<br>                     
                        '.$wc_email_bill.'<br>                         
                        '.$wc_phone_bill.'
                      </address>
                    </div>
                    <div class="col-6 text-right">
                      <address>
                        <strong>'.$this->lang->line("Seller").':</strong><br><br>
                        '.$store_name_formatted.'<br>
                        '.$store_address.'<br>                  
                        '.$store_email.'<br>
                        '.$store_phone.'
                      </address>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row '.$hide_order.'">
                <div class="col-md-12">
                  <div class="section-title">'.$this->lang->line("Order Summary").'</div>
                  '.$table_data.'
                  <div class="row">
                    '.$coupon_details.'
                    <div class="col-5 text-right">
                      <div class="invoice-detail-item"  style="margin-top: 20px;">
                        <div class="invoice-detail-name">'.$this->lang->line("Subtotal").'</div>
                        <div class="invoice-detail-value">'.$currency_icon.$subtotal.'</div>
                      </div>
                      '.$after_checkout_details.'
                    </div>
                  </div>
                </div>
              </div>
            </div>              
          </div>
        </div>
      </section>
    ';

    if($webhook_data_final['action_type']=='checkout' && $is_ajax=='1')
    {
      $messenger_confirmation_badge = '<span class="badge badge-light badge-pill">'.$this->lang->line("Unknown").'</span>';
      if(isset($confirmation_response['messenger']))
      {
        if(isset($confirmation_response['messenger']['status']) && $confirmation_response['messenger']['status']=='1') $messenger_confirmation_badge = '<span data-toggle="tooltip" title="'.htmlspecialchars($confirmation_response['messenger']['response']).'" class="badge badge-success badge-pill">'.$this->lang->line("Sent").'</span>';
        else if(isset($confirmation_response['messenger']['status']) && $confirmation_response['messenger']['status']=='0') $messenger_confirmation_badge = '<span data-toggle="tooltip" title="'.htmlspecialchars($confirmation_response['messenger']['response']).'" class="badge badge-danger badge-pill">'.$this->lang->line("Error").'</span>';
        else $messenger_confirmation_badge = '<span class="badge badge-dark badge-pill">'.$this->lang->line("Not Set").'</span>';
      }
      $messenger_li = '<li class="list-group-item d-flex justify-content-between align-items-center">
                        '.$this->lang->line("Messenger Confirmation").'
                        '.$messenger_confirmation_badge.'
                      </li>';

      $sms_li = $email_li = "";
      if($this->session->userdata('user_type') == 'Admin' || in_array(264,$this->module_access)) 
      {
        $sms_confirmation_badge = '<span class="badge badge-light badge-pill">'.$this->lang->line("Unknown").'</span>';
        if(isset($confirmation_response['sms']))
        {
          if(isset($confirmation_response['sms']['status']) && $confirmation_response['sms']['status']=='1') $sms_confirmation_badge = '<span data-toggle="tooltip" title="'.htmlspecialchars($confirmation_response['sms']['response']).'" class="badge badge-success badge-pill">'.$this->lang->line("Sent").'</span>';
          else if(isset($confirmation_response['sms']['status']) && $confirmation_response['sms']['status']=='0') $sms_confirmation_badge = '<span data-toggle="tooltip" title="'.htmlspecialchars($confirmation_response['sms']['response']).'" class="badge badge-danger badge-pill">'.$this->lang->line("Error").'</span>';
          else $sms_confirmation_badge = '<span class="badge badge-dark badge-pill">'.$this->lang->line("Not Set").'</span>';
        }
        $sms_li = '<li class="list-group-item d-flex justify-content-between align-items-center">
                    '.$this->lang->line("SMS Confirmation").'
                    '.$sms_confirmation_badge.'
                  </li>';
      }

      if($this->session->userdata('user_type') == 'Admin' || in_array(263,$this->module_access)) 
      {
        $email_confirmation_badge = '<span class="badge badge-light badge-pill">'.$this->lang->line("Unknown").'</span>';
        if(isset($confirmation_response['email']))
        {
          if(isset($confirmation_response['email']['status']) && $confirmation_response['email']['status']=='1') $email_confirmation_badge = '<span data-toggle="tooltip" title="'.htmlspecialchars($confirmation_response['email']['response']).'" class="badge badge-success badge-pill">'.$this->lang->line("Sent").'</span>';
          else if(isset($confirmation_response['email']['status']) && $confirmation_response['email']['status']=='0') $email_confirmation_badge ='<span data-toggle="tooltip" title="'.htmlspecialchars($confirmation_response['email']['response']).'" class="badge badge-danger badge-pill">'. $this->lang->line("Error").'</span>';
          else $email_confirmation_badge = '<span class="badge badge-dark badge-pill">'.$this->lang->line("Not Set").'</span>';
        }
        $email_li = '<li class="list-group-item d-flex justify-content-between align-items-center">
                      '.$this->lang->line("Email Confirmation").'
                      '.$email_confirmation_badge.'
                    </li>';
      }
      $output .=  
      '
        <section class="section">
          <div class="section-body">
            <div class="invoice" style="border:1px solid #dee2e6;">
              <div class="invoice-print">
                <div class="row">
                  <div class="col-12">
                    <div class="invoice-title">
                      <h4>'.$this->lang->line("Checkout Confirmation").'</h4>
                      <div class="invoice-number"></div>
                    </div>
                    <hr>
                    <ul class="list-group">
                      '.$messenger_li.$sms_li.$email_li.'
                    </ul>
                  </div>
                </div>              
              </div>
            </div>
          </div>
        </section>
      ';
      $output .=  "<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
    }

    $output.="<style>.section .section-title{margin:20px 0 20px 0;}</style>";

    if($is_ajax=='1') echo $output;
    else
    {
      $fb_app_id = $this->get_app_id();
      $data = array('output'=>$output,"page_title"=>$store_name." | Order# ".$order_no,"fb_app_id"=>$fb_app_id,"favicon"=>base_url('upload/ecommerce/'.$webhook_data_final['store_favicon']));
      $this->load->view('ecommerce/bare-theme', $data); 
    }
    
  }

  public function delete_cart_item()
  {
    $id = $this->input->post("id");
    $cart_id = $this->input->post("cart_id");
    $subscriber_id = $this->input->post("subscriber_id");
    $cart_data = $this->valid_cart_data($cart_id,$subscriber_id,"ecommerce_cart.id");
    if(isset($cart_data[0]))
    {
      $this->basic->delete_data("ecommerce_cart_item",array("id"=>$id,"cart_id"=>$cart_id));
      $this->basic->update_data("ecommerce_cart",array("id"=>$cart_id),array("action_type"=>"remove","updated_at"=>date("Y-m-d H:i:s")));
      echo json_encode(array('status'=>'1','message'=>$this->lang->line("Item deleted successfully.")));
    }
    else
    {
      echo json_encode(array('status'=>'0','message'=>$this->lang->line("Order data not found.")));
    }
  }

  public function update_cart_item()
  {
    $this->ajax_check();
    $mydata = json_decode($this->input->post("mydata"),true);
    $product_id = isset($mydata['product_id']) ? $mydata['product_id'] : 0;
    $action = isset($mydata['action']) ? $mydata['action'] : 'add';  // add,remove
    $subscriber_id = isset($mydata['subscriber_id']) ? $mydata['subscriber_id'] : '';
    $attribute_info = isset($mydata['attribute_info']) ? $mydata['attribute_info'] : array();
    $attribute_info_json = json_encode($attribute_info,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

    $message = $cart_url = "";
    if($subscriber_id=='')
    {
      echo json_encode(array('status'=>'0','message'=>$this->lang->line("Subscriber not found.")));
      exit();
    }

    $where_simple = array("ecommerce_product.id"=>$product_id,"ecommerce_product.status"=>"1","ecommerce_store.status"=>"1");
    $where = array('where'=>$where_simple);
    $join = array('ecommerce_store'=>"ecommerce_product.store_id=ecommerce_store.id,left");  
    $select = array("ecommerce_product.*","store_unique_id");   
    $product_data = $this->basic->get_data("ecommerce_product",$where,$select,$join);
    if(!isset($product_data[0]))
    {
      echo json_encode(array('status'=>'0','message'=>$this->lang->line("Product not found.")));
      exit();
    }

    $store_id = $product_data[0]['store_id'];
    $user_id = $product_data[0]['user_id'];
    $original_price = $product_data[0]['original_price'];
    $sell_price = $product_data[0]['sell_price'];
    $store_unique_id = $product_data[0]['store_unique_id'];
    $price = mec_display_price($original_price,$sell_price,'','2');
    $ecommerce_config = $this->get_ecommerce_config($user_id);
    $currency = isset($ecommerce_config['currency']) ? $ecommerce_config['currency'] : "USD";

    $cart_data = $this->basic->get_data("ecommerce_cart",array('where'=>array("ecommerce_cart.subscriber_id"=>$subscriber_id,"ecommerce_cart.store_id"=>$store_id,"action_type!="=>"checkout")));
    $cart_item_data = array();
    if(isset($cart_data[0])) // already have a cart running entry
    {
      $cart_id = isset($cart_data[0]['id']) ? $cart_data[0]['id'] : 0;
      $cart_item_data = $this->basic->get_data("ecommerce_cart_item",array("where"=>array("cart_id"=>$cart_id)));
    }

    if(!isset($cart_data[0]) && $action=="remove") // no cart, no removing, securty in case
    {
      echo json_encode(array('status'=>'0','message'=>$this->lang->line("Cart not found.")));
      exit();
    }

    $curdate = date("Y-m-d H:i:s");
    if($action=='add') // add item
    {
      if(!isset($cart_data[0])) // new cart, create cart first
      {
        $insert_data =array
        (
          'user_id' => $user_id,
          'store_id' => $store_id,
          'subscriber_id' => $subscriber_id,
          'currency' => $currency,
          'status' => "pending",
          'ordered_at' => $curdate,
          'payment_method'=>'',
          'updated_at' => $curdate,
          'initial_date' => $curdate,
          'confirmation_response' => '[]'
        );         
        $this->basic->insert_data("ecommerce_cart",$insert_data);
        $cart_id = $this->db->insert_id();         
      }

      $sql="INSERT INTO ecommerce_cart_item
      (
        store_id,cart_id,product_id,unit_price,quantity,attribute_info,updated_at
      ) 
      VALUES 
      (
        '$store_id','$cart_id','$product_id','$price','1','$attribute_info_json','$curdate'
      )
      ON DUPLICATE KEY UPDATE 
      unit_price='$price',quantity=quantity+1,updated_at='$curdate'; ";
      $this->basic->execute_complex_query($sql);

      $message = $this->lang->line("Product has been added to cart successfully.");
    }
    else // remove item
    {
        $sql="UPDATE ecommerce_cart_item SET unit_price='$price',quantity=quantity-1,updated_at='$curdate' WHERE cart_id='$cart_id' AND product_id='$product_id' AND attribute_info='$attribute_info_json'; ";
        $this->basic->execute_complex_query($sql);
        $message = $this->lang->line("Product has been removed from cart successfully.");       
    }

    $this_cart_item = array("quantity"=>"1");
    if(!empty($attribute_info))
    {
      $this_cart_item_data = $this->basic->get_data("ecommerce_cart_item",array("where"=>array("cart_id"=>$cart_id,"product_id"=>$product_id,"attribute_info"=>$attribute_info_json)),"quantity");
      if(isset($this_cart_item_data[0])) $this_cart_item = $this_cart_item_data[0];
    }

    $cart_url = base_url("ecommerce/cart/".$cart_id."?subscriber_id=".$subscriber_id);
    $current_cart_data = $this->get_current_cart($subscriber_id,$store_id);
    echo json_encode(array('status'=>'1','cart_url'=>$cart_url,'message'=>$message,"cart_data"=>$current_cart_data,"this_cart_item"=>$this_cart_item));
  }    

  public function update_cart($cart_id=0,$subscriber_id_passed='')
  {     
    if($subscriber_id_passed=='') $subscriber_id = $this->input->get_post("subscriber_id");
    else $subscriber_id = $subscriber_id_passed;

    if($cart_id!=0 && $subscriber_id!='')
    {
      $cart_data = $this->valid_cart_data($cart_id,$subscriber_id);
      if(isset($cart_data[0]))
      {          
        if($cart_data[0]["store_unique_id"]!="") // store availabe and online
        {
          $store_id = $cart_data[0]['store_id'];
          $user_id = $cart_data[0]['user_id'];
          $coupon_code = $cart_data[0]['coupon_code'];
          $tax_percentage = $cart_data[0]['tax_percentage'];
          $shipping_charge = $cart_data[0]['shipping_charge'];

          $product_list = $this->get_product_list_array($store_id);
          $cart_item_data = $this->basic->get_data("ecommerce_cart_item",array('where'=>array("cart_id"=>$cart_id)));
          $ecommerce_config = $this->get_ecommerce_config($user_id);
          $currency = isset($ecommerce_config["currency"]) ? $ecommerce_config["currency"] : "USD";
          $currency_icons = $this->currency_icon();
          $currency_icon = isset($currency_icons[$currency])?$currency_icons[$currency]:'$';   

          $product_list_assoc = array();
          $cart_item_data_assoc = array();

          foreach($product_list as $key => $value) 
          {
            $product_list_assoc[$value["id"]] = $value;
          }

          foreach($cart_item_data as $key => $value) 
          {
            $cart_item_data_assoc[$value["product_id"]] = $value;
          }
          $cart_item_data_new = $cart_item_data_assoc;

          $coupon_data = array();
          if($coupon_code!='') $coupon_data =$this->get_coupon_data($coupon_code,$store_id);
          $coupon_product_ids = isset($coupon_data["product_ids"]) ? $coupon_data["product_ids"] : '0';
          $coupon_product_ids_array = array_filter(explode(',', $coupon_product_ids));
          $free_shipping_enabled = isset($coupon_data["free_shipping_enabled"]) ? $coupon_data["free_shipping_enabled"] : "0";
          $coupon_type = isset($coupon_data["coupon_type"]) ? $coupon_data["coupon_type"] : "";
          $coupon_amount = isset($coupon_data["coupon_amount"]) ? $coupon_data["coupon_amount"] : 0;
          $coupon_code_new = isset($coupon_data["coupon_code"]) ? $coupon_data["coupon_code"] : '';

          $subtotal = 0;
          $taxable_amount = 0;
          $discount = 0;
          $tax = 0;            
          $shipping = 0;
          foreach($cart_item_data as $key => $value)
          {
            $product_id = $value['product_id'];
            if(array_key_exists($product_id, $product_list_assoc))
            {
              $new_price = mec_display_price($product_list_assoc[$product_id]["original_price"],$product_list_assoc[$product_id]["sell_price"],'','2');
             
              $coupon_info = "";

              if(!empty($coupon_data) && $coupon_amount>0 && ($coupon_product_ids=="0" || in_array($product_id, $coupon_product_ids_array)))
              {
                $new_price = $product_list_assoc[$product_id]["original_price"];
                if($coupon_type=="percent")
                {
                  $disc = ($new_price*$coupon_amount)/100;
                  if($disc<0) $disc=0;                    
                  $discount+=$disc;

                  $coupon_info = $coupon_amount."%";

                  $new_price = $new_price-$disc;
                }
                else if($coupon_type=="fixed product")
                {
                   $new_price = $new_price-$coupon_amount;
                   if($new_price<0) $new_price =0;
                   $coupon_info = $currency_icon.$coupon_amount;                     
                   $discount+=$coupon_amount;
                }
              }

              if($new_price!=$value['unit_price']) 
              $this->basic->update_data("ecommerce_cart_item",array("id"=>$value['id']),array("unit_price"=>$new_price,"coupon_info"=>$coupon_info));

              $total_price = $new_price*$value["quantity"];
              $subtotal+=$total_price;

              if($product_list_assoc[$product_id]["taxable"]=='1') $taxable_amount+=$new_price;
            }
            else
            {
              $this->basic->delete_data("ecommerce_cart_item",array("id"=>$value['id']));
            }
          }
          
          if($tax_percentage>0) $tax = ($tax_percentage*$taxable_amount)/100;
          if($free_shipping_enabled=='0') $shipping = $shipping_charge;
          $payment_amount = $subtotal + $shipping + $tax;

          if(!empty($coupon_data) && $coupon_amount>0 && $coupon_type=="fixed cart")
          {
            $discount = $coupon_amount;
            $payment_amount = $payment_amount - $discount;
            if($payment_amount<0) $payment_amount = 0;
          }

          $update_data = array
          (
            "subtotal"=>$subtotal,
            "tax"=>$tax,
            "shipping"=>$shipping,
            "coupon_code"=>$coupon_code_new,
            "discount"=>$discount,
            "coupon_type" =>  $coupon_type,
            "payment_amount"=>$payment_amount,
            "currency"=>$currency
          );
          $this->basic->update_data("ecommerce_cart",array("id"=>$cart_id),$update_data);
        }
        else // store not availabe anymore, delete cart
        {
          $this->basic->delete_data("ecommerce_cart",array("id"=>$cart_id));
          $this->basic->delete_data("ecommerce_cart_item",array("cart_id"=>$cart_id));
        }
      }
    }

  }

  public function apply_coupon()
  {
  	$this->ajax_check();
  	$cart_id = $this->input->post("cart_id");
  	$coupon_code = $this->input->post("coupon_code");
  	$subscriber_id = $this->input->post("subscriber_id");    	

    $select = array("store_id","store_unique_id","coupon_code");
    $cart_data = $this->valid_cart_data($cart_id,$subscriber_id,$select);

    $store_id = 0;
    if(isset($cart_data[0]) && $cart_data[0]["store_id"]!="")
    {
    	$store_id = $cart_data[0]["store_id"];
    	$xcoupon_code = $cart_data[0]["coupon_code"];
    	if($coupon_code=="")
    	{
    		if($xcoupon_code!="")
    		{
    			$this->basic->update_data("ecommerce_cart",array("id"=>$cart_id,"subscriber_id"=>$subscriber_id),array("coupon_code"=>$coupon_code));
    			echo json_encode(array('status'=>'1','message'=>$this->lang->line('Coupon has been removed successfully.')));
    		}
    		else echo json_encode(array('status'=>'0','message'=>$this->lang->line('No coupon code provided.')));
    		exit();    	
    	}
    }
    else
    {
    	echo json_encode(array('status'=>'0','message'=>$this->lang->line('Order data not found.')));
    	exit();
    }

  	$coupon_data =$this->get_coupon_data($coupon_code,$store_id);
  	if(!empty($coupon_data)) 
  	{
  		$this->basic->update_data("ecommerce_cart",array("id"=>$cart_id),array("coupon_code"=>$coupon_code));
  		echo json_encode(array('status'=>'1','message'=>$this->lang->line('Coupon has been applied successfully.')));
  	}
  	else echo json_encode(array('status'=>'0','message'=>$this->lang->line('Invalid coupon code.')));

  }

  public function proceed_checkout()
  {
  	$this->ajax_check();
  	$mydata = json_decode($this->input->post("mydata"),true);

  	$cart_id = isset($mydata["cart_id"]) ? $mydata["cart_id"] : 0;
  	$subscriber_id = isset($mydata["subscriber_id"]) ? $mydata["subscriber_id"] : '';

  	$select = array("store_name","store_id","store_unique_id","store_favicon","paypal_enabled","stripe_enabled","manual_enabled","cod_enabled","ecommerce_cart.user_id as user_id","payment_amount");
  	$cart_data = $this->valid_cart_data($cart_id,$subscriber_id,$select);

  	if(!isset($cart_data[0]) || (isset($cart_data[0]) && $cart_data[0]["store_id"]=="") )
  	{
  		echo json_encode(array('status'=>'0','message'=>$this->lang->line('Order data not found.')));
  		exit();
  	}

  	$address_data = isset($mydata["address_data"]) ? $mydata["address_data"] : array();
  	$subscriber_address = array();
  	$subscriber_address_index = array("street","city","state","country","zip");
  	foreach($address_data as $key => $value)
  	{
  		$value_escaped = strip_tags($this->security->xss_clean($value));
  		$address_data[$key] = $value_escaped;
  		if(in_array($key, $subscriber_address_index)) $subscriber_address[$key] = $value_escaped;
  	}
  	$subscriber_address = json_encode($subscriber_address);
  	$subscriber_email = isset($address_data['email']) ? $address_data['email'] : "";
  	$subscriber_mobile = isset($address_data['mobile']) ? $address_data['mobile'] : "";
  	$buyer_first_name = isset($address_data['buyer_first_name']) ? $address_data['buyer_first_name'] : "";
  	$buyer_last_name = isset($address_data['buyer_last_name']) ? $address_data['buyer_last_name'] : "";
  	$buyer_email = isset($address_data['buyer_email']) ? $address_data['buyer_email'] : "";
  	$buyer_mobile = isset($address_data['buyer_mobile']) ? $address_data['buyer_mobile'] : "";
  	$buyer_address = isset($address_data['buyer_address']) ? $address_data['buyer_address'] : "";
  	$buyer_state = isset($address_data['buyer_state']) ? $address_data['buyer_state'] : "";
    $buyer_city = isset($address_data['buyer_city']) ? $address_data['buyer_city'] : "";
  	$buyer_zip = isset($address_data['buyer_zip']) ? $address_data['buyer_zip'] : "";
  	$buyer_country = isset($address_data['buyer_country']) ? $address_data['buyer_country'] : "";

  	$store_id = $cart_data[0]["store_id"];
  	$user_id = $cart_data[0]["user_id"];
  	$payment_amount = $cart_data[0]["payment_amount"];
  	$store_name = $cart_data[0]["store_name"];
  	$paypal_enabled = $cart_data[0]["paypal_enabled"];
  	$stripe_enabled = $cart_data[0]["stripe_enabled"];
  	$manual_enabled = $cart_data[0]["manual_enabled"];
  	$cod_enabled = $cart_data[0]["cod_enabled"];
  	$store_favicon = $cart_data[0]["store_favicon"];
  	if($store_favicon!="") $store_favicon = base_url("upload/ecommerce/".$store_favicon);
  	
  	$ecommerce_config =  $this->get_ecommerce_config($user_id);
  	$paypal_email = isset($ecommerce_config['paypal_email']) ? $ecommerce_config['paypal_email'] : '';
  	$paypal_mode = isset($ecommerce_config['paypal_mode']) ? $ecommerce_config['paypal_mode'] : 'live';
  	$stripe_secret_key = isset($ecommerce_config['stripe_secret_key']) ? $ecommerce_config['stripe_secret_key'] : '';
  	$stripe_publishable_key = isset($ecommerce_config['stripe_publishable_key']) ? $ecommerce_config['stripe_publishable_key'] : '';
  	$manual_payment_instruction = isset($ecommerce_config['manual_payment_instruction']) ? $ecommerce_config['manual_payment_instruction'] : '';
  	$currency = isset($ecommerce_config['currency']) ? $ecommerce_config['currency'] : 'USD';

  	if($paypal_enabled=='1'  && $paypal_email=='') 
  	{
  	    echo json_encode(array('status'=>'0','message'=>$this->lang->line('PayPal payment settings not found.')));
  		exit();
  	}
  	if($stripe_enabled=='1'  && ($stripe_secret_key=='' || $stripe_publishable_key=='')) 
  	{
  	    echo json_encode(array('status'=>'0','message'=>$this->lang->line('Stripe payment settings not found.')));
  		exit();
  	}
  	if($manual_enabled=='1'  && $manual_payment_instruction=='') 
  	{
  	    echo json_encode(array('status'=>'0','message'=>$this->lang->line('Manual payment settings not found.')));
  		exit();
  	}

  	// $cart_item_data =  $this->basic->get_data("ecommerce_cart_item",array("where"=>array("cart_id"=>$cart_id)),"quantity,product_name",array('ecommerce_product'=>"ecommerce_cart_item.product_id=ecommerce_product.id,left"));
  	// foreach ($cart_item_data as $key => $value)
  	// {
  	// 	$product_names[]= $value['product_name'].":".$value['quantity'];
  	// }
  	// $product_names = implode(', ', $product_names);
  	// $product_name = $store_name." ( ".$product_names." )";

  	$curtime = date("Y-m-d H:i:s");
  	$update_data = array
  	(
  		"buyer_first_name"=>$buyer_first_name,
  		"buyer_last_name"=>$buyer_last_name,
  		"buyer_email"=>$buyer_email,
  		"buyer_mobile"=>$buyer_mobile,
  		"buyer_country"=>$buyer_country,
  		"buyer_state"=>$buyer_state,
      "buyer_city"=>$buyer_city,
  		"buyer_address"=>$buyer_address,
  		"buyer_zip"=>$buyer_zip,
  		"updated_at"=>$curtime,
  		"buyer_zip"=>$buyer_zip
  	);
  	$this->basic->update_data("ecommerce_cart",array("id"=>$cart_id,"subscriber_id"=>$subscriber_id,"action_type !="=>"checkout"),$update_data);
  	$update_data2 = array
  	(
  		"email" =>$subscriber_email,
  		"phone_number" =>$subscriber_mobile,
  		"user_location" =>$subscriber_address,
  		"entry_time"=>$curtime,
  		"phone_number_entry_time"=>$curtime
  	);  	    
  	$this->basic->update_data("messenger_bot_subscriber",array("subscribe_id"=>$subscriber_id,"user_id"=>$user_id),$update_data2);    	    

    
    $paypal_button = $stripe_button = $manual_button = $cod_button = "";
  	$product_name  = $store_name." : ".$this->lang->line("Order")." #".$cart_id;
    if($paypal_enabled=="1")
    { 
    		$this->load->library('paypal_class_ecommerce');
  		$cancel_url=base_url()."ecommerce/order/".$cart_id."?subscriber_id=".$subscriber_id."&action=cancel";
	    $success_url=base_url()."ecommerce/order/".$cart_id."?subscriber_id=".$subscriber_id."&action=success";    
	    
	    $this->paypal_class_ecommerce->mode=$paypal_mode;
	    $this->paypal_class_ecommerce->cancel_url=$cancel_url;
	    $this->paypal_class_ecommerce->success_url=$success_url;
	    $this->paypal_class_ecommerce->notify_url=base_url("ecommerce/paypal_action/".$user_id);
	    $this->paypal_class_ecommerce->business_email=$paypal_email;
      $this->paypal_class_ecommerce->amount=$payment_amount;
	    $this->paypal_class_ecommerce->user_id=$user_id;
	    $this->paypal_class_ecommerce->currency=$currency;
	    $this->paypal_class_ecommerce->cart_id=$cart_id;
	    $this->paypal_class_ecommerce->subscriber_id=$subscriber_id;
	    $this->paypal_class_ecommerce->product_name=$product_name;
	    $paypal_button = $this->paypal_class_ecommerce->set_button();
	    $paypal_button = '<div class="col-12 col-md-3"><br>'.$paypal_button.'</div>';
   	}

   	if($stripe_enabled=="1")
    { 
    	$this->load->library('stripe_class_ecommerce');
	    $this->stripe_class_ecommerce->secret_key=$stripe_secret_key;
	    $this->stripe_class_ecommerce->publishable_key=$stripe_publishable_key;
	    $this->stripe_class_ecommerce->title=$store_name;
	    $this->stripe_class_ecommerce->description=$this->lang->line("Order")." #".$cart_id;
	    $this->stripe_class_ecommerce->amount=$payment_amount;
	    $this->stripe_class_ecommerce->action_url=base_url("ecommerce/stripe_action");
	    $this->stripe_class_ecommerce->currency=$currency;
	    $this->stripe_class_ecommerce->img_url=$store_favicon;

	    // for action function, because it's not web hook based, it's js based
    	$this->session->set_userdata('ecommerce_stripe_payment_user_id',$user_id);
    	$this->session->set_userdata('ecommerce_stripe_payment_cart_id',$cart_id);
    	$this->session->set_userdata('ecommerce_stripe_payment_subscriber_id',$subscriber_id);
    	$this->session->set_userdata('ecommerce_stripe_payment_amount',$payment_amount);
    	$this->session->set_userdata('ecommerce_stripe_payment_currency',$currency);
    	$this->session->set_userdata('ecommerce_stripe_payment_description',$store_name);

	    $stripe_button = $this->stripe_class_ecommerce->set_button();
	    $stripe_button = '<div class="col-12 col-md-3"><br>'.$stripe_button.'</div>'; 
	  }

		if($manual_enabled=='1')
		{
			$manual_button = '<div class="col-12 col-md-3 text-center"><br><button id="manual-payment-button" class="btn btn-info btn-lg">Pay Manually</button><br><a href="#" id="show_manual_payment_instructions" class="pointer text-danger font-weight-bold" data-toggle="modal" data-target="#manual-payment-ins-modal"><i class="fas fa-exclamation-circle"></i> '.$this->lang->line("Manual Payment Instructions").'</a></div>';
		} 

		if($cod_enabled=='1')
		{
			$cod_button = '<div class="col-12 col-md-3"><br><button id="cod-payment-button" class="btn btn-info btn-lg">Pay on Delivery</button></div>';
		}          

	  $html ='<div class="section-title m-0">'.$this->lang->line("Payment Options").'</div><div class="row">'.$paypal_button.$stripe_button.$cod_button.$manual_button.'</div>';

	  echo json_encode(array('status'=>'1','message'=>'','html'=>$html,"manual_payment_instruction"=>$manual_payment_instruction));
  	
  }

  public function manual_payment() 
  {
      $this->ajax_check();
      if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) exit();

      // Sets validation rules
      $this->form_validation->set_rules('paid-amount', $this->lang->line('Amount'), 'required|numeric');
      $this->form_validation->set_rules('paid-currency', $this->lang->line('Currency'), 'required');
      $this->form_validation->set_rules('additional-info', $this->lang->line('Additional info'), 'trim');
      $this->form_validation->set_rules('cart_id', $this->lang->line('Cart'), 'required|numeric');
      $this->form_validation->set_rules('subscriber_id', $this->lang->line('Subscriber ID'), 'required|numeric');

      // Shows errors if user data is invalid
      if (false === $this->form_validation->run())
      {
        if ($this->form_validation->error('paid-amount')) $message = $this->form_validation->error('paid-amount');
        else if ($this->form_validation->error('paid-currency')) $message = $this->form_validation->error('paid-currency');
        else if ($this->form_validation->error('additional-info')) $message = $this->form_validation->error('additional-info');
        else if ($this->form_validation->error('cart_id')) $message = $this->form_validation->error('cart_id');
        else if ($this->form_validation->error('subscriber_id')) $message = $this->form_validation->error('subscriber_id');
        else $message = $this->lang->line('Something went wrong, please try again.');
          
        echo json_encode(['error' => strip_tags($message)]);
        exit;
      }

      $paid_amount = $this->input->post('paid-amount',true);
      $paid_currency = $this->input->post('paid-currency',true);
      $additional_info = strip_tags($this->input->post('additional-info',true));
      $cart_id = $this->input->post('cart_id',true);
      $subscriber_id = $this->input->post('subscriber_id',true);

      $this->load->library('upload');

      if ($_FILES['manual-payment-file']['size'] != 0) {

          $base_path = FCPATH.'upload/ecommerce';
          $filename = "payment_" . time() . substr(uniqid(mt_rand(), true), 0, 6).$_FILES['manual-payment-file']['name'];
          $config = array(
            "allowed_types" => 'pdf|doc|txt|png|jpg|jpeg|zip',
            "upload_path" => $base_path,
            "overwrite" => true,
            "file_name" => $filename,
            'max_size' => '5120',
          );

          $this->upload->initialize($config);
          $this->load->library('upload', $config);

          if (!$this->upload->do_upload('manual-payment-file')) {
            
            $message = $this->upload->display_errors();
            echo json_encode(['error' => $message]); exit;
          }
      }

      $curtime  = date('Y-m-d H:i:s');
      $transaction_id = strtoupper('MP'.$cart_id.hash_pbkdf2('sha512', $paid_amount, mt_rand(19999999, 99999999), 1000, 6));
      $data = [
          'manual_amount' => $paid_amount, 
          'manual_currency' => $paid_currency, 
          'manual_additional_info' => $additional_info,            
          'transaction_id' => $transaction_id,
          'manual_filename' => $filename,
          'paid_at' => $curtime,
          'status' => 'pending',
          'status_changed_at' => $curtime,
          'action_type'=>'checkout',
          'payment_method'=>'Manual'
      ];

      if($this->basic->update_data('ecommerce_cart',array("id"=>$cart_id,"subscriber_id"=>$subscriber_id,"action_type !="=>"checkout"), $data)) 
      {
          $message = "<i class='fas fa-check-circle'></i> ".$this->lang->line('Your order has been placed successfully and your payment request is now being reviewed. You can see your order status from this page')." : <br>";
          $invoice_link = base_url("ecommerce/order/".$cart_id."?subscriber_id=".$subscriber_id);
          $message .= "<a href='".$invoice_link."'>".$invoice_link."</a>";
          $this->session->set_userdata('payment_status','1');
          $this->session->set_userdata('payment_status_message',$message);
          echo json_encode(['success' => $message,'redirect'=>$invoice_link]);
          $this->confirmation_message_sender($cart_id,$subscriber_id);
          exit;
      }

      $message = $this->lang->line('Something went wrong, please try again.');
      echo json_encode(['error' => $message]);
  }

  public function cod_payment() 
  {
      $this->ajax_check();
      $cart_id = $this->input->post("cart_id",true);
      $subscriber_id = $this->input->post("subscriber_id",true);

      $curtime  = date('Y-m-d H:i:s');
      $transaction_id = strtoupper('PD'.$cart_id.hash_pbkdf2('sha512', $subscriber_id, mt_rand(19999999, 99999999), 1000, 6));
      $data = [                       
          'transaction_id' => $transaction_id,            
          'paid_at' => $curtime,
          'status' => 'pending',
          'status_changed_at' => $curtime,
          'action_type'=>'checkout',
          'payment_method'=>'Cash on Delivery'
      ];

      if($this->basic->update_data('ecommerce_cart',array("id"=>$cart_id,"subscriber_id"=>$subscriber_id,"action_type !="=>"checkout"), $data)) 
      {
          $message = "<i class='fas fa-check-circle'></i> ".$this->lang->line('Your order has been placed successfully and is now being reviewed. You can see your order status from this page')." : <br>";
          $invoice_link = base_url("ecommerce/order/".$cart_id."?subscriber_id=".$subscriber_id);
          $message .= "<a href='".$invoice_link."'>".$invoice_link."</a>";
          $this->session->set_userdata('payment_status','1');
          $this->session->set_userdata('payment_status_message',$message);
          $this->confirmation_message_sender($cart_id,$subscriber_id);
          echo json_encode(['success' => $message,'redirect'=>$invoice_link]);
          exit;
      }
      $message = $this->lang->line('Something went wrong, please try again.');
      echo json_encode(['error' => $message]);
  }

  public function stripe_action()
  {	
  		$this->load->library('stripe_class_ecommerce');
  		$user_id  = $this->session->userdata('ecommerce_stripe_payment_user_id');
  		$ecommerce_config =  $this->get_ecommerce_config($user_id);
      $stripe_secret_key = isset($ecommerce_config['stripe_secret_key']) ? $ecommerce_config['stripe_secret_key'] : '';
      $this->stripe_class_ecommerce->secret_key=$stripe_secret_key;
  		$response= $this->stripe_class_ecommerce->stripe_payment_action();

  		if($response['status']=='Error'){
  			echo $response['message'];
  			exit();
  		}
  		
  		$currency = isset($response['charge_info']['currency'])?$response['charge_info']['currency']:"";
  		$currency=strtoupper($currency);
  		
  		$receiver_email=$response['email'];

  		if($currency=='JPY' || $currency=='VND') $payment_amount=$response['charge_info']['amount'];
  		else $payment_amount=$response['charge_info']['amount']/100;

  		$transaction_id=$response['charge_info']['balance_transaction'];
  		$payment_date=date("Y-m-d H:i:s",$response['charge_info']['created']) ;
  		$country=isset($response['charge_info']['source']['country'])?$response['charge_info']['source']['country']:"";
  		
  		$stripe_card_source=isset($response['charge_info']['source'])?$response['charge_info']['source']:"";
  		$stripe_card_source=json_encode($stripe_card_source);		
  	
  		$curtime = date("Y-m-d H:i:s");
  		$insert_data=array
  		(
          'checkout_account_receiver_email' => $receiver_email, 
          'checkout_account_country' => $country, 
          'checkout_amount' => $payment_amount, 
          'checkout_currency' => $currency,           
          'checkout_timestamp' => $payment_date,           
          'transaction_id' => $transaction_id,
		      "checkout_source_json"=>$stripe_card_source,            
          'paid_at' => $curtime,
          'status' => 'approved',
          'status_changed_at' => $curtime,
          'action_type'=>'checkout',
          'payment_method'=>'Stripe'
      );		
  		$cart_id = $this->session->userdata('ecommerce_stripe_payment_cart_id');
  		$subscriber_id = $this->session->userdata('ecommerce_stripe_payment_subscriber_id');
      $this->basic->update_data('ecommerce_cart',array("id"=>$cart_id,"subscriber_id"=>$subscriber_id,"action_type !="=>"checkout"),$insert_data);
          
  		$message = "<i class='fas fa-check-circle'></i> ".$this->lang->line('Your payment has been received successfully and order will be processed soon. You can see your order status from this page')." : <br>";
  		$invoice_link = base_url("ecommerce/order/".$cart_id."?subscriber_id=".$subscriber_id);
  		$message .= "<a href='".$invoice_link."'>".$invoice_link."</a>";

  		$this->session->set_userdata('payment_status','1');
  		$this->session->set_userdata('payment_status_message',$message);

  		$this->confirmation_message_sender($cart_id,$subscriber_id);

  		redirect($invoice_link, 'location');		
	}

	public function paypal_action($user_id=0)
	{
    $this->load->library('paypal_class_ecommerce');
    $ecommerce_config =  $this->get_ecommerce_config($user_id);
    $paypal_mode = isset($ecommerce_config['paypal_mode']) ? $ecommerce_config['paypal_mode'] : 'live';
    $this->paypal_class_ecommerce->mode=$paypal_mode;
    $payment_info=$this->paypal_class_ecommerce->run_ipn();

    $api_data=$this->basic->get_data("native_api","","",$join='',$limit='1',$start=0,$order_by='id asc');
    $api_key="";
    if(count($api_data)>0) $api_key=$api_data[0]["api_key"];
    $payment_info['api_key'] =$api_key;

    $custom_data = isset($payment_info['data']['custom']) ? $payment_info['data']['custom'] : "";
    $explode=explode('_',$custom_data);
    $cart_id=isset($explode[0]) ? $explode[0] : 0;
    $subscriber_id=isset($explode[1]) ? $explode[1] : "";      

    $post_data_payment_info=array("response_raw"=>json_encode($payment_info));
    $url=base_url()."ecommerce/paypal_action_main";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data_payment_info);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    $reply_response=curl_exec($ch); 

    $curl_information =  curl_getinfo($ch);
  	$curl_error="";
    if($curl_information['http_code']!='200'){
      $curl_error = curl_error($ch);
    }

    $payment_info["error_log"] = $curl_information['http_code']." : ".$curl_error;
    $payment_info_json=json_encode($payment_info);
    $this->basic->update_data('ecommerce_cart',array("id"=>$cart_id,"subscriber_id"=>$subscriber_id,"action_type !="=>"checkout"),array("checkout_source_json"=>$payment_info_json));
	}
	    
  public function paypal_action_main()
  {    
    $response_raw=$this->input->post("response_raw");   
    $payment_info = json_decode($response_raw,TRUE);
    
    $verify_status=isset($payment_info['verify_status']) ? $payment_info['verify_status']:"";
    $first_name= isset($payment_info['data']['first_name']) ? $payment_info['data']['first_name']:"";
    $last_name= isset($payment_info['data']['last_name']) ? $payment_info['data']['last_name']:"";
    $buyer_email= isset($payment_info['data']['payer_email']) ? $payment_info['data']['payer_email']:"";
    $receiver_email= isset($payment_info['data']['receiver_email']) ? $payment_info['data']['receiver_email']:""; 
    $country= isset($payment_info['data']['address_country_code']) ? $payment_info['data']['address_country_code']:""; 
    $payment_date=isset($payment_info['data']['payment_date']) ? $payment_info['data']['payment_date']:""; 
    $transaction_id=isset($payment_info['data']['txn_id']) ? $payment_info['data']['txn_id']:""; 
    $payment_type=isset($payment_info['data']['payment_type']) ? "PAYPAL-".ucfirst($payment_info['data']['payment_type']) : "PAYPAL"; 
    $payment_amount=isset($payment_info['data']['mc_gross']) ? $payment_info['data']['mc_gross']:"";
    
    $custom_data = isset($payment_info['data']['custom']) ? $payment_info['data']['custom'] : "";
    $explode=explode('_',$custom_data);
    $cart_id=isset($explode[0]) ? $explode[0] : 0;
    $subscriber_id=isset($explode[1]) ? $explode[1] : "";

    $payment_date = date("Y-m-d H:i:s",strtotime($payment_date));

    /****Get API Key & Match With the post API Key, If not same , then exit it . ***/
    $api_data=$this->basic->get_data("native_api","","",$join='',$limit='1',$start=0,$order_by='id asc');
    $api_key="";
    if(count($api_data)>0) $api_key=$api_data[0]["api_key"];
    $post_api_from_ipn = $payment_info['api_key']; 
    if($api_key!=$post_api_from_ipn) exit();


    /***Check if the transaction id is already used or not, if used, then exit to prevent multiple add***/
    $simple_where_duplicate_check['where'] = array('transaction_id'=>$transaction_id,"payment_method"=>"PayPal");
    $prev_payment_info_transaction = $this->basic->get_data('ecommerce_cart',$simple_where_duplicate_check,"id",$join='',$limit='1',$start=0,$order_by='id desc');
    if(count($prev_payment_info_transaction)>0) exit;

    $cart_data = $this->basic->get_data("ecommerce_cart",array("where"=>array("id"=>$cart_id,"subscriber_id"=>$subscriber_id)),"payment_amount");
    if(!isset($cart_data[0])) exit();
    $price = $cart_data[0]["payment_amount"];        
   
    /** insert the transaction into database ***/       
    $paypal_status_verification = $this->config->item("paypal_status_verification");
    if($paypal_status_verification=='') $paypal_status_verification='1';
   
   /* if($paypal_status_verification=='1') if($verify_status!="VERIFIED" || $payment_amount<$price) exit();
    else if($payment_amount<$price)  exit(); */

    $curtime = date("Y-m-d H:i:s");
		$insert_data=array
		(
        'checkout_account_email' => $buyer_email, 
        'checkout_account_receiver_email' => $receiver_email, 
        'checkout_account_country' => $country,
        'checkout_account_first_name' => $first_name,
        'checkout_account_last_name' => $last_name,
        'checkout_amount' => $payment_amount, 
        'checkout_currency' => "",
        'checkout_verify_status' => $verify_status,
        'checkout_timestamp' => $payment_date,           
        'transaction_id' => $transaction_id,          
        'paid_at' => $curtime,
        'status' => 'approved',
        'status_changed_at' => $curtime,
        'action_type'=>'checkout',
        'payment_method'=>'PayPal'
    );		
    $this->basic->update_data('ecommerce_cart',array("id"=>$cart_id,"subscriber_id"=>$subscriber_id,"action_type !="=>"checkout"),$insert_data);
    $this->confirmation_message_sender($cart_id,$subscriber_id);     
  }

  private function spin_and_replace($str="",$replace = array(),$is_spin=true)
  {
    if(!isset($replace['first_name'])) $replace['first_name'] = '';
    if(!isset($replace['last_name'])) $replace['last_name'] = '';
    if(!isset($replace['email'])) $replace['email'] = '';
    if(!isset($replace['mobile'])) $replace['mobile'] = '';
    if(!isset($replace['order_url'])) $replace['order_url'] = '';
    if(!isset($replace['checkout_url'])) $replace['checkout_url'] = '';
    if(!isset($replace['store_url'])) $replace['store_url'] = '';
    if(!isset($replace['my_orders_url'])) $replace['my_orders_url'] = '';

    $replace_values = array_values($replace);
    $str = str_replace(array("{{first_name}}","{{last_name}}","{{email}}","{{mobile}}","{{order_url}}","{{checkout_url}}","{{store_url}}","{{my_orders_url}}"), $replace_values, $str);
    if($is_spin) return spintax_process($str);
    else return $str;
  }

  private function send_messenger_reminder($message='',$page_access_token='')
 	{        
 		$sent_response = array();
    $this->load->library("fb_rx_login"); 
    try
 		{
 		    $response = $this->fb_rx_login->send_non_promotional_message_subscription($message,$page_access_token);
 		
 		    if(isset($response['message_id']))
 		    {
 		       $sent_response = array("response"=>$response['message_id'],"status"=>'1'); 
 		    }
 		    else 
 		    {
 		        if(isset($response["error"]["message"])) 
 		        $sent_response = array("response"=> $response["error"]["message"],"status"=>'0');              
 		    }              
 		    
 		}
 		catch(Exception $e) 
 		{
 		  $sent_response = array("response"=> $e->getMessage(),"status"=>'0'); 
 		}
 		return $sent_response;
 	}

  private function confirmation_message_sender($cart_id=0,$subscriber_id="")
  {
    if($cart_id==0 || $subscriber_id=="") return false;
    $cart_select = array("ecommerce_cart.*","store_unique_id","page_id","messenger_content","sms_content","sms_api_id","email_content","email_api_id","email_subject","configure_email_table","label_ids");
    $cart_join = array('ecommerce_store'=>"ecommerce_cart.store_id=ecommerce_store.id,left");
    $cart_where = array('where'=>array("ecommerce_cart.subscriber_id"=>$subscriber_id,"ecommerce_cart.id"=>$cart_id,"ecommerce_store.status"=>"1"));      
    $cart_data_2d = $this->basic->get_data("ecommerce_cart",$cart_where,$cart_select,$cart_join);
    if(!isset($cart_data_2d[0])) return false;      

    $cart_data = $cart_data_2d[0];      

    $store_unique_id = isset($cart_data['store_unique_id'])?$cart_data['store_unique_id']:'';
    $store_id = isset($cart_data['store_id'])?$cart_data['store_id']:'0';
    $user_id = isset($cart_data['user_id'])?$cart_data['user_id']:'0';
    $page_id = isset($cart_data['page_id'])?$cart_data['page_id']:'0';
    $sms_api_id = isset($cart_data['sms_api_id'])?$cart_data['sms_api_id']:'0';
    $sms_content = isset($cart_data['sms_content'])?json_decode($cart_data['sms_content'],true):array();
    $email_api_id = isset($cart_data['email_api_id'])?$cart_data['email_api_id']:'0';
    $email_content = isset($cart_data['email_content'])?json_decode($cart_data['email_content'],true):array();
    $configure_email_table = isset($cart_data['configure_email_table'])?$cart_data['configure_email_table']:'';
    $email_subject = isset($cart_data['email_subject'])?$cart_data['email_subject']:'Order Update';
    $messenger_content = isset($cart_data['messenger_content'])?json_decode($cart_data['messenger_content'],true):array();
    $action_type = isset($cart_data['action_type'])?$cart_data['action_type']:'checkout';
    $buyer_first_name = isset($cart_data['buyer_first_name'])?$cart_data['buyer_first_name']:'';
    $buyer_last_name = isset($cart_data['buyer_last_name'])?$cart_data['buyer_last_name']:'';
    $buyer_email = isset($cart_data['buyer_email'])?$cart_data['buyer_email']:'';
    $buyer_mobile = isset($cart_data['buyer_mobile'])?$cart_data['buyer_mobile']:'';
    $buyer_country = isset($cart_data['buyer_country'])?$cart_data['buyer_country']:'-';
    $buyer_state = isset($cart_data['buyer_state'])?$cart_data['buyer_state']:'-';
    $buyer_city = isset($cart_data['buyer_city'])?$cart_data['buyer_city']:'-';
    $buyer_address = isset($cart_data['buyer_address'])?$cart_data['buyer_address']:'-';
    $buyer_zip = isset($cart_data['buyer_zip'])?$cart_data['buyer_zip']:'-';
    $subtotal = isset($cart_data['subtotal'])?$cart_data['subtotal']:0;
    $payment_amount = isset($cart_data['payment_amount'])?$cart_data['payment_amount']:0;
    $currency = isset($cart_data['currency'])?$cart_data['currency']:'USD';
    $shipping = isset($cart_data['shipping'])?$cart_data['shipping']:0;
    $tax = isset($cart_data['tax'])?$cart_data['tax']:0;
    $coupon_code = isset($cart_data['coupon_code'])?$cart_data['coupon_code']:"";
    $discount = isset($cart_data['discount'])?$cart_data['discount']:0;
    $payment_method = isset($cart_data['payment_method'])?$cart_data['payment_method']:"Cash on Delivery";

    $checkout_url = base_url("ecommerce/cart/".$cart_id."?subscriber_id=".$subscriber_id);
    $order_url = base_url("ecommerce/order/".$cart_id."?subscriber_id=".$subscriber_id);
    $store_url = base_url("ecommerce/store/".$store_unique_id."?subscriber_id=".$subscriber_id);
    $my_orders_url = base_url("ecommerce/my_orders/".$store_id."?subscriber_id=".$subscriber_id);

    $cart_info =  $this->basic->get_data("ecommerce_cart_item",array("where"=>array("cart_id"=>$cart_id)),"quantity,product_name,unit_price,coupon_info,attribute_info,thumbnail,product_id",array('ecommerce_product'=>"ecommerce_cart_item.product_id=ecommerce_product.id,left"));
    
    $curdate = date("Y-m-d H:i:s");

    $buyer_mobile = preg_replace("/[^0-9]+/", "", $buyer_mobile);
    $replace_variables = array("first_name"=>$buyer_first_name,"last_name"=>$buyer_last_name,"email"=>$buyer_email,"mobile"=>$buyer_mobile,"order_url"=>$order_url,"checkout_url"=>$checkout_url,"store_url"=>$store_url,"my_orders_url"=>$my_orders_url);

    $checkout_info = array();
    $confirmation_response = array();
    if($action_type=='checkout')
    { 
      $i=0;
      $elements = array();

      foreach ($cart_info as $key => $value) 
      {
        $elements[$i]['title'] = isset($value['product_name']) ? $value['product_name'] : "";
        
        $subtitle = array_values(json_decode($value["attribute_info"],true));
        $subtitle = implode(', ', $subtitle);
        $elements[$i]['subtitle'] = $subtitle;
        
        $elements[$i]['quantity'] = isset($value['quantity']) ? $value['quantity'] : 1;
        $elements[$i]['price'] = isset($value['unit_price']) ? $value['unit_price'] : 0;
        $elements[$i]['currency'] = $currency;

        if($value['thumbnail']=='') $image_url = base_url('assets/img/products/product-1.jpg');
        else $image_url = base_url('upload/ecommerce/'.$value['thumbnail']);
        $elements[$i]['image_url'] = $image_url;
        $i++;
        $update_sales_count_sql = "UPDATE ecommerce_product SET sales_count=sales_count+".$value["quantity"]." WHERE id=".$value["product_id"];
        $this->basic->execute_complex_query($update_sales_count_sql);
      }        
	    $address = array
	    (
	        "street_1" => $buyer_address,
	        "street_2" => "",
	        "city" => $buyer_city,
	        "postal_code" => $buyer_zip,
	        "state" => $buyer_state,
	        "country" => $buyer_country
	    );
	    $recipient_name = $buyer_first_name." ".$buyer_last_name;
	    if(trim($recipient_name=="")) $recipient_name="-";       

      $summary =array
      (
        "subtotal"=> $subtotal,
        "shipping_cost"=>$shipping,
        "total_tax"=> $tax,
        "total_cost"=> $payment_amount
      );

      $adjustments = array
      (
        0 => array
        (
          "name"=> $coupon_code,
          "amount"=> $discount
        )
      );

      $payload = array 
      (
        "template_type" => "receipt",
        "recipient_name"=> $recipient_name,
        "order_number"=> $cart_id,
        "currency"=> $currency,
        "payment_method"=> $payment_method,        
        "order_url"=> $order_url,
        "timestamp"=> time(),
        "address" => $address,
        "summary" => $summary,
        "elements" => $elements
      );
      if($coupon_code!="") $payload['adjustments'] = $adjustments;

      $messenger_checkout_confirmation = array 
      (
        "recipient" => array("id"=>$subscriber_id),
        "messaging_type" => "MESSAGE_TAG",
        "tag" => "POST_PURCHASE_UPDATE",
        'message' => array
        (
          'attachment' => 
          array 
          (
            'type' => 'template',
            'payload' => $payload              
          )
        )           
      );

      // Messenger send block
      $sent_response = array();
      $this->load->library("fb_rx_login"); 
      $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array('where'=>array('id'=>$page_id)));
      $page_access_token = isset($page_info[0]['page_access_token']) ? $page_info[0]['page_access_token'] : "";

      // template 1
      $intro_text = isset($messenger_content["checkout"]["checkout_text"]) ? $messenger_content["checkout"]["checkout_text"] : "";
      if($intro_text!="")
      {
      	$intro_text = $this->spin_and_replace($intro_text,$replace_variables);
      	$messenger_confirmation_template1 = json_encode(array("recipient"=>array("id"=>$subscriber_id),"message"=>array("text"=>$intro_text)));
          $this->send_messenger_reminder($messenger_confirmation_template1,$page_access_token);
      }

        // template 2
        $messenger_confirmation_template2 = json_encode($messenger_checkout_confirmation);
        $sent_response = $this->send_messenger_reminder($messenger_confirmation_template2,$page_access_token);
        
        // template 3
        $after_checkout_text = isset($messenger_content["checkout"]["checkout_text_next"]) ? $messenger_content["checkout"]["checkout_text_next"] : "";
        $after_checkout_btn = isset($messenger_content["checkout"]["checkout_btn_next"]) ? $messenger_content["checkout"]["checkout_btn_next"] : "MY ORDERS";
        if($after_checkout_text!="")
        {
        	$after_checkout_text = $this->spin_and_replace($after_checkout_text,$replace_variables);
        	$messenger_confirmation_template3 = array 
            (
      			  "recipient" => array("id"=>$subscriber_id),					  
      			  'message' => array
      			  (
      			  	'attachment' => 
      				  array 
      				  (
      				    'type' => 'template',
      				    'payload' => 
      				    array 
      				    (
      				      'template_type' => 'button',
      				      'text' => $after_checkout_text,
      				      'buttons'=> array(
      				      	0=>array(
      				      		"type"=>"web_url",
      				      		"url"=>$my_orders_url,
      				      		"title"=>$after_checkout_btn,
      				      		"messenger_extensions" => 'true',
      				      		"webview_height_ratio" => 'full'
      				      		)				      	
      				     	)
      				    ),
      				  )
      			  )					  
			     );
  			 $this->send_messenger_reminder(json_encode($messenger_confirmation_template3),$page_access_token);
  		}
      $confirmation_response['messenger'] = $sent_response;
      // Messenger send block


      //  SMS Sending block        
      if($buyer_mobile!="" && $sms_api_id!='0')
      {
        $checkout_text_sms = isset($sms_content['checkout']['checkout_text']) ? $this->spin_and_replace($sms_content['checkout']['checkout_text'],$replace_variables,false) : "";
        $checkout_text_sms = str_replace(array("'",'"'),array('`','`'),$checkout_text_sms);
        
        $sms_response = array("response"=> 'missing param',"status"=>'0');

        if(trim($checkout_text_sms)!="")
        {
          $this->load->library('Sms_manager');
          $this->sms_manager->set_credentioal($sms_api_id,$user_id);
          try
          {
              $response = $this->sms_manager->send_sms($checkout_text_sms, $buyer_mobile);

              if(isset($response['id']) && !empty($response['id']))
              {   
                  $message_sent_id = $response['id'];
                  $sms_response = array("response"=> $message_sent_id,"status"=>'1');              
              }
              else 
              {   if(isset($response['status']) && !empty($response['status']))
              {
                      $message_sent_id = $response["status"];
                      $sms_response = array("response"=> $message_sent_id,"status"=>'0');  
                  }
              }           
              
          }
          catch(Exception $e) 
          {
             $message_sent_id = $e->getMessage();
             $sms_response = array("response"=> $message_sent_id,"status"=>'0');
          }
        }

        $confirmation_response['sms']=$sms_response;
      }
      //  SMS Sending block

      //  Email Sending block
      if($buyer_email!="" && $email_api_id!='0')
      {
        $checkout_text_email = isset($email_content['checkout']['checkout_text']) ? $this->spin_and_replace($email_content['checkout']['checkout_text'],$replace_variables,false) : "";
        $from_email = "";

        if ($configure_email_table == "email_smtp_config") 
        {
          $from_email = "smtp_".$email_api_id;
        } 
        elseif ($configure_email_table == "email_mandrill_config") 
        {
          $from_email = "mandrill_".$email_api_id;
        } 
        elseif ($configure_email_table == "email_sendgrid_config") 
        {
          $from_email = "sendgrid_".$email_api_id;
        } 
        elseif ($configure_email_table == "email_mailgun_config") 
        {
          $from_email = "mailgun_".$email_api_id;
        }

        $email_response = array("response"=> 'missing param',"status"=>'0');  
        if(trim($checkout_text_email)!='')
        {
          try
          {
            $response = $this->_email_send_function($from_email, $checkout_text_email, $buyer_email, $email_subject, $attachement='', $filename='',$user_id);
  
            if(isset($response) && !empty($response) && $response == "Submited")
            {   
              $message_sent_id = $response;
              if($message_sent_id=="Submited") $message_sent_id = "Submitted";
              $email_response = array("response"=> $message_sent_id,"status"=>'1');  
            }
            else 
            {   
              $message_sent_id = $response;
              $email_response = array("response"=> $message_sent_id,"status"=>'0');  
            }           
          }
          catch(Exception $e) 
          {
            $message_sent_id = $e->getMessage();
            $email_response = array("response"=> $message_sent_id,"status"=>'0');  
          }
        }
        $confirmation_response['email']=$email_response;
      }
      //  Email Sending block

    	$confirmation_response = json_encode($confirmation_response);
    	$this->basic->update_data('ecommerce_cart',array("id"=>$cart_id,"subscriber_id"=>$subscriber_id),array("confirmation_response"=>$confirmation_response));
      if($coupon_code!="")
      {
         $coupon_used_sql = "UPDATE ecommerce_coupon SET used=used+1 WHERE coupon_code='".$coupon_code."' AND store_id=".$store_id;
         $this->basic->execute_complex_query($coupon_used_sql);
      }

    }

  }

  public function my_orders($store_id=0)
  {
    $subscriber_id = $this->input->get("subscriber_id",true);
    $store_data = $this->basic->get_data("ecommerce_store",array("where"=>array("id"=>$store_id)),"store_name,store_unique_id,store_logo");
    if($store_id==0 || $subscriber_id=="" || !isset($store_data[0]))
    {
    	$not_found = $this->lang->line("Order data not found.");
        echo '<br/><h1 style="text-align:center">'.$not_found.'</h1>';
        exit();
    }
    $data['store_data'] = $store_data[0];
    $data['store_id'] = $store_id;
    $data['subscriber_id'] = $subscriber_id;
    $data['body'] = 'ecommerce/my_orders';
    $data['page_title'] = $this->lang->line('My Orders');
    $data['status_list'] = $this->get_payment_status();
    $data["fb_app_id"] = $this->get_app_id();
    $this->load->view('ecommerce/bare-theme', $data);
  }

  public function my_orders_data()
  { 
      $this->ajax_check();
      $ecommerce_config = $this->get_ecommerce_config();

      $search_value = $this->input->post("search_value");
      $subscriber_id = $this->input->post("search_subscriber_id");        
      $store_id = $this->input->post("search_store_id");        
      $search_status = $this->input->post("search_status");        
      $search_date_range = $this->input->post("search_date_range");

      $display_columns = 
      array(
        "#",
        "CHECKBOX",
        "subscriber_id",
        'store_name',
        'status',
        'discount',
        'payment_amount',
        'payment_method',
        'transaction_id',
        'invoice',
        'manual_filename',
        'updated_at',
        'paid_at'
      );
      $search_columns = array('subscriber_id','coupon_code','buyer_zip','transaction_id','card_ending');

      $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
      $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
      $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
      $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 11;
      $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'updated_at';
      $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
      $order_by=$sort." ".$order;

      $where_custom="ecommerce_cart.subscriber_id = ".$subscriber_id;

      if ($search_value != '') 
      {
          foreach ($search_columns as $key => $value) 
          $temp[] = $value." LIKE "."'%$search_value%'";
          $imp = implode(" OR ", $temp);
          $where_custom .=" AND (".$imp.") ";
      }
      if($search_date_range!="")
      {
          $exp = explode('|', $search_date_range);
          $from_date = isset($exp[0])?$exp[0]:"";
          $to_date = isset($exp[1])?$exp[1]:"";
          if($from_date!="Invalid date" && $to_date!="Invalid date")
          $where_custom .= " AND ecommerce_cart.updated_at >= '{$from_date}' AND ecommerce_cart.updated_at <='{$to_date}'";
      }
      $this->db->where($where_custom);

      $this->db->where(array("store_id"=>$store_id));    
      if($search_status!="") $this->db->where(array("ecommerce_cart.status"=>$search_status));    
      
      $table="ecommerce_cart";
      $select = "ecommerce_cart.id,action_type,ecommerce_cart.user_id,store_id,subscriber_id,coupon_code,coupon_type,discount,payment_amount,currency,ordered_at,transaction_id,card_ending,payment_method,manual_additional_info,manual_filename,paid_at,ecommerce_cart.status,ecommerce_cart.updated_at,ecommerce_store.store_name";
      // $select = "ecommerce_cart.*,ecommerce_store.store_name";
      $join = array('ecommerce_store'=>"ecommerce_store.id=ecommerce_cart.store_id,left");
      $info=$this->basic->get_data($table,$where='',$select,$join,$limit,$start,$order_by,$group_by='');
      // echo $this->db->last_query();
      
      $this->db->where($where_custom);
      $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join,$group_by='');

      $total_result=$total_rows_array[0]['total_rows'];
      

      $payment_status = $this->get_payment_status();
      foreach($info as $key => $value) 
      {
          $config_currency = isset($value['currency']) ? $value['currency'] : "USD";
          $info[$key]['currency']= isset($this->currency_icon[$config_currency]) ? $this->currency_icon[$config_currency] : "$";

          if($value['coupon_code']!='')
          $info[$key]['discount']= $info[$key]['currency'].$this->two_decimal_place($info[$key]['discount']);
          else $info[$key]['discount'] = "";

          $info[$key]['payment_amount'] = $info[$key]['currency'].$this->two_decimal_place($info[$key]['payment_amount']);

          if($info[$key]['payment_method'] == 'Cash on Delivery') $pay = "Cash";
          else $pay = $info[$key]['payment_method'];
          
          $info[$key]['payment_method'] = $pay." ".$info[$key]['card_ending'];
          if(trim($info[$key]['payment_method'])=="") $info[$key]['payment_method'] = "x";

          $info[$key]['transaction_id'] = ($info[$key]['transaction_id']!="") ? "<b class='text-primary'>".$info[$key]['transaction_id']."</b>" : "x";

          $updated_at = date("M j, y H:i",strtotime($info[$key]['updated_at']));
          $info[$key]['updated_at'] =  "<div style='min-width:110px;'>".$updated_at."</div>";

          if($value["paid_at"]!='0000-00-00 00:00:00')
          {
            $paid_at = date("M j, y H:i",strtotime($info[$key]['paid_at']));
            $info[$key]['paid_at'] =  "<div style='min-width:110px;'>".$paid_at."</div>";
          }
          else $info[$key]['paid_at'] = 'x';

          $st1=$st2="";
          $file = base_url('upload/ecommerce/'.$value['manual_filename']);
          $st1 = ($value['payment_method']=='Manual') ? $this->handle_attachment($value['id'], $file):"";
          
          if($value['payment_method']=='Manual')
          $st2 = ' <a data-id="'.$value['id'].'" href="#"  class="btn btn-outline-primary additional_info" data-toggle="tooltip" title="" data-original-title="'.$this->lang->line("Additional Info").'"><i class="fas fa-info-circle"></i></a>';            

          $info[$key]['manual_filename'] = ($st1=="" && $st2=="") ? "x" : "<div style='width:100px;'>".$st1.$st2."</div>"; 
          
          if($value["action_type"]=="checkout") $info[$key]['invoice'] =  "<a class='btn btn-outline-primary' data-toggle='tooltip' title='".$this->lang->line("Invoice")."' href='".base_url("ecommerce/order/".$value['id']."?subscriber_id=".$subscriber_id)."'><i class='fas fa-receipt'></i></a>";
          else $info[$key]['invoice'] =  "<a class='btn btn-outline-primary' data-toggle='tooltip' title='".$this->lang->line("Checkout")."' href='".base_url("ecommerce/cart/".$value['id']."?subscriber_id=".$subscriber_id)."'><i class='fas fa-credit-card'></i></a>";

          $info[$key]["invoice"] .= '<script>$(\'[data-toggle="tooltip"]\').tooltip();</script>';

      }
      $data['draw'] = (int)$_POST['draw'] + 1;
      $data['recordsTotal'] = $total_result;
      $data['recordsFiltered'] = $total_result;
      $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");
      echo json_encode($data);
  }


  public function order_list()
  {
    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Store");
    $data['store_list'] = $store_list;
    $data['status_list'] = $this->get_payment_status();
    $data['body'] = 'ecommerce/order_list';
    $data['page_title'] = $this->lang->line('Orders')." : ".$this->session->userdata("ecommerce_selected_store_title");
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }

  public function order_list_data()
  { 
    $this->ajax_check();
    $ecommerce_config = $this->get_ecommerce_config();

    $search_value = $this->input->post("search_value");
    $store_id = $this->input->post("search_store_id");        
    $search_status = $this->input->post("search_status");        
    $search_date_range = $this->input->post("search_date_range");

    $display_columns = 
    array(
      "#",
      "CHECKBOX",
      "subscriber_id",
      'store_name',
      'status',
      'discount',
      'payment_amount',
      'payment_method',
      'transaction_id',
      'invoice',
      'manual_filename',
      'updated_at',
      'paid_at'
    );
    $search_columns = array('subscriber_id','coupon_code','buyer_zip','transaction_id','card_ending');

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 11;
    $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'updated_at';
    $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
    $order_by=$sort." ".$order;

    $where_custom="ecommerce_cart.user_id = ".$this->user_id;

    if ($search_value != '') 
    {
        foreach ($search_columns as $key => $value) 
        $temp[] = $value." LIKE "."'%$search_value%'";
        $imp = implode(" OR ", $temp);
        $where_custom .=" AND (".$imp.") ";
    }
    if($search_date_range!="")
    {
        $exp = explode('|', $search_date_range);
        $from_date = isset($exp[0])?$exp[0]:"";
        $to_date = isset($exp[1])?$exp[1]:"";
        if($from_date!="Invalid date" && $to_date!="Invalid date")
        $where_custom .= " AND ecommerce_cart.updated_at >= '{$from_date}' AND ecommerce_cart.updated_at <='{$to_date}'";
    }
    $this->db->where($where_custom);

    if($store_id!="") $this->db->where(array("store_id"=>$store_id));    
    if($search_status!="") $this->db->where(array("ecommerce_cart.status"=>$search_status));    
    
    $table="ecommerce_cart";
    $select = "ecommerce_cart.id,ecommerce_cart.user_id,store_id,subscriber_id,coupon_code,coupon_type,discount,payment_amount,currency,ordered_at,transaction_id,card_ending,payment_method,manual_additional_info,manual_filename,paid_at,ecommerce_cart.status,ecommerce_cart.updated_at,ecommerce_store.store_name";
    // $select = "ecommerce_cart.*,ecommerce_store.store_name";
    $join = array('ecommerce_store'=>"ecommerce_store.id=ecommerce_cart.store_id,left");
    $info=$this->basic->get_data($table,$where='',$select,$join,$limit,$start,$order_by,$group_by='');
    // echo $this->db->last_query();
    
    $this->db->where($where_custom);
    $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join,$group_by='');

    $total_result=$total_rows_array[0]['total_rows'];
    

    $payment_status = $this->get_payment_status();
    foreach($info as $key => $value) 
    {
        $config_currency = isset($value['currency']) ? $value['currency'] : "USD";
        $info[$key]['currency']= isset($this->currency_icon[$config_currency]) ? $this->currency_icon[$config_currency] : "$";

        $info[$key]['subscriber_id']= "<a target='_BLANK' href='".base_url("subscriber_manager/bot_subscribers/".$info[$key]['subscriber_id'])."'>".$info[$key]['subscriber_id']."</a>";

        if($value['coupon_code']!='')
        $info[$key]['discount']= $info[$key]['currency'].$this->two_decimal_place($info[$key]['discount']);
        else $info[$key]['discount'] = "";

        $info[$key]['payment_amount'] = $info[$key]['currency'].$this->two_decimal_place($info[$key]['payment_amount']);
        $info[$key]['payment_method'] = $info[$key]['payment_method']." ".$info[$key]['card_ending'];
        if(trim($info[$key]['payment_method'])=="") $info[$key]['payment_method'] = "x";

        $info[$key]['transaction_id'] = ($info[$key]['transaction_id']!="") ? "<b class='text-primary'>".$info[$key]['transaction_id']."</b>" : "x";

        $updated_at = date("M j, y H:i",strtotime($info[$key]['updated_at']));
        $info[$key]['updated_at'] =  "<div style='min-width:110px;'>".$updated_at."</div>";

        if($value["paid_at"]!='0000-00-00 00:00:00')
        {
          $paid_at = date("M j, y H:i",strtotime($info[$key]['paid_at']));
          $info[$key]['paid_at'] =  "<div style='min-width:110px;'>".$paid_at."</div>";
        }
        else $info[$key]['paid_at'] = 'x';

        $st1=$st2="";
        $file = base_url('upload/ecommerce/'.$value['manual_filename']);
        $st1 = ($value['payment_method']=='Manual') ? $this->handle_attachment($value['id'], $file):"";
        
        if($value['payment_method']=='Manual')
        $st2 = ' <a data-id="'.$value['id'].'" href="#"  class="btn btn-outline-primary additional_info" data-toggle="tooltip" title="" data-original-title="'.$this->lang->line("Additional Info").'"><i class="fas fa-info-circle"></i></a>';            

    	$info[$key]['manual_filename'] = ($st1=="" && $st2=="") ? "x" : "<div style='width:100px;'>".$st1.$st2."</div>"; 
        
        $info[$key]['status'] = form_dropdown('payment_status', $payment_status, $value["status"],'class="select2 payment_status" style="width:120px !important;" data-id="'.$value["id"].'" id="payment_status'.$value['id'].'"').'<script>$("#payment_status'.$value['id'].'").select2();$(\'[data-toggle="tooltip"]\').tooltip();</script>';
        
        $info[$key]['invoice'] =  "<a class='btn btn-outline-primary' data-toggle='tooltip' title='".$this->lang->line("Invoice")."' target='_BLANK' href='".base_url("ecommerce/order/".$value['id'])."'><i class='fas fa-receipt'></i></a>";

    }
    $data['draw'] = (int)$_POST['draw'] + 1;
    $data['recordsTotal'] = $total_result;
    $data['recordsFiltered'] = $total_result;
    $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");
    echo json_encode($data);
  }

  public function addtional_info_modal_content()
  {
  	$this->ajax_check();
  	$cart_id = $this->input->post("cart_id",true);
  	$cart_data = $this->basic->get_data("ecommerce_cart",array("where"=>array("id"=>$cart_id)),"manual_additional_info,manual_currency,manual_amount,paid_at");
  	$currency = isset($cart_data[0]["manual_currency"]) ? $cart_data[0]["manual_currency"] : "";
  	$manual_amount = isset($cart_data[0]["manual_amount"]) ? $cart_data[0]["manual_amount"] : "0";
  	$manual_additional_info = isset($cart_data[0]["manual_additional_info"]) ? $cart_data[0]["manual_additional_info"] : "";
  	$paid_at = isset($cart_data[0]["paid_at"]) ? date("M j, y H:i",strtotime($cart_data[0]["paid_at"])) : "";
  	// echo $this->db->last_query();

  	echo '<div class="list-group">
            <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
              <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">'.$this->lang->line("Paid Amount").' : '.$currency.' '.$this->two_decimal_place($manual_amount).'</h5>
              </div>
            </a>
            <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
              <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">'.$this->lang->line("Description").' : '.'</h5>
              </div>
              <p class="mb-1">'.$manual_additional_info.'</p>
            </a>
            <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
              <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">'.$this->lang->line("Paid at").' : '.$paid_at.'</h5>
              </div>
            </a>
          </div>';
  }

  public function change_payment_status()
  {
    $this->ajax_check();
    $id = $this->input->post("table_id",true);
    $payment_status = $this->input->post("payment_status",true);
    if($this->basic->update_data("ecommerce_cart",array("id"=>$id,"user_id"=>$this->user_id),array("status"=>$payment_status,"status_changed_at"=>date("Y-m-d H:i:s"))))
    echo json_encode(array('status'=>'1','message'=>$this->lang->line("Payment status has been updated successfully.")));
    else echo json_encode(array('status'=>'1','message'=>$this->lang->line("Something went wrong, please try again.")));
  }

  public function reminder_send_status_data()
  { 
    $this->ajax_check();

    $data_days = 30; 
    $from_date = $this->session->userdata("ecommerce_from_date");
    $to_date = $this->session->userdata("ecommerce_to_date");
    if($to_date=='') $to_date = date("Y-m-d");
    if($from_date=='') $from_date = date("Y-m-d",strtotime("$to_date - ".$data_days." days"));

    $search_value = $_POST['search']['value'];
    $page_id = $this->input->post("page_id",true);

    $display_columns = 
    array(
      "#",
      "CHECKBOX",
      'first_name',
      'last_name',
      'email',
      'subscriber_id',
      'last_completed_hour',
      'sent_at',
      'response',
      'cart_id'
    );
    $search_columns = array('first_name','last_name','email','subscriber_id');

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 7;
    $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'sent_at';
    $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
    $order_by=$sort." ".$order;

    $where_custom="user_id = ".$this->user_id." AND store_id = ".$this->session->userdata('ecommerce_selected_store')." AND sent_at >= '".$from_date."' AND sent_at <= '".$to_date."'";      

    if ($search_value != '') 
    {
        foreach ($search_columns as $key => $value) 
        $temp[] = $value." LIKE "."'%$search_value%'";
        $imp = implode(" OR ", $temp);
        $where_custom .=" AND (".$imp.") ";
    }
 
    $this->db->where($where_custom);
    
    $table="ecommerce_reminder_report";
    $info=$this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');
    // echo $this->db->last_query();

    $this->db->where($where_custom);
    $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join,$group_by='');

    $total_result=$total_rows_array[0]['total_rows'];

    foreach($info as $key => $value) 
    {
        if($info[$key]['is_sent']=='1' && $info[$key]['sent_at'] != "0000-00-00 00:00:00")
        $sent_time = date("M j, y H:i",strtotime($info[$key]['sent_at']));
        else $sent_time = '<span class="text-muted"><i class="fas fa-exclamation-circle"></i> '.$this->lang->line("Not Sent")."<span>";
        $info[$key]['sent_at'] =  $sent_time;

        $info[$key]['subscriber_id'] =  "<a href='".base_url("subscriber_manager/bot_subscribers/".$info[$key]['subscriber_id'])."' target='_BLANK'>".$info[$key]['subscriber_id']."</a>";
        
        $last_updated_at = date("M j, y H:i",strtotime($info[$key]['last_updated_at']));
        $info[$key]['last_updated_at'] =  $last_updated_at;

        $info[$key]['response'] =  "<a class='btn btn-sm btn-outline-primary woo_error_log' href='' data-id='".$info[$key]['id']."'><i class='fas fa-plug'></i> ".$this->lang->line('Response')."</a>";
        $info[$key]['cart_id'] =  "<a target='_BLANK' href='".base_url('ecommerce/order/'.$info[$key]['id'])."'>".$this->lang->line('Order').'#'.$info[$key]['id']."</a>";
    }
    $data['draw'] = (int)$_POST['draw'] + 1;
    $data['recordsTotal'] = $total_result;
    $data['recordsFiltered'] = $total_result;
    $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");
    echo json_encode($data);
  }

  public function reminder_response()
  {
    $this->ajax_check();
    $id = $this->input->post('id',true);
    $getdata = $this->basic->get_data("ecommerce_reminder_report",array("where"=>array("id"=>$id,"user_id"=>$this->user_id)),"sent_response");
    $response = isset($getdata[0]['sent_response']) ? $getdata[0]['sent_response'] : '';

    if(is_array(json_decode($response,true))) 
    {
        echo "<div class='list-group'>";
        $response = json_decode($response,true);
        foreach ($response as $key => $value) 
        {
          if($key=="messenger")
          {
            foreach ($value as $key2 => $value2) 
            {                  

              $tmp_heading = strtoupper($key)." : ".$key2;
              $tmp_status = (isset($value2['status']) && $value2['status']=='1') ? '<small class="text-success">'."SUCCESS".'</small>' : '<small class="text-danger">'."ERROR".'</small>';
              $tmp_response = isset($value2['response']) ? $value2['response'] : "";
              echo '
                <a  class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1" style="font-size: 1rem;">'.$tmp_heading.'</h5>
                  '.$tmp_status.'
                </div>
                <p class="mb-1 text-left" style="font-size: 12px;">'.$tmp_response.'</p>
              </a>';
            }
          }
          else
          {
              $tmp_heading = strtoupper($key);
              $tmp_status = (isset($value['status']) && $value['status']=='1') ? '<small class="text-success">'."SUCCESS".'</small>' : '<small class="text-danger">'."ERROR".'</small>';
              $tmp_response = isset($value['response']) ? $value['response'] : "";
              echo '
                <a  class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1" style="font-size: 1rem;">'.$tmp_heading.'</h5>
                  '.$tmp_status.'
                </div>
                <p class="mb-1 text-left" style="font-size: 12px;">'.$tmp_response.'</p>
              </a>';
          }
        }
        echo "</div>";
    } 
  }    

  public function add_store()
  {
    $data['body'] = 'ecommerce/store_add';
    $data['page_title'] = $this->lang->line("Create Store");
    $data['page_info'] = $this->get_user_page();
    
    $data['how_many_reminder'] = 3;
    $data['hours'] = $this->get_reminder_hour();

    $data['sms_option'] = $this->get_sms_api();
    $data['email_option'] = $this->get_email_api();
    $data['country_names'] = $this->get_country_names();
    $data['currency_icons'] = $this->currency_icon();
    $data['get_ecommerce_config'] = $this->get_ecommerce_config();
    $data["iframe"]="1";
    $this->_viewcontroller($data);    
    
  }

  public function add_store_action()
  {
    $this->ajax_check();
    $status=$this->_check_usage($module_id=268,$request=1);
    if($status=="3")  //monthly limit is exceeded, can not create another campaign this month
    {
        echo json_encode(array("status" => "0", "message" =>$this->lang->line("Limit has been exceeded. You can can not create more stores.")));
        exit();
    }

    $post=$_POST;
    $tag_allowed = array("email_reminder_text_checkout_next");
    foreach ($post as $key => $value) 
    {
      //$$key=$this->input->post($key,true);
      if(!is_array($value) && !in_array($key, $tag_allowed)) $temp = strip_tags($value);
      else $temp = $value;
      $$key=$this->security->xss_clean($temp);
    }

    if($paypal_enabled=='0' && $stripe_enabled=='0' && $manual_enabled=='0' && $cod_enabled=='0')
    {
    	echo json_encode(array("status" => "0", "message" =>$this->lang->line("You must select at least one payment method")));
        exit();
    }
    
    $messenger_content = array();
    $sms_content = array();
    $email_content = array();
    $created_at = date("Y-m-d H:i:s");
    $insert_data2 = array("user_id"=>$this->user_id,"page_id"=>$page,"created_at"=>$created_at);
    
    foreach ($msg_reminder_time as $key => $value) 
    {
      $i=$key;
      $j=$i+1;
      if($value!="")
      {            
        $tmp_msg_reminder_text = isset($msg_reminder_text[$i]) ? $msg_reminder_text[$i] : "";
        $tmp_msg_reminder_btn_details = isset($msg_reminder_btn_details[$i]) ? strtoupper($msg_reminder_btn_details[$i]) : "VISIT DETAILS";
        $tmp_msg_reminder_text_checkout = isset($msg_reminder_text_checkout[$i]) ? $msg_reminder_text_checkout[$i] : "Stock limited, complete your order before it is out of stock.";
        $tmp_msg_reminder_btn_checkout = isset($msg_reminder_btn_checkout[$i]) ? strtoupper($msg_reminder_btn_checkout[$i]) : "CHECKOUT NOW";
        $messenger_content['reminder'][$j] = array('hour'=>$value,"reminder_text"=>$tmp_msg_reminder_text,"reminder_btn_details"=>$tmp_msg_reminder_btn_details,"reminder_text_checkout"=>$tmp_msg_reminder_text_checkout,"reminder_btn_checkout"=>$tmp_msg_reminder_btn_checkout);
        $anything_found = true;
      }          
    }
    if($msg_reminder_text_checkout_next=="") $msg_reminder_text_checkout_next="You can see your order history and status here.";
    $messenger_content['checkout'] = array("checkout_text"=>$msg_checkout_text,"checkout_text_next"=>$msg_reminder_text_checkout_next,"checkout_btn_next"=>strtoupper($msg_checkout_btn_website));
    $insert_data2['messenger_content'] = json_encode($messenger_content);

    if($this->session->userdata('user_type') == 'Admin' || in_array(264,$this->module_access))
    {
      foreach ($sms_reminder_time as $key => $value) 
      {
        $i=$key;
        $j=$i+1;
        if($value!="")
        {           
          $temp_sms_reminder_text_checkout = isset($sms_reminder_text_checkout[$i]) ? $sms_reminder_text_checkout[$i] : "";
          $sms_content['reminder'][$j] = array('hour'=>$value,"reminder_text"=>$temp_sms_reminder_text_checkout);
          $anything_found = true;
        }
        
      }
      $sms_content['checkout'] = array("checkout_text"=>$sms_reminder_text_checkout_next);
      if(isset($sms_api_id) && $sms_api_id!="")
      {
        $insert_data2['sms_api_id'] = $sms_api_id;
        $insert_data2['sms_content'] = json_encode($sms_content);
      }
    }

    if($this->session->userdata('user_type') == 'Admin' || in_array(263,$this->module_access))
    {
      foreach ($email_reminder_time as $key => $value) 
      {
        $i=$key;  
        $j=$i+1; 
        if($value!="")
        {           
          $tmp_email_reminder_text_checkout = isset($email_reminder_text_checkout[$i]) ? $email_reminder_text_checkout[$i] : "";
          $email_content['reminder'][$j] = array('hour'=>$value,"reminder_text"=>$tmp_email_reminder_text_checkout);
          $anything_found = true;
        }
        
      }
      $email_content['checkout'] = array("checkout_text"=>$email_reminder_text_checkout_next);
      if(isset($email_api_id) && $email_api_id!="")
      {
        if($email_subject=="") $email_subject = "Order Update";
        $exp = explode('-', $email_api_id);
        $insert_data2['configure_email_table'] = isset($exp[0]) ? $exp[0] : '';
        $insert_data2['email_api_id'] = isset($exp[1]) ? $exp[1] : 0;
        $insert_data2['email_content'] = json_encode($email_content);
        $insert_data2['email_subject'] = $email_subject;
      }
    }       

    $store_unique_id = $this->user_id.time();
    if($this->basic->is_exist("ecommerce_store",array("store_unique_id"=>$store_unique_id))) 
    {
        echo json_encode(array("status" => "0", "message" =>$this->lang->line("Something went wrong, please try again.")));
        exit();
    }
  
    $this->db->trans_start(); 

    if(!isset($label_ids)) $label_ids=array();
    if(!isset($status) || $status=='') $status='0';
   
    $insert_data = array(
      "store_unique_id"=>$store_unique_id,
      "store_name"=>$store_name,
      "store_logo"=>$store_logo,
      "store_favicon"=>$store_favicon,
      "store_email"=> $store_email,
      "store_phone"=> $store_phone,
      "store_country"=> $store_country,
      "store_state"=> $store_state,
      "store_city"=> $store_city,
      "store_zip"=> $store_zip,
      "store_address"=> $store_address,
      "tax_percentage"=> $tax_percentage,
      "shipping_charge"=> $shipping_charge,
      "paypal_enabled"=> $paypal_enabled,
      "stripe_enabled"=> $stripe_enabled,
      "manual_enabled"=> $manual_enabled,
      "cod_enabled"=> $cod_enabled,
      "status"=> $status,
      "label_ids"=>implode(',',$label_ids),
    );

    $final_data = array_merge($insert_data,$insert_data2);
   
    $this->basic->insert_data("ecommerce_store",$final_data);
    $insert_id = $this->db->insert_id();
    $this->_insert_usage_log($module_id=268,$request=1);
    $this->db->trans_complete();

    if($this->db->trans_status() === false)
    {
         echo json_encode(array('status'=>'0','message'=>"".$this->lang->line('Something went wrong, please try again.')));
         exit();
    }
    else
    {
        echo json_encode(array('status'=>'1','message'=>$this->lang->line('Store has been created successfully.')));
        $this->session->set_userdata("ecommerce_selected_store",$insert_id);
        exit();
    } 
  }

  public function edit_store($id=0)
  {
    if($id==0) exit();
    $data['body'] = 'ecommerce/store_edit';
    $data['page_title'] = $this->lang->line("Edit Store");
    $data['page_info'] = $this->get_user_page();
    
    $data['how_many_reminder'] = 3;
    $data['hours'] = $this->get_reminder_hour();

    $data['sms_option'] = $this->get_sms_api();
    $data['email_option'] = $this->get_email_api();
    $data['country_names'] = $this->get_country_names();
    $data['currency_icons'] = $this->currency_icon();
    $data['get_ecommerce_config'] = $this->get_ecommerce_config();

    $xdata=$this->basic->get_data("ecommerce_store",array("where"=>array("id"=>$id,"user_id"=>$this->user_id)));
    if(!isset($xdata[0])) exit();
    $data['xdata']=$xdata[0];
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }

  public function edit_store_action()
  {
    $this->ajax_check();
    $post=$_POST;
    $tag_allowed = array("email_reminder_text_checkout_next");
    foreach ($post as $key => $value) 
    {
      //$$key=$this->input->post($key,true);
      if(!is_array($value) && !in_array($key, $tag_allowed)) $temp = strip_tags($value);
      else $temp = $value;
      $$key=$this->security->xss_clean($temp);
    }

    if($paypal_enabled=='0' && $stripe_enabled=='0' && $manual_enabled=='0' && $cod_enabled=='0')
    {
    	echo json_encode(array("status" => "0", "message" =>$this->lang->line("You must select at least one payment method")));
        exit();
    }       

    $xdata=$this->basic->get_data("ecommerce_store",array("where"=>array("id"=>$hidden_id,"user_id"=>$this->user_id)));
    $xstore_logo = isset($xdata[0]['store_logo']) ? $xdata[0]['store_logo'] : "";
    $xstore_favicon = isset($xdata[0]['store_favicon']) ? $xdata[0]['store_favicon'] : "";
    
    $messenger_content = array();
    $sms_content = array();
    $email_content = array();
    $created_at = date("Y-m-d H:i:s");
    $insert_data2 = array();
    
    foreach ($msg_reminder_time as $key => $value) 
    {
      $i=$key;
      $j=$i+1;
      if($value!="")
      {            
        $tmp_msg_reminder_text = isset($msg_reminder_text[$i]) ? $msg_reminder_text[$i] : "";
        $tmp_msg_reminder_btn_details = isset($msg_reminder_btn_details[$i]) ? strtoupper($msg_reminder_btn_details[$i]) : "VISIT DETAILS";
        $tmp_msg_reminder_text_checkout = isset($msg_reminder_text_checkout[$i]) ? $msg_reminder_text_checkout[$i] : "Stock limited, complete your order before it is out of stock.";
        $tmp_msg_reminder_btn_checkout = isset($msg_reminder_btn_checkout[$i]) ? strtoupper($msg_reminder_btn_checkout[$i]) : "CHECKOUT NOW";
        $messenger_content['reminder'][$j] = array('hour'=>$value,"reminder_text"=>$tmp_msg_reminder_text,"reminder_btn_details"=>$tmp_msg_reminder_btn_details,"reminder_text_checkout"=>$tmp_msg_reminder_text_checkout,"reminder_btn_checkout"=>$tmp_msg_reminder_btn_checkout);
        $anything_found = true;
      }          
    }
    if($msg_reminder_text_checkout_next=="") $msg_reminder_text_checkout_next="You can see your order history and status here.";
    $messenger_content['checkout'] = array("checkout_text"=>$msg_checkout_text,"checkout_text_next"=>$msg_reminder_text_checkout_next,"checkout_btn_next"=>strtoupper($msg_checkout_btn_website));
    $insert_data2['messenger_content'] = json_encode($messenger_content);

    if($this->session->userdata('user_type') == 'Admin' || in_array(264,$this->module_access))
    {
      foreach ($sms_reminder_time as $key => $value) 
      {
        $i=$key;
        $j=$i+1;
        if($value!="")
        {           
          $temp_sms_reminder_text_checkout = isset($sms_reminder_text_checkout[$i]) ? $sms_reminder_text_checkout[$i] : "";
          $sms_content['reminder'][$j] = array('hour'=>$value,"reminder_text"=>$temp_sms_reminder_text_checkout);
          $anything_found = true;
        }
        
      }
      $sms_content['checkout'] = array("checkout_text"=>$sms_reminder_text_checkout_next);
      if(isset($sms_api_id) && $sms_api_id!="")
      {
        $insert_data2['sms_api_id'] = $sms_api_id;
        $insert_data2['sms_content'] = json_encode($sms_content);
      }
    }

    if($this->session->userdata('user_type') == 'Admin' || in_array(263,$this->module_access))
    {
      foreach ($email_reminder_time as $key => $value) 
      {
        $i=$key;  
        $j=$i+1; 
        if($value!="")
        {           
          $tmp_email_reminder_text_checkout = isset($email_reminder_text_checkout[$i]) ? $email_reminder_text_checkout[$i] : "";
          $email_content['reminder'][$j] = array('hour'=>$value,"reminder_text"=>$tmp_email_reminder_text_checkout);
          $anything_found = true;
        }
        
      }
      $email_content['checkout'] = array("checkout_text"=>$email_reminder_text_checkout_next);
      if(isset($email_api_id) && $email_api_id!="")
      {
        if($email_subject=="") $email_subject = "Order Update";
        $exp = explode('-', $email_api_id);
        $insert_data2['configure_email_table'] = isset($exp[0]) ? $exp[0] : '';
        $insert_data2['email_api_id'] = isset($exp[1]) ? $exp[1] : 0;
        $insert_data2['email_content'] = json_encode($email_content);
        $insert_data2['email_subject'] = $email_subject;
      }
    }
  
    $this->db->trans_start(); 

    if(!isset($label_ids)) $label_ids=array();
    if(!isset($status) || $status=='') $status='0';
   
    $insert_data = array(
      "store_name"=>$store_name,
      "store_email"=> $store_email,
      "store_phone"=> $store_phone,
      "store_country"=> $store_country,
      "store_state"=> $store_state,
      "store_city"=> $store_city,
      "store_zip"=> $store_zip,
      "store_address"=> $store_address,
      "tax_percentage"=> $tax_percentage,
      "shipping_charge"=> $shipping_charge,
      "paypal_enabled"=> $paypal_enabled,
      "stripe_enabled"=> $stripe_enabled,
      "cod_enabled"=> $cod_enabled,
      "manual_enabled"=> $manual_enabled,
      "status"=> $status,
      "label_ids"=>implode(',',$label_ids),
    );
    if($store_logo!='') 
    {
      $insert_data["store_logo"] = $store_logo;
      if($xstore_logo!='') @unlink('upload/ecommerce/'.$xstore_logo);
    }
    if($store_favicon!='') 
    {
      $insert_data["store_favicon"] = $store_favicon;
      if($xstore_favicon!='') @unlink('upload/ecommerce/'.$xstore_favicon);
    }

    $final_data = array_merge($insert_data,$insert_data2);
   
    $this->basic->update_data("ecommerce_store",array("id"=>$hidden_id,"user_id"=>$this->user_id),$final_data);
    $this->db->trans_complete();

    if($this->db->trans_status() === false)
    {
         echo json_encode(array('status'=>'0','message'=>"".$this->lang->line('Something went wrong, please try again.')));
         exit();
    }
    else
    {
        echo json_encode(array('status'=>'1','message'=>$this->lang->line('Store has been updated successfully.')));
        exit();
    } 
  }

  public function product_list()
  {
    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Store");
    $data['store_list'] = $store_list;

    $data['body'] = 'ecommerce/product_list';
    $data['page_title'] = $this->lang->line('Product')." : ".$this->session->userdata("ecommerce_selected_store_title");
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }

  public function product_list_data()
  { 
    $this->ajax_check();
    $ecommerce_config = $this->get_ecommerce_config();

    $search_value = $this->input->post("search_value");
    $store_id = $this->input->post("search_store_id");        
    $search_date_range = $this->input->post("search_date_range");

    $display_columns = 
    array(
      "#",
      "CHECKBOX",
      "thumbnail",
      'product_name',
      'original_price',
      'store_name',
      'status',
      'actions',
      'taxable',
      'category_name',
      'updated_at',
    );
    $search_columns = array('product_name','original_price','sell_price');

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 3;
    $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'product_name';
    $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'asc';
    $order_by=$sort." ".$order;

    $where_custom="ecommerce_product.user_id = ".$this->user_id;

    if ($search_value != '') 
    {
        foreach ($search_columns as $key => $value) 
        $temp[] = $value." LIKE "."'%$search_value%'";
        $imp = implode(" OR ", $temp);
        $where_custom .=" AND (".$imp.") ";
    }
    if($search_date_range!="")
    {
        $exp = explode('|', $search_date_range);
        $from_date = isset($exp[0])?$exp[0]:"";
        $to_date = isset($exp[1])?$exp[1]:"";
        if($from_date!="Invalid date" && $to_date!="Invalid date")
        $where_custom .= " AND ecommerce_product.updated_at >= '{$from_date}' AND ecommerce_product.updated_at <='{$to_date}'";
    }
    $this->db->where($where_custom);

    if($store_id!="") $this->db->where(array("ecommerce_product.store_id"=>$store_id));       
    
    $table="ecommerce_product";
    $select = "ecommerce_product.*,ecommerce_store.store_name,ecommerce_category.category_name";
    $join = array('ecommerce_store'=>"ecommerce_store.id=ecommerce_product.store_id,left",'ecommerce_category'=>"ecommerce_category.id=ecommerce_product.category_id,left");
    $info=$this->basic->get_data($table,$where='',$select,$join,$limit,$start,$order_by,$group_by='');
    // echo $this->db->last_query(); exit();
    
    $this->db->where($where_custom);
    $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join,$group_by='');

    $total_result=$total_rows_array[0]['total_rows'];
    $config_currency = isset($ecommerce_config['currency']) ? $ecommerce_config['currency'] : "USD";
    $config_currency_icon = isset($this->currency_icon[$config_currency]) ? $this->currency_icon[$config_currency] : "$";

    foreach($info as $key => $value) 
    {
        $updated_at = date("M j, y H:i",strtotime($info[$key]['updated_at']));
        $info[$key]['updated_at'] =  "<div style='min-width:110px;'>".$updated_at."</div>";

        $info[$key]['actions'] = "<div style='min-width:150px'><a target='_BLANK' href='".base_url("ecommerce/product/".$info[$key]['id'])."' title='".$this->lang->line("Product Page")."' data-toggle='tooltip' class='btn btn-circle btn-outline-info'><i class='fa fa-eye'></i></a>&nbsp;&nbsp;";
        $info[$key]['actions'] .= "<a href='".base_url("ecommerce/edit_product/".$info[$key]['id'])."' title='".$this->lang->line("Edit")."' data-toggle='tooltip' class='btn btn-circle btn-outline-warning edit_row' table_id='".$info[$key]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";
        $info[$key]['actions'] .= "<a href='#' title='".$this->lang->line("Delete")."' data-toggle='tooltip' class='btn btn-circle btn-outline-danger delete_row' table_id='".$info[$key]['id']."'><i class='fa fa-trash-alt'></i></a></div>
            <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";

        if($info[$key]['status'] == 1) $info[$key]['status'] = "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Active')."</span>";
        else $info[$key]['status'] = "<span class='badge badge-status text-danger'><i class='fa fa-times-circle red'></i> ".$this->lang->line('Inactive')."</span>"; 

        if($info[$key]['taxable'] == 1) $info[$key]['taxable'] = "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Yes')."</span>";
        else $info[$key]['taxable'] = "<span class='badge badge-status text-danger'><i class='fa fa-times red'></i> ".$this->lang->line('No')."</span>";

        if($info[$key]['sell_price']>0) $info[$key]['original_price'] = "<span style='text-decoration: line-through;' class='text-muted'>".$config_currency_icon.$this->two_decimal_place($info[$key]['original_price']) ."</span> <b class='text-warning'>".$config_currency_icon.$this->two_decimal_place($info[$key]['sell_price'])."</b>";
        else $info[$key]['original_price'] = "<b>".$config_currency_icon.$this->two_decimal_place($info[$key]['original_price'])."</b>";

        if($info[$key]['thumbnail']=='') $url = base_url('assets/img/products/product-1.jpg');
        else $url = base_url('upload/ecommerce/'.$info[$key]['thumbnail']);
        $info[$key]['thumbnail'] = "<a  target='_BLANK' href='".$url."'><img class='img-fluid' style='height:80px;width:80px;border-radius:4px;border:1px solid #eee;padding:2px;' src='".$url."'></a>";
    }
    $data['draw'] = (int)$_POST['draw'] + 1;
    $data['recordsTotal'] = $total_result;
    $data['recordsFiltered'] = $total_result;
    $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");
    echo json_encode($data);
  }

  public function delete_store($campaign_id=0)
  {   
    $this->ajax_check();
    $id = $this->input->post('campaign_id',true);
    $response = array();
    $xdata=$this->basic->get_data("ecommerce_store",array("where"=>array("id"=>$id,"user_id"=>$this->user_id)));
    if(!isset($xdata[0]))
    {
        $response['status'] = '0';
        $response['message'] = $this->lang->line('Something went wrong, please try once again.');
    }
    $xstore_logo = isset($xdata[0]['store_logo']) ? $xdata[0]['store_logo'] : "";
    $xstore_favicon = isset($xdata[0]['store_favicon']) ? $xdata[0]['store_favicon'] : "";

    $this->db->trans_start();
    $this->basic->delete_data('ecommerce_store',$where=array('id'=>$id,"user_id"=>$this->user_id));
    $this->basic->delete_data('ecommerce_product',$where=array('store_id'=>$id,"user_id"=>$this->user_id));
    $this->basic->delete_data('ecommerce_coupon',$where=array('store_id'=>$id,"user_id"=>$this->user_id));
    $this->basic->delete_data('ecommerce_cart',$where=array('store_id'=>$id,"user_id"=>$this->user_id));
    $this->basic->delete_data('ecommerce_cart_item',$where=array('store_id'=>$id));
    $this->basic->delete_data('ecommerce_reminder_report',$where=array('store_id'=>$id,"user_id"=>$this->user_id));
    $this->basic->delete_data('ecommerce_category',$where=array('store_id'=>$id,"user_id"=>$this->user_id));
    $this->basic->delete_data('ecommerce_attribute',$where=array('store_id'=>$id,"user_id"=>$this->user_id));
    //******************************//
    // delete data to useges log table
    $this->_delete_usage_log($module_id=268,$request=1);   
    //******************************//

    $this->db->trans_complete();
    if($this->db->trans_status() === false) 
    {
      $response['status'] = '0';
      $response['message'] = $this->lang->line('Something went wrong.');
    } 
    else 
    {
      if($xstore_logo!='') @unlink('upload/ecommerce/'.$xstore_logo);         
      if($xstore_favicon!='') @unlink('upload/ecommerce/'.$xstore_favicon);          
      $response['status'] = '1';
      $response['message'] = $this->lang->line('Store has been deleted successfully.');
      $this->session->unset_userdata("ecommerce_selected_store");
    }
    echo json_encode($response);
  }


  public function add_product()
  {       
    $data['body']='ecommerce/product_add';     
    $data['page_title']=$this->lang->line('Add Product')." : ".$this->session->userdata("ecommerce_selected_store_title");

    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Select Store");
    $data['store_list'] = $store_list;

    $category_list = $this->get_category_list();
    $category_list[''] = $this->lang->line("Select Category");
    $data['category_list'] = $category_list;

    $attribute_list = $this->get_attribute_list();
    $data['attribute_list'] = $attribute_list;

    $data['ecommerce_config'] = $this->get_ecommerce_config();
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }


  public function add_product_action() 
  {
    if($_SERVER['REQUEST_METHOD'] === 'GET') 
    redirect('home/access_forbidden','location');

    if($_POST)
    {
        $this->form_validation->set_rules('store_id', '<b>'.$this->lang->line("Store").'</b>', 'trim|required');      
        $this->form_validation->set_rules('product_name', '<b>'.$this->lang->line("Product name").'</b>', 'trim|required');      
        $this->form_validation->set_rules('original_price', '<b>'.$this->lang->line("Original price").'</b>', 'trim|required|numeric');
        $this->form_validation->set_rules('sell_price', '<b>'.$this->lang->line("Sell price").'</b>', 'trim|numeric');
        $this->form_validation->set_rules('product_description', '<b>'.$this->lang->line("Product description").'</b>', 'trim');      
        $this->form_validation->set_rules('purchase_note', '<b>'.$this->lang->line("Purchase note").'</b>', 'trim');      
        $this->form_validation->set_rules('thumbnail', '<b>'.$this->lang->line("Thumbnail").'</b>', 'trim');      
            
        if ($this->form_validation->run() == FALSE)
        {
            $this->add_product(); 
        }
        else
        {   
            $store_id=$this->input->post('store_id',true);
            $category_id=$this->input->post('category_id',true);
            $attribute_ids=$this->input->post('attribute_ids',true);
            $product_name=strip_tags($this->input->post('product_name',true));
            $original_price=$this->input->post('original_price',true);
            $sell_price=$this->input->post('sell_price',true);
            $product_description=$this->input->post('product_description',true);
            $purchase_note=$this->input->post('purchase_note',true);
            $thumbnail=$this->input->post('thumbnail',true);
            $taxable=$this->input->post('taxable',true);
            $status=$this->input->post('status',true);

            if($status=='') $status='0';
            if($taxable=='') $taxable='0';
            if(!isset($attribute_ids) || !is_array($attribute_ids) || empty($attribute_ids)) $attribute_ids = '';
            else $attribute_ids = implode(',', $attribute_ids);
                                                   
            $data=array
            (
                'store_id'=>$store_id,
                'category_id'=>$category_id,
                'attribute_ids'=>$attribute_ids,
                'product_name'=>$product_name,
                'original_price'=>$original_price,
                'sell_price'=>$sell_price,
                'product_description'=>$product_description,
                'purchase_note'=>$purchase_note,
                'thumbnail'=>$thumbnail,
                'taxable' => $taxable,
                'status'=> $status,
                'user_id'=> $this->user_id,
                'deleted'=>'0',
                'updated_at'=>date("Y-m-d H:i:s")
            );
            
            if($this->basic->insert_data('ecommerce_product',$data)) $this->session->set_flashdata('success_message',1);   
            else $this->session->set_flashdata('error_message',1);     
            
            redirect('ecommerce/product_list','location');                 
            
        }
    }   
  }


  public function edit_product($id='0')
  {       
    if($id=='0') exit();
    $data['body']='ecommerce/product_edit';     
    $data['page_title']=$this->lang->line('Edit Product')." : ".$this->session->userdata("ecommerce_selected_store_title");

    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Select Store");
    $data['store_list'] = $store_list;

    $category_list = $this->get_category_list();
    $category_list[''] = $this->lang->line("Select Category");
    $data['category_list'] = $category_list;

    $attribute_list = $this->get_attribute_list();
    $data['attribute_list'] = $attribute_list;

    $data['ecommerce_config'] = $this->get_ecommerce_config();

    $xdata = $this->basic->get_data("ecommerce_product",array('where'=>array('id'=>$id,"user_id"=>$this->user_id)));
    if(!isset($xdata[0])) exit();
    $data['xdata'] = $xdata[0];
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }


  public function edit_product_action() 
  {
    if($_SERVER['REQUEST_METHOD'] === 'GET') 
    redirect('home/access_forbidden','location');

    if($_POST)
    {
        $id=$this->input->post('hidden_id',true);
        // $this->form_validation->set_rules('store_id', '<b>'.$this->lang->line("Store").'</b>', 'trim|required');      
        $this->form_validation->set_rules('product_name', '<b>'.$this->lang->line("Product name").'</b>', 'trim|required');      
        $this->form_validation->set_rules('original_price', '<b>'.$this->lang->line("Original price").'</b>', 'trim|required|numeric');
        $this->form_validation->set_rules('sell_price', '<b>'.$this->lang->line("Sell price").'</b>', 'trim|numeric');
        $this->form_validation->set_rules('product_description', '<b>'.$this->lang->line("Product description").'</b>', 'trim');      
        $this->form_validation->set_rules('purchase_note', '<b>'.$this->lang->line("Purchase note").'</b>', 'trim');      
        $this->form_validation->set_rules('thumbnail', '<b>'.$this->lang->line("Thumbnail").'</b>', 'trim');      
            
        if ($this->form_validation->run() == FALSE)
        {
            $this->edit_product($id); 
        }
        else
        {   
            $xdata = $this->basic->get_data("ecommerce_product",array('where'=>array('id'=>$id,"user_id"=>$this->user_id)));
            $xthumbnail = isset($xdata[0]['thumbnail']) ? $xdata[0]['thumbnail'] : "";

            // $store_id=$this->input->post('store_id',true);
            $category_id=$this->input->post('category_id',true);
            $attribute_ids=$this->input->post('attribute_ids',true);
            $product_name=strip_tags($this->input->post('product_name',true));
            $original_price=$this->input->post('original_price',true);
            $sell_price=$this->input->post('sell_price',true);
            $product_description=$this->input->post('product_description',true);
            $purchase_note=$this->input->post('purchase_note',true);
            $thumbnail=$this->input->post('thumbnail',true);
            $taxable=$this->input->post('taxable',true);
            $status=$this->input->post('status',true);

            if($status=='') $status='0';
            if($taxable=='') $taxable='0';
            if(!isset($attribute_ids) || !is_array($attribute_ids) || empty($attribute_ids)) $attribute_ids = '';
            else $attribute_ids = implode(',', $attribute_ids);
                                                   
            $data=array
            (
                'category_id'=>$category_id,
                'attribute_ids'=>$attribute_ids,
                'product_name'=>$product_name,
                'original_price'=>$original_price,
                'sell_price'=>$sell_price,
                'product_description'=>$product_description,
                'purchase_note'=>$purchase_note,
                'taxable' => $taxable,
                'status'=> $status,
                'updated_at'=>date("Y-m-d H:i:s")
            );
            if($thumbnail!='') 
            {
              $data['thumbnail'] = $thumbnail;
              if($xthumbnail!='') @unlink('upload/ecommerce/'.$xthumbnail);
            }
            
            if($this->basic->update_data('ecommerce_product',array("id"=>$id,"user_id"=>$this->user_id),$data)) $this->session->set_flashdata('success_message',1);   
            else $this->session->set_flashdata('error_message',1);     
            
            redirect('ecommerce/product_list','location');                 
            
        }
    }   
  }

  public function delete_product()
  {
    $this->ajax_check();
    $table_id = $this->input->post("table_id");

    $xdata=$this->basic->get_data("ecommerce_product",array("where"=>array("id"=>$table_id,"user_id"=>$this->user_id)));
    if(!isset($xdata[0]))
    {
        $response['status'] = '0';
        $response['message'] = $this->lang->line('Something went wrong, please try once again.');
    }
    $xthumbnail = isset($xdata[0]['thumbnail']) ? $xdata[0]['thumbnail'] : "";


    $result = array('status'=>'0','message'=>$this->lang->line("Something went wrong, please try once again."));
    if($table_id == "0" || $table_id == "")
    {
        echo json_encode($result); 
        exit();
    }
    if($this->basic->update_data("ecommerce_product", array("id"=>$table_id,'user_id'=>$this->user_id),array('deleted'=>'1')))
    {
      echo json_encode(array('message' => $this->lang->line("Product has been deleted successfully."),'status'=>'1'));
      if($xthumbnail!='') @unlink('upload/ecommerce/'.$xthumbnail);     
    }       
    else echo json_encode($result);  
  }  

  public function payment_accounts()
  {     
    $data['body'] = "ecommerce/payment_accounts";
    $data['page_title'] = $this->lang->line('E-commerce Payment Accounts');
    $data['xvalue'] = $this->get_ecommerce_config();
    if($this->is_demo == '1')$data["xvalue"]["stripe_secret_key"]=$data["xvalue"]["stripe_publishable_key"]=$data["xvalue"]["paypal_email"]="XXXXXXXXXX";
    $currency_list = $this->basic->get_enum_values_assoc("payment_config","currency");
    asort($currency_list);
    $data['currency_list'] = $currency_list;
    $data['currecny_list_all'] = $this->currecny_list_all();
    $this->_viewcontroller($data);
  }

  public function payment_accounts_action()
  {
    if($this->is_demo == '1')
    {
        echo "<h2 style='text-align:center;color:red;border:1px solid red; padding: 10px'>This feature is disabled in this demo.</h2>"; 
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') redirect('home/access_forbidden', 'location');
    if ($_POST) 
    {
        // validation
        $this->form_validation->set_rules('paypal_email','<b>'.$this->lang->line("Paypal Email").'</b>','trim');
        $this->form_validation->set_rules('paypal_mode','<b>'.$this->lang->line("Paypal Sandbox Mode").'</b>','trim');
        $this->form_validation->set_rules('stripe_secret_key','<b>'.$this->lang->line("Stripe Secret Key").'</b>','trim');
        $this->form_validation->set_rules('stripe_publishable_key','<b>'.$this->lang->line("Stripe Publishable Key").'</b>','trim');
        $this->form_validation->set_rules('currency','<b>'.$this->lang->line("Currency").'</b>',  'trim');
        $this->form_validation->set_rules('manual_payment_instruction','<b>'.$this->lang->line("Manual Payment Instruction").'</b>',  'trim');            

        // go to config form page if validation wrong
        if ($this->form_validation->run() == false) 
        {
            return $this->accounts();
        } 
        else 
        {
            // assign
            $paypal_email=strip_tags($this->input->post('paypal_email',true));
            $paypal_payment_type=strip_tags($this->input->post('paypal_payment_type',true));
            $paypal_mode=strip_tags($this->input->post('paypal_mode',true));
            $stripe_secret_key=strip_tags($this->input->post('stripe_secret_key',true));
            $stripe_publishable_key=strip_tags($this->input->post('stripe_publishable_key',true));
            $currency=strip_tags($this->input->post('currency',true));
            // $manual_payment=$this->input->post('manual_payment');
            $manual_payment='1';
            $manual_payment_instruction=$this->input->post('manual_payment_instruction',true);

            if($paypal_mode=="") $paypal_mode="live";
            if($manual_payment=="") $manual_payment="0";

            $update_data = 
            array
            (
                'paypal_email'=>$paypal_email,
                'paypal_mode'=>$paypal_mode,
                'stripe_secret_key'=>$stripe_secret_key,
                'stripe_publishable_key'=>$stripe_publishable_key,
                'currency'=>$currency,
                'manual_payment'=> $manual_payment,
                'manual_payment_instruction'=>$manual_payment_instruction,
                'user_id'=>$this->user_id,
                'updated_at'=>date("Y-m-d H:i:s")
            );

            $get_data = $this->basic->get_data("ecommerce_config",array("where"=>array("user_id"=>$this->user_id)));
            if(isset($get_data[0]))
            $this->basic->update_data("ecommerce_config",array("user_id"=>$this->user_id),$update_data);
            else $this->basic->insert_data("ecommerce_config",$update_data);      
                                     
            $this->session->set_flashdata('success_message', 1);
            redirect('ecommerce/payment_accounts', 'location');
        }
    }
  }

  public function attribute_list()
  {
    $data['body'] = 'ecommerce/attribute_list'; 
    $data['page_title'] = $this->lang->line('Attribute')." : ".$this->session->userdata("ecommerce_selected_store_title");
    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Store");
    $data['store_list'] = $store_list; 
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }

  public function attribute_list_data()
  {
    $search_value = $_POST['search']['value'];
    $display_columns = array("#",'CHECKBOX','attribute_name','attribute_values','status','actions','store_name','updated_at');
    $search_columns = array('attribute_name', 'attribute_values','store_name');

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
    $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'attribute_name';
    $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'asc';
    $order_by=$sort." ".$order;

    $where_custom = '';
    $where_custom="ecommerce_attribute.user_id = ".$this->user_id." AND ecommerce_attribute.store_id = ".$this->session->userdata("ecommerce_selected_store");
    if($search_value != '') 
    {
        foreach ($search_columns as $key => $value) 
        $temp[] = $value." LIKE "."'%$search_value%'";
        $imp = implode(" OR ", $temp);
        $where_custom .=" AND (".$imp.") ";
    }   

    $table = "ecommerce_attribute";
    $select = "ecommerce_attribute.*,ecommerce_store.store_name";
    $join = array('ecommerce_store'=>"ecommerce_store.id=ecommerce_attribute.store_id,left");
    $this->db->where($where_custom);
    $info = $this->basic->get_data($table,$where='',$select,$join,$limit,$start,$order_by,$group_by='');

    $this->db->where($where_custom);
    $total_rows_array = $this->basic->count_row($table,$where='',$count="ecommerce_attribute.id",$join,$group_by='');
    $total_result=$total_rows_array[0]['total_rows'];

    foreach ($info as $key => $value) 
    {
        $info[$key]['attribute_values'] = implode(', ', json_decode($info[$key]['attribute_values'],true));
        $info[$key]['actions'] = "<div style='min-width:100px'><a href='#' title='".$this->lang->line("Edit")."' data-toggle='tooltip' class='btn btn-circle btn-outline-warning edit_row' table_id='".$info[$key]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";

        $info[$key]['actions'] .= "<a href='#' title='".$this->lang->line("Delete")."' data-toggle='tooltip' class='btn btn-circle btn-outline-danger delete_row' table_id='".$info[$key]['id']."'><i class='fa fa-trash-alt'></i></a></div>
            <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";

        if($info[$key]['status'] == 1) $info[$key]['status'] = "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Active')."</span>";
        else $info[$key]['status'] = "<span class='badge badge-status text-danger'><i class='fa fa-times-circle red'></i> ".$this->lang->line('Inactive')."</span>";

        $info[$key]['updated_at'] = date("jS M y H:i",strtotime($info[$key]['updated_at']));     
    }

    $data['draw'] = (int)$_POST['draw'] + 1;
    $data['recordsTotal'] = $total_result;
    $data['recordsFiltered'] = $total_result;
    $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");
    echo json_encode($data);
  }

  public function ajax_create_new_attribute()
  {
    $this->ajax_check();
    $result = array('status'=>'0','message'=>$this->lang->line("Something went wrong, please try once again."));

    if($_POST) 
    {
        $attribute_name = strip_tags($this->input->post("attribute_name",true));
        $store_id = $this->input->post("store_id",true);

        $attribute_values = $this->input->post("attribute_values",true);
        if(!is_array($attribute_values)) $attribute_values=array();

        $status = $this->input->post("status",true);
        if(!isset($status) || $status=='') $status='0';

        $inserted_data = array
        (
        	"store_id"=>$store_id,
          "attribute_name"=>$attribute_name,
        	"attribute_values"=>json_encode($attribute_values),
        	"status"=>$status,
        	"user_id"=>$this->user_id,
          "updated_at"=>date("Y-m-d H:i:s")
        );

        if($this->basic->insert_data("ecommerce_attribute",$inserted_data))
        {
            $result['status'] = "1";
            $result['message'] = $this->lang->line("Attribute has been added successfully.");
        }            

        echo json_encode($result);
    }
  }

  public function ajax_get_attribute_update_info()
  {

    $this->ajax_check();

    $table_id = $this->input->post("table_id");
    $user_id = $this->user_id;

    if($table_id == "0" || $table_id == "") exit;

    $details = $this->basic->get_data("ecommerce_attribute",array('where'=>array('id'=> $table_id, 'user_id'=> $user_id)));
    $values = json_decode($details[0]['attribute_values'],true);
    $selected=($details[0]['status']=='1') ? 'checked' : '';

    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Store");

    $form = ' <div class="row">
                <div class="col-12">                    
                  <form action="#" enctype="multipart/form-data" id="row_update_form" method="post">
                    <input type="hidden" name="table_id" value="'.$table_id.'">
                    <div class="row">

                        <div class="col-12">
                          <div class="form-group">
                            <label for="name">'.$this->lang->line("Store").' *</label>
                            '.form_dropdown('', $store_list, $details[0]['store_id'],' style="width:100%;" disabled class="form-control seelct"').'
                          </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Attribute Name').' *</label>
                                <input type="text" class="form-control" name="attribute_name2" id="attribute_name2" value="'.$details[0]['attribute_name'].'">
                            </div>
                        </div>

                        <div class="col-12">
                          <div class="form-group">
                            <label>'.$this->lang->line('Attribute Values').' * ('.$this->lang->line('comma separated').')</label>
                            <select name="attribute_values2[]" id="attribute_values2" multiple class="form-control" style="width:100%;">';
                            foreach($values as $val)
                            $form .='<option value="'.$val.'" selected>'.$val.'</option>';
          							    $form .= '
          							    </select>
          		            </div>
          		          </div>

    		                <div class="col-12">
                          <div class="form-group">
                            <label class="custom-switch mt-2">
                              <input type="checkbox" name="status2" id="status2" value="1" class="custom-switch-input" '.$selected.'>
                              <span class="custom-switch-indicator"></span>
                              <span class="custom-switch-description">'.$this->lang->line('Active').'</span>
                            </label>
                            </div>
                        </div>
                		</div>
                  </form>
                </div>
              </div>
              <script>$("#attribute_values2").select2();$("#attribute_values2").select2({placeholder: "",tags: true,tokenSeparators: [","," "]});';
    echo $form;
  }

  public function ajax_update_attribute()
  {
    $this->ajax_check();
    $result = array('status'=>'0','message'=>$this->lang->line("Something went wrong, please try once again."));

    if($_POST) 
    {
    	$table_id = $this->input->post("table_id",true);
        $attribute_name = strip_tags($this->input->post("attribute_name2",true));

        $attribute_values = $this->input->post("attribute_values2",true);
        if(!is_array($attribute_values)) $attribute_values=array();

        $status = $this->input->post("status2",true);
        if(!isset($status) || $status=='') $status='0';

        $updated_data = array
        (
        	"attribute_name"=>$attribute_name,
        	"attribute_values"=>json_encode($attribute_values),
        	"status"=>$status,
          "updated_at"=>date("Y-m-d H:i:s")
        );

        if($this->basic->update_data("ecommerce_attribute",array("id"=>$table_id,"user_id"=>$this->user_id),$updated_data))
        {
            $result['status'] = "1";
            $result['message'] = $this->lang->line("Attribute has been updated successfully.");
        }                     

        echo json_encode($result);
    }
  }

  public function delete_attribute()
  {
    $this->ajax_check();
    $table_id = $this->input->post("table_id");
    $result = array('status'=>'0','message'=>$this->lang->line("Something went wrong, please try once again."));
    if($table_id == "0" || $table_id == "")
    {
    	echo json_encode($result); 
    	exit();
    }

    if($this->basic->delete_data("ecommerce_attribute", array("id"=>$table_id,'user_id'=>$this->user_id)))       	
    echo json_encode(array('message' => $this->lang->line("Attribute has been deleted successfully."),'status'=>'1'));       	
    else echo json_encode($result);  
  }


  public function category_list()
  {
    $data['body'] = 'ecommerce/category_list'; 
    $data['page_title'] = $this->lang->line('Category')." : ".$this->session->userdata("ecommerce_selected_store_title");
    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Store");
    $data['store_list'] = $store_list; 
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }

  public function category_list_data()
  {
    $search_value = $_POST['search']['value'];
    $display_columns = array("#",'CHECKBOX','category_name','status','actions','store_name','updated_at',);
    $search_columns = array('category_name','store_name');

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
    $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'category_name';
    $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'asc';
    $order_by=$sort." ".$order;

    $where_custom = '';
    $where_custom="ecommerce_category.user_id = ".$this->user_id." AND ecommerce_category.store_id = ".$this->session->userdata("ecommerce_selected_store");
    if($search_value != '') 
    {
        foreach ($search_columns as $key => $value) 
        $temp[] = $value." LIKE "."'%$search_value%'";
        $imp = implode(" OR ", $temp);
        $where_custom .=" AND (".$imp.") ";
    }

    $table = "ecommerce_category";
    $select = "ecommerce_category.*,ecommerce_store.store_name";
    $join = array('ecommerce_store'=>"ecommerce_store.id=ecommerce_category.store_id,left");
    $this->db->where($where_custom);
    $info = $this->basic->get_data($table,$where='',$select,$join,$limit,$start,$order_by,$group_by='');

    $this->db->where($where_custom);
    $total_rows_array = $this->basic->count_row($table,$where='',$count="ecommerce_category.id",$join,$group_by='');
    $total_result=$total_rows_array[0]['total_rows'];

    foreach ($info as $key => $value) 
    {
        $info[$key]['actions'] = "<div style='min-width:100px'><a href='#' title='".$this->lang->line("Edit")."' data-toggle='tooltip' class='btn btn-circle btn-outline-warning edit_row' table_id='".$info[$key]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";

        $info[$key]['actions'] .= "<a href='#' title='".$this->lang->line("Delete")."' data-toggle='tooltip' class='btn btn-circle btn-outline-danger delete_row' table_id='".$info[$key]['id']."'><i class='fa fa-trash-alt'></i></a></div>
            <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";

        if($info[$key]['status'] == 1) $info[$key]['status'] = "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Active')."</span>";
        else $info[$key]['status'] = "<span class='badge badge-status text-danger'><i class='fa fa-times-circle red'></i> ".$this->lang->line('Inactive')."</span>";

        $info[$key]['updated_at'] = date("jS M y H:i",strtotime($info[$key]['updated_at']));   
    }

    $data['draw'] = (int)$_POST['draw'] + 1;
    $data['recordsTotal'] = $total_result;
    $data['recordsFiltered'] = $total_result;
    $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");
    echo json_encode($data);
  }

  public function ajax_create_new_category()
  {
    $this->ajax_check();
    $result = array('status'=>'0','message'=>$this->lang->line("Something went wrong, please try once again."));

    if($_POST) 
    {
        $category_name = strip_tags($this->input->post("category_name",true));
        $store_id = $this->input->post("store_id",true);

        $status = $this->input->post("status",true);
        if(!isset($status) || $status=='') $status='0';

        $inserted_data = array
        (
            "store_id"=>$store_id,
            "category_name"=>$category_name,
            "status"=>$status,
            "user_id"=>$this->user_id,
            "updated_at"=>date("Y-m-d H:i:s")
        );

        if($this->basic->insert_data("ecommerce_category",$inserted_data))
        {
            $result['status'] = "1";
            $result['message'] = $this->lang->line("Category has been added successfully.");
        }            

        echo json_encode($result);

    }
  }

  public function ajax_get_category_update_info()
  {

    $this->ajax_check();

    $table_id = $this->input->post("table_id");
    $user_id = $this->user_id;

    if($table_id == "0" || $table_id == "") exit;

    $details = $this->basic->get_data("ecommerce_category",array('where'=>array('id'=> $table_id, 'user_id'=> $user_id)));
    $selected=($details[0]['status']=='1') ? 'checked' : '';

    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Store");

    $form = '<div class="row">
                <div class="col-12">                    
                    <form action="#" enctype="multipart/form-data" id="row_update_form" method="post">
                        <input type="hidden" name="table_id" value="'.$table_id.'">
                        <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label for="name">'.$this->lang->line("Store").' *</label>
                                '.form_dropdown('', $store_list, $details[0]['store_id'],' style="width:100%;" disabled class="form-control seelct"').'
                              </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>'.$this->lang->line('Category Name').' *</label>
                                    <input type="text" class="form-control" name="category_name2" id="category_name2" value="'.$details[0]['category_name'].'">
                                </div>
                            </div>
                            <div class="col-12">
                              <div class="form-group">
                                <label class="custom-switch mt-2">
                                  <input type="checkbox" name="status2" id="status2" value="1" class="custom-switch-input" '.$selected.'>
                                  <span class="custom-switch-indicator"></span>
                                  <span class="custom-switch-description">'.$this->lang->line('Active').'</span>
                                </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>';
    echo $form;
  }

  public function ajax_update_category()
  {
    $this->ajax_check();
    $result = array('status'=>'0','message'=>$this->lang->line("Something went wrong, please try once again."));

    if($_POST) 
    {
        $table_id = $this->input->post("table_id",true);
        $category_name = strip_tags($this->input->post("category_name2",true));

        $status = $this->input->post("status2",true);
        if(!isset($status) || $status=='') $status='0';

        $updated_data = array
        (
            "category_name"=>$category_name,
            "status"=>$status,                
            "updated_at"=>date("Y-m-d H:i:s")
        );

        if($this->basic->update_data("ecommerce_category",array("id"=>$table_id,"user_id"=>$this->user_id),$updated_data))
        {
            $result['status'] = "1";
            $result['message'] = $this->lang->line("Category has been updated successfully.");
        }                     

        echo json_encode($result);

    }
  }

  public function delete_category()
  {
    $this->ajax_check();
    $table_id = $this->input->post("table_id");
    $result = array('status'=>'0','message'=>$this->lang->line("Something went wrong, please try once again."));
    if($table_id == "0" || $table_id == "")
    {
        echo json_encode($result); 
        exit();
    }

    if($this->basic->delete_data("ecommerce_category", array("id"=>$table_id,'user_id'=>$this->user_id)))          
    echo json_encode(array('message' => $this->lang->line("Category has been deleted successfully."),'status'=>'1'));        
    else echo json_encode($result);  
  }  

  public function coupon_list()
  {
    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Store");
    $data['store_list'] = $store_list;

    $data['body'] = 'ecommerce/coupon_list';
    $data['page_title'] = $this->lang->line('Coupon')." : ".$this->session->userdata("ecommerce_selected_store_title");
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }


  public function coupon_list_data()
  { 
    $this->ajax_check();

    $search_value = $this->input->post("search_value");
    $store_id = $this->input->post("search_store_id");        
    $search_date_range = $this->input->post("search_date_range");

    $display_columns = 
    array(
      "#",
      "CHECKBOX",
      'coupon_code',
      'coupon_amount',
      'coupon_type',
      'expiry_date',
      'status',
      'actions',
      'store_name',
      'free_shipping_enabled',
      'used',
      'updated_at',
    );
    $search_columns = array('coupon_code','coupon_amount');

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 5;
    $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'expiry_date';
    $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
    $order_by=$sort." ".$order;

    $where_custom="ecommerce_coupon.user_id = ".$this->user_id;

    if ($search_value != '') 
    {
        foreach ($search_columns as $key => $value) 
        $temp[] = $value." LIKE "."'%$search_value%'";
        $imp = implode(" OR ", $temp);
        $where_custom .=" AND (".$imp.") ";
    }
    if($search_date_range!="")
    {
        $exp = explode('|', $search_date_range);
        $from_date = isset($exp[0])?$exp[0]:"";
        $to_date = isset($exp[1])?$exp[1]:"";
        if($from_date!="Invalid date" && $to_date!="Invalid date")
        $where_custom .= " AND expiry_date >= '{$from_date}' AND expiry_date <='{$to_date}'";
    }
    $this->db->where($where_custom);

    if($store_id!="") $this->db->where(array("store_id"=>$store_id));       
    
    $table="ecommerce_coupon";
    $select = "ecommerce_coupon.*,ecommerce_store.store_name";
    $join = array('ecommerce_store'=>"ecommerce_store.id=ecommerce_coupon.store_id,left");
    $info=$this->basic->get_data($table,$where='',$select,$join,$limit,$start,$order_by,$group_by='');

    // echo $this->db->last_query(); exit();
    
    $this->db->where($where_custom);
    $total_rows_array=$this->basic->count_row($table,$where='',$count=$table.".id",$join,$group_by='');

    $total_result=$total_rows_array[0]['total_rows'];

    foreach($info as $key => $value) 
    {
        $expiry_date = date("M j, y H:i",strtotime($info[$key]['expiry_date']));
        $info[$key]['expiry_date'] =  "<div style='min-width:110px;'>".$expiry_date."</div>";

        $updated_at = date("M j, y H:i",strtotime($info[$key]['updated_at']));
        $info[$key]['updated_at'] =  "<div style='min-width:110px;'>".$updated_at."</div>";

        $info[$key]['actions'] = "<div style='min-width:100px'><a href='".base_url("ecommerce/edit_coupon/".$info[$key]['id'])."' title='".$this->lang->line("Edit")."' data-toggle='tooltip' class='btn btn-circle btn-outline-warning edit_row' table_id='".$info[$key]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";
        $info[$key]['actions'] .= "<a href='#' title='".$this->lang->line("Delete")."' data-toggle='tooltip' class='btn btn-circle btn-outline-danger delete_row' table_id='".$info[$key]['id']."'><i class='fa fa-trash-alt'></i></a></div>
            <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";

        if($info[$key]['status'] == 1) $info[$key]['status'] = "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Active')."</span>";
        else $info[$key]['status'] = "<span class='badge badge-status text-danger'><i class='fa fa-times-circle red'></i> ".$this->lang->line('Inactive')."</span>"; 

        if($info[$key]['free_shipping_enabled'] == 1) $info[$key]['free_shipping_enabled'] = "<span class='badge badge-status text-success'><i class='fa fa-check-circle green'></i> ".$this->lang->line('Enabled')."</span>";
        else $info[$key]['free_shipping_enabled'] = "<span class='badge badge-status text-danger'><i class='fa fa-times red'></i> ".$this->lang->line('Disabled')."</span>";

        if($info[$key]['max_usage_limit'] == '' || $info[$key]['max_usage_limit'] == '0')  $info[$key]['max_usage_limit'] = '∞';
        $info[$key]['used'] = $info[$key]['used']."/".$info[$key]['max_usage_limit'];

        $info[$key]['coupon_type'] = ucfirst($info[$key]['coupon_type']);
    }
    $data['draw'] = (int)$_POST['draw'] + 1;
    $data['recordsTotal'] = $total_result;
    $data['recordsFiltered'] = $total_result;
    $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");
    echo json_encode($data);
  }

  public function add_coupon()
  {       
    $data['body']='ecommerce/coupon_add';     
    $data['page_title']=$this->lang->line('Add Coupon')." : ".$this->session->userdata("ecommerce_selected_store_title");

    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Select Store");
    $data['store_list'] = $store_list;

    $product_list = $this->get_product_list();  
    $product_list['0'] = $this->lang->line("Select Product");
    $data['product_list'] = $product_list;

    $data['coupon_type_list'] = $this->basic->get_enum_values("ecommerce_coupon","coupon_type");
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }


  public function add_coupon_action() 
  {
    if($_SERVER['REQUEST_METHOD'] === 'GET') 
    redirect('home/access_forbidden','location');

    if($_POST)
    {
      $this->form_validation->set_rules('store_id', '<b>'.$this->lang->line("Store").'</b>', 'trim|required');      
      $this->form_validation->set_rules('coupon_code', '<b>'.$this->lang->line("Coupon code").'</b>', 'trim|required|callback_check_coupon');      
      $this->form_validation->set_rules('coupon_amount', '<b>'.$this->lang->line("Coupon amount").'</b>', 'trim|required');      
      $this->form_validation->set_rules('expiry_date', '<b>'.$this->lang->line("Expiry date").'</b>', 'trim|required');      
      $this->form_validation->set_rules('max_usage_limit', '<b>'.$this->lang->line("Max usage limit").'</b>', 'trim|numeric');
          
      if ($this->form_validation->run() == FALSE)
      {
          $this->add_coupon(); 
      }
      else
      {           

          $store_id=$this->input->post('store_id',true);
          $product_ids=$this->input->post('product_ids',true);
          $coupon_type=$this->input->post('coupon_type',true);
          $coupon_code=strip_tags($this->input->post('coupon_code',true));
          $coupon_amount=$this->input->post('coupon_amount',true);
          $expiry_date=$this->input->post('expiry_date',true);
          $max_usage_limit=$this->input->post('max_usage_limit',true);
          $free_shipping_enabled=$this->input->post('free_shipping_enabled',true);
          $status=$this->input->post('status',true);

          if($status=='') $status='0';
          if($free_shipping_enabled=='') $free_shipping_enabled='0';
          if(!isset($product_ids) || !is_array($product_ids) || empty($product_ids)) $product_ids = '0';
          else $product_ids = implode(',', $product_ids);
                                                 
          $data=array
          (
              'store_id'=>$store_id,
              'product_ids'=>$product_ids,
              'coupon_type'=>$coupon_type,
              'coupon_code'=>$coupon_code,
              'coupon_amount'=>$coupon_amount,
              'expiry_date'=>$expiry_date,
              'max_usage_limit'=>$max_usage_limit,
              'free_shipping_enabled'=>$free_shipping_enabled,
              'status'=>$status,
              'updated_at' => date("Y-m-d H:i:s"),
              'user_id'=>$this->user_id
          );

          
          if($this->basic->insert_data('ecommerce_coupon',$data)) $this->session->set_flashdata('success_message',1);   
          else $this->session->set_flashdata('error_message',1);     
          
          redirect('ecommerce/coupon_list','location');
      }
    }   
  }


  public function edit_coupon($id='0')
  {       
    if($id=='0') exit();
    $data['body']='ecommerce/coupon_edit';     
    $data['page_title']=$this->lang->line('Edit Coupon')." : ".$this->session->userdata("ecommerce_selected_store_title");

    $store_list = $this->get_store_list();  
    $store_list[''] = $this->lang->line("Select Store");
    $data['store_list'] = $store_list;

    $product_list = $this->get_product_list();  
    $product_list['0'] = $this->lang->line("Select Product");
    $data['product_list'] = $product_list;

    $data['coupon_type_list'] = $this->basic->get_enum_values("ecommerce_coupon","coupon_type");

    $xdata = $this->basic->get_data("ecommerce_coupon",array('where'=>array('id'=>$id,"user_id"=>$this->user_id)));
    if(!isset($xdata[0])) exit();
    $data['xdata'] = $xdata[0];
    $data["iframe"]="1";
    
    $this->_viewcontroller($data);
  }

  public function edit_coupon_action() 
  {
    if($_SERVER['REQUEST_METHOD'] === 'GET') 
    redirect('home/access_forbidden','location');

    if($_POST)
    {
        $id=$this->input->post('hidden_id',true);
        // $this->form_validation->set_rules('store_id', '<b>'.$this->lang->line("Store").'</b>', 'trim|required');      
        $this->form_validation->set_rules('coupon_code', '<b>'.$this->lang->line("Coupon code").'</b>', 'trim|required|callback_check_coupon');      
        $this->form_validation->set_rules('coupon_amount', '<b>'.$this->lang->line("Coupon amount").'</b>', 'trim|required');      
        $this->form_validation->set_rules('expiry_date', '<b>'.$this->lang->line("Expiry date").'</b>', 'trim|required');      
        $this->form_validation->set_rules('max_usage_limit', '<b>'.$this->lang->line("Max usage limit").'</b>', 'trim|numeric');
            
        if ($this->form_validation->run() == FALSE)
        {
            $this->edit_coupon($id); 
        }
        else
        {   
            // $store_id=$this->input->post('store_id',true);
            $product_ids=$this->input->post('product_ids',true);
            $coupon_type=$this->input->post('coupon_type',true);
            $coupon_code=strip_tags($this->input->post('coupon_code',true));
            $coupon_amount=$this->input->post('coupon_amount',true);
            $expiry_date=$this->input->post('expiry_date',true);
            $max_usage_limit=$this->input->post('max_usage_limit',true);
            $free_shipping_enabled=$this->input->post('free_shipping_enabled',true);
            $status=$this->input->post('status',true);

            if($status=='') $status='0';
            if($free_shipping_enabled=='') $free_shipping_enabled='0';
            if(!isset($product_ids) || !is_array($product_ids) || empty($product_ids)) $product_ids = '0';
            else $product_ids = implode(',', $product_ids);
                                                   
            $data=array
            (
                'product_ids'=>$product_ids,
                'coupon_type'=>$coupon_type,
                'coupon_code'=>$coupon_code,
                'coupon_amount'=>$coupon_amount,
                'expiry_date'=>$expiry_date,
                'max_usage_limit'=>$max_usage_limit,
                'free_shipping_enabled'=>$free_shipping_enabled,
                'status'=>$status,
                'updated_at' => date("Y-m-d H:i:s")
            );

            
            if($this->basic->update_data('ecommerce_coupon',array("id"=>$id,"user_id"=>$this->user_id),$data)) $this->session->set_flashdata('success_message',1);   
            else $this->session->set_flashdata('error_message',1); 
            redirect('ecommerce/coupon_list','location');                 
            
        }
    }   
  }

  public function delete_coupon()
  {
    $this->ajax_check();
    $table_id = $this->input->post("table_id");
    $result = array('status'=>'0','message'=>$this->lang->line("Something went wrong, please try once again."));
    if($table_id == "0" || $table_id == "")
    {
        echo json_encode($result); 
        exit();
    }

    if($this->basic->delete_data("ecommerce_coupon", array("id"=>$table_id,'user_id'=>$this->user_id)))          
    echo json_encode(array('message' => $this->lang->line("Coupon has been deleted successfully."),'status'=>'1'));          
    else echo json_encode($result);  
  }

  public function upload_product_thumb() 
  {
      // Kicks out if not a ajax request
      $this->ajax_check();

      if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
          exit();
      }

      $upload_dir = APPPATH . '../upload/ecommerce';

      // Makes upload directory
      if( ! file_exists($upload_dir)) {
          mkdir($upload_dir, 0777, true);
      }

      if (isset($_FILES['file'])) {

        $file_size = $_FILES['file']['size'];
        if ($file_size > 1048576) {
            $message = $this->lang->line('The file size exceeds the limit. Please remove the file and upload again.');
            echo json_encode(['error' => $message]);
            exit;
        }
        
        // Holds tmp file
        $tmp_file = $_FILES['file']['tmp_name'];

          if (is_uploaded_file($tmp_file)) {

            $post_fileName = $_FILES['file']['name'];
            $post_fileName_array = explode('.', $post_fileName);
            $ext = array_pop($post_fileName_array);

            $allow_ext = ['png', 'jpg', 'jpeg'];
            if(! in_array(strtolower($ext), $allow_ext)) {
                $message = $this->lang->line('Invalid file type');
                echo json_encode(['error' => $message]);
                exit;
            }

            $filename = implode('.', $post_fileName_array);
            $filename = strtolower(strip_tags(str_replace(' ', '-', $filename)));
            $filename = "product_".$this->user_id . '_' . time() . substr(uniqid(mt_rand(), true), 0, 6) . '.' . $ext;

            // Moves file to the upload dir
            $dest_file = $upload_dir . DIRECTORY_SEPARATOR . $filename;
            if (! @move_uploaded_file($tmp_file, $dest_file)) {
                $message = $this->lang->line('That was not a valid upload file.');
                echo json_encode(['error' => $message]);
                exit;
            }

            // Sets filename to session
            $this->session->set_userdata('product_thumb_uploaded_file', $filename);

            // Returns response
            echo json_encode([ 'filename' => $filename]);
        }
     }        
  }

  public function delete_product_thumb() 
  {
    // Kicks out if not a ajax request
    $this->ajax_check();

    if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
        exit();
    }

    // Upload dir path
    $upload_dir = APPPATH . '../upload/ecommerce';

    // Grabs filename
    $filename = (string) $this->input->post('filename');
    $session_filename = $this->session->userdata('product_thumb_uploaded_file');
    if ($filename !== $session_filename) {
        exit;
    }

    // Prepares file path
    $filepath = $upload_dir . DIRECTORY_SEPARATOR . $filename;
    
    // Tries to remove file
    if (file_exists($filepath)) {
        // Deletes file from disk
        unlink($filepath);

        // Clears the file from cache 
        clearstatcache();

        // Deletes file from session
        $this->session->unset_userdata('product_thumb_uploaded_file');
        
        echo json_encode(['deleted' => 'yes']);
        exit();
    }

    echo json_encode(['deleted' => 'no']);
  }

  public function upload_store_logo() 
  {
    // Kicks out if not a ajax request
    $this->ajax_check();

    if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
        exit();
    }

    $upload_dir = APPPATH . '../upload/ecommerce';

    // Makes upload directory
    if( ! file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

   if (isset($_FILES['file'])) {

        $file_size = $_FILES['file']['size'];
        if ($file_size > 1048576) {
            $message = $this->lang->line('The file size exceeds the limit. Please remove the file and upload again.');
            echo json_encode(['error' => $message]);
            exit;
        }
        
        // Holds tmp file
        $tmp_file = $_FILES['file']['tmp_name'];

        if (is_uploaded_file($tmp_file)) {

            $post_fileName = $_FILES['file']['name'];
            $post_fileName_array = explode('.', $post_fileName);
            $ext = array_pop($post_fileName_array);

            $allow_ext = ['png', 'jpg', 'jpeg'];
            if(! in_array(strtolower($ext), $allow_ext)) {
                $message = $this->lang->line('Invalid file type');
                echo json_encode(['error' => $message]);
                exit;
            }

            $filename = implode('.', $post_fileName_array);
            $filename = strtolower(strip_tags(str_replace(' ', '-', $filename)));
            $filename = "storelogo_".$this->user_id . '_' . time() . substr(uniqid(mt_rand(), true), 0, 6) . '.' . $ext;

            // Moves file to the upload dir
            $dest_file = $upload_dir . DIRECTORY_SEPARATOR . $filename;
            if (! @move_uploaded_file($tmp_file, $dest_file)) {
                $message = $this->lang->line('That was not a valid upload file.');
                echo json_encode(['error' => $message]);
                exit;
            }

            // Sets filename to session
            $this->session->set_userdata('store_logo_uploaded_file', $filename);

            // Returns response
            echo json_encode([ 'filename' => $filename]);
        }
     }        
  }

  public function delete_store_logo() 
  {
      // Kicks out if not a ajax request
      $this->ajax_check();

      if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
          exit();
      }

      // Upload dir path
      $upload_dir = APPPATH . '../upload/ecommerce';

      // Grabs filename
      $filename = (string) $this->input->post('filename');
      $session_filename = $this->session->userdata('store_logo_uploaded_file');
      if ($filename !== $session_filename) {
          exit;
      }

      // Prepares file path
      $filepath = $upload_dir . DIRECTORY_SEPARATOR . $filename;
      
      // Tries to remove file
      if (file_exists($filepath)) {
          // Deletes file from disk
          unlink($filepath);

          // Clears the file from cache 
          clearstatcache();

          // Deletes file from session
          $this->session->unset_userdata('store_logo_uploaded_file');
          
          echo json_encode(['deleted' => 'yes']);
          exit();
      }

      echo json_encode(['deleted' => 'no']);
  }


  public function upload_store_favicon() 
  {
      // Kicks out if not a ajax request
      $this->ajax_check();

      if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
          exit();
      }

      $upload_dir = APPPATH . '../upload/ecommerce';

      // Makes upload directory
      if( ! file_exists($upload_dir)) {
          mkdir($upload_dir, 0777, true);
      }

     if (isset($_FILES['file'])) {

          $file_size = $_FILES['file']['size'];
          if ($file_size > 1048576) {
              $message = $this->lang->line('The file size exceeds the limit. Please remove the file and upload again.');
              echo json_encode(['error' => $message]);
              exit;
          }
          
          // Holds tmp file
          $tmp_file = $_FILES['file']['tmp_name'];

          if (is_uploaded_file($tmp_file)) {

              $post_fileName = $_FILES['file']['name'];
              $post_fileName_array = explode('.', $post_fileName);
              $ext = array_pop($post_fileName_array);

              $allow_ext = ['png', 'jpg', 'jpeg'];
              if(! in_array(strtolower($ext), $allow_ext)) {
                  $message = $this->lang->line('Invalid file type');
                  echo json_encode(['error' => $message]);
                  exit;
              }

              $filename = implode('.', $post_fileName_array);
              $filename = strtolower(strip_tags(str_replace(' ', '-', $filename)));
              $filename = "storefavicon_".$this->user_id . '_' . time() . substr(uniqid(mt_rand(), true), 0, 6) . '.' . $ext;

              // Moves file to the upload dir
              $dest_file = $upload_dir . DIRECTORY_SEPARATOR . $filename;
              if (! @move_uploaded_file($tmp_file, $dest_file)) {
                  $message = $this->lang->line('That was not a valid upload file.');
                  echo json_encode(['error' => $message]);
                  exit;
              }

              // Sets filename to session
              $this->session->set_userdata('store_favicon_uploaded_file', $filename);

              // Returns response
              echo json_encode([ 'filename' => $filename]);
          }
     }        
  }

  public function delete_store_favicon() 
  {
      // Kicks out if not a ajax request
      $this->ajax_check();

      if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
          exit();
      }

      // Upload dir path
      $upload_dir = APPPATH . '../upload/ecommerce';

      // Grabs filename
      $filename = (string) $this->input->post('filename');
      $session_filename = $this->session->userdata('store_favicon_uploaded_file');
      if ($filename !== $session_filename) {
          exit;
      }

      // Prepares file path
      $filepath = $upload_dir . DIRECTORY_SEPARATOR . $filename;
      
      // Tries to remove file
      if (file_exists($filepath)) {
          // Deletes file from disk
          unlink($filepath);

          // Clears the file from cache 
          clearstatcache();

          // Deletes file from session
          $this->session->unset_userdata('store_logo_uploaded_file');
          
          echo json_encode(['deleted' => 'yes']);
          exit();
      }

      echo json_encode(['deleted' => 'no']);
  }

  public function manual_payment_upload_file() 
  {
      // Kicks out if not a ajax request
      $this->ajax_check();

      if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
          exit();
      }

      $upload_dir = APPPATH . '../upload/ecommerce';

      // Makes upload directory
      if( ! file_exists($upload_dir)) {
          mkdir($upload_dir, 0777, true);
      }

     if (isset($_FILES['file'])) {

          $file_size = $_FILES['file']['size'];
          if ($file_size > 5242880) {
              $message = $this->lang->line('The file size exceeds the limit. Allowed size is 5MB. Please remove the file and upload again.');
              echo json_encode(['error' => $message]);
              exit;
          }
          
          // Holds tmp file
          $tmp_file = $_FILES['file']['tmp_name'];

          if (is_uploaded_file($tmp_file)) {

              $post_fileName = $_FILES['file']['name'];
              $post_fileName_array = explode('.', $post_fileName);
              $ext = array_pop($post_fileName_array);

              $allow_ext = ['pdf', 'doc', 'txt', 'png', 'jpg', 'jpeg', 'zip'];
              if(! in_array(strtolower($ext), $allow_ext)) {
                  $message = $this->lang->line('Invalid file type');
                  echo json_encode(['error' => $message]);
                  exit;
              }

              $filename = implode('.', $post_fileName_array);
              $filename = strtolower(strip_tags(str_replace(' ', '-', $filename)));
              $filename = "payment_" . time() . substr(uniqid(mt_rand(), true), 0, 6) . '.' . $ext;

              // Moves file to the upload dir
              $dest_file = $upload_dir . DIRECTORY_SEPARATOR . $filename;
              if (! @move_uploaded_file($tmp_file, $dest_file)) {
                  $message = $this->lang->line('That was not a valid upload file.');
                  echo json_encode(['error' => $message]);
                  exit;
              }

              // Sets filename to session
              $this->session->set_userdata('ecommerce_manual_payment_uploaded_file', $filename);

              // Returns response
              echo json_encode([ 'filename' => $filename]);
          }
     }        
  }

  public function manual_payment_delete_file() 
  {
      // Kicks out if not a ajax request
      $this->ajax_check();

      if ('get' == strtolower($_SERVER['REQUEST_METHOD'])) {
          exit();
      }

      // Upload dir path
      $upload_dir = APPPATH . '../upload/ecommerce';

      // Grabs filename
      $filename = (string) $this->input->post('filename');
      $session_filename = $this->session->userdata('ecommerce_manual_payment_uploaded_file');
      if ($filename !== $session_filename) {
          exit;
      }

      // Prepares file path
      $filepath = $upload_dir . DIRECTORY_SEPARATOR . $filename;
      
      // Tries to remove file
      if (file_exists($filepath)) {
          // Deletes file from disk
          unlink($filepath);

          // Clears the file from cache 
          clearstatcache();

          // Deletes file from session
          $this->session->unset_userdata('ecommerce_manual_payment_uploaded_file');
          
          echo json_encode(['deleted' => 'yes']);
          exit();
      }

      echo json_encode(['deleted' => 'no']);
  }

  private function valid_cart_data($cart_id=0,$subscriber_id="",$select="")
  {
  	$join = array('ecommerce_store'=>"ecommerce_cart.store_id=ecommerce_store.id,left");
    $where = array('where'=>array("ecommerce_cart.subscriber_id"=>$subscriber_id,"ecommerce_cart.id"=>$cart_id,"action_type!="=>"checkout","ecommerce_store.status"=>"1"));
    if($select=="") $select = array("ecommerce_cart.*","tax_percentage","shipping_charge","store_unique_id");
    return $cart_data = $this->basic->get_data("ecommerce_cart",$where,$select,$join);
  }

  private function get_app_id()
  {
    $fb_app_id_info=$this->basic->get_data('facebook_rx_config',$where=array('where'=>array('status'=>'1')),"api_id");
    $fb_app_id = isset($fb_app_id_info[0]['api_id']) ? $fb_app_id_info[0]['api_id'] : "";
    return $fb_app_id;
  }

  public function get_coupon_data($coupon_code='',$store_id='0')
  {
    $data = $this->basic->get_data("ecommerce_coupon",array("where"=>array("store_id"=>$store_id,"coupon_code"=>$coupon_code,"status"=>"1","expiry_date >="=>date("Y-m-d H:i:s"))));
    if(isset($data[0])) 
    {
      if($data[0]['max_usage_limit']>0 && $data[0]['used']==$data[0]["max_usage_limit"]) return array();
      else return $data[0];
    }
    else return array();
  }


  private function get_ecommerce_config($user_id='0')
  {
    if($user_id=='0') $user_id = $this->user_id;
    $data = $this->basic->get_data("ecommerce_config",array("where"=>array("user_id"=>$user_id)));
    if(isset($data[0])) return $data[0];
    else return array();
  }

  private function get_current_store_data()
  {
    $data = $this->basic->get_data("ecommerce_store",array("where"=>array("id"=>$this->session->userdata("ecommerce_selected_store"),"status"=>"1")),"store_name,id,store_unique_id");
    if(isset($data[0])) return $data[0];
    else return array();
  }

  private function get_current_cart($subscriber_id="",$store_id=0)
  {
    $current_cart = array("cart_count"=>0,"cart_id"=>0,"cart_data"=>array());
    if($store_id!=0)
    {          
        $join = array('ecommerce_cart_item'=>"ecommerce_cart.id=ecommerce_cart_item.cart_id,right");
        $where_simple = array("ecommerce_cart.store_id"=>$store_id,"action_type!="=>"checkout");
        if($subscriber_id!="") $where_simple["ecommerce_cart.subscriber_id"] = $subscriber_id;
        else $where_simple["ecommerce_cart.user_id"] = $this->user_id;
        $where = array('where'=>$where_simple);
        $select = array("ecommerce_cart.*","ecommerce_cart_item.id as ecommerce_cart_item_id","cart_id","product_id","unit_price","coupon_info","quantity","attribute_info");
        $cart_data = $this->basic->get_data("ecommerce_cart",$where,$select,$join);
        $cart_id = isset($cart_data[0]['cart_id']) ? $cart_data[0]['cart_id'] : 0;
        $cart_data_final = array();
        foreach ($cart_data as $key => $value) 
        {
          if($value["quantity"]<=0) 
          {
            $this->basic->delete_data("ecommerce_cart_item",array("id"=>$value["ecommerce_cart_item_id"]));
            unset($cart_data[$key]);
          }           
          else $cart_data_final[$value['product_id']] = $value;              
        }
        $cart_count = count($cart_data);
        $cart_url = base_url("ecommerce/cart/".$cart_id."?subscriber_id=".$subscriber_id);
        if($cart_count==0)
        {
          $this->basic->delete_data("ecommerce_cart",array("id"=>$cart_id));
          $cart_id = 0;
          $cart_url= "";
        }
        $current_cart = array("cart_count"=>$cart_count,"cart_id"=>$cart_id,"cart_url"=>$cart_url,"cart_data"=>$cart_data_final,"cart_data_raw"=>$cart_data);          
    }
    return $current_cart;        
  }

  private function get_store_list()
  {
    $store_list = $this->basic->get_data("ecommerce_store",array("where"=>array("user_id"=>$this->user_id,"status"=>"1")),$select='',$join='',$limit='',$start=NULL,$order_by='store_name ASC');
    $store_info=array();
    foreach($store_list as $value)
    {
      $store_info[$value['id']] = $value['store_name'];
    }
    return $store_info;
  }

  private function get_product_list_array($store_id=0,$default_where="",$order_by="")
  {
    $where_simple = array("store_id"=>$store_id,"status"=>"1");
    if(isset($default_where['product_name'])) {
      $product_name = $default_where['product_name'];
      $this->db->where(" product_name LIKE "."'%".$product_name."%'");
      unset($default_where['product_name']);
    }
    if(is_array($default_where) && !empty($default_where))
    {
      foreach($default_where as $key => $value) 
      {
        $where_simple[$key] = $value;
      }
    }      
    if($order_by=="") $order_by = "product_name ASC";     
    $product_list = $this->basic->get_data("ecommerce_product",array("where"=>$where_simple),$select='',$join='',$limit='',$start=NULL,$order_by);
    
    // echo $this->db->last_query();
    return $product_list;
  }

  private function get_category_list($store_id=0)
  {
    if($store_id==0) $store_id = $this->session->userdata("ecommerce_selected_store");
    $cat_list = $this->basic->get_data("ecommerce_category",array("where"=>array("store_id"=>$store_id,"status"=>"1")),$select='',$join='',$limit='',$start=NULL,$order_by='category_name ASC');
    $cat_info=array();
    foreach($cat_list as $value)
    {
      $cat_info[$value['id']] = $value['category_name'];
    }
    return $cat_info;
  }

  private function get_attribute_list($store_id=0,$raw_data=false)
  {
    if($store_id==0) $store_id = $this->session->userdata("ecommerce_selected_store");
    $at_list = $this->basic->get_data("ecommerce_attribute",array("where"=>array("store_id"=>$store_id,"status"=>"1")),$select='',$join='',$limit='',$start=NULL,$order_by='attribute_name ASC');
    if($raw_data) return $at_list;
    $at_info=array();
    foreach($at_list as $value)
    {
      $at_info[$value['id']] = $value['attribute_name'];
    }
    return $at_info;
  }

  public function get_product_list($store_id='0',$ajax='0',$multiselect='0')
  {
    if($ajax=='1') 
    {
        $this->ajax_check();
        if($store_id=='' || $store_id=='0')
        {
            echo form_dropdown('product_ids[]', array(),'','class="form-control select2" id="product_ids" multiple');
            echo "<script>$('.select2').select2();</script>";
            exit();
        }
    }

    $product_list = $this->basic->get_data("ecommerce_product",array("where"=>array("user_id"=>$this->user_id,"store_id"=>$store_id,"status"=>"1")),$select='',$join='',$limit='',$start=NULL,$order_by='product_name ASC');
    $product_info=array();
    foreach($product_list as $value)
    {
      $product_info[$value['id']] = $value['product_name'];
    }

    if($ajax=='0') return $product_info;
    else
    {
        if($multiselect=='0') echo form_dropdown('product_id', $product_info,'','class="form-control select2" id="product_id"');
        else echo form_dropdown('product_ids[]', $product_info,'0','class="form-control select2" id="product_ids" multiple');
        echo "<script>$('.select2').select2();</script>";
    }
  }

  public function check_coupon() 
  {
    $coupon_code = $this->input->post('coupon_code',true);
    $store_id = $this->input->post('store_id',true);
    $id = $this->input->post('hidden_id',true);
    $this->db->select('id');
    $this->db->from('ecommerce_coupon');
    $this->db->where('store_id', $store_id);
    $this->db->where('coupon_code', $coupon_code);
    // $this->db->where('user_id', $this->user_id);
    if($id!='') $this->db->where('id !=', $id);
    $query = $this->db->get();
    $num = $query->num_rows();
    if ($num > 0) 
    {
        $message = "<b>".$this->lang->line("Coupon code")." </b>".$this->lang->line("must be unique");
        $this->form_validation->set_message('check_coupon', $message);
        return FALSE;
    }
    else return TRUE;       
  }

  private function two_decimal_place($number=0)
  {
      return number_format((float)$number, 2, '.', '');
  }

  public function get_template_label_dropdown()
  {
      $this->ajax_check();
      if(!$_POST) exit();
      $page_id=$this->input->post('page_id');// database id

      $label_list=$this->get_page_label($page_id);

      $dropdown=array();
      $js='<script>
            $("document").ready(function()  {
              $("#label_ids").select2();
            });


          </script>';
      $str='';
      foreach ($label_list as  $key=>$value)
      {            
          $str.=  "<option value='{$key}'>".$value."</option>";
      }
      echo json_encode(array('label_option'=>$str,"script"=>$js));
  }

  public function get_template_label_dropdown_edit()
  {
      $this->ajax_check();
      if(!$_POST) exit();
      $page_id=$this->input->post('page_id');// database id
      $table_name="ecommerce_store";
      $id=$this->input->post('id');

      $xdata=$this->basic->get_data($table_name,array("where"=>array("id"=>$id)));
      $xlabel_ids=isset($xdata[0]["label_ids"])?$xdata[0]["label_ids"]:"";
      $xlabel_ids=explode(',', $xlabel_ids);
      $label_list=$this->get_page_label($page_id);

      $dropdown=array();
      $js='<script>
            $("document").ready(function()  {
              $("#label_ids").select2();
              $("#template_id").select2();
            });


          </script>';
      $str='';
      foreach ($label_list as  $key=>$value)
      {            
          if(in_array($key, $xlabel_ids)) $selected="selected";
          else $selected="";
          $str.=  "<option value='{$key}' {$selected}>".$value."</option>";
      }
     

      echo json_encode(array('label_option'=>$str,"script"=>$js));
  }

  private function get_page_label($page_id=0)
  {
      if($page_id==0) return array();  

      if(!$this->db->table_exists('messenger_bot_broadcast_contact_group')) return array();

      $label_data=$this->basic->get_data("messenger_bot_broadcast_contact_group",array("where"=>array("page_id"=>$page_id,"unsubscribe"=>"0","invisible"=>"0")),'','','',$start=NULL,$order_by="group_name ASC");
      $push_label=array();
      foreach ($label_data as $key => $value) 
      {    
          $push_label[$value['id']]=$value['group_name'].' ['.$value['label_id'].']';
      }
      return $push_label;
  }


  private function get_user_page()
  {
      $facebook_rx_fb_user_info = $this->session->userdata('facebook_rx_fb_user_info');

      $page_data=$this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("facebook_rx_fb_user_info_id"=>$facebook_rx_fb_user_info,"bot_enabled"=>"1")),'','','',$start=NULL,$order_by="page_name ASC");
      $push_page=array();
      foreach ($page_data as $key => $value) 
      {
          $push_page[$value['id']]=$value['page_name'];
      }
      return $push_page;
  }

  private function get_payment_status()
  {
    return array('pending'=>$this->lang->line('Pending'),'approved'=>$this->lang->line('Approved'),'rejected'=>$this->lang->line('Rejected'),'shipped'=>$this->lang->line('Shipped'),'delivered'=>$this->lang->line('Delivered'),'completed'=>$this->lang->line('Completed'));
  }


  private function get_page_template($page_id=0)
  {
      if($page_id==0) return array();  

      $postback_data=$this->basic->get_data("messenger_bot_postback",array("where"=>array("page_id"=>$page_id,"is_template"=>"1","template_for"=>"reply_message")),'','','',$start=NULL,$order_by="template_name ASC");
      $push_postback=array();
      foreach ($postback_data as $key => $value) 
      {
          $push_postback[$value['id']]=$value['template_name'].' ['.$value['postback_id'].']';
      }
      return $push_postback;
  }

  private function get_reminder_hour()
  {
    return array(
        "" => "--".$this->lang->line("Do not send")."--",
        "1"=>$this->lang->line("After 1 hour"),
        "2"=>$this->lang->line("After 2 hours"),
        "3"=>$this->lang->line("After 3 hours"),
        "4"=>$this->lang->line("After 4 hours"),
        "5"=>$this->lang->line("After 5 hours"),
        "6"=>$this->lang->line("After 6 hours"),
        "7"=>$this->lang->line("After 7 hours"),
        "8"=>$this->lang->line("After 8 hours"),
        "9"=>$this->lang->line("After 9 hours"),
        "10"=>$this->lang->line("After 10 hours"),
        "11"=>$this->lang->line("After 11 hours"),
        "12"=>$this->lang->line("After 12 hours"),
        "13"=>$this->lang->line("After 13 hours"),
        "14"=>$this->lang->line("After 14 hours"),
        "15"=>$this->lang->line("After 15 hours"),
        "16"=>$this->lang->line("After 16 hours"),
        "17"=>$this->lang->line("After 17 hours"),
        "18"=>$this->lang->line("After 18 hours"),
        "19"=>$this->lang->line("After 19 hours"),
        "20"=>$this->lang->line("After 20 hours"),
        "21"=>$this->lang->line("After 21 hours"),
        "22"=>$this->lang->line("After 22 hours"),
        "23"=>$this->lang->line("After 23 hours"),
      );
  }

  private function get_sms_api()
  {
      $where = array("where" => array('user_id'=>$this->user_id,'status'=>'1'));
      $sms_api_config=$this->basic->get_data('sms_api_config', $where, $select='', $join='', $limit='', $start='', $order_by='phone_number ASC', $group_by='', $num_rows=0);
      $sms_api_config_option=array(''=>$this->lang->line("Select Sender"));
      foreach ($sms_api_config as $info) 
      {
          $id=$info['id'];

          if($info['phone_number'] !="") $sms_api_config_option[$id]=$info['gateway_name'].": ".$info['phone_number'];
          else $sms_api_config_option[$id]=$info['gateway_name'];
      }
      return $sms_api_config_option;
  }

  private function get_email_api()
  {
    
    if(!$this->basic->is_exist("modules",array("id"=>263))) return array();

    /***get smtp  option***/
    $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
    $smtp_info=$this->basic->get_data('email_smtp_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
    
    $smtp_option=array(''=>$this->lang->line("Select Sender"));
    foreach ($smtp_info as $info) {
        $id="email_smtp_config-".$info['id'];
        $smtp_option[$id]="SMTP: ".$info['email_address'];
    }
    
    /***get mandrill option***/
    $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
    $smtp_info=$this->basic->get_data('email_mandrill_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
    
    foreach ($smtp_info as $info) {
        $id="email_mandrill_config-".$info['id'];
        $smtp_option[$id]="Mandrill: ".$info['email_address'];
    }

    /***get sendgrid option***/
    $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
    $smtp_info=$this->basic->get_data('email_sendgrid_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
    
    foreach ($smtp_info as $info) {
        $id="email_sendgrid_config-".$info['id'];
        $smtp_option[$id]="SendGrid: ".$info['email_address'];
    }

    /***get mailgun option***/
    $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
    $smtp_info=$this->basic->get_data('email_mailgun_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
    
    foreach ($smtp_info as $info) {
        $id="email_mailgun_config-".$info['id'];
        $smtp_option[$id]="Mailgun: ".$info['email_address'];
    }
    return $smtp_option;
  }

  private function handle_attachment($id, $file) 
  {
      $info = pathinfo($file);
      if (isset($info['extension']) && ! empty($info['extension'])) {
          switch (strtolower($info['extension'])) {
              case 'jpg':
              case 'jpeg':
              case 'png':
              case 'gif':
                  return $this->manual_payment_display_attachment($file);
              case 'zip':
              case 'pdf':
              case 'txt':
                  return '<div data-id="' . $id . '" id="mp-download-file" class="btn btn-outline-info" data-toggle="tooltip" title="'.$this->lang->line("Attachment").'"><i class="fas fa-download"></i></div>';
          }
      }
  }

  public function manual_payment_download_file() 
  {
      // Prevents out-of-memory issue
      if (ob_get_level()) {
          ob_end_clean();
      }

      // If it is GET request let it download file
      $method = $this->input->method();
      if ('get' == $method) {
          $filename = $this->session->userdata('ecommerce_manual_payment_download_file');

          if (! $filename) {
              $message = $this->lang->line('No file to download.');
              echo json_encode(['msg' => $message]);
          } else {
              $file = FCPATH.'upload/ecommerce/' . $filename;
              header('Expires: 0');
              header('Pragma: public');
              header('Cache-Control: must-revalidate');
              header('Content-Length: ' . filesize($file));
              header('Content-Description: File Transfer');
              header('Content-Type: application/octet-stream');
              header('Content-Disposition: attachment; filename="' . $filename . '"');
              readfile($file);
              $this->session->unset_userdata('ecommerce_manual_payment_download_file');
              exit;
          }

      // If it is POST request, grabs the file
      } elseif ('post' === $method) {
          if (! $this->input->is_ajax_request()) {
              $message = $this->lang->line('Bad Request.');
              echo json_encode(['msg' => $message]);
              exit;
          }

          // Grabs transaction ID
          $id = (int) $this->input->post('file');

          // Checks file owner
          $select = ['id', 'user_id', 'manual_filename'];            
          $where = [
              'where' => [
                  'id' => $id,
                  'user_id' => $this->user_id,
              ],
          ];           

          $result = $this->basic->get_data('ecommerce_cart', $where, $select, [], 1);
          if (1 != count($result)) {
              $message = $this->lang->line('You do not have permission to download this file.');
              echo json_encode(['error' => $message]);
              exit;
          }

          $filename = $result[0]['manual_filename'];
          $this->session->set_userdata('ecommerce_manual_payment_download_file', $filename);

          echo json_encode(['status' => 'ok']);
      }
  }


  private function manual_payment_display_attachment($file) 
  {
      $output = '<div class="mp-display-img d-inline">';
      $output .= '<a class="mp-img-item btn btn-outline-info" data-image="' . $file . '" href="' . $file . '">';
      $output .= '<i class="fa fa-image"></i>';
      $output .= '</a>';
      $output .= '</div>';
      $output .= '<script>$(".mp-display-img").Chocolat({className: "mp-display-img", imageSelector: ".mp-img-item"});</script>';

      return $output;
  }



}