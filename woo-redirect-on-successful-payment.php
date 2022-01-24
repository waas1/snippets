<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_thankyou', 'waas1_woocommerce_thankyou_redirect');
  
function waas1_woocommerce_thankyou_redirect( $orderId ){
	
	$thankYouPageSlug 			= 'thank-you';
	$renewalThankYouPageSlug 	= 'renewal-thank-you';
	
	$order 	= wc_get_order( $orderId );
	
	
	if ( !$order->has_status('failed') ) {
		
		$subscriptions = wcs_get_subscriptions_for_order( $orderId, array('order_type'=>'any') );

		foreach( $subscriptions as $subscription ){
			$parentOrderId = $subscription->data['parent_id'];
			break;
		}
		
		if( $orderId == $parentOrderId ){
			$url = get_home_url().'/'.$thankYouPageSlug.'/?unique-order-id='.$orderId;
		}else{
			$url = get_home_url().'/'.$renewalThankYouPageSlug.'/?parent-order-id='.$parentOrderId;
		}
		
		wp_safe_redirect( $url );
		//make sure to exit from here
		exit;
		
	}
	 
}

?>