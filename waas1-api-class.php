<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Waas1Api{
	
	
	//-----------------------------
	//setup variables here start
	//-----------------------------
	
	//api key - you will find the key in web control panel.
	private $_api_key = '';
	
	//your network primary domain
	private $_api_base_domain = '';
	
	//admin email sent on error
	private $_admin_email = '';
	
	//set to true if you have multi node setup
	private $_skip_primary_node_for_new_sites = 'false'; 
	
	//api user - usually superduper
	private $_api_user = 'superduper'; 
	
	//api version - current is version is 1
	private $_api_version = '1';
	
	//other variables
	private $_api_timeout = 60;
	private $_api_url;
	
	//-----------------------------
	//setup variables here end
	//-----------------------------
	

	
	
	function __construct(){
		$this->_api_url = '.'.$this->_api_base_domain.'/api-v'.$this->_api_version.'/';
	}
	
	
	
	//Test if api is responding or not
	function ping( $msg='Api test from mu-plugin' ){
		
		$payload_array = array('msg'=>$msg);
	
		$response = $this->_send_api_request( 'ping/check/', $payload_array );
		return $response;
	}
	
	
	//This component will accept API calls only from CTRL-1 "Primary Controller
	//Use this endpoint to select the least busy node.
	//If any node is down it will skip it from the calculations.
	function network_info(){
		
		$payload_array = array( 'skip-primary-node'=>$this->_skip_primary_node_for_new_sites );
	
		$response = $this->_send_api_request( 'network/info/', $payload_array, '1' );
		return $response;
		
	}
	
	
	//This component will accept API calls only from CTRL-1 "Primary Controller
	//Use this endpoint to search the site on the whole network.
	//Suppose if we have 10 node cluster server setup. This endpoint will search all of the servers against provided parameters
	function network_get_site_info( $paramters_array ){
		$response = $this->_send_api_request( 'network/get-site-info/', $paramters_array, '1' );
		return $response;
	}
	
	function network_get_site_info_by_email( $client_email ){
		
		$payload_array = array( 'client-email'=>$client_email );
	
		$response = $this->_send_api_request( 'network/get-site-info/', $payload_array, '1' );
		return $response;
		
	}
	
	function network_get_site_info_by_order_id( $order_id ){
		
		$payload_array = array( 'unique-order-id'=>$order_id );
	
		$response = $this->_send_api_request( 'network/get-site-info/', $payload_array, '1' );
		return $response;
		
	}
	
	function network_domain_search( $domain ){
		
		$payload_array = array( 'domain'=>$domain );
	
		$response = $this->_send_api_request( 'network/domain-search/', $payload_array, '1' );
		return $response;
		
	}
	
	
	function network_domain_connect( $domain, $clientEmail=NULL ){
		
		$payload_array = array( 'domain'=>$domain );
		if( $clientEmail ){
			$payload_array['client-email'] = $clientEmail;
		}
	
		$response = $this->_send_api_request( 'network/domain-connect/', $payload_array, '1' );
		return $response;
		
	}
	
	
	function network_domain_register( $domain, $clientEmail=NULL ){
		
		$payload_array = array( 'domain'=>$domain );
		if( $clientEmail ){
			$payload_array['client-email'] = $clientEmail;
		}

		$response = $this->_send_api_request( 'network/domain-register/', $payload_array, '1' );
		return $response;
		
	}
	
	
	function network_domain_list( $clientEmail=NULL, $domain=NULL ){
		
		$payload_array = array( 'client-email'=>$clientEmail );
		if( $domain ){
			$payload_array['domain'] = $domain;
		}
	
		$response = $this->_send_api_request( 'network/domain-list/', $payload_array, '1' );
		return $response;
		
	}
	
	
	//Create a new WordPress site or clone any other site on the network.
	function site_new( $node_to_use, $paramters_array ){
		
		$payload_array = array();
		foreach( $paramters_array as $key=>$parameter ){
			$payload_array[$key] = $parameter;
		}
		
		$response = $this->_send_api_request( 'site/new/', $payload_array, $node_to_use );
		return $response;
	}
	
	//update the site
	function site_update( $node_to_use, $paramters_array ){
		
		$payload_array = array();
		foreach( $paramters_array as $key=>$parameter ){
			$payload_array[$key] = $parameter;
		}
		
		$response = $this->_send_api_request( 'site/update/', $payload_array, $node_to_use );
		return $response;
	}
	
	
	//suspend/active the site.
	function site_status( $node_to_use, $site_id, $status ){
		
		$payload_array = array( 'site-id'=>$site_id, 'status'=>$status );
		
		$response = $this->_send_api_request( 'site/status/', $payload_array, $node_to_use );
		return $response;
	}
	
	
	//remove the site.
	function site_remove( $node_to_use, $site_id ){
		
		$payload_array = array( 'site-id'=>$site_id );
		
		$response = $this->_send_api_request( 'site/remove/', $payload_array, $node_to_use );
		return $response;
		
	}
	
	
	
	
	
	private function _send_api_request( $api_path, $payload_array, $node_to_use='1' ){
		
		$endpoint_url = 'https://ctrl-'.$node_to_use.$this->_api_url.$api_path;
		
		$parameters = array(
					'user' 		=> 	$this->_api_user,
					'key' 		=> 	$this->_api_key,
					'node-id'	=>  $node_to_use,
		);
		
		foreach( $payload_array as $key=>$parameter ){
			$parameters[$key] = $parameter;
		}
		
		
		$response = wp_remote_request( $endpoint_url, array(
				'method'     => 'POST',
				'timeout'     => $this->_api_timeout,
				'sslverify' 	=> false,
				'body'        	=> $parameters
			)
		);
		
		if( isset($response['body']) ){
			$returnArray = json_decode( $response['body'], true );
		}else{
			$returnArray = array( 'status'=>false, 'errorMsg'=>'Connection timed out.' );
		}
		
		if( !is_array($returnArray) ){
			$returnArray = array( 'status'=>false, 'errorMsg'=>'Something wrong went wrong. #3233456' );
		}

		return $returnArray;
		
	}
	
}


?>