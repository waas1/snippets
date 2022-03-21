<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Waas1ApiAjax();
class Waas1ApiAjax{
	
	
	function __construct(){
		
		add_action( 'wp_ajax_nopriv_waas1_get_site_progress', array($this, 'guestUser'), 11, 3 ); //hook for guest users
		add_action( 'wp_ajax_waas1_get_site_progress', array($this, 'getSiteProgress'), 11, 3 ); //hook for logged in users
		
		add_action( 'wp_ajax_nopriv_waas1_search_domain', array($this, 'guestUser'), 11, 3 ); //hook for guest users
		add_action( 'wp_ajax_waas1_search_domain', array($this, 'searchDomain'), 11, 3 ); //hook for logged in users
		
		add_action( 'wp_ajax_nopriv_waas1_connect_domain', array($this, 'guestUser'), 11, 3 ); //hook for guest users
		add_action( 'wp_ajax_waas1_connect_domain', array($this, 'connectDomain'), 11, 3 ); //hook for logged in users
		
	}
	
	
	public function guestUser(){
		$this->sendJsonResponse( 403, 'Who are you?' );
	}
	
	
	
	public function connectDomain(){
		
		$domain = sanitize_text_field( $_POST['domain'] );
		if( $domain == '' ){
			$this->sendJsonResponse( 403, 'domain is required' );
			exit;
		}
		
		$currentUser = wp_get_current_user();
		$clientEmail = $currentUser->user_email;
		
		//now check the network using API if the domain is available or not.
		$waas1_api = new Waas1Api();
		$responseBody = $waas1_api->network_domain_connect( $domain, $clientEmail );
		
	
		if( $responseBody['status'] == false ){
			$this->sendJsonResponse( 403, $responseBody['errorMsg'] );
			exit;
		}
		
		$returnData = array( 'msg'=>$responseBody['msg'] );		
		$this->sendJsonResponse( 200, 'Success!', $returnData );
		exit;
	}
	
	
	
	public function searchDomain(){
		
		$domain = sanitize_text_field( $_POST['domain'] );
		if( $domain == '' ){
			$this->sendJsonResponse( 403, 'domain is required' );
			exit;
		}
		
		//now check the network using API if the domain is available or not.
		$waas1_api = new Waas1Api();
		$responseBody = $waas1_api->network_domain_search( $domain );
		
	
		if( $responseBody['status'] == false ){
			$this->sendJsonResponse( 403, $responseBody['errorMsg'] );
			exit;
		}
		
		$returnData = array( 'msg'=>$responseBody['msg'], 'pricing'=>$responseBody['pricing'] );		
		$this->sendJsonResponse( 200, 'Success!', $returnData );
		exit;
	}
	
	
	public function getSiteProgress(){
		
		//get required parameters
		$uniqueOrderId = sanitize_text_field( $_POST['unique-order-id'] );
		if( $uniqueOrderId == '' ){
			$this->sendJsonResponse( 403, 'unique-order-id is required' );
			exit;
		}
		
		$waas1_api = new Waas1Api();
		$responseBody = $waas1_api->network_get_site_info_by_order_id( $uniqueOrderId );
		
		
		if( $responseBody['status'] == false ){
			$this->sendJsonResponse( 200, 'Site with unique order id not found!' );
			exit;
		}

		$returnData = array( 'progress_completed'=>$responseBody['data'][0]['progress_completed'] );
		$returnData['domain'] = $responseBody['data'][0]['domain'];
		$returnData['client_email'] = $responseBody['data'][0]['client_email'];
		
		$returnData['all'] = $responseBody['data'][0];
		
		$this->sendJsonResponse( 200, 'Success!', $returnData );
		exit;
	}
	
	
	
	
	
	
	
	private function sendJsonResponse( $status=false, $msg=false, $data=false ){ //sendJsonPayload start
		header('Content-Type: application/json');
		
		$ajaxData = array();
		$ajaxData['status'] = $status;
		$ajaxData['msg'] = $msg;
		$ajaxData['data'] = $data;
		
		if( $status === 200 ){
			$ajaxData['status'] = true;
		}else{
			header( 'HTTP/1.1 403 Forbidden', true, 403 );
		}
		
		echo json_encode( $ajaxData) ;
		exit; //do not process anything
	} //sendJsonPayload end
	
}
?>