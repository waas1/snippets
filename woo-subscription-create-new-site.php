<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//--------------------
//Note: This snippet relies on Waas1Api class. You also need to add Waas1Api class snippet and properly configure it.
//--------------------

new waas1_woo_subscription_create_new_site_class();

class waas1_woo_subscription_create_new_site_class{
	
	
	function __construct(){
		add_action( 'woocommerce_subscription_status_updated', array($this, 'wooSubStatusUpdate'), 11, 3 );
	}
	
	
	public function wooSubStatusUpdate( $subscription, $new_status, $old_status ){ //wooSubStatusUpdate start
		
		
		
		$orderId 		= $this->getOrderId( $subscription );
		$waas1_api 		= new Waas1Api();
		$apiCheckOrderId = $waas1_api->network_get_site_info_by_order_id( $orderId );

		
		$orderAlreadyExists = $apiCheckOrderId['status'];
		
		if( $orderAlreadyExists == false ){
			
			if( $new_status == 'active' ){
				$response = $this->cloneNewSite( $subscription, $orderId );
			}
			
		}else{
			
			if( $new_status == 'active' ){
				$response = $this->changeSiteStatus( $apiCheckOrderId, 'active' );
			}else{
				$response = $this->changeSiteStatus( $apiCheckOrderId, 'suspend' );
			}
			
		}
		
		
		//remove object cache for every status change
		$clientEmail = $this->getClientEmailAddress( $subscription );
		$cache_key 	= 'waas1_list_all_sites_short_code_'.$clientEmail;
		wp_cache_delete( $cache_key, 'waas1_list_all_sites_short_code' );
		
	
	} //wooSubStatusUpdate end
	
	
	
	
	
	
	
	
	
	
	
	
	private function cloneNewSite( $subscription, $orderId ){ //cloneNewSite start
		
		
		//build the node selection logic here
		$node_to_use = $this->getSmallestNodeId();

	
		
		$clientEmail 		= $this->getClientEmailAddress( $subscription );
		$cloneSourceNodeId 	= $this->getCustomField( $subscription, 'template_node_id' );
		$cloneSourceSiteId 	= $this->getCustomField( $subscription, 'template_site_id' );
		
		$billing_first_name 	= $this->getClientBillingData( $subscription, 'first_name' );
		$billing_last_name 		= $this->getClientBillingData( $subscription, 'last_name' );
		
		//--------------------
		//uncomment following if you need to inject additional fields
		//--------------------
		
		//$billing_address_1		= $this->getClientBillingData( $subscription, 'address_1' );
		//$billing_address_2		= $this->getClientBillingData( $subscription, 'address_2' );
		//$billing_city 			= $this->getClientBillingData( $subscription, 'city' );
		//$billing_state			= $this->getClientBillingData( $subscription, 'state' );
		//$billing_postcode 		= $this->getClientBillingData( $subscription, 'postcode' );
		//$billing_country 		= $this->getClientBillingData( $subscription, 'country' );
		//$billing_phone			= $this->getClientBillingData( $subscription, 'phone' );
		//$billing_company		= $this->getClientBillingData( $subscription, 'company' );
		
		//$billing_country_full	= $this->codeToCountry( $billing_country );
		
		//$completeBillingAddress = $billing_address_1.' '.$billing_address_2.' '.$billing_city.' '.$billing_state.' '.$billing_postcode.' '.$billing_country_full;
		
		
		$paramters_array = array(
			'unique-order-id'			=> 	$orderId,
			'client-email'				=>  $clientEmail,
			'clone-source-node-id'		=>  $cloneSourceNodeId,
			'clone-source-site-id'		=>  $cloneSourceSiteId,
			'inject-first_name'			=>  $billing_first_name,
			'inject-last_name'			=>  $billing_last_name,
			'inject-email_address'		=>  $clientEmail,
			//'inject-phone_number'		=>  $billing_phone,
			//'inject-physical_address'	=>  $completeBillingAddress,
		);
		
		$waas1_api = new Waas1Api();
		$returnArray = $waas1_api->site_new( $node_to_use, $paramters_array );
		return $returnArray;

	} //cloneNewSite end
	
	
	
	
	
	
	private function getSmallestNodeId(){ //getSmallestNodeId start
	
		$waas1_api = new Waas1Api();
		$returnArray = $waas1_api->network_info();
		return $returnArray['smallest_node_id'];

	} //getSmallestNodeId end
	
	
	
	
	
	private function changeSiteStatus( $apiCheckOrderId, $status ){ //changeSiteStatus start
		
		$siteData = $apiCheckOrderId['data'][0];
		
		if( $status == 'active' ){
			$statusToSet = 'activate';
		}else{
			$statusToSet = 'deactivate';
		}
		
		$waas1_api = new Waas1Api();
		$returnArray = $waas1_api->site_status( $siteData['node-id'], $siteData['site_id'], $statusToSet );
		return $returnArray;
		
	} //changeSiteStatus end
	
	
	
	
	
	
	
	
	
	
	private function getClientEmailAddress( $subscription ){ //getClientEmailAddress start
		
		$subscription_data 	= $subscription->get_data();
		$customerId 		= $subscription_data['customer_id'];
		$customerData 		= get_userdata( $customerId );
		return $customerData->user_email;
		
	} //getClientEmailAddress end
	
	
	
	
	private function getClientBillingData( $subscription, $field ){ //getClientBillingData start
		
		$subscription_data 	= $subscription->get_data();
		return $subscription_data['billing'][$field];
		
	} //getClientBillingData end
	
	
	
	
	
	
	
	
	
	private function getCustomField( $subscription, $fieldName ){ //getCustomField start
	  
		$subscription_products = $subscription->get_items();
		foreach( $subscription_products as $product ){

			$productData = $product->get_data();
			$requiredField = get_post_meta( $productData['product_id'], $fieldName, true ); 
			if( $requiredField != ''){
				break; //break from the loop
			}

		}
		return $requiredField;

	} //getCustomField end
	
	
	
	
	
	
	
	
	
	private function getOrderId( $subscription ){ //getOrderId start
		$subscription_data = $subscription->get_data();
		return $subscription_data['parent_id'];
	} //getOrderId end
	
	
	

	
	
	private function codeToCountry( $code ){

		$code = strtoupper( $code );
		
		$countryList = array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas the',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island (Bouvetoya)',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros the',
			'CD' => 'Congo',
			'CG' => 'Congo the',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => "Cote d'Ivoire",
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FO' => 'Faroe Islands',
			'FK' => 'Falkland Islands (Malvinas)',
			'FJ' => 'Fiji the Fiji Islands',
			'FI' => 'Finland',
			'FR' => 'France, French Republic',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia the',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'Korea',
			'KR' => 'Korea',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyz Republic',
			'LA' => 'Lao',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'AN' => 'Netherlands Antilles',
			'NL' => 'Netherlands the',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn Islands',
			'PL' => 'Poland',
			'PT' => 'Portugal, Portuguese Republic',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia (Slovak Republic)',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia, Somali Republic',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard & Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland, Swiss Confederation',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States of America',
			'UM' => 'United States Minor Outlying Islands',
			'VI' => 'United States Virgin Islands',
			'UY' => 'Uruguay, Eastern Republic of',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);

		

    	if( !$countryList[$code] ){
			return $code;
		}else{
			return $countryList[$code];
		}

    }
	
	
	
}

?>