<?php 

/**
 * 
 * asyad class 
 */
class AsyadExpress
{

	public $UserName ; 
	public $Password  ; 
	public $ApiUrl  ; 
	public $Options;
	
	function __construct()
	{
		$asyadOptions = get_option('woocommerce_asyad_methode_settings');
		$this->Options = $asyadOptions;
		$this->UserName = $asyadOptions['asyad_username'];
		$this->Password = $asyadOptions['asyad_password'];
		$this->ApiUrl =  $asyadOptions['asyadexpress_api_url'];
		

	}


    public function AsyadLogin()
	{
         $data = [
         	'username'  => $this->UserName ,  
         	'password' => $this->Password
         ];
		$result =  $this->PostRequest($data , $this->ApiUrl .'login');
	
		if($result['status'] == 302) {
			if(!empty($result['data']['token'])){
				return array('status' => true , 'token' => $result['data']['token']) ;
			}
			return array('status' => false , 'message' => $result['message']);
		}
	}

	function CreateShipment($order_id){
		global $woocommerce;
		$asyad_dir = WP_PLUGIN_DIR . '/asyad-shipping-plugin';
        require_once( $asyad_dir  . '/countries_en.php' );
        $asyadOptions = $this->Options;
		$token  = null;
		$login = $this->AsyadLogin();
		if($login['status'] == true ) {
			$token = $login['token'];
		}
		else {
			return ['status' => false , 'message'=> "error with login .."];
		}
         $order = new WC_Order($order_id);
         $billing_country_name= $countryList[$order->get_billing_country()];
         $ProductDescription = ''; 
         foreach ( $order->get_items() as $item_id => $item ) {
         	$ProductDescription .= $product_name = $item->get_name() . ' - ';
         }

		 $shipmentData = [
				   "ClientOrderRef" => 'SDG_'.$order->id, 
				   "Description" => "Sending a test shipment of 2 packages with 0 items listed - domestic only", 
				   "HandlingTypee" => "Others", 
				   "ShippingCost" =>  $order->get_shipping_total(), 
				   "PaymentType" => 'PREPAID' ,// COD || $order->get_payment_method_title(), 
				   "CODAmount" => 0, 
				 
				   "ShipmentProduct" => "EXPRESS", 
				   "ShipmentService" => "ALL_DAY", 
				   "OrderType" => "DROPOFF", 
				   "PickupType" => "", 
				   "PickupDate" => "", 
				   "TotalShipmentValue" => $order->get_shipping_total(), 
				   "JourneyOptions" => [
				         "AdditionalInfo" => "", 
				         "NOReturn" => false, 
				         "Extra" => [
				         ] 
				      ], 
			   "Consignee" => [

			   	           "Name" => !empty( $order->get_formatted_shipping_full_name() ) ? $order->get_formatted_shipping_full_name() :  $order->get_formatted_billing_full_name(),
			               "AddressLine1" =>!empty( $order->get_shipping_address_1() ) ? $order->get_shipping_address_1() :  $order->get_billing_address_1(),
			               "AddressLine2" =>!empty( $order->get_shipping_address_1() ) ? $order->get_shipping_address_1() :  $order->get_billing_address_1(),
			               "Area" =>!empty( $order->get_shipping_address_1() ) ? $order->get_shipping_address_1() :  $order->get_billing_address_1(),
			               "City" => !empty( $order->get_shipping_city() ) ? $order->get_shipping_city() :  $order->get_billing_city(),
			               "Region" => !empty( $order->get_shipping_state() ) ? $order->get_shipping_state() :  $order->get_billing_state(),
			               "Country" => $billing_country_name,
			               "ZipCode" => !empty( $order->get_shipping_postcode() ) ? $order->get_shipping_postcode() :  $order->get_billing_postcode(),
			               "MobileNo" => !empty( $order->get_shipping_phone() ) ? $order->get_shipping_phone() :  $order->get_billing_phone(),
			               "PhoneNo" =>!empty( $order->get_shipping_phone() ) ? $order->get_shipping_phone() :  $order->get_billing_phone(),
			               "Email" =>$order->get_billing_email(), 
			               "Latitude" => !empty(get_post_meta($order->get_id(), 'shipping_lat', true)) ? get_post_meta($order->get_id(), 'shipping_lat', true) : get_post_meta($order->get_id(), 'billing_lat', true), 
			               "Longitude" => !empty(get_post_meta($order->get_id(), 'shipping_long', true)) ? get_post_meta($order->get_id(), 'shipping_long', true) : get_post_meta($order->get_id(), 'billing_long', true), 
			               "Instruction" => "Delivery Instructions", 
			               "What3Words" => "", 
			               "NationalId" => "", 
			               "ReferenceNo" => "", 
			               "Vattaxcode" => "", 
			               "Eorinumber" => "" 
			            ], 
			   "Shipper" => [
			                  "ReturnAsSame" => true, 
			                  "ContactName" => $asyadOptions['ContactName'], 
			                  "CompanyName" => $asyadOptions['CompanyName'],
			                  "AddressLine1" => $asyadOptions['company_addressLine1'],
			                  "AddressLine2" => "Additional Sender Address Line", 
			                  "Area" => $asyadOptions['company_area'],
			                  "City" => $asyadOptions['company_city'],
			                  "Region" => $asyadOptions['ContactName'],
			                  "Country" => $asyadOptions['company_country'],
			                  "ZipCode" => $asyadOptions['company_zipCode'],
			                  "MobileNo" =>$asyadOptions['company_mobile_no'],
			                  "TelephoneNo" => "", 
			                  "Email" => $asyadOptions['company_email'], 
			                  "Latitude" => "23.581069146", 
			                  "Longitude" => "58.257017583", 
			                  "NationalId" => "", 
			                  "What3Words" => "", 
			                  "ReferenceOrderNo" => "", 
			                  "Vattaxcode" => "", 
			                  "Eorinumber" => "" 
			               ], 
			  
			    "PackageDetails" => [
				                        [
				                           "Package_AWB" => "SDGOrd1Pkg3", 
				                           "Weight" => 0.1, 
				                           "Width" => 10, 
				                           "Length" => 15, 
				                           "Height" => 20 
				                        ] 
			                       ] ,
			    "ShipmentPerformaInvoice" => [
											    [
											      "HSCode" => "04090000",
											      "ProductDescription" => $ProductDescription,
											      "ItemQuantity"=> $order->get_item_count(),
											      "ProductDeclaredValue" => $order->get_item_count(),
											      "itemRef" => $order->id,
											      "ShipmentTypeCode" => "Document",
											      "PackageTypeCode" => "POUCH"
											    ]
         									]                 
        ]; 

       return $this->PostRequest($shipmentData , $this->ApiUrl.'v2/orders' , $token);
	}

