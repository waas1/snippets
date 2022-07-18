jQuery( document ).ready(function( $ ){
    
	let searchParams = new URLSearchParams( window.location.search );
	let uniqueOrderId = searchParams.get('unique-order-id');
	let refreshIntervalId;
	
	$('#gotoWebsiteBackend i').addClass( 'fa-spin' );
	
	
	
	
	if( uniqueOrderId != null ){
		checkForSiteProgress();
		refreshIntervalId = setInterval( checkForSiteProgress, 5000 );
	}
	
	
	function checkForSiteProgress( getLoginData=false ){

		let postData = { 'action':'waas1_get_site_progress', 'unique-order-id':uniqueOrderId };
		
		if( getLoginData ){
		  postData['with-one-time-login'] = "true";
		}
		
		$.ajax({
			type : "POST",
			dataType : "json",
			url : "/encrypted-admin/wp-admin/admin-ajax.php",
			data : postData,
			success: function( data ){
				
				if( data['data'] ){
			
					let progressWithPercent = data['data']['progress_completed']+'%';
					$( '#siteProgressBar .elementor-progress-bar' ).css( 'width', progressWithPercent );
					$( '#siteProgressBar .elementor-progress-percentage' ).html( progressWithPercent );

					if( data['data']['progress_completed'] === '100' ){
						clearInterval( refreshIntervalId );
						
						//if we have site 100% get the login data
						if( getLoginData ){
							$('#gotoWebsiteBackend').removeClass('disabled').attr('disabled', false).text('Go to website back-end');
							$('#gotoWebsiteBackend').attr( 'href', data['data']['all']['one-time-login'] );
							
						}else{
							injectHtmlData( data['data'] );
							//setup wp admin button.
							setupWpAdminButton();
						}
						
						
					}
				}

			}
		});
		
	}
	
	
	function injectHtmlData( data ){
		
		$( '#progressWrapper1' ).slideUp();
		$( '#progressWrapper2' ).slideDown();
		$( '#progressWrapper3' ).slideDown();
		
		let html = '';
		html += 'Your website is ready. <br /> ';
		html += 'You should receive an email at: <strong>'+data['client_email']+'</strong> with login details. <br /> ';
		
		html += 'Website: <strong><a target="_blank" href="https://'+data['domain']+'">';
			html += data['domain'];
		html += '</strong></a>';
		
		$( '#progressWrapper2HtmlInject p' ).html( html );
		
	}
	
	
	function setupWpAdminButton(){
		$('#gotoWebsiteBackend').addClass('disabled').attr('disabled', true);
		checkForSiteProgress( true );
	}
	
	
	
	
});

