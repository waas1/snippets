<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//--------------------
//Note: This snippet relies on Waas1Api class. You also need to add Waas1Api class snippet and properly configure it.
//usage: [waas1_list_all_domains update_domain_url="https://yourdomain.com/contact-us/"]. default is allow site cloning
//use [waas1_list_all_domains update_domain_url="https://yourdomain.com/contact-us/" allow_site_clone="no"] 
//--------------------



class waas1_list_all_domains_shortcode_class{

	private $_attributeKey = 'select-plan'; //set this to false if you are not offering diferent plans
	private $_wpThumbShots = 'https://s5.wp.com/mshots/v1/';
	private $_loggedInUserEmail;
	
	
	
	function __construct(){
		add_shortcode('waas1_list_all_domains', array($this, 'render_shortcode') );
		add_action( 'init', array($this, 'register_query_vars') );
		if( $this->_attributeKey ){
			strtolower( $this->_attributeKey );
		}
	}
	
	
	
	function register_query_vars() { 
		global $wp;
		$wp->add_query_var( 'waas1_clone_site' ); 
		$wp->add_query_var( 'waas1-clone-node-id' ); 
		$wp->add_query_var( 'waas1-clone-site-id' ); 
	}

	
	
	
	
	function add_js_css(){
		$css ='<style>
		
		#waas1_list_all_domains_shortcode_wrapper{
		}
		
