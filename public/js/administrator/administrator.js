var Administrator = function() {
    var btnPressed;
    var formName;

    // Submit admin forms
    $(document).on('submit', '.admin-form', function() {
        event.preventDefault();

        var formData    = $(this).serializeArray();
        var id          = $(this).prop('id').replace('form-', '');
        var formName    = id.replace('-add', '').ucFirst();

        $.ajax({
            type:       'POST',
            url:        'http://localhost/stage/administrator',
            data:       {form:formData, request:id},
            encode:     true,
            success:    function() {
                $(".overlay").fadeToggle('fast');
                var activeDiv = $('#popup-wrapper');

                activeDiv.toggleClass('centered');
                activeDiv.fadeToggle('fast');
                activeDiv.html(formName +  ' Added Successfully!');
            }, 
            error:      function(data) {
                console.log(data);
            }
        }).done(function(data) {
            console.log(data);
        });
    });

    // function to capitalize first letter of the form name
    String.prototype.ucFirst = function() {
        return this.charAt(0).toUpperCase() + this.substr(1);
    }
};