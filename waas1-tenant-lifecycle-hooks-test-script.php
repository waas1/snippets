<?php


//update the email to receive hooks notifications.
define( 'WAAS1_TEST_EMAIL_ADDRESS', '' );




//post version
add_action( 'waas1_tenant_lifecycle_post_wp_ver_change', function( $assoc_args ){
	
	$hookName = 'POST WP VERSION CHANGED';
	
	$body = $hookName.' hook dispached';
	$body .= '<br /><br />';
	$body .= print_r( $assoc_args, true );
	

	wp_mail( WAAS1_TEST_EMAIL_ADDRESS, $hookName, $body );

});



add_action( 'waas1_tenant_lifecycle_post_php_ver_change', function( $assoc_args ){
	
	$hookName = 'POST PHP VERSION CHANGED';
	
	$body = $hookName.' hook dispached';
	$body .= '<br /><br />';
	$body .= print_r( $assoc_args, true );
	

	wp_mail( WAAS1_TEST_EMAIL_ADDRESS, $hookName, $body );
	
});




add_action( 'waas1_tenant_lifecycle_post_restrictions_group_change', function( $assoc_args ){
	
	$hookName = 'POST RESTRICTIONS GROUP CHANGED';
	
	$body = $hookName.' hook dispached';
	$body .= '<br /><br />';
	$body .= print_r( $assoc_args, true );
	

	wp_mail( WAAS1_TEST_EMAIL_ADDRESS, $hookName, $body );
	
});



add_action( 'waas1_tenant_lifecycle_post_domain_change', function( $assoc_args ){
	
	$hookName = 'POST DOMAIN CHANGED';
	
	$body = $hookName.' hook dispached';
	$body .= '<br /><br />';
	$body .= print_r( $assoc_args, true );
	

	wp_mail( WAAS1_TEST_EMAIL_ADDRESS, $hookName, $body );
	
});





add_action( 'waas1_tenant_post_created', function( $assoc_args ){
	
	$hookName = 'New Tenant Created';
	
	$body = $hookName.' hook dispached';
	$body .= '<br /><br />';
	$body .= print_r( $assoc_args, true );
	

	wp_mail( WAAS1_TEST_EMAIL_ADDRESS, $hookName, $body );
	
});




add_action( 'waas1_tenant_pre_delete', function( $assoc_args ){
	
	$hookName = 'Tenant Pre Delete';
	
	$body = $hookName.' hook dispached';
	$body .= '<br /><br />';
	$body .= print_r( $assoc_args, true );
	

	wp_mail( WAAS1_TEST_EMAIL_ADDRESS, $hookName, $body );
	
});



add_action( 'waas1_tenant_lifecycle_post_activate', function( $assoc_args ){
	
	$hookName = 'Tenant POST activated';
	
	$body = $hookName.' hook dispached';
	$body .= '<br /><br />';
	$body .= print_r( $assoc_args, true );
	

	wp_mail( WAAS1_TEST_EMAIL_ADDRESS, $hookName, $body );
	
});



add_action( 'waas1_tenant_lifecycle_post_deactivate', function( $assoc_args ){
	
	$hookName = 'Tenant POST deactivated';
	
	$body = $hookName.' hook dispached';
	$body .= '<br /><br />';
	$body .= print_r( $assoc_args, true );
	

	wp_mail( WAAS1_TEST_EMAIL_ADDRESS, $hookName, $body );
	
});

?>