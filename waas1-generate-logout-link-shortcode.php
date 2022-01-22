<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'waas1-generate-logout-link', 'waas1_generate_logout_link' ); 

function waas1_generate_logout_link() { 
	return wp_logout_url( home_url() );
}
?>