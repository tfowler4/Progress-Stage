var Administrator = function() {
    // Submit admin forms
    $(document).on('submit', '.admin-form', function() {
        event.preventDefault();

        var formData       = $(this).serializeArray();
        var id             = $(this).prop('id').replace('form-', '');
        var currentPageUrl = document.URL;
        //var formName    = id.replace('-', ' ').ucFirst();

        $.ajax({
            type:    'POST',
            url:     'http://localhost/stage/administrator',
            data:    {form:formData, request:id},
            encode:  true,
            success: function(data) {
                console.log(data);
            },
            error:  function(data) {
                console.log(data);
            }
        });
    });

    // Display guild details from drop down selection
    $(document).on('change', '.admin-select.guild.edit', function() {
        var guildId        = $(this).val();
        var currentPageUrl = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'guild-edit', guild:guildId},
            encode:  true,
            success: function(data) {
                console.log(data);
                    var activeDiv = $('#admin-guild-listing');
                    activeDiv.html(data);
            },
            error:  function(data) {
                console.log(data);
            }
        });
    });

    // Display tier details from drop down selection
    $(document).on('change', '.admin-select.tier.edit', function() {
        var tierId         = $(this).val();
        var currentPageUrl = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'tier-edit', tier:tierId},
            encode:  true,
            success: function(data) {
                console.log(data);
                    var activeDiv = $('#admin-tier-listing');
                    activeDiv.html(data);
            },
            error:  function(data) {
                console.log(data);
            }
        });
    });

    // function to capitalize first letter of the form name
    String.prototype.ucFirst = function() {
        return this.charAt(0).toUpperCase() + this.substr(1);
    }
};