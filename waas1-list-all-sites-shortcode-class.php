<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//--------------------
//Note: This snippet relies on Waas1Api class. You also need to add Waas1Api class snippet and properly configure it.
//usage: [waas1_list_all_sites]. default is allow site cloning to yes
//use [waas1_list_all_sites allow_site_clone="no"]  to disable site cloning
//--------------------



class waas1_list_all_sites_shortcode_class{

	private $_attributeKey = 'select-plan'; //set this to false if you are not offering diferent plans
	private $_wpThumbShots = 'https://s5.wp.com/mshots/v1/';
	private $_loggedInUserEmail;
	
	
	
	function __construct(){
		add_shortcode('waas1_list_all_sites', array($this, 'render_shortcode') );
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
		
		$wp->add_query_var( 'waas1_change_site_domain' ); 
		$wp->add_query_var( 'waas1-change-site-domain-node-id' ); 
		$wp->add_query_var( 'waas1-change-site-domain-site-id' ); 
		$wp->add_query_var( 'waas1-change-subdomain' ); 
		$wp->add_query_var( 'waas1-change-domain' ); 
		
	}

	
	
	
	
	function add_js_css(){
		$css ='<style>
		
		#waas1_list_all_sites_shortcode_wrapper{
		}
		
		#waas1_list_all_sites_shortcode_wrapper .site-wrapper{
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
			background: #F8F8F8;
			margin-bottom: 15px;
			padding: 10px;
			align-items: center;
		}
		
		#waas1_list_all_sites_shortcode_wrapper .thumbnail{
			margin-right: 5px;
		}
		#waas1_list_all_sites_shortcode_wrapper .thumbnail img{
			max-width: 145px;
			display: block;
		}
		
		
		#waas1_list_all_sites_shortcode_wrapper .thumbnail a.manage-subscription{
			display: block;
			text-align: center;
			background: var(--e-global-color-primary);
			color: #fff;
			padding: 2px 0;
			font-size: 14px;
			border-radius: 0 0 6px 6px;
		}
		
		#waas1_list_all_sites_shortcode_wrapper span.selected-plan{
			text-transform: capitalize;
			display: block;
			margin-bottom: 5px;
		}
		
		
		#waas1_list_all_sites_shortcode_wrapper h4{
			margin: 0 0 5px 0;
			font-size: 18px;
		}
		
		
		#waas1_list_all_sites_shortcode_wrapper .col-stats,
		#waas1_list_all_sites_shortcode_wrapper .col-disk-usage{
			font-size: 12px; 
		}
		#waas1_list_all_sites_shortcode_wrapper .col-stats h4,
		#waas1_list_all_sites_shortcode_wrapper .col-disk-usage h4{
			font-size: 14px; 
		}
		
		#waas1_list_all_sites_shortcode_wrapper .admin_login{
			background: var(--e-global-color-accent);
			color: #fff;
			padding: 10px 12px;
			border-radius: 6px;
			display: block;
			margin-bottom: 5px;
			text-align: center;
		}
		#waas1_list_all_sites_shortcode_wrapper .change_domain{
			background: var(--e-global-color-accent);
			color: #fff;
			padding: 10px 12px;
			border-radius: 6px;
			display: block;
			margin-bottom: 5px;
			text-align: center;
		}
		
		#waas1_list_all_sites_shortcode_wrapper .clone_site{
			background: var(--e-global-color-primary);
			color: #fff;
			padding: 10px 12px;
			border-radius: 6px;
			display: block;
			text-align: center;
		}
		
		
		
		
		
		#waas1_list_all_sites_shortcode_wrapper .col-site-details{
			width: 190px;
		}
		#waas1_list_all_sites_shortcode_wrapper .col-site-details h4 i.fa-check{
			color: #16b300;
		}
		#waas1_list_all_sites_shortcode_wrapper .col-site-details h4 i.fa-times{
			color: #ff0033;
		}
		
		
		#waas1_list_all_sites_shortcode_wrapper .col-stats{
			width: 150px;
		}
		#waas1_list_all_sites_shortcode_wrapper .col-disk-usage{
			width: 100px;
		}
		
		
		
		#waas1_list_all_sites_shortcode_wrapper a.site-domain{
			margin-bottom: 5px;
			display: block;
		}
		
		#waas1_list_all_sites_shortcode_wrapper span.site-note-details{
			display: block;
			font-size: 12px;
		}
		
		
		#waas1_list_all_sites_shortcode_wrapper .col-full-width-change-domain{
			width: 100%;
			padding: 15px;
			border-radius: 0 0 6px 6px;
			background: #d9d9d9;
			margin-top: 10px;
		}
		
		#waas1_list_all_sites_shortcode_wrapper .col-full-width-change-domain h4{
			text-align: center;
			font-size: 18px;
		}
		#waas1_list_all_sites_shortcode_wrapper .col-full-width-change-domain p{
			margin-bottom: 20px;
			font-size: 80%;
			text-align: center;
		}
		
		#waas1_list_all_sites_shortcode_wrapper div.col-4{
			display: inline-block;
			padding-right: 10px;
			width: 33%;
		}
		
		
			
		#waas1_list_all_sites_shortcode_wrapper div.col-4:last-child{
			padding-right: 0;
		}
		
		#waas1_list_all_sites_shortcode_wrapper .col-full-width-change-domain div.col-4 label{
			font-size: 80%;
			font-size: 80%;
			display: block;
			margin-bottom: 3px;
		}
		
		#waas1_list_all_sites_shortcode_wrapper .col-full-width-change-domain div.col-4 label[for="changeSubdomain"]{
			text-align: right;
		}
		
	
		
		
		#waas1_list_all_sites_shortcode_wrapper div.col-4 input.changeSubdomain{
			text-align: right;
		}
		
		#waas1_list_all_sites_shortcode_wrapper div.col-4 input.changeDomainNow{
			width: 100%;
			background-color: var(--e-global-color-primary);
			color: #fff;
		}
		
		
		#waas1_list_all_sites_notice_wrapper{
			margin-bottom: 35px;
		}
		
		#waas1_list_all_sites_notice_wrapper .notice{
			padding: 15px;
			border-radius: 6px;
		}
		
		#waas1_list_all_sites_notice_wrapper .notice p{
			color: #000;
			margin: 0;
		}
		
		#waas1_list_all_sites_notice_wrapper .notice.error{
			background-color: #fff;
			border-left: 5px solid #DF3B2F;
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			border-right: 1px solid #ccc;
		}
		
		#waas1_list_all_sites_notice_wrapper .notice.success{
			background-color: #fff;
			border-left: 5px solid #7BD330;
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			border-right: 1px solid #ccc;
		}
		
		#waas1_list_all_sites_notice_wrapper .notice.warning{
			background-color: #fff;
			border-left: 5px solid #FFBB00;
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			border-right: 1px solid #ccc;
		}
		
		
		
		</style>';
		echo $css;
		
		
		$js = '<script>
		jQuery( document).ready(function( $ ){
			
			$( ".change_domain" ).click(function( e ){
				e.preventDefault();

				var wrapperId = $( this ).data( "wrapper-id" );
				$( "#"+wrapperId ).slideToggle();
				
			});
			
			
			$( ".changeDomainNow" ).click(function(e){
				e.preventDefault();
				
				var actionUrl = $( this ).data( "action-url" );
				var wrapperId = $( this ).data( "wrapper-id" );
				var changeSubdomain = $( "#"+wrapperId+" .changeSubdomain" ).val();
				var changeDomain = $( "#"+wrapperId+" .changeDomain" ).val();
				
				var fullFinalUrl = actionUrl + "&waas1-change-subdomain="+changeSubdomain + "&waas1-change-domain="+changeDomain;
				window.location.href = fullFinalUrl;


			});
			
			
		});
		</script>
		';
		echo $js;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	

	function render_shortcode( $atts ){
		
		
		//add css and js
		add_action( 'wp_footer', array($this, 'add_js_css'), 1800 );
		
		global $wp;
		$waas1_api = new Waas1Api();
		$current_url = home_url( add_query_arg( array(), $wp->request ) ).'/';
		
		$args = shortcode_atts( array(
									'allow_site_clone'=>'yes',
									), $atts );
									
		$args['allow_site_clone'] 	= esc_attr( $args['allow_site_clone'] );
		
		
		
		//get all the sites created by this user email id:
		$loggedInUser = wp_get_current_user();
		$this->_loggedInUserEmail = $loggedInUser->user_email;
		
		
		
		//try to get the data from cache
		//get site data
		$cache_key = 'waas1_list_all_sites_short_code_'.$this->_loggedInUserEmail;
		$all_sites = wp_cache_get( $cache_key, 'waas1_list_all_sites_short_code');
		
		
		if( !$all_sites ){
			$all_sites = $waas1_api->network_get_site_info( array(
															'client-email'=>$this->_loggedInUserEmail, 
															'with-one-time-login'=>$this->_loggedInUserEmail,
															));
		}
		
		//get domain data
		$cache_key_domain = 'waas1_list_all_domains_short_code_'.$this->_loggedInUserEmail;
		$all_domains = wp_cache_get( $cache_key_domain, 'waas1_list_all_domains_short_code');
		if( !$all_domains ){
			$all_domains = $waas1_api->network_domain_list( $this->_loggedInUserEmail );
		}
		wp_cache_add( $cache_key_domain, $all_domains, 'waas1_list_all_domains_short_code', 60 );

		
		$domainList = array( WAAS1_PLATFORM_DOMAIN );
		
		if( !empty($all_domains['data']) ){
			foreach( $all_domains['data'] as $domain){
				$domainList[] = $domain['domain'];
			}
		}
	
		
		
		
		//if no site found
		if( !$all_sites['status'] ){
			$html = '<div id="waas1_list_all_sites_notice_wrapper">';
				$html .= '<div class="warning notice">';
					$html .= '<p>Your site list is empty or your site creation is still under processing. Please contact us if you do not see your website listed here.</p>';
				$html .= '</div>';
			$html .= '</div>';
			echo $html;
			return; //make sure to return from here
		}
		
		
		
		$count_all_sites = count( $all_sites['data'] );
		
		if( $count_all_sites == 1 && $all_sites['data'][0]['progress_completed'] != '100' ){
			
			$html = '<div id="waas1_list_all_sites_notice_wrapper">';
				$html .= '<div class="warning notice">';
					$html .= '<p>Your site list is empty or your site creation is still under processing. Please contact us if you do not see your website listed here.</p>';
				$html .= '</div>';
			$html .= '</div>';
			echo $html;
			return; //make sure to return from here
			
		}else{
			
			//now it's safe to add the results to cache
			wp_cache_add( $cache_key, $all_sites, 'waas1_list_all_sites_short_code', 60 );
			
		}
		
		
		
		

		
		
		//clone site starts
		if( get_query_var('waas1_clone_site') && get_query_var('waas1_clone_site') == 'yes' && $args['allow_site_clone'] == 'yes' ){

			
			$url_array = array( 'waas1-alert'=>'yes' );
			$url_array['waas1-alert-type'] = 'error';
			$url_array['waas1-alert-msg'] = 'Something went wrong';
			
			$clone_site_node_id = get_query_var('waas1-clone-node-id');
			$clone_site_site_id = get_query_var('waas1-clone-site-id');
			
			if( !$clone_site_node_id || !$clone_site_site_id ){
				$url_array['waas1-alert-msg'] = 'Clone site node id or site id URL paramters not found!';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
			}
			
			
			
			
			$siteOwnerFound = false;
			$completeSiteArray = false;
			//now check if the current logged in user is owner of the site-id and node-id
			foreach( $all_sites['data'] as $site ){
				if( $site['site_id'] == $clone_site_site_id && $site['node_id'] == $clone_site_node_id ){
					$siteOwnerFound = true;
					$completeSiteArray = $site;
					break;
				}
			}
			
			
			if( !$siteOwnerFound ){
				$url_array['waas1-alert-msg'] = 'Clone site call is not authorized.';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
			}
			
			
			if( $completeSiteArray['status'] != '1' ){
				$url_array['waas1-alert-msg'] = 'Cannot clone a deactivated site.';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
			}
			

			
			
			//current logged in user is site owner and allowed to clone a site
			
			$apiResponse = $waas1_api->network_info();
			$node_to_use = $apiResponse['smallest_node_id'];

					
			$paramters_array = array(
				'client-email'				=>  $this->_loggedInUserEmail,
				'clone-source-node-id'		=>  $clone_site_node_id,
				'clone-source-site-id'		=>  $clone_site_site_id,
			);
			$apiResponse = $waas1_api->site_new( $node_to_use, $paramters_array );
			if( isset($apiResponse['status']) && $apiResponse['status'] == true ){
				
				$url_array['waas1-alert-type'] = 'success';
				$url_array['waas1-alert-msg'] = 'Please allow few minutes to complete the site cloning process.';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
				
			}
			
			$url_array['waas1-alert-msg'] = $apiResponse['errorMsg'];
			$redirect_url = add_query_arg( $url_array, $current_url );
			wp_redirect( $redirect_url );
			exit();
		}
		//clone site ends
		
		
		
		

		//change domain starts
		if( get_query_var('waas1_change_site_domain') && get_query_var('waas1_change_site_domain') == 'yes' ){
			
			
			
			$url_array = array( 'waas1-alert'=>'yes' );
			$url_array['waas1-alert-type'] = 'error';
			$url_array['waas1-alert-msg'] = 'Something went wrong';
			
			$change_site_node_id = get_query_var('waas1-change-site-domain-node-id');
			$change_site_site_id = get_query_var('waas1-change-site-domain-site-id');
			$change_site_subdomain = get_query_var('waas1-change-subdomain');
			$change_site_domain = get_query_var('waas1-change-domain');
			
			
			if( !$change_site_node_id || !$change_site_site_id || !$change_site_domain ){
				$url_array['waas1-alert-msg'] = 'site node id or site id or domain URL paramters not found!';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
			}
			
			
			$siteOwnerFound = false;
			$completeSiteArray = false;
			//now check if the current logged in user is owner of the site-id and node-id
			foreach( $all_sites['data'] as $site ){
				if( $site['site_id'] == $change_site_site_id && $site['node_id'] == $change_site_node_id ){
					$siteOwnerFound = true;
					$completeSiteArray = $site;
					break;
				}
			}
			
			
			if( !$siteOwnerFound ){
				$url_array['waas1-alert-msg'] = 'Change site domain call is not authorized.';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
			}
			
			
			if( $completeSiteArray['status'] != '1' ){
				$url_array['waas1-alert-msg'] = 'Cannot change site domain on a deactivated site.';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
			}
			
			
			//now check if the domain is in the list
			if( !in_array($change_site_domain, $domainList) ){
				$url_array['waas1-alert-msg'] = 'This domain is not currently in your list. Please add a domain first.';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
			}
			
			
			
			$paramters_array = array(
				'node-id'		=>  $change_site_node_id,
				'site-id'		=>  $change_site_site_id,
			);
			
			if( $change_site_subdomain != '' ){
				$paramters_array['domain'] = $change_site_subdomain.'.'.$change_site_domain;
			}else{
				$paramters_array['domain'] = $change_site_domain;
			}
			$apiResponse = $waas1_api->site_update( $change_site_node_id, $paramters_array );
			

			if( isset($apiResponse['status']) && $apiResponse['status'] == true ){
				
				//remove the cache
				wp_cache_delete( $cache_key, 'waas1_list_all_sites_short_code' );
				
				$url_array['waas1-alert-type'] = 'success';
				$url_array['waas1-alert-msg'] = 'Successfully changed the site domain.';
				$redirect_url = add_query_arg( $url_array, $current_url );
				wp_redirect( $redirect_url );
				exit();
				
			}
			
			$url_array['waas1-alert-msg'] = $apiResponse['errorMsg'];
			$redirect_url = add_query_arg( $url_array, $current_url );
			wp_redirect( $redirect_url );
			exit();
			
			
		}
		//change domain ends
		
		

		
		

		

		
		$html = '';

		if( isset($_GET['waas1-alert']) && $_GET['waas1-alert'] == 'yes' ){
			
			$html .= '<div id="waas1_list_all_sites_notice_wrapper">';
				$html .= '<div class="'.$_GET['waas1-alert-type'].' notice">';
					$html .= '<p>'.$_GET['waas1-alert-msg'].'</p>';
				$html .= '</div>';
			$html .= '</div>';
			
		}
	

		
		
		$html .= '<div id="waas1_list_all_sites_shortcode_wrapper">';
		foreach( $all_sites['data'] as $site ){
			if( $site['progress_completed'] == '100' ){
				$html .= $this->generate_html( $site, $domainList, $args, $current_url );
			}
		}
		$html .= '</div>';
		
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
	
	
	
	
	
	
	
	
	
	
	function generate_html( $site, $domainList, $args, $current_url ){
		

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
				$html .= $site['TOTAL_UNIQUE_VISITORS'].' Unique visitors';
				$html .= '<br />';
				$html .= $site['TOTAL_PAGE_VIEWS'].' Page views';
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
					if( $site['status'] == '1' ){ //if site status = 1
						$html .= '<a class="admin_login" href="'.$site['one-time-login'].'" target="_blank"><i aria-hidden="true" class="fas fa-key"></i> Admin</a>';
					}//if site status = 1
				}
			
				if( $site['status'] == '1' ){ //if site status = 1
					$html .= '<a class="change_domain" href="#" data-wrapper-id="changeDomainPanel_'.$site['node_id'].'_'.$site['site_id'].'">Change domain</a>';
				}//if site status = 1
				
				if( $args['allow_site_clone'] == 'yes' && $site['status'] == '1' ){
					
					$url_array = array( 'waas1_clone_site'=>'yes', 'waas1-clone-node-id'=>$site['node_id'], 'waas1-clone-site-id'=>$site['site_id'] );
					$clone_site_url = add_query_arg( $url_array, $current_url );
		
					$html .= '<a class="clone_site" href="'.$clone_site_url.'"><i aria-hidden="true" class="fas fa-copy"></i> Clone site</a>';
				}
				
				
				$html .= '</div>';
			
			
			
			if( $site['status'] == '1' ){ //if site status = 1
				
			$html .= '<div id="changeDomainPanel_'.$site['node_id'].'_'.$site['site_id'].'" class="col-full-width-change-domain" style="display:none;">';
				$html .= '<h4>Change domain</h4>';
				$html .= '<p>Remove subdomain/www if you want the website to be accessible from the primary domain only.</p>';
				$html .= '<div>';
					
					
					$html .= '<div class="col-4">';
						$html .= '<label for="changeSubdomain">Subdomain</label>';
						if( $site['subdomain'] ){
							$html .= '<input type="text" class="changeSubdomain" name="changeSubdomain" value="'.$site['subdomain'].'" />';
						}else{
							$html .= '<input type="text" class="changeSubdomain" name="changeSubdomain" value="" />';
						}
						
					$html .= '</div>';
					
					$html .= '<div class="col-4">';
						$html .= '<label for="changeDomain">Domain</label>';
						$html .= '<select class="changeDomain" name="changeDomain">';
							foreach( $domainList as $selectDomain ){
								
								if(	strpos($site['domain'], $selectDomain) !== false	){
									$html .= '<option selected="selected">'.$selectDomain.'</option>';
								} else{
									$html .= '<option>'.$selectDomain.'</option>';
								}
								
							}
						$html .= '</select>';
					$html .= '</div>';
					
					$html .= '<div class="col-4">';
					
						$url_array = array( 
							'waas1_change_site_domain'=>'yes', 
							'waas1-change-site-domain-node-id'=>$site['node_id'],
							'waas1-change-site-domain-site-id'=>$site['site_id'] 
							);
						$change_site_domain_url = add_query_arg( $url_array, $current_url );
						
						
						$html .= '<input class="changeDomainNow" data-action-url="'.$change_site_domain_url.'" data-wrapper-id="changeDomainPanel_'.$site['node_id'].'_'.$site['site_id'].'" class="col-4" type="submit" value="Change domain now">';
					$html .= '</div>';
					
					
				$html .= '</div>';
			$html .= '</div>';
			
			}//if site status = 1
			
			
			
		$html .= '</div>';
		
		return $html;
	}
	
	
}

new waas1_list_all_sites_shortcode_class();

?>