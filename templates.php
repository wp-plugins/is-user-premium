<?php
/* 
* Templates for IUP.
* $filling is the array that contains the arguments used
* to fill the templates
*/
function iup_templates_subscribe_form( $filling ){
	$name = esc_attr( $filling['name'] );
	$value = esc_attr( $filling['value'] );
	$business = esc_attr( $filling['business'] );
	$url = esc_attr( $filling['url'] );
	$button = esc_attr( $filling['button'] );
	$html = '
		<span>'.$filling['name'].'</span>
		<span>'.$filling['value'].' '.$filling['symbol'].'</span>
		<form method="post" action="https://www.paypal.com/cgi-bin/webscr">
				<input type="hidden" name="cmd" value="_cart">
				<input type="hidden" value="'.$name.'" name="item_name_1">
				<input type="hidden" value="'.$filling['value'].'" name="amount_1">
				<input type="hidden" value="1" name="quantity_1">
				<input type="hidden" value="'.$filling['currency'].'" name="currency_code">
				<input type="hidden" value="1" name="upload">
				<input type="hidden" value="utf-8" name="charset">
				<input type="hidden" value="2" name="rm">  
				<input type="hidden" value="'.$business.'" name="business">
				<input type="hidden" value="'.$url.'/iup_ipn/paypal" name="notify_url">
				<input type="hidden" value="'.$filling['user_id'].'&'.$filling['duration'].'" name="custom">
				<input type="image" src="'.$button.'"
				alt="Make payments with payPal - it\'s fast, free and secure!">
		</form>
	';
	return $html;
}

function iup_templates_info_container( $filling, $classes = '' ){
	$html = '
		<div class="iup-info '.$classes.'">
			'.$filling.'
		</div>';
	return $html;
}

function iup_templates_settings_page ( $filling ){
	$html = '
		<div class="wrap">
			<h2>IUP settings page</h2>
			<p>Welcome to the Is user premium? settings page. IUP is a rather simple plugin, that adds a shortcode
			that displays a PayPal button and a system to register when a user subscription expires. </p>
			<p> All the configuration is done through the shortcode parameters. </p>
			<h3>Shortcodes</h3>
			<p>[display_upgrade] shows the subscribe button. In order for it to work, you
			will need to add the item name, price and the merchant email address, eg: <br>
			<b>[display_upgrade business="test@test.it" value="100.00" name="Standard subscription"]</b></p>
			<p>Some more examples:</p>
			<b>[display_upgrade business="test@test.it" value="100" name="Standard subscription" currency="usd"
			login-msg="Please login!"]</b><br>
			<b>[display_upgrade business="test@test.it" value="100.00" name="Standard subscription" duration="86400" 
			subscribed-msg="&lt;div&gt;&lt;h3&gt;Subscribed!&lt;/h3&gt;&lt;p&gt;Thank you very kindly!&lt;/p&gt;&lt;/div&gt;"]</b><br>
			<h3>Styling</h3>
			<p>The plugin does not add any style, therefore it will use your current styling. </p>
			<table>
				<tr><th>Container</th><th>Usage</th></tr>
				<tr><td>div.iup-info</td><td>The main container</td></tr>
				<tr><td>.iup-login</td><td>This class is added to the main container if the user is anon</td></tr>
				<tr><td>.iup-subscribe</td><td>This class is added to the main container if the user is logged in.</td></tr>
				<tr><td>.iup-subscribed</td><td>This class is added to the main container if the user is subscribed.</td></tr>
				<tr><td>p.was-subscribed</td><td>This is the default container for was-subscribed-msg (so it is overwritten if was-subscribed-msg is).</td></tr>
			</table>
			<h3>Current plugin settings</h3>
			<table>
				<tr><th>Default meta</th><td>'.$filling.'</td></tr>
				<tr><th>Subscription duration</th><td>31556926 seconds (1 year)</td></tr>
				<tr><th>Button image</th><td> subscribe-en (<a href="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif">View</a>) </td></tr>
				<tr><th>Default merchant</th><td> - </td></tr>
				<tr><th>Default currency</th><td> EUR </td></tr>
			</table>
			<h3>Shortcode parameters</h3>
			<p>These parameters can be used with [display_upgrade], eg. [display_upgrade parameter="value"]</p>
			<table>
				<tr><th>Parameter</th><th>Description</th><th>Placeholders</th></tr>
				<tr><td>business</td><td>The merchant account that will receive the payment</td><td></td></tr>
				<tr><td>value</td><td>The item price</td><td></td></tr>
				<tr><td>name</td><td>The item name (will be displayed to the user)</td><td></td></tr>
				<tr><td>duration</td><td>The subscription duration in seconds.</td><td></td></tr>
				<tr><td>button</td><td>The paypal button image es: button="subscribe-en"(supports en, it, de, fr)</td><td></td></tr>
				<tr><td>currency</td><td>Currency, supports: "eur", "yen", "usd", "cad", "aud"</td><td></td></tr>
				<tr><td>login-msg</td><td>The message displayed to anons</td><td></td></tr>
				<tr><td>subscribed-msg</td><td>The message displayed to subscribed users</td><td>%until%</td></tr>
				<tr><td>was-subscribed-msg</td><td>The message displayed to users with an expired subscription </td><td>%until%</td></tr>
			</table>
			<h3>Placeholders</h3>
			<p>Placeholders are allowed on certain parameters. They will be replaced with a specific variable.</p>
			<table>
				<tr><th>Placeholder</th><th>Variable</th></tr>
				<tr><td>%until%</td><td>The date until which the user is or was subscribed.</td></tr>
			</table>
		</div>
		';
		echo $html;
}


?>