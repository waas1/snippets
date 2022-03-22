<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//--------------------
//Note: We need to restrict users from changing their email addresses as we will list their sites using the registered email address.
//if you are using [waas1_list_all_sites] shortcode you might want to use this snippet.
//--------------------



//if the call is from "wp-cli" don't run the code below
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	return;
}



add_filter( 'wp_pre_insert_user_data', 'waas1_wp_pre_insert_user_data', 10, 4 );
function waas1_wp_pre_insert_user_data( $data, $update, $id, $userdata ){
	
	$currentLoggedInUser = wp_get_current_user();
	//allow superduper
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return $data;
	}
	
	//allow if data is not being updated
	if( !$update ){
		return $data;
	}
	
	
	//if user is not logged in return the data
	if( !$currentLoggedInUser->ID ){
		return $data;
	}
	
	$old_email = $currentLoggedInUser->data->user_email;
	$data['user_email'] = $old_email;
	
	return $data;
	
}




//also disable the email notification when users update their email address:

add_filter( 'send_email_change_email', 'waas1_send_email_change_email', 1, 3 );
function waas1_send_email_change_email( $send, $user, $userdata ){
	
	$currentLoggedInUser = wp_get_current_user();
	//allow superduper
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return $send;
	}
	
    return false;
}


?>