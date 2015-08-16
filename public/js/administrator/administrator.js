var Administrator = function() {
    var id;
    var activeDiv;
    // Submit admin forms
    $(document).on('submit', '.admin-form', function() {
        event.preventDefault();

        var formData = $(this).serializeArray();
        id           = $(this).prop('id').replace('form-', '');
        //var formName    = id.replace('-', ' ').ucFirst();

        console.log(id);
        $.ajax({
            type:    'POST',
            url:     'http://localhost/stage/administrator',
            data:    {form:formData, request:id},
            encode:  true,
            success: function(data) {
                console.log(data);
                    activeDiv = $('#admin-guild-listing');
                    activeDiv.html(data);
            }, 
            error:  function(data) {
                console.log(data);
            }
        });
    });

    $(document).on('change', '.admin-select.guild.edit', function() {
        var guildId = this.value;
    });

    // function to capitalize first letter of the form name
    String.prototype.ucFirst = function() {
        return this.charAt(0).toUpperCase() + this.substr(1);
    }
};