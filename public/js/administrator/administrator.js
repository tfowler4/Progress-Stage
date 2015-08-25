var Administrator = function() {
    var activeDiv;
    var currentPageUrl;

    // Submit admin forms
    $(document).on('submit', '.admin-form', function(event) {
        event.preventDefault();

        var form       = $(this).closest('form');
        var id         = $(this).prop('id').replace('form-', '');
        var formData   = $(this).serialize() + '&request=' + id;
        currentPageUrl = document.URL;

        $.ajax({
            type:    'POST',
            url:     currentPageUrl,
            data:    formData,
            encode:  true,
            success: function(data) {
                //console.log(data);
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