<?php
/*
Plugin Name: Asyadexpress - Shippment Getway
Description: Asyadexpress - Shippment Getway.
Version: 1.0
Author: mhmd shaheen 
Author URI:https://mhmdshaheen.com/
*/
$asyad_dir = WP_PLUGIN_DIR . '/asyad-shipping-plugin';
require_once( $asyad_dir  . '/new-order-status.php' );
require_once($asyad_dir . '/asyad-class.php');
add_filter( 'woocommerce_shipping_init', 'Wc_syad_init' );

function Wc_syad_init() {
      if(!class_exists('WC_Shipping_Method')) return;
        add_filter( 'woocommerce_shipping_methods', 'register_asyad_method' );
        function register_asyad_method( $methods ) {
         // $method contains available shipping methods
            $methods[ 'asyad_methode' ] = 'WC_Asyad_Shipping';
            return $methods;
        }
     
     
     class WC_Asyad_Shipping extends WC_Shipping_Method {

             /**
             * Constructor for the gateway.
             */
            public function  __construct($instance_id = 0) {
                $this->id                   = 'asyad_methode';
                $this->instance_id           = absint( $instance_id );
                $this->has_fields           = false;
                $this->method_title         =  'Asyad Express'  ;
                $this->method_description   = __( 'Asyad Shipment Gateway.' , 'asyad');
                $this->asyad_username           = $this->get_option('asyad_username');
                $this->asyad_password           = $this->get_option('asyad_password');
                 $this->supports              = array('shipping-zones','instance-settings','settings' , 'instance-settings-modal'
                 );
                // Define user set variables.
                $this->asyadexpress_api_url = $this->get_option('asyadexpress_api_url');
                $this->enabled               = $this->get_option('enabled');
                $this->title                 = __( 'Asyad Shipment Gateway .' , 'asyad');
                $this->description           = __('Asyad Shipment Gateway' , 'asyad');
                $this->success_msg =  $this->get_option('success_msg');
                $this->fail_msg  =  $this->get_option('fail_msg');
                $this -> msg['message'] = "";
                $this -> msg['class']   = "";
                $this->init();
                  
            }

            public function init() {
                // Load the settings.
                $this->init_form_fields();
                $this->init_settings();
                $this->instance_form_fields();
                  add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            public function instance_form_fields(){
                 $this->instance_form_fields = array(
                        'cost' => array(
                            'title'         => __( 'cost' ),
                            'type'             => 'text',
                            'description'     => __( 'This controls the cost which the user sees during checkout.' ),
                            'default'        => __( '50' ),
                            'desc_tip'        => true
                        )
                    );
            
            }

            public function init_form_fields(){
                $this->form_fields = array(
                'enabled'           => array(
                    'title'   => __('Enable/Disable', 'Fatora'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable Asyad Shipment Gateway.', 'asyad'),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => __('title', 'asyad'),
                    'type'  => 'text',
                     'default' => 'Asyad Express',
                ),
                'asyad_username' => array(
                    'title' => __('user naame:', 'asyad'),
                    'type'  => 'text',
                     'default' => 'sandbox',
                ),
                'asyad_password' => array(
                    'title' => __('Password' , 'asyad'), 
                    'type'  => 'text',
                    'default' => 'Admin@123',
                ),
                 'cost' => array(
                    'title' => __('cost' , 'asyad'), 
                    'type'  => 'text',
                     'default' => '10',
                ),
                  'asyadexpress_api_url' => array(
                      'title' => __('Asyad Api Url:', 'asyad'),
                      'type'  => 'text',
                      'default' => "https://apix.stag.asyadexpress.com/"

                ) ,
            
                 'ContactName' => array(
                      'title' => __('Contact Name:', 'asyad'),
                      'type'  => 'text',
                      'default' => 'Senders Company',
                ) , 
                 'CompanyName' => array(
                      'title' => __('Company Name:', 'asyad'),
                      'type'  => 'text',
                      'default' => 'Senders Company',
                ) ,

                 'company_addressLine1' => array(
                      'title' => __('Company Address:', 'asyad'),
                      'type'  => 'text',
                       'default' => 'House & Building number',
                ) ,
                  'company_country' => array(
                      'title' => __('Company Country:', 'asyad'),
                      'type'  => 'text',
                       'default' => 'Oman',
                ) ,

                'company_area' => array(
                      'title' => __('Company Area:', 'asyad'),
                      'type'  => 'text',
                       'default' => 'Al Souq',
                ) ,

                 'company_city' => array(
                      'title' => __('Company City:', 'asyad'),
                      'type'  => 'text',
                       'default' => 'Al Seeb',
                ) ,
                 'company_zipCode' => array(
                      'title' => __('Company zipCode:', 'asyad'),
                      'type'  => 'text',
                       'default' => '121',
                ) ,
                 'company_mobile_no' => array(
                      'title' => __('Company Mobile:', 'asyad'),
                      'type'  => 'text',
                       'default' => 'Oman',
                ) ,
                 'company_email' => array(
                      'title' => __('Company email:', 'asyad'),
                      'type'  => 'text',
                       'default' => 'admin@yoursite.com',
                ) ,


              );
            }

            public function calculate_shipping( $package = array() ) {
              
                 $cost =  $this->get_instance_option( 'cost' );

                  $this->add_rate( array(
                     'id'    => $this->id . $this->instance_id,
                    'label'  => $this->settings['title'],
                    'cost'   => $cost
                  ));
            }

     }

    add_action( 'woocommerce_order_actions', 'cancel_shipment_action'  );

    function cancel_shipment_action($actions)
    {
        $actions['wc_cancel_shipment'] = __( 'Cancel Shipment', 'sh');

        return $actions;
    }

     add_action( 'woocommerce_order_action_wc_cancel_shipment', 'DoCancelledShipment' , 10, 2 ) ;
     function DoCancelledShipment($order){
        $order_id = $order->ID;
        $shipment_number = get_post_meta($order_id , 'Asyad_order_awb_number' , true );
        $AsyadExpress = new AsyadExpress ();
        global $wpdb;
        $cancelShipment = $AsyadExpress->CancelShipment($shipment_number);
         if($cancelShipment['status'] == 200){
              $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_shipment_status_canceld',
                            'meta_value' =>'true',
                        ));  

                $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_shipment_status_message',
                            'meta_value' =>"shipment canceld successfully",
                    ));            
               update_post_meta( $order_id, 'Asyad_shipment_status_cancelled', 'true' ); 
               update_post_meta( $order_id, 'Asyad_shipment_status_message', "shipment cancelled successfully" );
         }else {
            $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_shipment_status_cancelled',
                            'meta_value' =>'false',
                        ));  

                $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_shipment_status_message',
                            'meta_value' =>"shipment cancelled not success",
                    ));            
               update_post_meta( $order_id, 'Asyad_shipment_status_cancelled', 'false' ); 
               update_post_meta( $order_id, 'Asyad_shipment_status_message', "shipment cancelled not success" );
         }
     }
     add_action( 'woocommerce_order_action_wc_shipped', 'sh_wc_shipped_order_meta_box_action' );

