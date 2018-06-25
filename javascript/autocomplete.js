var currentAutocomplete = null;
$(document).on('focus', '.autocomplete',
	function () {
		if (currentAutocomplete != null && currentAutocomplete != this) {
			$(currentAutocomplete).autocomplete('destroy');
		}
		$('.autocomplete').autocomplete({
							minLength: 2,
							source:
								function (request, response) {
									var data = [];
									$.ajax ({
										url: 'switch_sank.php',
										type: 'POST',
										dataType: 'json',
										data: {action: 'autocomplete', autocomplete: request.term},
										success: 
											function (data) {	
												
												response($.map(data,
													function (n, i) {
														
														return{label: n.cl_imeprezime, value: n.cl_imeprezime + '-' + n.cl_broj};
													}
												));
											}

									});
									response(data);
								}
							
						});
		currentAutocomplete = this;
	}
);