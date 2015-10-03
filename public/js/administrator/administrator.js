var Administrator = function() {
    var activeDiv;
    var currentPageUrl;

    // Submit admin forms
    $(document).on('submit', '.admin-form', function(event) {
        event.preventDefault();

        var formData = new FormData();

        var form       = $(this).closest('form');
        var id         = $(this).prop('id').replace('form-', '');
        currentPageUrl = document.URL;

        var data = $(this).serializeArray();
        $.each(data, function(key, input) {
            formData.append(input.name, input.value);
        });

        if ( form.find("input[name=screenshot]").length > 0 ) {
            var screenshot = form.find("input[name=screenshot]")[0].files[0];
            formData.append('screenshot', screenshot);
        }

        formData.append('request', id);

        console.log(formData);

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    formData,
            encode:  true,
            contentType: false,
            processData: false,
            success: function(data) {
                console.log(data);
            },
            error:  function(data) {
                console.log('ERROR');
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

    // Display guild details from drop down selection
    $(document).on('change', '.admin-select.kill.remove', function() {
        var guildId = $(this).val();
        currentPageUrl  = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'kill-remove-listing', 'guild-id':guildId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-remove-kill-listing');
                activeDiv.html(data);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    // Display article details from drop down selection
    $(document).on('change', '.admin-select.article.edit', function() {
        var articleId = $(this).val();
        currentPageUrl  = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'article-edit', article:articleId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-article-listing');
                activeDiv.html(data);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });
};