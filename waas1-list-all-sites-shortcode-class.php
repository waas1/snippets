<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//--------------------
//Note: This snippet relies on Waas1Api class. You also need to add Waas1Api class snippet and properly configure it.
//usage: [waas1_list_all_sites update_domain_url="https://yourdomain.com/contact-us/"]. default is allow site cloning
//use [waas1_list_all_sites update_domain_url="https://yourdomain.com/contact-us/" allow_site_clone="no"] 
//--------------------

new waas1_list_all_sites_shortcode_class();

class waas1_list_all_sites_shortcode_class{


	private $wpThumbShots = 'https://s5.wp.com/mshots/v1/';
	private $loggedInUserEmail;
	
	
	
	function __construct(){
		add_shortcode('waas1_list_all_sites', array($this, 'render_shortcode') );
		add_action( 'init', array($this, 'register_query_vars') );
	}
	
	
	
	function register_query_vars() { 
		global $wp;
		$wp->add_query_var( 'waas1_clone_site' ); 
		$wp->add_query_var( 'waas1-clone-node-id' ); 
		$wp->add_query_var( 'waas1-clone-site-id' ); 
	}

	
	
	
	
	function add_js_css(){
		$css ='<style>
		
		#waas1_list_all_sites_shortcode_wrapper{
		}
		
		#waas1_list_all_sites_shortcode_wrapper .site-wrapper{
			display: flex;
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
			border-radius: 6px;
			max-width: 120px;
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
		$this->loggedInUserEmail = $loggedInUser->user_email;
		
		//try to get the data from cache
		$cache_key = 'waas1_list_all_sites_short_code_'.$this->loggedInUserEmail;
		$all_sites = wp_cache_get( $cache_key, 'waas1_list_all_sites_short_code');
		
		if( !$all_sites ){
			$all_sites = $waas1_api->network_get_site_info( array(
															'client-email'=>$this->loggedInUserEmail, 
															'with-one-time-login'=>$this->loggedInUserEmail,
															));
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
				'client-email'				=>  $this->loggedInUserEmail,
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
			
			
			$redirect_url = add_query_arg( $url_array, $current_url );
			wp_redirect( $redirect_url );
			exit();
		}
		//clone site ends
		
		

		
		

		

		
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
				$html .= $this->generate_html( $site, $args, $current_url );
			}
		}
		$html .= '</div>';
		
		echo $html;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function generate_html( $site, $args, $current_url ){
		
		$site_url = 'https://'.$site['domain'].'/';
		
		$html = '<div class="site-wrapper">';
		
			$html .= '<div class="col-one thumbnail">';
				$html .= '<a href="'.$site_url.'" target="_blank"><img src="' . $this->wpThumbShots .urlencode( $site_url ).'?w=120" /></a>';
			$html .= '</div>';
			
			
			$html .= '<div class="col-site-details">';
				if( $site['status'] == '1' ){
					$html .= '<h4>Active</h4>';
				}else{
					$html .= '<h4>Deactivated</h4>';
				}
				$html .= '<a class="site-domain" href="'.$site_url.'" target="_blank">'.$site['domain'].'</a>';
				$html .= '<span class="site-note-details">(Node: '.$site['node_id'].' - Site: '.$site['site_id'].')</span>';
			$html .= '</div>';
			
			
			
			if( strpos($site['domain'], $site['WAAS1_PLATFORM_DOMAIN']) !== false ) {
				$html .= '<div class="col-stats">';
				$html .= '<h4>Monthly stats</h4>';
				$html .= 'Once you add a domain to this site. Your monthly website visitors usage stats will appear here.';
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
				$html .= '<a class="admin_login" href="'.$site['one-time-login'].'" target="_blank"><i aria-hidden="true" class="fas fa-key"></i> Admin</a>';
				$html .= '<a class="admin_login" href="'.$args['update_domain_url'].'" target="_blank">Update domain</a>';
				
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

?>