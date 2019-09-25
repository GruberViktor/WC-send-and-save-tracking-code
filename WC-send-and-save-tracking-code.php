<?php

defined( 'ABSPATH' ) or die();


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
		$order_data = $order->get_data();

        $code = $_POST['code'];
		$order->update_meta_data( '_tracking_code', $code );
		$order->save();

        $subject = get_option('wc_trackingmail_emailsubject');
        $emailtext = get_option('wc_trackingmail_emailtext');


        $first_name = $order_data['billing']['first_name'];
        $last_name = $order_data['billing']['last_name'];
        $email = $order_data['billing']['email'];



        $message = str_replace(
        	array('[code]','[first_name]','[last_name]'),
        	array($code, $first_name, $last_name),
        	$emailtext);
		
        if(wp_mail($email, $subject, $message))
        {
            echo "<p style='margin-bottom: 10px;'>Email sent!</p>";
    } else {
        echo "<p style='margin-bottom: 10px;'>Error, email not sent.</p>";
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



/////////////////////////// Options Page /////////////////////////////////

class WC_trackingmail_options {
    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_trackingmail', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_trackingmail', __CLASS__ . '::update_settings' );
    }
    
    
    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['trackingmail'] = __( 'WC send tracking email', 'wc_send_and_save_tracking_code_tab' );
        return $settings_tabs;
    }
    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }
    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }
    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
public static function get_settings() {
        $settings = array(
            'section_title' => array(
                'name'     => __( 'WooCommerce send and save tracking code - Settings', 'wc-send-tracking-code' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_demo_section_title'
            ),
            'subject' => array(
                'name' => __( 'Email Subject', 'wc-send-tracking-code' ),
                'type' => 'textarea',
                'desc' => __( 'Set the subject of your email.', 'wc-send-tracking-code' ),
                'id'   => 'wc_trackingmail_emailsubject'
            ),
            'emailtext' => array(
                'name' => __( 'Email text', 'wc-send-tracking-code' ),
                'type' => 'textarea',
                'desc' => __( 'Here you can modify the text of your email.', 'wc-send-tracking-code' ),
                'id'   => 'wc_trackingmail_emailtext'
            ),
            'section_end' => array(
                 'type' => 'sectionend',
                 'id' => 'wc_settings_tab_demo_section_end'
            )
        );
        return $settings;
    }
}
WC_trackingmail_options::init();


?>