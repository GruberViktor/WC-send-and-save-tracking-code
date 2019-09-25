jQuery(document).ready(function($) { 

	$(document).on('submit','#trackingmailform', (function(e) {
	
	e.preventDefault();

	var data = {
	    action: 'trackingmail',
		code: $('#code').val(),
		order_id: $('#order_id').val()
	}

	jQuery.post(window.location.origin + "/wp-admin/admin-ajax.php", data, function(response) {
	    $("#trackingmailform").after( response );
		});


	}));
	 });
