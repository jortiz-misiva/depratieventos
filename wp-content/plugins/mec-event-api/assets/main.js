jQuery(document).ready(function($) {

	function mecExternalAddDomainRow(){
		$('#tmpl-mec-add-row').tmpl({'id':Math.random()}).appendTo('#mec-external-sites');
	}

	mecExternalAddDomainRow();


	$('#mec-external-addrow').click(function(event) {
		mecExternalAddDomainRow();
	});


	$("#col-container").on('click', '.mec-delrow',function () {

		var delid = $(this).attr('data-delid');
		if(delid && delid != ''){
			var r = confirm("Are Youe sure, Delete?");
			if (r == true) {

                var dataReq = {
                    'id': delid,
                    'action': 'mec_external_delsite',
                };


                $.ajax({
                        url: window.ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        'data': dataReq,
                        timeout: 10000
                    })
                    .done(function(ret) {
                        if (ret && ret.success == true) {
                        	var el=$(this).closest('p');
							el.remove();
							return true;
                        }
                    })
                    .fail(function(ret) {
                        console.log(ret);
                    });

			} else{
				return false;
			}
		}


		var el=$(this).closest('p');
		console.log(el);
		el.remove();
	});
});