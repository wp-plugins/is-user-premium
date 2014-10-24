<?php
/**
 * Plugin Name: Is user premium?
 * Description: This simple plugin implements PayPal subscriptions in Wordpress, recording them as custom user meta. A shortcode can be used to display the subscription status to the users. 
 * Version: 0.1
 * Author: Aria s.r.l.
 * Author URI: https://github.com/Ariacorporate
 * License: GPL2
 */
defined( 'ABSPATH' ) or die( "No script kiddies please!" );
require "templates.php";

function iup_filling_generator($array = array(), $arg){
	if ( is_array( $arg ) ){
		$filling = $array + $arg;
		return $filling;
	}
}

function iup_button_select( $btn_string){
	// Returns a PayPal button, based on type and language.
	//en_US/i/btn/btn_cart_LG.gif
	$url = 'https://www.paypalobjects.com/';
	$subscribe = 'btn/btn_subscribeCC_LG.gif';
	//
	$subscribe = array(
		'it' => 'it_IT/IT/i/',
		'fr' => 'fr_FR/FR/i/',
		'de' => 'de_DE/DE/i',
		'en' => 'en_US/i/'
	);
	$buttons = array(
		'subscribe' => $subscribe
	);
	$options = split( '-', $btn_string );
	$btn_type = $options[0];
	$lang = $options[1];
	return $buttons[ $btn_type ][ $lang ];
}

function iup_currency ( $var ){
	if ( $var == 'eur' ){
		$currency = 'EUR';
		$symbol = '&euro;';
	}
	else if ( $var == 'usd' ){
		$currency = 'USD';
		$symbol = '&#36;';
	}
	else if ( $var == 'yen' ){
		$currency = 'JPY';
		$symbol = '&yen;';
	}
	else if ( $var == 'aud' ) {
		$currency = 'AUD';
		$symbol = '&#36;';
	}
	else if ( $var == 'cad' ) {
		$currency = 'CAD';
		$symbol = '&#36;';
	}
	
	return array(
		"currency" => $currency,
		"symbol" => $symbol
	);
} 

/* */ 
function iup_install() {
	// Init function, run on activation/installation.
	// Stores the name of the meta used by the plugin
	add_option( 'iup_meta', 'iup_premium' ); 
	$users = get_users();
	foreach ( $users as $selected_user ){
		add_user_meta( $selected_user->ID, 'iup_premium', 0 ); 
	}    
}

function iup_settings() {
	$iup_meta = get_option( 'iup_meta' );
	$filling = $iup_meta;
	iup_templates_settings_page( $filling );
}

function iup_admin_menu(){
	add_options_page( 'IUP Settings', 'IUP settings', 'manage_options', 'iup-settings-page', 'iup_settings' );
	
}

function iup_users_view($columns){
	// Adds column(s) to the users table header.
	$option = get_option( 'iup_meta' );
	return array_merge( $columns, array( $option => __( 'Premium until' ) ) );
}

function iup_show_premium_status( $value, $column_name, $user_id ) {
	// Fetches data for the new column.
	$option = get_option( 'iup_meta' );
	$iup_status = get_user_meta( $user_id, $option, true );
	if ( $option  == $column_name ){
		if ( $iup_status != 0 ){
			$date_format = get_option( 'date_format' );
			return date_i18n( $date_format, $iup_status );
		}
		else {
			return $iup_status;
		}
	}
	else {
		return $value;
	}
}

function iup_display_upgrade_shortcode( $args ){
	// Displays the payment button
	$a = shortcode_atts(
			array(
			'business' => '',
			'value' => '0.00',
			'name' => '',
			'duration' => 31556926,
			'button' => 'subscribe-en',
			'currency' => 'eur',
			'login-msg' => '<p>You must be registered!</p>',
			'subscribed-msg' => '<p>You are subscribed until %until%</p>',
			'was-subscribed-msg' => '<p class="was-subscribed">You were subscribed until %until%</p>'
			), $args
	);
	if ( is_user_logged_in() ){
		$current_user = wp_get_current_user();
		$iup_meta = get_option( 'iup_meta' );
		$meta = get_user_meta( $current_user->ID, $iup_meta, true );
		if ( $meta == 0 || $meta < time() ){
			// The user is registered, but not subscribed.
			$currency = iup_currency( $a['currency'] );
			$a['button'] = iup_button_select( $a['button'] );
			$filling = iup_filling_generator( $a, $currency );
			$filling = iup_filling_generator( $filling, array(
				'url' => get_site_url(), 
				'user_id' => $current_user->ID
				)
			);
			$filling = iup_templates_subscribe_form( $filling );
			if ( $meta != 0 ){
				// The user was subscribed.
				$d = date_i18n( get_option('date_format'), $meta );
				$string = str_replace( "%until%", $d, $a['was-subscribed-msg'] );
				$html = iup_templates_info_container( $string.$filling, 'iup-subscribe' );
			}
			else {
				// The user was never subscribed.
				$html = iup_templates_info_container( $filling, 'iup-subscribe' );
			}
		}
		else {
			// The user is subscribed.
			$d = date_i18n( get_option( 'date_format' ), $meta );
			$string = str_replace( "%until%", $d, $a['subscribed-msg'] );
			$html = iup_templates_info_container( $string, 'iup-subscribed' );
		}
	}
	else {
		// The user is not logged in.
		$html = iup_templates_info_container( $a['login-msg'], 'iup-login' );
	}
	return $html;
}

