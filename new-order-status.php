<?php

function register_shipment_order_status() {
    register_post_status( 'wc-shipped', array(
        'label'                     => 'Shipped',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Shipped (%s)', 'Shipped (%s)' )
    ) );


    register_post_status( 'wc-paidprocess', array(
        'label'                     => 'Processing Paid',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Processing Paid (%s)', 'Processing Paid (%s)' )
    ) );
}
add_action( 'init', 'register_shipment_order_status' );


add_action('admin_head', 'styling_admin_order_list' );
function styling_admin_order_list() {
    global $pagenow, $post;

    if( $pagenow != 'edit.php') return; // Exit
    if( get_post_type($post->ID) != 'shop_order' ) return; // Exit

    // HERE we set your custom status
    $order_status = 'shipped'; // <==== HERE
    ?>
    <style>
        .order-status.status-<?php echo sanitize_title( $order_status ); ?> {
            background: #96588a;
            color: #FFF;
        }
    </style>
    <?php
}


function add_shipment_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-shipped'] = 'Shipped';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_shipment_to_order_statuses' );


//add_filter( 'woocommerce_email_actions', 'sh_add_order_meta_box_actions' );


add_action( 'woocommerce_order_actions', 'sh_add_order_meta_box_actions'  );
/* Add Order action to Order action meta box */

function sh_add_order_meta_box_actions($actions)
{
    $actions['wc_shipped'] = __( 'Shipped', 'sh');

    return $actions;
}

function sh_add_order_email_actions($actions)
{
    $actions[] = __( 'Shipped', 'sh');

    return $actions;
}


function sh_wc_shipped_order_meta_box_action( $order ) {
    // add the order note
    // translators: Placeholders: %s is a user's display name
    $message = sprintf( __( 'Order status changed to shipped status .', 'my-textdomain' ), wp_get_current_user()->display_name );
    $order->add_order_note( $message );

    // add the flag
    //update_post_meta( $order->id, '_wc_order_marked_printed_for_packaging', 'yes' );
}
add_action( 'woocommerce_order_action_wc_processing_paid', 'sh_wc_processing_paid_order_meta_box_action' );


add_filter( 'bulk_actions-edit-shop_order', 'sh_bulk_actions_change_status_to_shipped', 20, 1 );
function sh_bulk_actions_change_status_to_shipped( $actions ) {
    $actions['mark_shipped'] = __( 'Change status to shipped', 'woocommerce' );
    return $actions;
}


// Make the action from selected orders
add_filter( 'handle_bulk_actions-edit-shop_order', 'sh_bulk_actions_change_status_to_shipped_action', 10, 3 );
function sh_bulk_actions_change_status_to_shipped_action( $redirect_to, $action, $post_ids ) {
    if ( $action !== 'mark_shipped' )
        return $redirect_to; // Exit



    $processed_ids = array();

    foreach ( $post_ids as $post_id ) {


        $order = wc_get_order( $post_id );
        $message = sprintf( __( 'Order status changed to shipped status .', 'my-textdomain' ), wp_get_current_user()->display_name );
        $order_data = $order->add_order_note( $message );
        $order->update_status( 'wc_shipped' );


       if($order->parent_id == 0) {
           //wp_die($order);
           $heading = 'Order shipped';
           $subject = 'Order shipped';
           // Get WooCommerce email objects
           $mailer = WC()->mailer()->get_emails();

           $mailer['WC_shipped_Order_Email']->heading = $heading;
           $mailer['WC_shipped_Order_Email']->settings['heading'] = $heading;
           $mailer['WC_shipped_Order_Email']->subject = $subject;
           $mailer['WC_shipped_Order_Email']->settings['subject'] = $subject;

           // Send the email with custom heading & subject
           $mailer['WC_shipped_Order_Email']->trigger( $post_id );
       }


        // Your code to be executed on each selected order

        $processed_ids[] = $post_id;
    }

    return $redirect_to ;
}


add_filter( 'woocommerce_reports_order_statuses',  'add_custom_order_statuses_to_reports' , 0 );
 function add_custom_order_statuses_to_reports( $order_statuses ) {
    // wp_die(var_dump($order_statuses));
    //
     return array_merge( $order_statuses,  array( 'shipped' ) );

}


//add_filter( 'wc_order_statuses', 'wc_renaming_order_status' );
function wc_renaming_order_status( $order_statuses ) {
    foreach ( $order_statuses as $key => $status ) {
        if ( 'wc-processing' === $key )
            $order_statuses['wc-processing'] = _x( 'processing paid', 'Order status', 'woocommerce' );
    }
    return $order_statuses;
}



function add_shipped_order_woocommerce_email( $email_classes ) {

    // include our custom email class
    include(  WP_PLUGIN_DIR . '/asyad-shipping-plugin/class-wc-shipped-order-email.php' );
    // add the email class to the list of email classes that WooCommerce loads
    $email_classes['WC_shipped_Order_Email'] = new WC_shipped_Order_Email();
    define( 'CUSTOM_WC_EMAIL_PATH', plugin_dir_path( __FILE__ ) );
    return $email_classes;

}
add_filter( 'woocommerce_email_classes', 'add_shipped_order_woocommerce_email' );