		#waas1_list_all_domains_shortcode_wrapper td{
			vertical-align: middle;
		}
		
		#waas1_list_all_domains_shortcode_wrapper .site-wrapper{
			display: flex;
			justify-content: space-between;
			background: #F8F8F8;
			margin-bottom: 15px;
			padding: 10px;
			align-items: center;
		}
		
		#waas1_list_all_domains_shortcode_wrapper .thumbnail{
			margin-right: 5px;
		}
		#waas1_list_all_domains_shortcode_wrapper .thumbnail img{
			max-width: 145px;
			display: block;
		}
		
		
		#waas1_list_all_domains_shortcode_wrapper .thumbnail a.manage-subscription{
			display: block;
			text-align: center;
			background: var(--e-global-color-primary);
			color: #fff;
			padding: 2px 0;
			font-size: 14px;
			border-radius: 0 0 6px 6px;
		}
		
		#waas1_list_all_domains_shortcode_wrapper span.selected-plan{
			text-transform: capitalize;
			display: block;
			margin-bottom: 5px;
		}
		
		
		#waas1_list_all_domains_shortcode_wrapper h4{
			margin: 0 0 5px 0;
			font-size: 18px;
		}
		
		
		#waas1_list_all_domains_shortcode_wrapper .col-stats,
		#waas1_list_all_domains_shortcode_wrapper .col-disk-usage{
			font-size: 12px; 
		}
		#waas1_list_all_domains_shortcode_wrapper .col-stats h4,
		#waas1_list_all_domains_shortcode_wrapper .col-disk-usage h4{
			font-size: 14px; 
		}
		
		#waas1_list_all_domains_shortcode_wrapper .admin_login{
			background: var(--e-global-color-accent);
			color: #fff;
			padding: 10px 12px;
			border-radius: 6px;
			display: block;
			margin-bottom: 5px;
			text-align: center;
		}
		
		#waas1_list_all_domains_shortcode_wrapper .clone_site{
			background: var(--e-global-color-primary);
			color: #fff;
			padding: 10px 12px;
			border-radius: 6px;
			display: block;
			text-align: center;
		}
		
		
		
		
		
		#waas1_list_all_domains_shortcode_wrapper .col-site-details{
			width: 190px;
		}
		#waas1_list_all_domains_shortcode_wrapper .col-site-details h4 i.fa-check{
			color: #16b300;
		}
		#waas1_list_all_domains_shortcode_wrapper .col-site-details h4 i.fa-times{
			color: #ff0033;
		}
		
		
		#waas1_list_all_domains_shortcode_wrapper .col-stats{
			width: 150px;
		}
		#waas1_list_all_domains_shortcode_wrapper .col-disk-usage{
			width: 100px;
		}
		
		
		
		#waas1_list_all_domains_shortcode_wrapper a.site-domain{
			margin-bottom: 5px;
			display: block;
		}
		
		#waas1_list_all_domains_shortcode_wrapper span.site-note-details{
			display: block;
			font-size: 12px;
		}
		
		
		
		#waas1_list_all_domain_notice_wrapper{
			margin-bottom: 35px;
		}
		
		#waas1_list_all_domain_notice_wrapper .notice{
			padding: 15px;
			border-radius: 6px;
		}
		
		#waas1_list_all_domain_notice_wrapper .notice p{
			color: #000;
			margin: 0;
		}
		
		#waas1_list_all_domain_notice_wrapper .notice.error{
			background-color: #fff;
			border-left: 5px solid #DF3B2F;
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			border-right: 1px solid #ccc;
		}
		
		#waas1_list_all_domain_notice_wrapper .notice.success{
			background-color: #fff;
			border-left: 5px solid #7BD330;
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			border-right: 1px solid #ccc;
		}
		
		#waas1_list_all_domain_notice_wrapper .notice.warning{
			background-color: #fff;
			border-left: 5px solid #FFBB00;
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			border-right: 1px solid #ccc;
		}
		
		
		
		</style>';
		echo $css;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	

	function render_shortcode( $atts ){
		
		
		//add css and js
		add_action( 'wp_footer', array($this, 'add_js_css'), 1800 );
		
		global $wp;
		$waas1_api = new Waas1Api();
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		
		$args = shortcode_atts( array(
									'allow_site_clone'=>'yes',
									'update_domain_url'=>home_url().'/contact-us/',
									), $atts );
									
		$args['allow_site_clone'] 	= esc_attr( $args['allow_site_clone'] );
		$args['update_domain_url']	= esc_attr( $args['update_domain_url'] );
		
		
		
		//get all the sites created by this user email id:
		$loggedInUser = wp_get_current_user();
		$this->_loggedInUserEmail = $loggedInUser->user_email;
		

		
		//try to get the data from cache
		$cache_key = 'waas1_list_all_domains_short_code_'.$this->_loggedInUserEmail;
		$all_domains = wp_cache_get( $cache_key, 'waas1_list_all_domains_short_code');
		
		
		if( !$all_domains ){
			$all_domains = $waas1_api->network_domain_list( $this->_loggedInUserEmail );
		}
		

		//if no domain found
		if( !$all_domains['status'] ){
			$html = '<div id="waas1_list_all_domain_notice_wrapper">';
				$html .= '<div class="warning notice">';
					$html .= '<p>Your domain list is empty. Please contact us if you do not see your domain listed here.</p>';
				$html .= '</div>';
			$html .= '</div>';
			echo $html;
			return; //make sure to return from here
		}
		
		
		
		
		
		$count_all_domains = count( $all_domains['data'] );
		//now it's safe to add the results to cache
		wp_cache_add( $cache_key, $all_domains, 'waas1_list_all_domains_short_code', 60 );

		

		
		$html = '';

		if( isset($_GET['waas1-alert']) && $_GET['waas1-alert'] == 'yes' ){
			
			$html .= '<div id="waas1_list_all_domain_notice_wrapper">';
				$html .= '<div class="'.$_GET['waas1-alert-type'].' notice">';
					$html .= '<p>'.$_GET['waas1-alert-msg'].'</p>';
				$html .= '</div>';
			$html .= '</div>';
			
		}
	

		
		
		$html .= '<div id="waas1_list_all_domains_shortcode_wrapper"><table>';
			$html .='<tr><th>#</th><th>Domain name</th><th>Purchased/Connected</th><th>Added time</th><th>NS Servers</th></tr>';
			$count = 1;
			foreach( $all_domains['data'] as $domain ){
				$html .= '<tr>';
				$html .= '<td>'.$count.'</td>';
				$html .= $this->generate_html( $domain, $args, $current_url );
				$html .= '</tr>';
				$count++;
			}
		$html .= '</table></div>';
		
		echo $html;
	}
	
	
	
	
	private function getAttribute( $subscription, $attributeKey ){
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
		
	}
	
	
	
	
	
	
	function generate_html( $domain ){
		$html = '<td><strong>'.$domain['domain'].'</strong></td>';
		if( $domain['purchased_using_namecheap_api'] == '1' ){
			$html .= '<td>Purchased</td>';
		}else{
			$html .= '<td>Connected external</td>';
		}
		$html .= '<td>'.gmdate( 'd-M-Y', $domain['domain_added_timestamp'] ).'</td>';
		$html .= '<td>'.$domain['cloudflare_ns1'].'<br />'.$domain['cloudflare_ns2'].'</td>';
		return $html;
	}
	
	
	
	
	
	function generate_html_old( $site, $args, $current_url ){
		
		$site_url = 'https://'.$site['domain'].'/';
		
		$activeSubscription = false;
		$planUsed = 'N/A';
		
		if( isset($site['unique_order_id']) ){
			if( $this->_attributeKey ){
				
				$subscriptionId = $site['unique_order_id']+1;
				$subscriptions = wcs_get_subscriptions_for_order( $site['unique_order_id'], array('order_type'=>'any') );
				
				if( isset($subscriptions[$subscriptionId]) ){
					$activeSubscription = true;
					$planUsed = $this->getAttribute( $subscriptions[$subscriptionId], $this->_attributeKey );
				}

			}
		}
		
		
		
		
		$html = '<div class="site-wrapper">';
		
			$html .= '<div class="col-one thumbnail">';
		
				if( isset($site['unique_order_id']) ){
					$main_action_url = home_url().'/my-account/view-subscription/'.($site['unique_order_id']+1).'/';
					
					$html .= '<a href="'.$main_action_url.'"><img src="' . $this->_wpThumbShots .urlencode( $site_url ).'?w=145" /></a>';
					if( $activeSubscription ){
						$html .= '<a class="manage-subscription" href="'.$main_action_url.'">Manage subscription</a>';
					}
					
				}else{
					$html .= '<a href="'.$site_url.'"><img src="' . $this->_wpThumbShots .urlencode( $site_url ).'?w=145" /></a>';
				}
		
			$html .= '</div>';
			
			
			$html .= '<div class="col-site-details">';
				if( $site['status'] == '1' ){
					$html .= '<h4>Active <i class="fas fa-check"></i></h4>';
				}else{
					$html .= '<h4>Deactivated <i class="fas fa-times"></i></h4>';
				}
				$html .= '<a class="site-domain" href="'.$site_url.'" target="_blank">'.$site['domain'].'</a>';
				
				
				if( $activeSubscription ){
					$html .= '<span class="selected-plan">Plan: '.$planUsed.'</span>';
				}
				
				$html .= '<span class="site-note-details">(Node: '.$site['node_id'].' - Site: '.$site['site_id'].')</span>';
				
				
				
			$html .= '</div>';
			
			
			
			if( strpos($site['domain'], $site['WAAS1_PLATFORM_DOMAIN']) !== false ) {
				$html .= '<div class="col-stats">';
				$html .= '<h4>Monthly stats</h4>';
				$html .= 'Once we add a domain to this site. Your monthly website visitors usage stats will appear here.';
				$html .= '</div>';
			}else{
				$html .= '<div class="col-stats">';
				$html .= '<h4>Monthly stats</h4>';
				$html .= $site['TOTAL_UNIQUE_VISITORS'].' Unique Visitors';
				$html .= '<br />';
				$html .= $site['TOTAL_PAGE_VIEWS'].' Unique Visitors';
				$html .= '<br />';
				$html .= $site['TOTAL_BANDWIDTH_MB'].'MB Bandwidth used';
				$html .= '</div>';
			}
			
			
			
			
			
			$html .= '<div class="col-disk-usage">';
				$html .= '<h4>Disk usage</h4>';
				$html .= 'Disk: '.$site['WAAS1_TOTAL_APP_SIZE_MB'].'MB';
				$html .= '<br />';
				$html .= 'Database: '.$site['WAAS1_TOTAL_DB_SIZE_MB'].'MB';
				$html .= '<br />';
				$html .= 'Inodes: '.$site['WAAS1_TOTAL_APP_INODES'];
			$html .= '</div>';
			
			$html .= '<div class="col-four">';
				
				
				if( strpos( strtolower($site['one-time-login']), 'error#98807') !== false ){
				}else{
					$html .= '<a class="admin_login" href="'.$site['one-time-login'].'" target="_blank"><i aria-hidden="true" class="fas fa-key"></i> Admin</a>';
				}
			
				
				$html .= '<a class="admin_login" href="'.$args['update_domain_url'].'" target="_blank">Add domain</a>';
				
				if( $args['allow_site_clone'] == 'yes' && $site['status'] == '1' ){
					
					$url_array = array( 'waas1_clone_site'=>'yes', 'waas1-clone-node-id'=>$site['node_id'], 'waas1-clone-site-id'=>$site['site_id'] );
					$clone_site_url = add_query_arg( $url_array, $current_url );
		
					$html .= '<a class="clone_site" href="'.$clone_site_url.'"><i aria-hidden="true" class="fas fa-copy"></i> Clone site</a>';
				}
				
				
			$html .= '</div>';
			
			
			
			
			
			
		$html .= '</div>';
		
		return $html;
	}
	
	
}

new waas1_list_all_domains_shortcode_class();

?>