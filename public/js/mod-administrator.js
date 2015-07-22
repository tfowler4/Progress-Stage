$(document).ready(function() {
	var activePopup;

	var btnPressed;
	var formName;

	$('form').submit(function(event) {
		event.preventDefault();
		


		console.log('WORKING!');

		var formData 	= $(this).serializeArray();
		var id 			= $(this).prop('id').replace('form-', '');
		var formName  	= id.replace('-add', '').ucFirst();

		console.log(formData);
		//console.log(id);
		//console.log(formName);

		$.ajax({
			type: 		'POST',
			url:  		'http://localhost/stage/administrator',
			data: 		{form:formData, request:id},
			encode: 	true,
			success: 	function() {
				$(".overlay").fadeToggle('fast');

				var activeDiv = $('#popup-wrapper');

                activeDiv.toggleClass('centered');
                activeDiv.fadeToggle('fast');
                activeDiv.html(formName +  ' Added Successfully!');
                activePopup = activeDiv;
			}, 
			error: 		function(data) {
				console.log("Error!");
				console.log(data);
			}
		}).done(function(data) {
			console.log("Done!");
			console.log(data);
		});
	});
							
	$('.overlay').click(function() {
        activePopup.fadeToggle('fast');
        activePopup.removeClass('centered');
        activePopup.html('');
        activePopup = '';
        //$(".overlay").fadeToggle('fast');
    });

    $(document).on('click', '.closePopup', function() {
        activePopup.fadeToggle('fast');
        activePopup.removeClass('centered');
        activePopup.html('');
        activePopup = '';
        $(".overlay").fadeToggle('fast');
    });
	
	String.prototype.ucFirst = function() {
    	return this.charAt(0).toUpperCase() + this.substr(1);
	}
});