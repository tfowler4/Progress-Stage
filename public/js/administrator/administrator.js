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
            data:    {request: 'guild-edit', 'adminpanel-guild':guildId},
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
            data:    {request: 'tier-edit', 'adminpanel-tier':tierId},
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
            data:    {request: 'dungeon-edit', 'adminpanel-dungeon':dungeonId},
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
            data:    {request: 'encounter-edit', 'adminpanel-encounter':encounterId},
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
    $(document).on('change', '#admin-select-edit-kill', function() {
        var guildId = $(this).val();
        currentPageUrl  = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'kill-edit-listing', 'adminpanel-guild':guildId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-edit-kill-guild-listing');
                activeDiv.html(data);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    // Display encounter details from drop down selection
    $(document).on('change', '#kill-edit-encounter', function() {
        var encounterId = $(this).val();
        currentPageUrl  = document.URL;

        var form    = $(this).closest('form');
        var guildId = form.find("input[name=guild-id]").val();

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'kill-edit-listing', 'guild-id':guildId, 'edit-kill-encounter-id':encounterId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-edit-kill-encounter-listing');
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

        tinyMCE.execCommand('mceRemoveEditor', false, 'edit-article'); 

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    {request: 'article-edit', article:articleId},
            encode:  true,
            success: function(data) {
                activeDiv = $('#admin-article-listing');
                activeDiv.html(data);

                tinyMCE.execCommand('mceAddEditor', false, 'edit-article'); 
            },
            error: function(data) {
                console.log(data);
            }
        });
    });
};