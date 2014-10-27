<?php 
if (defined( 'WP_UNINSTALL_PLUGIN' ) ){ 	
	$users = get_users();
	$iup_meta = get_option( 'iup_meta' );
	foreach ($users as $selected_user){
		delete_user_meta( $selected_user->ID, 'iup_premium'); 
	}    
}
?>