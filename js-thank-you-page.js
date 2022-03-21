jQuery( document ).ready(function( $ ){
    
	let searchParams = new URLSearchParams( window.location.search );
	let uniqueOrderId = searchParams.get('unique-order-id');
	let refreshIntervalId;
	
	if( uniqueOrderId != null ){
		checkForSiteProgress();
		refreshIntervalId = setInterval( checkForSiteProgress, 10000 );
	}
	
	
	function checkForSiteProgress(){
		
		$.ajax({
			type : "POST",
			dataType : "json",
			url : "/encrypted-admin/wp-admin/admin-ajax.php",
			data : { 'action':'waas1_get_site_progress', 'unique-order-id':uniqueOrderId },
			success: function( data ){
				
				if( data['data'] ){
			
					let progressWithPercent = data['data']['progress_completed']+'%';
					$( '#siteProgressBar .elementor-progress-bar' ).css( 'width', progressWithPercent );
					$( '#siteProgressBar .elementor-progress-percentage' ).html( progressWithPercent );

					if( data['data']['progress_completed'] === '100' ){
						clearInterval( refreshIntervalId );
						injectHtmlData( data['data'] );
					}
				}

			}
		});
		
	}
	
	
	function injectHtmlData( data ){
		
		$( '#progressWrapper1' ).slideUp();
		$( '#progressWrapper2' ).slideDown();
		
		let html = 'Perfect! Your shiny new website is ready. <br /> ';
		html += 'Check out: <strong><a target="_blank" href="https://'+data['domain']+'">';
			html += data['domain'];
		html += '</strong></a></p><p>';
		html += 'Don\'t forget to check your email: <strong>'+data['client_email']+'</strong> for login details.</p>';
		$( '#progressWrapper2HtmlInject p' ).html( html );
		
	}
	
	
	
	
	
});