	function CreateShipmentFulfillment($order_id){
		global $woocommerce;
		$asyad_dir = WP_PLUGIN_DIR . '/asyad-shipping-plugin';
        require_once( $asyad_dir  . '/countries_en.php' );
        $asyadOptions = $this->Options;
		$token  = null;
		$login = $this->AsyadLogin();
		if($login['status'] == true ) {
			$token = $login['token'];
		}
		else {
			return ['status' => false , 'message'=> "error with login .."];
		}
         $order = new WC_Order($order_id);
         $billing_country_name= $countryList[$order->get_billing_country()];
         $ProductDescription = ''; 
         $itemsFulfillment = [];
         foreach ( $order->get_items() as $item_id => $item ) {
         	$ProductDescription .= $product_name = $item->get_name() . ' - ';
         	$product = wc_get_product($item->get_product_id());
         	$itemulfillment = ['quantity' => $item['qty'] , 'sku' =>   $product->get_sku() ];
         	array_push($itemsFulfillment, $itemulfillment);

         }

		 $shipmentData = [
				   "ClientOrderRef" => 'SDG_'.$order->id, 
				   "Description" => "Sending a test shipment of 2 packages with 0 items listed - domestic only", 
				   "HandlingTypee" => "Others", 
				   "ShippingCost" =>  $order->get_shipping_total(), 
				   "PaymentType" => 'PREPAID' ,// COD || $order->get_payment_method_title(), 
				   "CODAmount" => 0, 
				 
				   "ShipmentProduct" => "EXPRESS", 
				   "ShipmentService" => "ALL_DAY", 
				   "OrderType" => "DROPOFF", 
				   "PickupType" => "", 
				   "PickupDate" => "", 
				   "TotalShipmentValue" => $order->get_shipping_total(), 
				   "JourneyOptions" => [
				         "AdditionalInfo" => "", 
				         "NOReturn" => false, 
				         "Extra" => [
				         ] 
				      ], 
			   "Consignee" => [

			   	           "Name" => !empty( $order->get_formatted_shipping_full_name() ) ? $order->get_formatted_shipping_full_name() :  $order->get_formatted_billing_full_name(),
			               "AddressLine1" =>!empty( $order->get_shipping_address_1() ) ? $order->get_shipping_address_1() :  $order->get_billing_address_1(),
			               "AddressLine2" =>!empty( $order->get_shipping_address_1() ) ? $order->get_shipping_address_1() :  $order->get_billing_address_1(),
			               "Area" =>!empty( $order->get_shipping_address_1() ) ? $order->get_shipping_address_1() :  $order->get_billing_address_1(),
			               "City" => !empty( $order->get_shipping_city() ) ? $order->get_shipping_city() :  $order->get_billing_city(),
			               "Region" => !empty( $order->get_shipping_state() ) ? $order->get_shipping_state() :  $order->get_billing_state(),
			               "Country" => $billing_country_name,
			               "ZipCode" => !empty( $order->get_shipping_postcode() ) ? $order->get_shipping_postcode() :  $order->get_billing_postcode(),
			               "MobileNo" => !empty( $order->get_shipping_phone() ) ? $order->get_shipping_phone() :  $order->get_billing_phone(),
			               "PhoneNo" =>!empty( $order->get_shipping_phone() ) ? $order->get_shipping_phone() :  $order->get_billing_phone(),
			               "Email" =>$order->get_billing_email(), 
			               "Latitude" => !empty(get_post_meta($order->get_id(), 'shipping_lat', true)) ? get_post_meta($order->get_id(), 'shipping_lat', true) : get_post_meta($order->get_id(), 'billing_lat', true), 
			               "Longitude" => !empty(get_post_meta($order->get_id(), 'shipping_long', true)) ? get_post_meta($order->get_id(), 'shipping_long', true) : get_post_meta($order->get_id(), 'billing_long', true), 
			               "Instruction" => "Delivery Instructions", 
			               "What3Words" => "", 
			               "NationalId" => "", 
			               "ReferenceNo" => "", 
			               "Vattaxcode" => "", 
			               "Eorinumber" => "" 
			            ], 

			    "items" => json_encode($itemsFulfillment),         
			    "Shipper" => [
			                  "ReturnAsSame" => true, 
			                  "ContactName" => $asyadOptions['ContactName'], 
			                  "CompanyName" => $asyadOptions['CompanyName'],
			                  "AddressLine1" => $asyadOptions['company_addressLine1'],
			                  "AddressLine2" => "Additional Sender Address Line", 
			                  "Area" => $asyadOptions['company_area'],
			                  "City" => $asyadOptions['company_city'],
			                  "Region" => $asyadOptions['ContactName'],
			                  "Country" => $asyadOptions['company_country'],
			                  "ZipCode" => $asyadOptions['company_zipCode'],
			                  "MobileNo" =>$asyadOptions['company_mobile_no'],
			                  "TelephoneNo" => "", 
			                  "Email" => $asyadOptions['company_email'], 
			                  "Latitude" => "23.581069146", 
			                  "Longitude" => "58.257017583", 
			                  "NationalId" => "", 
			                  "What3Words" => "", 
			                  "ReferenceOrderNo" => "", 
			                  "Vattaxcode" => "", 
			                  "Eorinumber" => "" 
			               ], 
			  
			    "PackageDetails" => [
				                        [
				                           "Package_AWB" => "SDGOrd1Pkg3", 
				                           "Weight" => 0.1, 
				                           "Width" => 10, 
				                           "Length" => 15, 
				                           "Height" => 20 
				                        ] 
			                       ] ,
			    "ShipmentPerformaInvoice" => [
											    [
											      "HSCode" => "04090000",
											      "ProductDescription" => $ProductDescription,
											      "ItemQuantity"=> $order->get_item_count(),
											      "ProductDeclaredValue" => $order->get_item_count(),
											      "itemRef" => $order->id,
											      "ShipmentTypeCode" => "Document",
											      "PackageTypeCode" => "POUCH"
											    ]
         									]                 
        ]; 

       return $this->PostRequest($shipmentData , $this->ApiUrl.'v2/orders' , $token);
	}
	function CancelShipment($shipmentId){
		$token  = null;
		$login = $this->AsyadLogin();
		if($login['status'] == true ) {
			$token = $login['token'];
		}
		else {
			return ['status' => false , 'message'=> "error with login .."];
		}

       $shipmentData = [];

       return $this->PostRequest($shipmentData , $this->ApiUrl.'v2/orders/'.$shipmentId , $token , 'DELETE');
	}


	public function PostRequest($data , $url , $token=null , $method = 'POST'){

				 $headers = [
			 	'Content-Type: application/json',
			 	'Authorization: Bearer ' .$token
			     ];
			//open connection
				$ch = curl_init();

				//set the url, number of POST vars, POST data
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($data));

				//So that curl_exec returns the contents of the cURL; rather than echoing it
				curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

				//execute post
				$server_output = curl_exec($ch);

			return json_decode($server_output , true );
	}
}
