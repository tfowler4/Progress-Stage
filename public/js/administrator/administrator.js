var Administrator = function() {
    var activeDiv;
    var currentPageUrl;
    // Submit admin forms
    $(document).on('submit', '.admin-form', function() {
        event.preventDefault();

        var formData   = $(this).serializeArray();
        var id         = $(this).prop('id').replace('form-', '');
        currentPageUrl = document.URL;
        //var formName    = id.replace('-', ' ').ucFirst();

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
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
        var guildId    = $(this).val();
        currentPageUrl = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'guild-edit', guild:guildId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-guild-listing');
                activeDiv.html(data);
            },
            error:  function(data) {
                console.log(data);
            }
        });
    });

    // Display tier details from drop down selection
    $(document).on('change', '.admin-select.tier.edit', function() {
        var tierId     = $(this).val();
        currentPageUrl = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'tier-edit', tier:tierId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-tier-listing');
                activeDiv.html(data);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    // Display dungeon details from drop down selection
    $(document).on('change', '.admin-select.dungeon.edit', function() {
        var dungeonId  = $(this).val();
        currentPageUrl = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'dungeon-edit', dungeon:dungeonId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-dungeon-listing');
                activeDiv.html(data);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    // Display encounter details from drop down selection
    $(document).on('change', '.admin-select.encounter.edit', function() {
        var encounterId = $(this).val();
        currentPageUrl  = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'encounter-edit', encounter:encounterId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-encounter-listing');
                activeDiv.html(data);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    // function to capitalize first letter of the form name
    String.prototype.ucFirst = function() {
        return this.charAt(0).toUpperCase() + this.substr(1);
    }
};