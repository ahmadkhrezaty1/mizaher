<style type="text/css">
	.checkout-content{
		display: none;
	}
	.order-heading {
		padding: 50px 20px 20px;
	}
	.order-data {
		background-color: #2A3F54;
		max-width: 280px;
		margin: 50px auto 20px;
		color: #fff;
		text-align: center;
		border-radius: 10px;
	}
	.package-header {
	    padding: 30px 10px;
	    border-bottom: solid thin #ffffff;
	}
	.package-header h4 {
		color: #ffffff !important;
		font-size: 24px;
		font-weight: 600;
		letter-spacing: .8px;
	}
	.package-body {
		padding: 20px 0;
		font-size: 18px;
	}
	.package-divider {
		max-width: 50px;
		height: 50px;
		text-align: center;
		line-height: 44px;
		border-radius: 30px;
		border: solid;
		font-size: 20px;
		margin: -25px auto 0;
		color: #728ca7;
		background: #ffffff;
	}
	form#myCCForm {
		max-width: 600px;
		padding: 20px;
		background: #ededed;
		margin: 0 auto;
	}
	.sub-heading {
		padding: 0px 10px;
	}
	.sub-heading h3 {
		margin: 0px;
		padding: 5px 0;
		border-bottom: solid thin rgba(55,74,94,0.3);
		margin-bottom: 15px;
	}
</style>
		
