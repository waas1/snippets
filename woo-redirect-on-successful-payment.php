<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_thankyou', 'waas1_woocommerce_thankyou_redirect');
  
function waas1_woocommerce_thankyou_redirect( $orderId ){
	
	$conditionCatSlugs 			= array( 'templates' ); //add all product categories where you want to run this snippet.
	$currentProductCat			= ''; //keep it empty
	$thankYouPageSlug 			= 'thank-you';
	$renewalThankYouPageSlug 	= 'renewal-thank-you';
	
	
	
	$order 	= wc_get_order( $orderId );
	

	if ( !$order->has_status('failed') ) {
		
		
		$items = $order->get_items();
		$processCustomRedirect = false;
		
		foreach ( $items as $item ) {
			
			$productId = $item->get_product_id();
			$allCats = get_the_terms( $productId, 'product_cat' );
			
			if( empty($allCats) ){
				return false;
			}
			
			foreach( $allCats as $cat ){
				foreach( $conditionCatSlugs as $conditionCatSlug ){
					if( $cat->slug == $conditionCatSlug ){
						$processCustomRedirect = true;
						$currentProductCat = $cat->slug;
						break;
					}
				}
			}
			
		}//endforeach

		
		
		if( !$processCustomRedirect ){
			return false;
		}
		
		
		$subscriptions = wcs_get_subscriptions_for_order( $orderId, array('order_type'=>'any') );

		foreach( $subscriptions as $subscription ){
			$parentOrderId = $subscription->data['parent_id'];
			break;
		}
		
		if( $orderId == $parentOrderId ){
			$url = get_home_url().'/'.$thankYouPageSlug.'-'.$currentProductCat.'/?unique-order-id='.$orderId;
		}else{
			$url = get_home_url().'/'.$renewalThankYouPageSlug.'-'.$currentProductCat.'/?parent-order-id='.$orderId;
		}
		
		wp_safe_redirect( $url );
		//make sure to exit from here
		exit;
		
	}
	 
}

?>