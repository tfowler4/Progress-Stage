$(document).ready(function(){
    var activePopup;

    $("#register-guild-logo").change(function(){
        changeGuildLogo(this);
    });

    $("#register-faction").change(function(){
        changeFactionLogo(this);
    });

    $("#register-country").change(function(){
        changeCountryFlag(this);
    });

    $(window).bind('scroll', function() {
        if ($(window).scrollTop() > 0) { //150
            $('#menu-wrapper').addClass('fixed');
        } else {
            $('#menu-wrapper').removeClass('fixed');
        }
    });

    $(document).on('click', '.closePopup', function() {
        activePopup.fadeToggle('fast');
        activePopup.removeClass('centered');
        activePopup.html('');
        activePopup = '';
        $(".overlay").fadeToggle('fast');
    });

    $('.activatePopUp').click(function() {
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

    $('.overlay').click(function() {
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

    $('.spreadsheet').click(function(event){
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
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    });

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

    var changeFactionLogo = function(input) {
        var faction = input.value.toLowerCase();

        $('#faction-logo-preview').children().fadeTo('fast', .3); //addClass('faded');
        
        if ( faction != '' ) {
            $('.' + faction).fadeTo('fast', 1); //removeClass('faded');
        }
    }

    var getFlagLargeDirectory = function() {
        var href = window.location.href;
        var addressArray = href.split('/');

        addressArray.pop();
        addressArray.pop();
        
        var rootDir = addressArray.join('/');
        rootDir += '/public/images/flags/large/';

        return rootDir;
    }
});