/* Ipn related functions - these functions are here to make paypal's ipn work. */
function iup_query_vars( $vars ) {
	// Adds iup_ipn to the accepted queries.
	$new_vars = array( 'iup_ipn' );
	$vars = $new_vars + $vars;
    return $vars;
}

function iup_parse_request( $wp ){
	 // Only process requests with "iup_ipn=paypal".
    if (array_key_exists( 'iup_ipn', $wp->query_vars ) && $wp->query_vars['iup_ipn'] == 'paypal' ) {
        iup_ipn_process( $wp );
    }
}

function iup_rewrite_rules( $wp_rewrite ) {
	// Adds a rewrite rule.
	$new_rules = array( 'iup_ipn/paypal' => 'index.php?iup_ipn=paypal' );
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

function iup_ipn_process( $wp ){
	// Finally, process the ipn.
	require "ipn-handler.php";
	global $wpdb;
	class IPN_ext extends IPN_Handler
	{
		public function process( array $post_data )
		{
			$data = parent::process( $post_data );
			return $data;
		}
	}
	
	$handler = new IPN_ext();
	$ipn_check = $handler->process( $_POST );
	if ( $ipn_check !== false ){
		//file_put_contents('ipn.txt', json_encode($ipn_check).PHP_EOL); // debug line
		iup_register_payment( $ipn_check['custom'] );
	}
}

function iup_register_payment( $custom ){
	// Updates the user meta when a payment is done.
	$splitted = split( '&', $custom );
	$user_id = $splitted[0];
	$duration = intval($splitted[1]);
	$iup_meta = get_option( 'iup_meta' );
	$meta = get_user_meta( $user_id, $iup_meta, true );
	$until = intval( $meta );
	if ( $until == 0 || $until < time() ){
		$t = time() + $duration; 
	}
	else {
		$t = intval( $meta ) + $duration;
	}
	update_user_meta( $user_id, $iup_meta, $t, $meta );
}
/* Ipn block end */

function iup_update_registration_meta( $user_id ){
	// Adds the meta to an user when it is created.
	$iup_meta = get_option( 'iup_meta' );
	add_user_meta( $user_id, $iup_meta, 0 ); 
}

function iup_edit_user_profile( $user ){
	$iup_meta = get_option( 'iup_meta' );
	$meta = get_user_meta( $user->ID, $iup_meta, true );
	$day = date( 'd', $meta );
	$month = date( 'm', $meta );
	$year = date( 'Y', $meta );
	
	// Wordpress default admin uses tables to display forms.
	$html = '
		<table class="form-table">
			<tr>
				<th><label for="iup-premium">Premium until</label></th>
				<td>
					<input type="text" id="iup-premium" name="iup_premium_d" value="'.$day.'">
					<input type="text" name="iup_premium_m" value="'.$month.'">
					<input type="text" name="iup_premium_y" value="'.$year.'">
					<span class="description">Enter the date as dd mm yyyy values. Default value is 01 01 1970.</span>
				</td>
			</tr>
		</table>
	';
	echo $html;
}

function iup_update_profile( $user_id ){
	// Updates the user profile from admin.
	if ( current_user_can( 'edit_user', $user_id ) ){
		$iup_meta = get_option( 'iup_meta' );
		$meta = get_user_meta( $user_id, $iup_meta, true );
		$day = $_POST['iup_premium_d'];
		$month = $_POST['iup_premium_m'];
		$year = $_POST['iup_premium_y'];
		$t = strtotime( $day.'-'.$month.'-'.$year );
		update_user_meta( $user_id, $iup_meta, $t, $meta );
	}
}

/* Registers all the functions */
register_activation_hook(  __FILE__, 'iup_install' );

// Registers the menu.
add_action( 'admin_menu', 'iup_admin_menu' );
// Users table action.
add_action( 'manage_users_columns', 'iup_users_view' );
add_action( 'manage_users_custom_column',  'iup_show_premium_status', 10, 3 );
// Ipn related
add_action( 'parse_request', 'iup_parse_request' );
add_action( 'generate_rewrite_rules', 'iup_rewrite_rules' );
//
add_action( 'user_register', 'iup_update_registration_meta', 10, 1 );
// User profile editing
add_action( 'show_user_profile', 'iup_edit_user_profile' );
add_action( 'edit_user_profile', 'iup_edit_user_profile' );
add_action( 'personal_options_update', 'iup_update_profile' );
add_action( 'edit_user_profile_update', 'iup_update_profile' );
// Shortcodes
add_shortcode( 'display_upgrade', 'iup_display_upgrade_shortcode' );
//Filters
add_filter( 'query_vars', 'iup_query_vars' );