function sh_wc_processing_paid_order_meta_box_action( $order ) {
    $order_id = $order->ID;
    $message = sprintf( __( 'Order status changed to shipped status .', 'my-textdomain' ), wp_get_current_user()->display_name );
    $order->add_order_note( $message );
    CreateAsyadShippingRequset($order_id , 'shipped' , 'null');

}

    add_action( 'woocommerce_order_status_changed', 'CreateAsyadShippingRequset', 10, 4);
    function CreateAsyadShippingRequset($order_id , $old_status , $new_status) {
        global $wpdb;
        $Asyad_order_awb_number = get_post_meta($order_id, 'Asyad_order_awb_number', true );
        if(empty($Asyad_order_awb_number)){
            if($new_status == 'wc-shipped' || $new_status  == 'shipped') {
                $AsyadExpress = new AsyadExpress ();
                $createShipment = $AsyadExpress->CreateShipment($order_id);
                if($createShipment['status'] == 201){
                    $CreateShipmentData = $createShipment['data'];
                    $order_awb_number = $CreateShipmentData['order_awb_number'];
                    $ClientOrderRef = $CreateShipmentData['ClientOrderRef'];
                     $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_order_awb_number',
                            'meta_value' =>$order_awb_number,
                        ));
                        
                        $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_ClientOrderRef',
                            'meta_value' =>$ClientOrderRef,
                        ));
                        $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_shipment_status',
                            'meta_value' =>'true',
                        ));
                        $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_shipment_status_message',
                            'meta_value' =>$createShipment['message'],
                        ));
                     update_post_meta( $order_id, 'Asyad_order_awb_number', $order_awb_number );
                     update_post_meta( $order_id, 'Asyad_ClientOrderRef', $ClientOrderRef );
                     update_post_meta( $order_id, 'Asyad_shipment_status', 'true' );
                     update_post_meta( $order_id, 'Asyad_shipment_status_message', $createShipment['message'] );
            
                }else {
                     $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_shipment_status',
                            'meta_value' =>'false',
                        ));
                     $wpdb->insert('wp_wc_orders_meta', array(
                            'order_id' => $order_id,
                            'meta_key' => 'Asyad_shipment_status_message',
                            'meta_value' =>$createShipment['message'],
                        ));
                     update_post_meta( $order_id, 'Asyad_shipment_status', 'false' );
                     update_post_meta( $order_id, 'Asyad_shipment_status_message', $createShipment['message'] );
                }

            }
        }elseif(!empty($Asyad_order_awb_number)){
            if($new_status == 'wc-cancelled' || $new_status  == 'cancelled') {
                $order = new WC_Order($order_id);
                DoCancelledShipment($order);
            }
        }
        

    }
}