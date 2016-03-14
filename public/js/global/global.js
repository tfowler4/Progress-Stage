// global event binder
var GlobalEventBinder = function() {
    var activePopup;

    // on user selecting a guild logo image, display a preview of the image
    $(document).on('change', '#user-form-guild-logo', function() { changeGuildLogo(this); });
    var changeGuildLogo = function(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var imgSrc = e.target.result;

                $('#guild-logo-preview').html('<img class="img-responsive" id="guild-logo" src="' + imgSrc + '">');
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // on user selecting a kill screenshot image, display a preview of the image
    $(document).on('change', '#user-form-screenshot, #user-form-popup-screenshot', function() { changeScreenshot(this); });
    var changeScreenshot = function(input) {
        if ( input.files && input.files[0] ) {
            var reader = new FileReader();
            var id     = input.id;

            reader.onload = function (e) {
                var imgSrc = e.target.result;

                if ( id == 'user-form-popup-screenshot' ) { $('#popup-screenshot-preview').html('<img class="img-responsive" src="' + imgSrc + '">'); }
                if ( id == 'user-form-screenshot' ) { $('#screenshot-preview').html('<img class="img-responsive" src="' + imgSrc + '">'); }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // on user selecting a faction, fade in the selected faction and fade out other factions
    $(document).on('change', '#user-form-faction', function() { changeFactionLogo(this); });
    var changeFactionLogo = function(input) {
        var faction = input.value.toLowerCase();

        $('#faction-logo-preview-wrapper').children().fadeTo('fast', .3, function() {
            if ( faction != '' ) {

                $('.' + faction).fadeTo('fast', 1);
            }
        });
    }

    // on user selecting a country, display a preview of the country flag image
    $(document).on('change', '#user-form-country', function() { changeCountryFlag(this); });
    var changeCountryFlag = function(input) {
        var country = input.value.toLowerCase().replace(' ', '_');

        if ( country != '' ) {
            var dir = getFlagLargeDirectory();
            var imgSrc = dir + country + '.png';

            $('#country-flag-preview').html('<img class="img-responsive" id="country-flag" src="' + imgSrc + '">');
        } else {
            $('#country-flag-preview').html('');
        }
    }

    // on user selecting a guild, send ajax call to fetch avaialble encounters to submit
    $(document).on('change', '#user-form-guild, #user-form-popup-guild, #quick-form-guild', function() { updateGuildEncounters(this); });
    var updateGuildEncounters = function(input) {
        var currentPageUrl = document.URL;
        var guildId        = input.value;
        var elementId      = input.id;

        // ajax call to retrieve new encounter dropdown select html
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'encounterList', guild: guildId},
            success: function(data) {
                var selectElement;

                if ( elementId == 'user-form-popup-guild' ) { selectElement = $('#user-form-popup-encounter') }
                if ( elementId == 'user-form-guild' ) { selectElement = $('#user-form-encounter') }
                if ( elementId == 'quick-form-guild' ) { selectElement = $('#quick-form-encounter') }

                $("#" + elementId + " option[value='']").remove();

                selectElement.html(data);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    }

    // on click of submit on search modal
    $(document).on('click touchstart', '#search-form-submit', function() {
        var currentPageUrl = document.URL;
        var searchTerm     = $('#search-input').val();

        // Ajax call to retrieve guild search html
        $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'search', queryTerm: searchTerm, formId: 'search'},
            success: function(data) {
                var searchResultsDiv = $('#search-results');
                searchResultsDiv.html(data);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    });

    // on click of "Add New Video" text link, a new 
    $(document).on('click touchstart', '.new-video-link', function(event) {
        event.preventDefault();

        var videoLinkContainer     = $(this).parent().find('.video-link-container');
        var numOfVideoLinkElements = videoLinkContainer.find('.video-link-wrapper').length;
        var videoLinkNum           = numOfVideoLinkElements + 1;

        // maximum number of video links is 10 currently
        if ( numOfVideoLinkElements == 10 ) { return; }

        // if previous video url is empty, do not add a new pane
        if ( videoLinkNum > 1 ) {
            var previousNum = videoLinkNum - 1;
            var previousUrl = $('#user-form-video-url-' + previousNum).val();

            // loop through all previous url/title text fields to see if any are empty before adding a new video link
            for ( var linkNum = previousNum; previousNum > 0; previousNum-- ) {
                var previousTitle = $('#user-form-video-title-' + linkNum).val();
                var previousUrl = $('#user-form-video-url-' + linkNum).val();

                if ( previousUrl == '' || previousTitle == '' ) {
                    return;
                }
            }
        }

        var html  = '<div class="video-link-wrapper">';
            html += '<div class="form-group">';
                html += '<label for="" class="control-label col-lg-2 col-md-2 col-sm-12 col-xs-12">' + 'Video #' + videoLinkNum + '</label>';
                html += '<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">';
                    html += '<div class="input-group">';
                        html += '<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-film"></span></span>';
                        html += '<input type="text" class="form-control"  id="user-form-video-title-' + videoLinkNum + '" name="video-link-title[]" placeholder="Notes">';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
            html += '<div class="form-group">';
                html += '<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">';
                    html += '<div class="input-group">';
                        html += '<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-globe"></span></span>';
                        html += '<input type="text" class="form-control" id="user-form-video-url-' + videoLinkNum + '" name="video-link-url[]" placeholder="Video URL">';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
            html += '<div class="form-group">';
                html += '<label for="" class="control-label col-lg-2 col-md-2 col-sm-12 col-xs-12">Type</label>';
                html += '<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">';
                    html += '<select id="user-form-video-type-' + videoLinkNum + '" name="video-link-type[]" class="form-control">';
                        html += '<option value="0">General Kill</option>';
                        html += '<option value="1">Encounter Guide</option>';
                    html += '</select>';
                html += '</div>';
            html += '</div>';
        html += '</div><br>';

        videoLinkContainer.append(html);
    });

    // get large flag image directory to return for country preview
    var getFlagLargeDirectory = function() {
        var href              = window.location.href;
        var fullAddressArray  = href.split('//');
        var paramAddressArray = fullAddressArray[1].split('/');
        var rootDir           = 'http://' + paramAddressArray[0];

        if ( paramAddressArray[1] == 'stage' || paramAddressArray[1] == 'bootstrap-test' ) {
            rootDir += '/' + paramAddressArray[1];
        }

        rootDir += '/public/images/flags/large/';

        return rootDir;
    }

    // on click of modal activator link to process html before displaying modal
    $(document).on('click touchstart', '.modal-activator', function() {
        var currentPageUrl = document.URL;
        var modalId        = $(this).attr('data-target');
        var modal          = modalId.replace("#", "");

        var modalWrapper = $('#modal-wrapper');
        var modalBackdrop = $('.modal-backdrop');
        modalWrapper.empty();
        modalBackdrop.remove();

        // Ajax call to retrieve guild modal html
        $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'modal', formId: modal },
            success: function(data) {
                modalWrapper.html(data);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            },
            complete: function(data) {
                $(modalId).modal("show");
            }
        });
    });

    // on user clicking 'View' for videos to fetch all videos for that encounter/guild
    $(document).on('click touchstart', '.video-activator', function() {
        event.preventDefault();

        var currentPageUrl   = document.URL;
        var modalWrapper     = $('#modal-wrapper');
        var modalBackdrop    = $('.modal-backdrop');
        modalWrapper.empty();
        modalBackdrop.remove();

        var guildId        = $(this).attr('data-guild');
        var encounterId    = $(this).attr('data-encounter');

        // ajax call to retrieve new video list html
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'modal', formId: 'videoModal'},
            success: function(html) {
                modalWrapper.html(html);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            },
            complete: function(data) {
                $('#videoModal').modal("show");

                $.ajax({
                    url: currentPageUrl,
                    type: 'POST',
                    data: { request: 'videoList', guild: guildId, encounter: encounterId},
                    success: function(data) {
                        var spreadsheetDiv = $('#videoModal .modal-body');
                        spreadsheetDiv.empty();
                        spreadsheetDiv.html(data);
                    },
                    error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                    }
                });
            }
        });
    });

    // on click of "View as Spreadsheet" button, make ajax call to display spreadsheet popup
    $(document).on('click touchstart', '.spreadsheet', function(event) {
        event.preventDefault();

        var currentPageUrl   = document.URL;
        var dungeonId        = $(this).prop('id');
        var modalWrapper     = $('#modal-wrapper');
        var modalBackdrop    = $('.modal-backdrop');
        modalWrapper.empty();
        modalBackdrop.remove();

        // Ajax call to get the modal html
        $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'modal', formId: 'spreadsheetModal' },
            success: function(html) {
                modalWrapper.html(html);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            },
            complete: function(data) {
                $('#spreadsheetModal').modal("show");

                $.ajax({
                    url: currentPageUrl,
                    type: 'POST',
                    data: { request: 'spreadsheet', dungeon: dungeonId},
                    success: function(data) {
                        var spreadsheetDiv = $('#spreadsheetModal .modal-body');
                        spreadsheetDiv.empty();
                        spreadsheetDiv.html(data);
                    },
                    error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                    }
                });
            }
        });
    });
}; 