<section>
	<!-- <div class="order-heading">
		<h3>Order Details</h3>
	</div> -->
	<div class="order-data">
		<div class="package-header"><h4><?=$package['name']?></h4></div>
		<div class="package-divider"><i class="fa fa-check"></i></div>
		<div class="package-body">$<?=$package['price']?>/<?=$package['validity']?> days</div>
	</div>
	<div class="col-sm-12">
		<form id="myCCForm" action="https://facebook.milanaproject.org/payment/make_payment" onsubmit="return submit_form();" method="post" class="form form-horizontal">
			<input name="token" type="hidden" value="" />
			<input name="package_id" type="hidden" value="<?=$package['id']?>" />
			<div class="sub-heading">
                <h3>Personal Information</h3>
            </div>
			<div class="form-group">
				<div class="col-sm-6">
					<input type="text" name="fname" readonly value="<?=$userdata['fname']?>" class="form-control" placeholder="First Name">	
				</div>
				<div class="col-sm-6">
					<input type="text" readonly name="lname" value="<?=$userdata['lname']?>" class="form-control" placeholder="Last Name">	
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-6">
					<input type="email" name="email" value="<?=$userdata['email']?>" class="form-control" placeholder="Email" readonly>	
				</div>
				<div class="col-sm-6">
					<input type="tel" name="phone" value="<?=$userdata['phone']?>" class="form-control" placeholder="Phone Number">	
				</div>
			</div>

			<div class="sub-heading">
                <h3>Billing Address</h3>
            </div>
			
			<div class="form-group">
				<div class="col-sm-12">
					<input type="text" name="address1" value="" class="form-control" placeholder="Street Address">	
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<input type="text" name="address2" value="" class="form-control" placeholder="Street Address 2">	
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-4">
					<input type="text" name="city" value="" class="form-control" placeholder="City">
				</div>
				<div class="col-sm-5">
					<input type="text" name="state" value="" class="form-control" placeholder="State">
				</div>
				<div class="col-sm-3">
					<input type="text" name="zip" value="" class="form-control" placeholder="Postcode">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<!-- <input type="text" name="country" class="form-control"> -->
					<select name="country" class="form-control" required>
						<option>Country</option>
						<option value="ALA">Åland Islands</option>
						<option value="AFG">Afghanistan</option>
						<option value="ALB">Albania</option>
						<option value="DZA">Algeria</option>
						<option value="ASM">American Samoa</option>
						<option value="AND">Andorra</option>
						<option value="AGO">Angola</option>
						<option value="AIA">Anguilla</option>
						<option value="ATA">Antarctica</option>
						<option value="ATG">Antigua and Barbuda</option>
						<option value="ARG">Argentina</option>
						<option value="ARM">Armenia</option>
						<option value="ABW">Aruba</option>
						<option value="AUS">Australia</option>
						<option value="AUT">Austria</option>
						<option value="AZE">Azerbaijan</option>
						<option value="BHS">Bahamas</option>
						<option value="BHR">Bahrain</option>
						<option value="BGD">Bangladesh</option>
						<option value="BRB">Barbados</option>
						<option value="BLR">Belarus</option>
						<option value="BEL">Belgium</option>
						<option value="BLZ">Belize</option>
						<option value="BEN">Benin</option>
						<option value="BMU">Bermuda</option>
						<option value="BTN">Bhutan</option>
						<option value="BOL">Bolivia</option>
						<option value="BES">Bonaire, Sint Eustatius and Saba</option>
						<option value="BIH">Bosnia and Herzegovina</option>
						<option value="BWA">Botswana</option>
						<option value="BVT">Bouvet Island</option>
						<option value="BRA">Brazil</option>
						<option value="IOT">British Indian Ocean Territory</option>
						<option value="BRN">Brunei Darussalam</option>
						<option value="BGR">Bulgaria</option>
						<option value="BFA">Burkina Faso</option>
						<option value="BDI">Burundi</option>
						<option value="KHM">Cambodia</option>
						<option value="CMR">Cameroon</option>
						<option value="CAN">Canada</option>
						<option value="CPV">Cape Verde</option>
						<option value="CYM">Cayman Islands</option>
						<option value="CAF">Central African Republic</option>
						<option value="TCD">Chad</option>
						<option value="CHL">Chile</option>
						<option value="CHN">China</option>
						<option value="CXR">Christmas Island</option>
						<option value="CCK">Cocos (Keeling) Islands</option>
						<option value="COL">Colombia</option>
						<option value="COM">Comoros</option>
						<option value="COG">Congo</option>
						<option value="COD">Congo, the Democratic Republic of the</option>
						<option value="COK">Cook Islands</option>
						<option value="CRI">Costa Rica</option>
						<option value="CIV">Cote D'ivoire</option>
						<option value="HRV">Croatia (Hrvatska)</option>
						<option value="CYP">Cyprus</option>
						<option value="CZE">Czech Republic</option>
						<option value="DNK">Denmark</option>
						<option value="DJI">Djibouti</option>
						<option value="DMA">Dominica</option>
						<option value="DOM">Dominican Republic</option>
						<option value="ECU">Ecuador</option>
						<option value="EGY">Egypt</option>
						<option value="SLV">El Salvador</option>
						<option value="GNQ">Equatorial Guinea</option>
						<option value="ERI">Eritrea</option>
						<option value="EST">Estonia</option>
						<option value="ETH">Ethiopia</option>
						<option value="FLK">Falkland Islands (Malvinas)</option>
						<option value="FRO">Faroe Islands</option>
						<option value="FJI">Fiji</option>
						<option value="FIN">Finland</option>
						<option value="FRA">France</option>
						<option value="FXX">France, Metropolitan</option>
						<option value="GUF">French Guiana</option>
						<option value="PYF">French Polynesia</option>
						<option value="ATF">French Southern Territories</option>
						<option value="GAB">Gabon</option>
						<option value="GMB">Gambia</option>
						<option value="GEO">Georgia</option>
						<option value="DEU">Germany</option>
						<option value="GHA">Ghana</option>
						<option value="GIB">Gibraltar</option>
						<option value="GRC">Greece</option>
						<option value="GRL">Greenland</option>
						<option value="GRD">Grenada</option>
						<option value="GLP">Guadeloupe</option>
						<option value="GUM">Guam</option>
						<option value="GTM">Guatemala</option>
						<option value="GGY">Guernsey</option>
						<option value="GIN">Guinea</option>
						<option value="GNB">Guinea-Bissau</option>
						<option value="GUY">Guyana</option>
						<option value="HTI">Haiti</option>
						<option value="HMD">Heard Island and Mcdonald Islands</option>
						<option value="HND">Honduras</option>
						<option value="HKG">Hong Kong</option>
						<option value="HUN">Hungary</option>
						<option value="ISL">Iceland</option>
						<option value="IND">India</option>
						<option value="IDN">Indonesia</option>
						<option value="IRQ">Iraq</option>
						<option value="IRL">Ireland</option>
						<option value="IMN">Isle of Man</option>
						<option value="ISR">Israel</option>
						<option value="ITA">Italy</option>
						<option value="JAM">Jamaica</option>
						<option value="JPN">Japan</option>
						<option value="JEY">Jersey</option>
						<option value="JOR">Jordan</option>
						<option value="KAZ">Kazakhstan</option>
						<option value="KEN">Kenya</option>
						<option value="KIR">Kiribati</option>
						<option value="KOR">Korea, Republic of</option>
						<option value="UNK">UNK</option>
						<option value="KWT">Kuwait</option>
						<option value="KGZ">Kyrgyzstan</option>
						<option value="LAO">Lao People's Democratic Republic</option>
						<option value="LVA">Latvia</option>
						<option value="LBN">Lebanon</option>
						<option value="LSO">Lesotho</option>
						<option value="LBR">Liberia</option>
						<option value="LBY">Libyan Arab Jamahiriya</option>
						<option value="LIE">Liechtenstein</option>
						<option value="LTU">Lithuania</option>
						<option value="LUX">Luxembourg</option>
						<option value="MAC">Macao</option>
						<option value="MKD">Macedonia</option>
						<option value="MDG">Madagascar</option>
						<option value="MWI">Malawi</option>
						<option value="MYS">Malaysia</option>
						<option value="MDV">Maldives</option>
						<option value="MLI">Mali</option>
						<option value="MLT">Malta</option>
						<option value="MHL">Marshall Islands</option>
						<option value="MTQ">Martinique</option>
						<option value="MRT">Mauritania</option>
						<option value="MUS">Mauritius</option>
						<option value="MYT">Mayotte</option>
						<option value="MEX">Mexico</option>
						<option value="FSM">Micronesia, Federated States of</option>
						<option value="MDA">Moldova, Republic of</option>
						<option value="MCO">Monaco</option>
						<option value="MNG">Mongolia</option>
						<option value="MNE">Montenegro</option>
						<option value="MSR">Montserrat</option>
						<option value="MAR">Morocco</option>
						<option value="MOZ">Mozambique</option>
						<option value="MMR">Myanmar</option>
						<option value="NAM">Namibia</option>
						<option value="NRU">Nauru</option>
						<option value="NPL">Nepal</option>
						<option value="NLD">Netherlands</option>
						<option value="ANT">Netherlands Antilles</option>
						<option value="NCL">New Caledonia</option>
						<option value="NZL">New Zealand</option>
						<option value="NIC">Nicaragua</option>
						<option value="NER">Niger</option>
						<option value="NGA">Nigeria</option>
						<option value="NIU">Niue</option>
						<option value="NFK">Norfolk Island</option>
						<option value="MNP">Northern Mariana Islands</option>
						<option value="NOR">Norway</option>
						<option value="OMN">Oman</option>
						<option value="PAK">Pakistan</option>
						<option value="PLW">Palau</option>
						<option value="PSE">Palestinian Territory, Occupied</option>
						<option value="PAN">Panama</option>
						<option value="PNG">Papua New Guinea</option>
						<option value="PRY">Paraguay</option>
						<option value="PER">Peru</option>
						<option value="PHL">Philippines</option>
						<option value="PCN">Pitcairn</option>
						<option value="POL">Poland</option>
						<option value="PRT">Portugal</option>
						<option value="PRI">Puerto Rico</option>
						<option value="QAT">Qatar</option>
						<option value="REU">Reunion</option>
						<option value="ROU">Romania</option>
						<option value="RUS">Russian Federation</option>
						<option value="RWA">Rwanda</option>
						<option value="SHN">Saint Helena</option>
						<option value="KNA">Saint Kitts and Nevis</option>
						<option value="LCA">Saint Lucia</option>
						<option value="SPM">Saint Pierre and Miquelon</option>
						<option value="VCT">Saint Vincent and the Grenadines</option>
						<option value="WSM">Samoa</option>
						<option value="SMR">San Marino</option>
						<option value="STP">Sao Tome and Principe</option>
						<option value="SAU">Saudi Arabia</option>
						<option value="SEN">Senegal</option>
						<option value="SRB">Serbia</option>
						<option value="SCG">Serbia and Montenegro</option>
						<option value="SYC">Seychelles</option>
						<option value="SLE">Sierra Leone</option>
						<option value="SGP">Singapore</option>
						<option value="SVK">Slovakia</option>
						<option value="SVN">Slovenia</option>
						<option value="SLB">Solomon Islands</option>
						<option value="SOM">Somalia</option>
						<option value="ZAF">South Africa</option>
						<option value="SGS">South Georgia and the South Sandwich Islands</option>
						<option value="ESP">Spain</option>
						<option value="LKA">Sri Lanka</option>
						<option value="SUR">Suriname</option>
						<option value="SJM">Svalbard and Jan Mayen Islands</option>
						<option value="SWZ">Swaziland</option>
						<option value="SWE">Sweden</option>
						<option value="CHE">Switzerland</option>
						<option value="TWN">Taiwan</option>
						<option value="TJK">Tajikistan</option>
						<option value="TZA">Tanzania, United Republic of</option>
						<option value="THA">Thailand</option>
						<option value="TLS">Timor-Leste</option>
						<option value="TGO">Togo</option>
						<option value="TKL">Tokelau</option>
						<option value="TON">Tonga</option>
						<option value="TTO">Trinidad and Tobago</option>
						<option value="TUN">Tunisia</option>
						<option value="TUR">Turkey</option>
						<option value="TKM">Turkmenistan</option>
						<option value="TCA">Turks and Caicos Islands</option>
						<option value="TUV">Tuvalu</option>
						<option value="UGA">Uganda</option>
						<option value="UKR">Ukraine</option>
						<option value="ARE">United Arab Emirates</option>
						<option value="GBR">United Kingdom</option>
						<option value="USA">United States</option>
						<option value="UMI">United States Minor Outlying Islands</option>
						<option value="URY">Uruguay</option>
						<option value="UZB">Uzbekistan</option>
						<option value="VUT">Vanuatu</option>
						<option value="VAT">Vatican City State (Holy See)</option>
						<option value="VEN">Venezuela</option>
						<option value="VNM">Viet Nam</option>
						<option value="VGB">Virgin Islands, British</option>
						<option value="VIR">Virgin Islands, U.S.</option>
						<option value="WLF">Wallis and Futuna Islands</option>
						<option value="ESH">Western Sahara</option>
						<option value="YEM">Yemen</option>
						<option value="YUG">Yugoslavia</option>
						<option value="ZAR">Zaire</option>
						<option value="ZMB">Zambia</option>
						<option value="ZWE">Zimbabwe</option>
					</select>
				</div>
			</div>
			<div class="sub-heading">
                <h3>Payment Details</h3>
            </div>
			<div class="col-sm-12">
				<div class="alert alert-success text-center large-text" role="alert">
	                Total Due Today: &nbsp; <strong>$<?=$package['price']?>.00 USD</strong>
	            </div>
	        </div>
            <!-- <div class="form-group">
            	<label>Mode of Payment</label>
            	<label><input type="radio" name="pay_mode" value="tco" class="" > 2Checkout</label>
            	<label><input type="radio" name="pay_mode" value="paypal" class=""> Paypal</label>
            </div> -->
            <!-- <div class="checkout-content">
	            <div class="form-group">
	            	<input id="ccNo" type="text" value="" autocomplete="off" required class="form-control" placeholder="Card Number" />				
				</div>
				<div class="form-group">
					<div class="col-sm-6">
						<input id="expMonth" type="text" size="2" required class="form-control" placeholder="MM" />
						<span> / </span>
						<input id="expYear" type="text" size="4" required class="form-control" placeholder="YYYY"/>	
					</div>
					<div class="col-sm-6">
						<input id="cvv" type="text" value="" autocomplete="off" required class="form-control" placeholder="CVV Security Number" />
					</div>
				</div>
			</div> -->

			<div class="form-group">
				<div class="col-sm-12">
					<!-- <input type="submit" value="Submit Payment" class="btn btn-primary" id="normal-btn" /> -->
					<input type="button" value="Submit Payment" class="btn ar-btn btn-primary" id="pay-btn" >
				</div>
			</div>
			
		</form>

		<form action="https://www.2checkout.com/checkout/purchase" id="paypal_direct" method="post">
			<input name="sid" type="hidden" value="203863115"><!-- 901394511 -->
			<!-- 203863115 -->
			<input name="mode" type="hidden" value="2CO">
			<!-- <input name="return_url" type="hidden" value="https://facebook.milanaproject.org/payment/success/"> -->
			<input name="li_0_type" type="hidden" value="product">
			<input name="merchant_order_id" type="hidden" value="<?=$last_id?>">
			<input name="li_0_name" type="hidden" value="<?=$package['name']?>">
			<input name="li_0_price" type="hidden" value="<?=$package['price']?>">
			<input name="li_0_recurrence" type="hidden" value="<?=($package['validity']=='30')? '1 Month' : ($package['validity']/7).' Week'?>">
			<input name="li_0_duration" type="hidden" value="<?='Forever'?>">
			<input name="li_0_product_id" type="hidden" value="<?=$package['id']?>">
			<input name="currency_code" type="hidden" value="USD">
			<input name="card_holder_name" type="hidden" value="<?=$userdata['fname']?> <?=$userdata['lname']?>">
			<input name="street_address" type="hidden" value="123 Test Address">
			<input name="street_address2" type="hidden" value="Suite 200">
			<input name="city" type="hidden" value="Columbus">
			<input name="state" type="hidden" value="OH">
			<input name="zip" type="hidden" value="43228">
			<input name="country" type="hidden" value="USA">
			<input name="email" type="hidden" value="example@2co.com">
			<input name="phone" type="hidden" value="614-921-2450">
			<input name="ship_name" type="hidden" value="Gift Receiver">
			<input name="ship_street_address" type="hidden" value="1234 Address Road">
			<input name="ship_street_address2" type="hidden" value="Apartment 123">
			<input name="ship_city" type="hidden" value="Columbus">
			<input name="ship_state" type="hidden" value="OH">
			<input name="ship_zip" type="hidden" value="43235">
			<input name="ship_country" type="hidden" value="USA">
			<!-- <input name="paypal_direct" type="hidden" value="N"> -->
			<input type="submit" id="pay_submit" value="Submit Payment" style="display: none;">
		</form>
	</div>
	<script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
	<script src="https://www.2checkout.com/static/checkout/javascript/direct.min.js"></script>
	<script >
		// jQuery('input[name="pay_mode"]').click(function(){
		// 	if(jQuery(this).val()=='tco'){
		// 		jQuery('.checkout-content').show();
		// 		jQuery('#normal-btn').show();
		// 		jQuery('#pay-btn').hide();
		// 	} else {
		// 		jQuery('.checkout-content').hide();
		// 		jQuery('#normal-btn').hide();
		// 		jQuery('#pay-btn').show();
		// 	}
		// });

		jQuery('#pay-btn').click(function(){
			var form = jQuery('#paypal_direct');
			form.find('input[name="ship_country"]').val(jQuery('#myCCForm select[name="country"]').val());
			form.find('input[name="country"]').val(jQuery('#myCCForm select[name="country"]').val());
			form.find('input[name="ship_zip"]').val(jQuery('#myCCForm input[name="zip"]').val());
			form.find('input[name="zip"]').val(jQuery('#myCCForm input[name="zip"]').val());
			form.find('input[name="ship_state"]').val(jQuery('#myCCForm input[name="state"]').val());
			form.find('input[name="state"]').val(jQuery('#myCCForm input[name="state"]').val());
			form.find('input[name="ship_city"]').val(jQuery('#myCCForm input[name="city"]').val());
			form.find('input[name="city"]').val(jQuery('#myCCForm input[name="city"]').val());
			form.find('input[name="ship_name"]').val(jQuery('#myCCForm input[name="fname"]').val()+' '+jQuery('#myCCForm input[name="lname"]').val());
			form.find('input[name="card_holder_name"]').val(jQuery('#myCCForm input[name="fname"]').val()+' '+jQuery('#myCCForm input[name="lname"]').val());
			form.find('input[name="street_address"]').val(jQuery('#myCCForm input[name="address1"]').val());
			form.find('input[name="ship_street_address"]').val(jQuery('#myCCForm input[name="address1"]').val());
			form.find('input[name="street_address2"]').val(jQuery('#myCCForm input[name="address2"]').val());
			form.find('input[name="ship_street_address2"]').val(jQuery('#myCCForm input[name="address2"]').val());
			form.find('input[name="phone"]').val(jQuery('#myCCForm input[name="phone"]').val());
			form.find('input[name="email"]').val(jQuery('#myCCForm input[name="email"]').val());
			form.submit();
		});
	</script>
	<script>
      	// Called when token created successfully.
	    var successCallback = function(data) {
			var myForm = document.getElementById('myCCForm');

			// Set the token as the value for the token input
			myForm.token.value = data.response.token.token;

			// IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.
			myForm.submit();
	    };

	    // Called when token creation fails.
	    var errorCallback = function(data) {
	        // Retry the token request if ajax call fails
	        if (data.errorCode === 200) {console.log(data); return false;
	           // This error code indicates that the ajax call failed. We recommend that you retry the token request.
	        } else {
	          alert(data.errorMsg);
	        }
      	};

      	var tokenRequest = function() {
	        // Setup token request arguments
	        var args = {
				sellerId: "203863115",
				publishableKey: "9ACDF3C6-CCFC-48E8-8A5D-3A5DAFCEA829",
				// sellerId: "901394511",
				// publishableKey: "C44C6E59-CDF2-4735-AF20-00A146EC3C51",
				ccNo: $("#ccNo").val(),
				cvv: $("#cvv").val(),
				expMonth: $("#expMonth").val(),
				expYear: $("#expYear").val()
	        };

	        // Make the token request
	        TCO.requestToken(successCallback, errorCallback, args);
      	};


      // $(function() {
        // Pull in the public encryption key for our environment
        TCO.loadPubKey('production');
        // TCO.loadPubKey('sandbox');
        	
        function submit_form(){
        	tokenRequest();
        	return false;
        }
        $("#myCCForm").submit(function(e) {
        	// e.preventDefault();
        	// alert('abc');
         	//  // Call our token request function
         	//  tokenRequest();

          	// Prevent form from submitting
          	return false;
        });
      // });

    </script>
</section>