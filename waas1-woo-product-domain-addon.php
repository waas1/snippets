<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//--------------------
//Note: This snippet relies on Waas1Api class. You also need to add Waas1Api class snippet and properly configure it.
//--------------------




new waas1_woo_product_domain_addon_class();

class waas1_woo_product_domain_addon_class{
	
	
	
	
	private $_conditionCatSlugs = array( 'domain' ); //add all product categories where you want to run this snippet.
	private $_buttonSearchNewDomainText = 'Search';
	private $_addToCartButtonText = 'Buy';

	
	function __construct(){
	
		//skip when the subscription is getting switched
		if( isset($_GET['switch-subscription']) ){
			return false;
		}
		
		//skip when the subscription is renewal manually
		if( isset($_GET['subscription_renewal']) ){
			return false;
		}

		
		// 1. Show custom input field above Add to Cart
		add_action( 'woocommerce_before_add_to_cart_button', array($this, 'woocommerce_before_add_to_cart_button'), 9 );
		add_action( 'woocommerce_after_add_to_cart_button', array($this, 'woocommerce_after_add_to_cart_button'), 9 );
		// 2. Throw error if custom input field empty
		add_filter( 'woocommerce_add_to_cart_validation', array($this, 'woocommerce_add_to_cart_validation'), 10, 3 );
		// 3. Save custom input field value into cart item data
		add_filter( 'woocommerce_add_cart_item_data', array($this, 'woocommerce_add_cart_item_data'), 10, 2 );
		// 4. override price
		add_action( 'woocommerce_before_calculate_totals', array($this, 'woocommerce_before_calculate_totals'), 10, 1 );
		// 5. Display custom input field value @ Cart
		add_filter( 'woocommerce_get_item_data', array($this, 'woocommerce_get_item_data'), 10, 2 );
		// 6. Save custom input field value into order item meta
		add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'woocommerce_checkout_create_order_line_item'), 10, 4 );
		// 7. Display custom input field value into order table
		add_filter( 'woocommerce_order_item_product', array($this, 'woocommerce_order_item_product'), 10, 2 );
		// 8. Display custom input field value into order emails
		add_filter( 'woocommerce_email_order_meta_fields', array($this, 'woocommerce_email_order_meta_fields') );
		// 9. To change add to cart buton text on single product page
		add_filter( 'woocommerce_product_single_add_to_cart_text', array($this, 'woocommerce_product_single_add_to_cart_text') ); 
		
		
		//add css and js
		add_action( 'wp_footer', array($this, 'add_js_css'), 1800 );
		
	}
	

	
	
	
	
	//1
	public function woocommerce_before_add_to_cart_button(){
		
		if( !$this->checkAllConditions() ){
			return false;
		}
		
		
		if( isset($_POST['domain_text_addon']) && sanitize_text_field($_POST['domain_text_addon']) ){
			$value = $_POST['domain_text_addon'];
		}else{
			$value = '';
		}

		$html = '<div class="waas1-woo-domain-wrapper">';
		
			$html .= '<label for="inputSearchNewDomain">Domain <small>*</small></label>';

			$html .= '<input id="inputSearchNewDomain" name="search_new_domain" value="" />';
			$html .= '<a id="btnSearchNewDomain" class="btn btn-success" href="#">'.$this->_buttonSearchNewDomainText.'</a>';
			$html .= '<p>Enter the domain you want to buy without "www" or "http". e.g mydomain.com </p>';
			
		$html .= '</div>';
		
		
		$html .= '<div class="waas1-woo-domain-buy-now-wrapper">';
			$html .= '<input type="hidden" class="domain_text_addon" name="domain_text_addon" value="' . $value . '">';
			$html .= '<input type="hidden" name="domain_text_addon_price" value="">';
			
			
			$html .= '<h6>Congratulations your domain is available:</h6>';
			$html .= '<div class="new_domain_details">';
				$html .= '<div class="newDomainName">test.com</div>';
				$html .= '<div class="newDomainprice">$250</div>';
		
		echo $html;
		
	}
	public function woocommerce_after_add_to_cart_button(){
		if( !$this->checkAllConditions() ){
			return false;
		}
		$html = '</div></div>';
		echo $html;
	}
	
	
	
	
	//2
	public function woocommerce_add_to_cart_validation( $passed, $productId, $qty ){
		
		
		if( !$this->checkAllConditions( $productId ) ){
			return $passed;
		}


		if( isset( $_POST['domain_text_addon'] ) && sanitize_text_field( $_POST['domain_text_addon'] ) == '' ) {
			wc_add_notice( 'Domain is required!', 'error' );
			return false;
		}
		
		$domain = strtolower( sanitize_text_field($_POST['domain_text_addon']) );
		
		
		//validate the subdomain string
		$validDomain = $this->is_valid_domain_name( $domain );
		if( !$validDomain ){
			wc_add_notice( 'Invalid domain. Please make sure that domain does not contain any special characters. e.g "http://", "www" ', 'error' );
			return false;
		}
		
		if( strlen($domain) < 3 ){
			wc_add_notice( 'Domain must be at least 3 characters long.', 'error' );
			return false;
		}
		
		if( strlen($domain) > 60 ){
			wc_add_notice( 'Domain cannot not be more than 16 characters long.', 'error' );
			return false;
		}
		
		$detectDots = explode( '.', $domain );
		
		if( count($detectDots) == 1 ){
			wc_add_notice( 'Please provide a domain extension. e.g ".com", ".net"', 'error' );
			return false;
		}
		
		if( count($detectDots) > 2 ){
			wc_add_notice( 'Please search for a domain without "www" or subdomain. e.g "mydomain.com"', 'error' );
			return false;
		}
		
		//now check the network using API if the domain is available or not.
		$waas1_api = new Waas1Api();
		$searchDomain = $waas1_api->network_domain_search( $domain );
		
		
		if( !$searchDomain['status'] ){ //if get the status to true it means we already have a website with this subdomain.
			wc_add_notice( $searchDomain['errorMsg'], 'error' );
			return false;
		}

		
		$totalDomainPrice = ( $searchDomain['pricing']['regular_price'] + $searchDomain['pricing']['additional_cost'] ); //always round the price up
		
		$product = wc_get_product( $productId );
		$surchargePrice = $product->get_price();
		$_POST['domain_text_addon_price'] = ceil( $totalDomainPrice + $surchargePrice );
		
		return $passed;
	}
	
	
	//3
	public function woocommerce_add_cart_item_data( $cart_item, $productId ){

		if( isset( $_POST['domain_text_addon'] ) ) {
			$cart_item['domain_text_addon'] = sanitize_text_field( $_POST['domain_text_addon'] );
			$cart_item['domain_text_addon_price'] = sanitize_text_field( $_POST['domain_text_addon_price'] );
		}
		
		return $cart_item;
	}
	
	
	
	
	//4
	function woocommerce_before_calculate_totals( $cart_obj ) {
		foreach( $cart_obj->get_cart() as $key=>$value ) {
			if( isset( $value['domain_text_addon_price'] ) ) {
				$value['data']->set_price( $value['domain_text_addon_price']  );
			}
		}
	}
	
	
	
	
	//5
	public function woocommerce_get_item_data( $data, $cart_item ) {
		if ( isset( $cart_item['domain_text_addon'] ) ){
			$data[] = array(
				'name' => 'Selected domain',
				'value' => sanitize_text_field( $cart_item['domain_text_addon'] )
			);
		}
		return $data;
	}
	
	//6
	public function woocommerce_checkout_create_order_line_item( $item, $cart_item_key, $values, $order  ){
		if ( !empty( $values['domain_text_addon'] ) ) {
			$item->update_meta_data( 'Selected Domain', $values['domain_text_addon'], true );
		}
	}
	
	//7
	public function woocommerce_order_item_product( $cart_item, $order_item ){
		if( isset( $order_item['domain_text_addon'] ) ){
			$cart_item['domain_text_addon'] = $order_item['domain_text_addon'];
		}
		return $cart_item;
	}
	
	//8
	public function woocommerce_email_order_meta_fields(){
		if( isset( $order_item['domain_text_addon'] ) ){
			$fields['domain_text_addon'] = 'Selected Domain';
		}
		return $fields; 
	}
	
	
	//9
	function woocommerce_product_single_add_to_cart_text( $text ){
		if( !$this->checkAllConditions() ){
			return $text;
		}
		return $this->_addToCartButtonText;
	}
	
	
	
	function add_js_css(){
		
		if( !$this->checkAllConditions() ){
			return false;
		}

		$css ='<style>
		
		.waas1-woo-domain-wrapper{
			background: #e9e9e9;
			padding: 18px 10px 10px 10px
		}
		.waas1-woo-domain-wrapper label{
			display: block;
			margin-bottom: 10px;
			font-weight: bold;
		}
		.waas1-woo-domain-wrapper label small{
			color: red;
		}
		
		.waas1-woo-domain-wrapper input{
			border: 1px solid #ccc;
			padding: 5px;
			margin: 0;
			display: inline-block;
			width: 75%;
		}
		.waas1-woo-domain-wrapper input:focus-visible{
			outline: none;
		}
		
		.waas1-woo-domain-wrapper a#btnSearchNewDomain{
			display: inline-block;
			width: 25%;
			background: var(--e-global-color-accent);
			color: #fff;
			padding: 6px;
			text-align: center;
		}
		
		.waas1-woo-domain-wrapper p{
			font-size: 12px;
			margin: 0;
		}
		
		
		
		.waas1-woo-domain-buy-now-wrapper{
			padding-top: 15px;
			display: none;
		}
		
		.new_domain_details{
			display: flex;
			align-items: center;
			border-top: 1px solid #ccc;
			padding: 10px 0;
			border-bottom: 1px solid #ccc;
		}
		
		.new_domain_details .newDomainName{
			width: 65%;
		}
		
		.new_domain_details .newDomainprice{
			width: 15%;
			font-weight: bold;
		}
		
		.woocommerce div.product form.cart .new_domain_details div.quantity{
			display: none;
		}
		
		.woocommerce div.product.elementor .elementor-widget-woocommerce-product-add-to-cart .elementor-add-to-cart form.cart .new_domain_details button.single_add_to_cart_button{
			width: 20%;
		}
		
		</style>';
		echo $css;
	}
	
	
	

	
	
	private function checkAllConditions( $productId=false ){
		
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
	}
	
	
	

	private function is_valid_domain_name( $string ){
		return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $string) //valid chars check
				&& preg_match("/^.{1,253}$/", $string) //overall length check
				&& preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $string)   ); //length of each label
	}
	
	
}

?>