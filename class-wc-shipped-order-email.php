<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * A custom Expedited Order WooCommerce Email class
 *
 * @since 0.1
 * @extends \WC_Email
 */
class WC_shipped_Order_Email extends WC_Email {

    public function __construct() {

        // set ID, this simply needs to be a unique name
        $this->id = 'wc_shipped_order';

        // this is the title in WooCommerce Email settings
        $this->title = 'shipped Order';
        $this->customer_email = true;

        // this is the description in WooCommerce email settings
        $this->description = 'shipped Order Notification emails are sent when order status changed to  shipped status and you can track your order 
        https://www.asyadexpress.om/track-trace/';

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = 'shipped  Order';
        $this->subject = 'shipped  Order';

        // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
        $this->template_html  = 'emails/customer-completed-order.php';
        $this->template_plain = 'emails/plain/customer-completed-order.php';

        //$this->template_base  = CUSTOM_WC_EMAIL_PATH . '/Custome-WooCommerce/';

        // Trigger on new paid orders

       // add_action( 'woocommerce_order_status_shipped', array( $this, 'trigger' ) ,10,1 );
        //add_action( 'woocommerce_order_status_pending_to_shipped_notification', array( $this, 'trigger' ));
       // add_action( 'woocommerce_order_status_processing_to_shipped_notification', array( $this, 'trigger' ) );
        add_action( 'woocommerce_order_action_wc_shipped', array( $this, 'trigger' ) , 10, 2 ) ;
       // add_action( 'Shipped_notification', array( $this, 'trigger' ) , 10, 2 ) ;


        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();
        

        // this sets the recipient to the settings defined below in init_form_fields()

    }

     function trigger( $order_id ) {

        // bail if no order ID is present
        if ( ! $order_id )
            return;

        // setup order object
        $this->object = new WC_Order( $order_id );

        // bail if shipping method is not expedited

        // replace variables in the subject/headings
        $this->find[] = '{order_date}';
        $this->replace[] = date_i18n( woocommerce_date_format(), strtotime( $this->object->order_date ) );

        $this->find[] = '{order_number}';
        $this->replace[] = $this->object->get_order_number();
        $this->recipient = $this->object->billing_email;

         if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
             return;
         }


         if( $this->object->parent_id == 0) {
             $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
         }
    }


    /**
     * get_content_html function.
     *
     * @since 0.1
     * @return string
     */
    public function get_content_html() {
        ob_start();
        woocommerce_get_template( $this->template_html, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading()
        ) );
        return ob_get_clean();
    }


    /**
     * get_content_plain function.
     *
     * @since 0.1
     * @return string
     */
    public function get_content_plain() {
        ob_start();
        woocommerce_get_template( $this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading()
        ) );
        return ob_get_clean();
    }

    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'    => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable this email notification',
                'default' => 'yes'
            ),
            'recipient'  => array(
                'title'       => 'Recipient(s)',
                'type'        => 'text',
                'description' => sprintf( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
                'placeholder' => '',
                'default'     => ''
            ),
            'subject'    => array(
                'title'       => 'Subject',
                'type'        => 'text',
                'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
                'placeholder' => '',
                'default'     => ''
            ),
            'heading'    => array(
                'title'       => 'Email Heading',
                'type'        => 'text',
                'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
                'placeholder' => '',
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => 'Email type',
                'type'        => 'select',
                'description' => 'Choose which format of email to send.',
                'default'     => 'html',
                'class'       => 'email_type',
                'options'     => array(
                    'plain'     => 'Plain text',
                    'html'      => 'HTML', 'woocommerce',
                    'multipart' => 'Multipart', 'woocommerce',
                )
            )
        );
    }

}
