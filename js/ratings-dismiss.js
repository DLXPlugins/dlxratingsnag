/**
 * Onload, set up dismiss events. Vanilla JS.
 */
document.addEventListener('readystatechange', function() {
	if (document.readyState !== "complete") {
		return;
	}
	// Set up function to make ajax call.
	function dismissNotice( noticeElement ) {
		// do fetch request to ajax endpoint.
		fetch( ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
			},
			body: 'action=dlx_ratings_nag_dismiss&_ajax_nonce=' + dlxRatingsNag.nonce,
		} )
		.then( function( response ) {
			return response.json();
		} )
		.then( function( json ) {
			if ( json.success ) {
				// Hide the notice.
				noticeElement.style.display = 'none';
			}
		} );
	}

	// Set up button events.
	var ratingNotice = document.querySelector( '.notice-rating' );
	if ( null !== ratingNotice ) {
		// Get the yes button.
		var yesButton = ratingNotice.querySelector( '.yes-button' );
		// Set up event.
		yesButton.addEventListener( 'click', function( event ) {
			dismissNotice( ratingNotice );
		} );
		// Get the no button.
		var noButton = ratingNotice.querySelector( '.dismissable-button' );
		// Set up event.
		noButton.addEventListener( 'click', function( event ) {
			event.preventDefault();
			// Hide the notice.
			dismissNotice( ratingNotice );
		} );
		// Get the dismiss button.
		var dismissButton = ratingNotice.querySelector( '.notice-dismiss' );
		// Set up event.
		dismissButton.addEventListener( 'click', function( event ) {
			event.preventDefault();
			// Hide the notice.
			dismissNotice( ratingNotice );
		} );
	}	
} );

