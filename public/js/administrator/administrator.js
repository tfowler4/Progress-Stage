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
        var guildId        = $(this).val();
        var currentPageUrl = document.URL;

        // ajax call to retrieve new encounter dropdown select html
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'guild-edit', guild: guildId},
            success: function(data) {
                var guildDiv = $('#admin-guild-listing');

                guildDiv.html(data);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });

        console.log('Guild ID: '+guildId);
    });

    // function to capitalize first letter of the form name
    String.prototype.ucFirst = function() {
        return this.charAt(0).toUpperCase() + this.substr(1);
    }
};