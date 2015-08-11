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
    $(document).on('change', '#user-form-screenshot', function() { changeScreenshot(this); });
    var changeScreenshot = function(input) {
        if ( input.files && input.files[0] ) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var imgSrc = e.target.result;

                $('#screenshot-preview').html('<img class="screenshot-large" src="' + imgSrc + '">');
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

        $(".overlay").fadeToggle('fast');

        // Ajax call to retrieve spreadsheet html
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'spreadsheet', dungeon: dungeonId},
            success: function(data) {
                var spreadsheetDiv = $('#popup-wrapper');

                spreadsheetDiv.toggleClass('centered');
                spreadsheetDiv.fadeToggle('fast');
                spreadsheetDiv.html(data);

                // To help resizing with vertical scrollbar
                var currentWidth = parseInt(spreadsheetDiv.find('div').css('width').replace('px', ''));
                var newWidth     = currentWidth + 50;

                spreadsheetDiv.find('div').css('width', newWidth);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
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
};