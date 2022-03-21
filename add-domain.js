jQuery( document ).ready(function( $ ){
	
	var buyDomainSurcharge = 5;
	var buyDomainCurrency = '$';
	var connectDomainSuccessRedirect = 'https://woo2.local-waas1.com/my-account/my-domains/';
	
	
	var formBuy = $( '#formDomainBuy form' );
	var btnConnectDomain = $( '#btnConnectNewDomain' );
	var btnConnectDomainOriginalText = btnConnectDomain.html();
	var btnSearchDomain = $( '#btnSearchNewDomain' );
	var btnSearchDomainOriginalText = btnSearchDomain.html();

	
	$( '#btnDomainConnect' ).click(function( e ){
		$( '#domainConnectWrapper' ).slideDown();
		$( '#domainBuyWrapper' ).slideUp();
	});
	
	
	$( '#btnDomainBuyNew' ).click(function( e ){
		$( '#domainConnectWrapper' ).slideUp();
		$( '#domainBuyWrapper' ).slideDown();
	});
	
	
	$( '#inputSearchNewDomain' ).keypress(function (e) {
	  if (e.which == 13) {
		btnSearchDomain.click();
		return false;
	  }
	});
	
	
	
	
	
	//button connect domain
	btnConnectDomain.click(function( e ){	
		e.preventDefault();
		
		if( btnConnectDomain.hasClass('disabled') ){
			return false;
		}
		
		var inputDomain = $( 'input#form-field-connect_domain' );
		var domain = inputDomain.val().trim();
		if( domain == '' ){
		   alert( 'Please provide a domain name to connect.' );
			return false;
		}
		
		btnConnectDomain.html( '<i class="fas fa-spinner fa-spin"></i>' ).addClass( 'disabled' );
		
		$.ajax({
			type : "POST",
			dataType : "json",
			url : "/encrypted-admin/wp-admin/admin-ajax.php",
			data : { 'action':'waas1_connect_domain', 'domain':domain }
		}).done(function( data ){
			if( data.status ){
				btnConnectDomain.html( 'Success...' );
				window.location.href = connectDomainSuccessRedirect;
			}
		}).fail(function( data ){
			var response = data.responseJSON;
			alert( response.msg );
			btnConnectDomain.html( btnConnectDomainOriginalText ).removeClass( 'disabled' );
		});
		
	});
	
	
	
	//button buy domain
	btnSearchDomain.click(function( e ){	
		e.preventDefault();
	
		
		if( btnSearchDomain.hasClass('disabled') ){
			return false;
		}
		
		$( '.waas1-woo-domain-buy-now-wrapper' ).slideUp();
	
		
		var inputDomain = $( 'input#inputSearchNewDomain' );
		var domain = inputDomain.val().trim();
		if( domain == '' ){
		   alert( 'Please provide a domain name.' );
			return false;
		}
		
		
		btnSearchDomain.html( '<i class="fas fa-spinner fa-spin"></i>' ).addClass( 'disabled' );
		
		$.ajax({
			type : "POST",
			dataType : "json",
			url : "/encrypted-admin/wp-admin/admin-ajax.php",
			data : { 'action':'waas1_search_domain', 'domain':domain }
		}).done(function( data ){

			var totalDomainPrice = +data.data.pricing.regular_price + +data.data.pricing.additional_cost;
			var finalDomainPrice = Math.ceil( totalDomainPrice + buyDomainSurcharge );
			
			$( '.domain_text_addon' ).val( domain );
			$( '.domain_text_addon_price' ).val( finalDomainPrice );
			$( '.newDomainName' ).html( domain );
			$( '.newDomainprice' ).html( buyDomainCurrency+finalDomainPrice );
			$( '.waas1-woo-domain-buy-now-wrapper' ).slideDown();

		}).fail(function( data ){
			var response = data.responseJSON;
			alert( response.msg );
		}).always(function( data ){
			btnSearchDomain.html( btnSearchDomainOriginalText ).removeClass( 'disabled' );
		});
		
	});

	
	
});



