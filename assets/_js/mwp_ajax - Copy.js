jQuery(document).ready(function(){


	jQuery( "#mwp_form" ).on( "submit", function(event) {

	    event.preventDefault();
		var form = jQuery(this);

		jQuery('input[type="submit"]', '#mwp_form').attr('disabled','disabled');

		progressLabel = jQuery( ".progress-label" );
		progressbar = jQuery( "#progressbar" );

		progressbar.progressbar({
		  value: false
		});

		progressLabel.show();

		var dataString = {
			   secure: mwpAjax.ajaxnonce,
			   action: "mwp_ajax_update",
			   postdata: form.serialize()
			}

		jQuery.ajax({

			type : "POST",
			data :  dataString,
			dataType : "html",
			url : mwpAjax.ajaxurl,
			success : function(data) {

				progressLabel.hide();
				progressbar.progressbar('destroy');
				jQuery('#mwp_response').html(data);
				jQuery('input[type="submit"]', '#mwp_form').removeAttr('disabled');

			},
			error : function(XMLHttpRequest, textStatus,
					errorThrown) {

				console.log("Status: " + textStatus);
				console.log("Error: " + errorThrown);

				 jQuery('input[type="submit"]' , '#mwp_form').removeAttr('disabled');
			}

		});


   });



});
