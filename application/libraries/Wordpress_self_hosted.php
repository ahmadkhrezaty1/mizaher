<?php
class Wordpress_self_hosted {
	public $user_id;
	// public $domain_name;
	// public $user_key;
	// public $authentication_key;


	public function __construct() {
		$this->CI =& get_instance();
		$this->CI->load->database();
		$this->CI->load->helper('my_helper');
		$this->CI->load->library('session');
		$this->CI->load->model('basic');
		$this->user_id=$this->CI->session->userdata("user_id");

		// $wp_sh_config = $this->CI->basic->get_data("wordpress_config_self_hosted",
		// 	['where' => ['deleted' => '0', 'status' => '1']]);		

		// if(isset($wp_sh_config[0])) {
		// 	$this->user_key = $wp_sh_config[0]["user_key"];
		// 	$this->authentication_key = $wp_sh_config[0]["authentication_key"];
		// 	$this->domain_name = $wp_sh_config[0]["domain_name"];
		// 	$this->CI->session->set_userdata('wp_sh_user_key',$this->user_key);
		// 	$this->CI->session->set_userdata('wp_sh_authentication_key',$this->authentication_key);
		// }
	}

	public function login_button()
	{
		return "<a href='" . base_url('social_apps/wordpress_settings_self_hosted') . "' class='btn btn-outline-primary login_button' social_account='twitter'><i class='fas fa-plus-circle'></i> ".$this->CI->lang->line('Import Account')."</a>";
	}

	public function post_to_wordpress_self_hosted($data)
	{

		if (! isset($data['domain_name']) || empty($data['domain_name'])) {
			throw new \Exception('Wordpress (self-hosted) domain name had not specified.');
		}

		if (! isset($data['user_key']) || empty($data['user_key'])) {
			throw new \Exception('The user key had not specified.');
		}

		if (! isset($data['authentication_key']) || empty($data['authentication_key'])) {
			throw new \Exception('The authentication key had not specified.');
		}				

		$domain_name = $this->trail_slash($data['domain_name']);

		// Inits CURL session
		$ch = curl_init();

		$url = "{$domain_name}xit-sh-endpoint/post";
		$headers = [
			'Cache-Control: no-cache',
		];

		$post_data = http_build_query($data);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);

		// Closes CURL session
		curl_close($ch);

		return $response;
	}

	/**
	 * Trail slash to the url
	 * @param string $url The URL to be suffixed with a forward slash
	 * @return string
	 * @since 1.0.0
	 */ 
	private function trail_slash ( $url ) {
	    if ( empty( $url ) ) {
	        return $url;
	    }
	    
	    if ( '/' === substr( $url, -1 ) ) {
	        return $url;
	    }
	    
	    return $url . '/';
	}	
}
