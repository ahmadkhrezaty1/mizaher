<?php
require_once('Stripe/lib/Stripe.php');
class Stripe_class_ecommerce{

	public $secret_key;
	public $publishable_key;	
	public $description;
	public $amount;
	public $action_url;	
	public $currency;
	public $img_url;
	public $title;

	function __construct()
	{		
		$this->CI =& get_instance();
	}
	
	function set_button(){
	
		// $base_url=base_url();
		if(strtoupper($this->currency)=='JPY' || strtoupper($this->currency)=='VND') $amount=$this->amount;
		else $amount=$this->amount*100;
		
		$button="";
		
		$button.="<form action='{$this->action_url}' method='POST'>
			<script
		    src='https://checkout.stripe.com/checkout.js' class='stripe-button'
		    data-key='{$this->publishable_key}'
		    data-image='{$this->img_url}'
		    data-name='{$this->title}'
		    data-currency='{$this->currency}'
		    data-description='{$this->description}'
		    data-amount='{$amount}'>
		  	</script>
		</form>";

		return $button;
		
	}
	
	
	
public function stripe_payment_action()
{		
	$response=array();		
	$amount= $this->CI->session->userdata('ecommerce_stripe_payment_amount');	
	$currency= $this->CI->session->userdata('ecommerce_stripe_payment_currency');
	$description= $this->CI->session->userdata('ecommerce_stripe_payment_description');

	if(strtoupper($currency)=='JPY' || strtoupper($currency)=='VND')$amount=$amount;
	else $amount=$amount*100;
		
	try
	{
	
		Stripe::setApiKey($this->secret_key);	
		$charge = Stripe_Charge::create(array(
		  	"amount" => $amount,
		  	"currency" => $currency,
		  	"card" => $_POST['stripeToken'],
		  	"description" => $description
		));
		
		$charge_array=$charge->__toArray(true);
		
		$email	= $_POST['stripeEmail'];
		
		$response['status']="Success";
		$response['email']=$email;
		$response['charge_info']=$charge_array;

		return $response;
	
	}
	
	catch(Stripe_CardError $e) {
		$response['status'] ="Error";
		$response['message'] ="Stripe_CardError";
		return $response;
	}
	
	 catch (Stripe_InvalidRequestError $e) {
		$response['status'] ="Error";
		$response['message'] ="Stripe_InvalidRequestError";
		return $response;
	
	} catch (Stripe_AuthenticationError $e) {
		$response['status'] ="Error";
		$response['message'] ="Stripe_AuthenticationError";
		return $response;
	
	} catch (Stripe_ApiConnectionError $e) {
	 	$response['status'] ="Error";
		$response['message'] ="Stripe_ApiConnectionError";
		return $response;
	} catch (Stripe_Error $e) {
		$response['status'] ="Error";
		$response['message'] ="Stripe_Error";
		return $response;
	  
	} catch (Exception $e) {
		$response['status'] ="Error";
		$response['message'] ="Stripe_Error";
		return $response;
	}
		
  }
}

?>