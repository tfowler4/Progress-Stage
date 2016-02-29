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

                $('#guild-logo-preview').html('<img id="guild-logo" src="' + imgSrc + '">');
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

                if ( id == 'user-form-popup-screenshot' ) { $('#popup-screenshot-preview').html('<img class="screenshot-medium" src="' + imgSrc + '">'); }
                if ( id == 'user-form-screenshot' ) { $('#screenshot-preview').html('<img class="screenshot-large" src="' + imgSrc + '">'); }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // on user selecting a faction, fade in the selected faction and fade out other factions
    $(document).on('change', '#user-form-faction', function() { changeFactionLogo(this); });
    var changeFactionLogo = function(input) {
        var faction = input.value.toLowerCase();

        $('#faction-logo-preview-wrapper').children().fadeTo('fast', .3); //addClass('faded');
        
        if ( faction != '' ) {
            $('.' + faction).fadeTo('fast', 1); //removeClass('faded');
        }
    }

    // on user selecting a country, display a preview of the country flag image
    $(document).on('change', '#user-form-country', function() { changeCountryFlag(this); });
    var changeCountryFlag = function(input) {
        var country = input.value.toLowerCase().replace(' ', '_');

        if ( country != '' ) {
            var dir = getFlagLargeDirectory();
            var imgSrc = dir + country + '.png';

            $('#country-flag-preview').html('<img id="country-flag" src="' + imgSrc + '">');
        } else {
            $('#country-flag-preview').html('');
        }
    }

    // on user selecting a guild, send ajax call to fetch avaialble encounters to submit
    $(document).on('change', '#user-form-guild, #user-form-popup-guild', function() { updateGuildEncounters(this); });
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

                $("#" + elementId + " option[value='']").remove();

                selectElement.html(data);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    }

    // on user clicking 'View' for videos to fetch all videos for that encounter/guild
    $(document).on('click touchstart', '.video-activator', function() {
        $(".overlay").fadeToggle('fast');

        var currentPageUrl = document.URL;
        var guildId        = $(this).attr('data-guild');
        var encounterId    = $(this).attr('data-encounter');

        // ajax call to retrieve new video list html
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'videoList', guild: guildId, encounter: encounterId},
            success: function(data) {
                var activeDiv = $('#popup-wrapper');

                activeDiv.toggleClass('centered');
                activeDiv.fadeToggle('fast');
                activeDiv.html(data);
                activePopup = activeDiv;
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    });

    // bind searchGuilds event to textbox submit and image click to display guild search results
    var searchGuilds = function(event) {
        event.preventDefault();

        var currentPageUrl = document.URL;
        var searchTerm     = $('#search-input').val();

        $(".overlay").fadeToggle('fast');

        // Ajax call to retrieve guild search html
        $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'search', queryTerm: searchTerm, formId: 'search'},
            success: function(data) {
                var searchResultsDiv = $('#popup-wrapper');

                searchResultsDiv.toggleClass('centered');
                searchResultsDiv.fadeToggle('fast');
                searchResultsDiv.html(data);

                // To help resizing with vertical scrollbar
                var currentWidth = parseInt(searchResultsDiv.find('div').css('width').replace('px', ''));
                var newWidth     = currentWidth + 50;

                searchResultsDiv.find('div').css('width', newWidth);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    };
    $(document).on('submit', '#search-form', searchGuilds);
    $(document).on('click touchstart', '#search-activator', searchGuilds);

    // on user selecting a guild logo image, display a preview of the image
    $(document).on('click touchstart', '.closePopup', function() {
        activePopup.fadeToggle('fast');
        activePopup.removeClass('centered');
        activePopup.html('');
        activePopup = '';
        $(".overlay").fadeToggle('fast');
    });

    // on click of activatePopup class elements, display popup screen
    $(document).on('click touchstart', '.activatePopUp', function() {
        $(".overlay").fadeToggle('fast');

        var currentPageUrl = document.URL;
        var id             = $(this).attr('id').replace('-activator', '');
        var popupId        = id + '-popup';

        // Ajax Call for Forms
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'form', formId: id},
            success: function(data) {
                var activeDiv = $('#popup-wrapper');

                activeDiv.toggleClass('centered');
                activeDiv.fadeToggle('fast');
                activeDiv.html(data);
                activePopup = activeDiv;
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });

        if ( $('#' + popupId) != undefined ) {
            activePopup = $('#' + popupId);

            $('#' + popupId).toggleClass('centered');
            $('#' + popupId).fadeToggle('fast');
        }
    });

    // on click of the overlay, remove the overlay and popup screen
    $(document).on('click touchstart', '.overlay', function() {
        //Temporary
        if ( !activePopup || activePopup.length === 0 ) {
            activePopup = $('#popup-wrapper');
        }

        activePopup.fadeToggle('fast');
        activePopup.removeClass('centered');
        activePopup.html('');
        activePopup = '';
        $(".overlay").fadeToggle('fast');
    });

    // on click of "View as Spreadsheet" button, make ajax call to display spreadsheet popup
    $(document).on('click touchstart', '.spreadsheet', function(event) {
        event.preventDefault();

        var currentPageUrl = document.URL;
        var dungeonId      = $(this).prop('id');

        //$(".overlay").fadeToggle('fast');

        // Ajax call to retrieve spreadsheet html
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'spreadsheet', dungeon: dungeonId},
            success: function(data) {
                var spreadsheetDiv = $('#spreadsheetModal .modal-body');

                //spreadsheetDiv.toggleClass('centered');
                //spreadsheetDiv.fadeToggle('fast');
                spreadsheetDiv.html(data);

                // To help resizing with vertical scrollbar
                //var currentWidth = parseInt(spreadsheetDiv.find('div').css('width').replace('px', ''));
                //var newWidth     = currentWidth + 50;

                //spreadsheetDiv.find('div').css('width', newWidth);
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
            html += 'Video #' + videoLinkNum + '<br>';
            html += '<div>';
            html += '<label class="video-link-label">Notes: </label>';
            html += '<input id="user-form-video-title-' + videoLinkNum + '" type="text" name="video-link-title[]" class="width-200" />';
            html += '</div>';
            html += '<div>';
            html += '<label class="video-link-label">URL: </label>';
            html += '<input id="user-form-video-url-' + videoLinkNum + '" type="text" name="video-link-url[]" class="width-200" />';
            html += '</div>';
            html += '<div>';
            html += '<label class="video-link-label">Type: </label>';
            html += '<select id="user-form-video-type-' + videoLinkNum + '" name="video-link-type[]">';
            html += '<option value="0">General Kill</option>';
            html += '<option value="1">Encounter Guide</option>';
            html += '</select>';
            html += '</div>';
            html += '</div>';

        videoLinkContainer.append(html);
    });

    // help keep popup menu centered if window gets resized
    $(window).resize(function() {
        $('.centered').css({
            position:'absolute',
            left: ($(window).width() - $('.centered').outerWidth())/2,
            top: ($(window).height() - $('.centered').outerHeight())/2
        });
    });

    // get large flag image directory to return for country preview
    var getFlagLargeDirectory = function() {
        var href              = window.location.href;
        var fullAddressArray  = href.split('//');
        var paramAddressArray = fullAddressArray[1].split('/');
        var rootDir           = 'http://' + paramAddressArray[0];

        if ( paramAddressArray[1] == 'stage' ) {
            rootDir += '/' + paramAddressArray[1];
        }

        rootDir += '/public/images/flags/large/';

        return rootDir;
    }

    // update site skin via select dropbox on footer of page
    $(document).on('change', '#skin-selector', function() { updateSiteSkin($('#skin-selector')); });
    var updateSiteSkin = function(input) {
        var currentPageUrl = document.URL;
        var skinValue      = input.val();

        console.log(input);
        console.log(skinValue);
        // ajax call to set session value and reload page
        $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'siteSkin', skinValue: skinValue},
            success: function(data) {
                location.reload();
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    }
};