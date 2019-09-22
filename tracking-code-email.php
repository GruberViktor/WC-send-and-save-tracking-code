<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/*
Plugin Name: Send Tracking Code from order preview
Description: Send your customers an email with a tracking code.
Version:     0.0.1
Author:      Viktor Gruber
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

*/


function tracking_email_activate() {
	// nothing to do
}

function tracking_email_deactivate() {
	// Nothing to do
}

	

register_activation_hook( __FILE__, 'tracking_email_activate' );
register_deactivation_hook( __FILE__, 'tracking_email_deactivate' );


//
// Tracking Link Schicken

//Form
add_action( 'woocommerce_admin_order_preview_end', 'tracking_code_input', 20);
function tracking_code_input(){
	echo "<center>";
	echo "<# if ( data.tracking_code ) { #><p>Tracking-Code: {{data.tracking_code}} <# } #>";
	echo "<form name='trackingmail' id='trackingmailform' method='post' action='' autocomplete='off' >";
	echo " <input name='code' id='code' type='text' size='20' style='margin:auto; display:inline-block; text-align:center; margin-right:10px;' autofocus/>";
	echo "<input type='submit' value='send email' id='submittracking' />";
	echo "<input type='hidden' value={{data.data.billing.email}}  id='customeremail' />";
	echo "<input type='hidden' value={{data.data.billing.first_name}}  id='fname' />";
	echo "<input type='hidden' value={{data.data.id}}  id='order_id' />";
	echo "</form>";
	echo "</center>";
}

//Ajax
add_action( 'wp_ajax_trackingmail', 'trackingmail');
function trackingmail($order){

	if(empty($_POST['code'])){
		
		echo "Bitte Code eingeben";
	} else {

		$order_id = $_POST['order_id'];
		$order = wc_get_order( $order_id );
		$code = $_POST['code'];
		echo $code;
		$order->update_meta_data( '_tracking_code', $code );
		$order->save();


        $subject = "[fermentationculture.eu] Your tracking link";
        $message = "Hi " . $_POST['fname'] . "! 


You can track your order from fermentationculture.eu at this link: https://www.post.at/en/track_trace.php/details?pnum1=" . $_POST['code'] . "

Thanks for your order!


Kind regards,
Viktor and Christine";
		
        if(wp_mail($_POST['to'], $subject, $message))
        {
            echo "mail sent";
    } else {
        echo "mail not sent";
    }

	die();

}
}





// Jquery script
// 
function trackingmailscriptadmin( $hook ) {
    if ( 'edit.php' != $hook ) {
        return;
    }
    wp_enqueue_script( 'trackingmailscript', plugin_dir_url( __FILE__ ) . 'includes/trackingmailscript.js');
}
add_action( 'admin_enqueue_scripts', 'trackingmailscriptadmin' );



//Add custom order meta data to make it accessible in Order preview template
add_filter( 'woocommerce_admin_order_preview_get_order_details', 'admin_order_preview_add_tracking_code', 10, 2 );
function admin_order_preview_add_tracking_code( $data, $order ) {
    // Replace '_custom_meta_key' by the correct postmeta key
    if( $custom_value = $order->get_meta('_tracking_code') )
        $data['tracking_code'] = $custom_value; // <= Store the value in the data array.

    return $data;
}



?>