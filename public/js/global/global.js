// global event binder
var GlobalEventBinder = function() {
    var activePopup;

    // on user selecting a guild logo image, display a preview of the image
    $(document).on('change', '.form-guild-logo', function() { changeGuildLogo(this); });
    var changeGuildLogo = function(input) {
        var dataType = input.dataset.type;
        var dataId;

        if ( input.hasAttribute('data-id') ) {
            dataId = input.dataset.id;
        }

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var imgSrc = e.target.result;

                if ( dataId ) {
                    $('.guild-logo-preview[data-type="' + dataType + '"][data-id="' + dataId + '"]').html('<img class="img-responsive" src="' + imgSrc + '">');
                } else {
                    $('.guild-logo-preview[data-type="' + dataType + '"]').html('<img class="img-responsive" src="' + imgSrc + '">');
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // on user selecting a kill screenshot image, display a preview of the image
    $(document).on('change', '.form-screenshot', function() { changeScreenshot(this); });
    var changeScreenshot = function(input) {
        var dataType = input.dataset.type;
        var dataId;

        if ( input.hasAttribute('data-id') ) {
            dataId = input.dataset.id;
        }

        if ( input.files && input.files[0] ) {
            var reader = new FileReader();
            var id     = input.id;

            reader.onload = function (e) {
                var imgSrc = e.target.result;
                var html   = '<img class="img-responsive" src="' + imgSrc + '">';

                if ( dataId ) {
                    $('.screenshot-preview[data-type="' + dataType + '"][data-id="' + dataId + '"]').html(html);
                } else {
                    $('.screenshot-preview[data-type="' + dataType + '"]').html(html);
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // on user selecting a faction, fade in the selected faction and fade out other factions
    $(document).on('change', '.form-faction', function() { changeFactionLogo(this); });
    var changeFactionLogo = function(input) {
        var faction  = input.value.toLowerCase();
        var dataType = input.dataset.type;
        var dataId;

        if ( input.hasAttribute('data-id') ) {
            dataId = input.dataset.id;
        }

        var selector;

        if ( dataId ) {
            selector = '.faction-logo-preview-wrapper[data-type="' + dataType + '"][data-id="' + dataId + '"]';
        } else {
            selector = '.faction-logo-preview-wrapper[data-type="' + dataType + '"]';
        }

        $(selector).children().fadeTo('fast', .3, function() {
            if ( faction != '' && $(this).hasClass(faction)) {

                $(this).fadeTo('fast', 1);
            }
        });
    }

    // on user selecting a country, display a preview of the country flag image
    $(document).on('change', '.form-country', function() { changeCountryFlag(this); });
    var changeCountryFlag = function(input) {
        var country  = input.value.toLowerCase().replace(' ', '_');
        var dataType = input.dataset.type;
        var html     = '';
        var dataId;

        if ( input.hasAttribute('data-id') ) {
            dataId = input.dataset.id;
        }

        if ( country != '' ) {
            var dir    = getFlagLargeDirectory();
            var imgSrc = dir + country + '.png';
            html = '<img class="img-responsive" src="' + imgSrc + '">';
        }

        if ( dataId ) {
            $('.country-flag-preview[data-type="' + dataType + '"][data-id="' + dataId + '"]').html(html);
        } else {
            $('.country-flag-preview[data-type="' + dataType + '"]').html(html);
        }
    }

    // on user selecting a guild, send ajax call to fetch avaialble encounters to submit
    $(document).on('change', '.form-guild', function() { updateGuildEncounters(this); });
    var updateGuildEncounters = function(input) {
        var currentPageUrl = document.URL;
        var guildId        = input.value;
        var elementId      = input.id;
        var dataType       = input.dataset.type;

        // ajax call to retrieve new encounter dropdown select html
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'encounterList', guild: guildId},
            success: function(data) {
                $(".form-encounter[data-type='" + dataType + "'] option[value='']").remove();
                $(".form-encounter[data-type='" + dataType + "']").html(data)
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    }

    // get search results from navbar search modal
    $(document).on('submit','#search-form', function(event) {
        event.preventDefault();
        event.stopPropagation();

        getSearchResults();
    });
    var getSearchResults = function() {
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

        return false;
    }

    // on click of submit on search modal
    $(document).on('click touchstart tap', '#search-form-submit', function() {
        getSearchResults();
    });

    // on click of "Add New Video" text link, a new 
    $(document).on('click touchstart tap', '.new-video-link', function(event) {
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
                html += '<label for="" class="control-label col-lg-3 col-md-12 col-sm-12 col-xs-12">' + 'Video #' + videoLinkNum + '</label>';
            html += '</div>';
            html += '<div class="form-group">';
                html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
                    html += '<div class="input-group">';
                        html += '<span class="input-group-addon"><span class="glyphicon glyphicon-film"></span></span>';
                        html += '<input type="text" class="form-control"  id="user-form-video-title-' + videoLinkNum + '" name="video-link-title[]" placeholder="Notes">';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
            html += '<div class="form-group">';
                html += '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
                    html += '<div class="input-group">';
                        html += '<span class="input-group-addon"><span class="glyphicon glyphicon-globe"></span></span>';
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
    $(document).on('click touchstart tap', '.modal-activator', function(event) {
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
                $(modalId).modal("toggle");
            }
        });

        event.preventDefault();
        event.stopPropagation();
    });

    // on user clicking 'View' for videos to fetch all videos for that encounter/guild
    $(document).on('click touchstart tap', '.video-activator', function() {
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
    $(document).on('click touchstart tap', '.spreadsheet', function(event) {
        event.preventDefault();

        var currentPageUrl   = document.URL;
        var dungeonId        = $(this).data('dungeon-id');
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

    $(document).on('touchstart tap', 'li.dropdown-first-level a.tab-index', function(event) {
        event.preventDefault();
        event.stopPropagation();

        if ( $(this).prop('tabIndex').length == 0 ) {
            console.log("length is 0");
            return false;
        }

        console.log('first level');

        // checking all other dropdowns
        $(this).parent().parent().find('ul.dropdown-menu').css('display', 'none');

        
        if ( $(this).parent().children('ul.dropdown-menu').length > 0 ) {
            $(this).parent().children('ul.dropdown-menu').css('display', 'block');
        }
    });

    $(document).on('touchstart tap', 'li.dropdown-link', function(event) {
        event.preventDefault();
        event.stopPropagation();

        // get link value
        var href = $(this).children('a').prop("href");
        window.location.href = href;
    });

    $(document).on('touchstart tap', 'li.dropdown-submenu', function(event) {
        event.preventDefault();
        event.stopPropagation();

        // get link value
        var href = $(this).find('a').prop("href");
        var isLinkValid = false;

        // check if link is  valid location
        if ( href.indexOf("#") == -1 ) {
            isLinkValid = true;
        }

        if ( $(this).find('ul.dropdown-menu').length > 0 ) {
            if ( $(this).children('ul.dropdown-menu').css('display') != 'block' ) {
                
                $(this).children('ul.dropdown-menu').css('display', 'block');
            } else {
                if ( isLinkValid ) {
                    window.location.href = href;
                } else {
                    $(this).children('ul.dropdown-menu').css('display', 'none');
                }
            }
        } else {
            if ( isLinkValid ) {
                window.location.href = href;
            }
        }
    });
};