<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//--------------------
//Note: This snippet relies on Waas1Api class. You also need to add Waas1Api class snippet and properly configure it.
//--------------------

new waas1_woo_subscription_register_new_domain();


class waas1_woo_subscription_register_new_domain{
	
	
	private $_conditionCatSlugs = array( 'domain' ); //add all product categories where you want to run this snippet.
	private $_customDomain = 'Selected Domain'; //set this to false if you are not using custom subdomain
				
	
	
	function __construct(){
		//when subscription status changes
		add_action( 'woocommerce_subscription_status_updated', array($this, 'woocommerce_subscription_status_updated'), 11, 3 );
		
		if( $this->_customDomain ){
			$this->_customDomain = strtolower( $this->_customDomain );
		}
		
	}
	
	

	
	public function woocommerce_subscription_status_updated( $subscription, $new_status, $old_status ){//woocommerce_subscription_status_updated start
	
		$orderId 	= $this->getOrderId( $subscription );
		$order 		= wc_get_order( $orderId );
		$items 		= $order->get_items();
		
		foreach ( $items as $item ) {
			$productId = $item->get_product_id();
			if( !$this->checkAllConditions($productId) ){
				return false; // do not process this hook
			}
		}//endforeach
		
		
		if( $new_status == 'active' ){
			
			$clientEmail = $this->getClientEmailAddress( $subscription );
			$customDomain = $this->getAttribute( $subscription, $this->_customDomain );

			$waas1_api	= new Waas1Api();
			$apiResult	= $waas1_api->network_domain_register( $customDomain, $clientEmail );
		
		}


		
	
	}//woocommerce_subscription_status_updated end
	
	
	
	
	
	

	
	private function getClientEmailAddress( $subscription ){ //getClientEmailAddress start
		
		$subscription_data 	= $subscription->get_data();
		$customerId 		= $subscription_data['customer_id'];
		$customerData 		= get_userdata( $customerId );
		return $customerData->user_email;
		
	} //getClientEmailAddress end
	
	
	

	private function getAttribute( $subscription, $attributeKey ){ //getAttribute
		
		$subscription_products = $subscription->get_items();
		foreach( $subscription_products as $product ){
			
			$productData = $product->get_meta_data();
			foreach( $productData as $meta ){
				
				if( strtolower($meta->key) == $attributeKey ){
					$requiredField = strtolower($meta->value);
					break;
				}else{
					$requiredField = false;
				}
			}
			
		}
		
		return $requiredField;
		
	} //getAttribute
	
	
	
	
	private function getOrderId( $subscription ){ //getOrderId start
		$subscription_data = $subscription->get_data();
		return $subscription_data['parent_id'];
	} //getOrderId end
	
	
	
	
	
	
	private function checkAllConditions( $productId=false ){ //checkAllConditions
		
		if( $productId ){
			$allCats = get_the_terms( $productId, 'product_cat' );
		}else{
			$allCats = get_the_terms( get_the_ID(), 'product_cat' );
		}
		
		if( empty($allCats) ){
			return false;
		}
		
		foreach( $allCats as $cat ){
			if( in_array( $cat->slug, $this->_conditionCatSlugs ) ){
				return true;
				break;
			}
		}
		return false;
	} //checkAllConditions

	
	
	
}

?>