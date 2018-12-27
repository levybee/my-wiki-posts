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

       var url = "http://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exchars=1000&exlimit=1&continue=&titles=Rob_Holding";
			jQuery.get( url, function( data ) {

           	console.log('Success data', data);

						progressLabel.hide();
				 		progressbar.progressbar('destroy');
					 jQuery('input[type="submit"]' , '#mwp_form').removeAttr('disabled');

		  })
			.done(function(data) {
        console.log('Done data', data);
        // handle success case here
    })
    .fail(function($xhr) {
        console.log('Fail data', $xhr);

				progressLabel.hide();
				progressbar.progressbar('destroy');
			 jQuery('input[type="submit"]' , '#mwp_form').removeAttr('disabled');
        // handle error case here
    });


		// jQuery.ajax({
		//   url: url,
		//   type: "GET",
		//   dataType: "text",
		//   success: function (data, status, jqXHR) {
		//
		// 		console.log('Success data', data);
		// 		progressLabel.hide();
		// 		progressbar.progressbar('destroy');
		// 	 jQuery('input[type="submit"]' , '#mwp_form').removeAttr('disabled');
		//   },
		//   error: function (jqXHR, status, err) {
		// 		console.log('Fail data', jqXHR);
		// 		progressLabel.hide();
		// 		progressbar.progressbar('destroy');
		// 	 jQuery('input[type="submit"]' , '#mwp_form').removeAttr('disabled');
		//   },
		//   complete: function (jqXHR, status) {
		// 		console.log('Complete data', status);
		//
		//   }
		// });


		// ajax_get(url, function(data) {
	 //
		// console.log('Success data', data);
		// progressLabel.hide();
		// progressbar.progressbar('destroy');
	 // jQuery('input[type="submit"]' , '#mwp_form').removeAttr('disabled');
   /// });

		// jQuery.ajax({
		//
		// 	type : "GET",
		// 	dataType : "json",
		// 	url : "http://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exchars=1000&continue=&titles=Rob_Holding",
		// 	success : function(data) {
		//
		// 		progressLabel.hide();
		// 		progressbar.progressbar('destroy');
		// 		// jQuery('#mwp_response').html(data);
		// 		// jQuery('input[type="submit"]', '#mwp_form').removeAttr('disabled');
		//
		// 		console.log('Success data', data);
		//
		// 	},
		// 	error : function(XMLHttpRequest, textStatus,
		// 			errorThrown) {
		//
		// 		console.log("Status: " + textStatus);
		// 		console.log("Error: " + errorThrown);
		//
		// 		 jQuery('input[type="submit"]' , '#mwp_form').removeAttr('disabled');
		// 	}
		//
		// });


   });



	 function ajax_get(url, callback) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            try {
                var data = xmlhttp.responseText;

            } catch(err) {
                console.log(err.message + " in " + xmlhttp.responseText);
                return;
            }
            callback(data);
        }
    };

    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}




});
