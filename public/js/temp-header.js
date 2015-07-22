$(document).ready(function(){
    var activePopup;

    var numOfRecentItems  = Math.ceil($("#latest-kills  ul li").length / 8);
    var recentSlideDelay  = 500;
    var recentSlideWidth  = 1198;
    var maxRecentPaneSize = numOfRecentItems * recentSlideWidth;

    var numOfMediaItems   = $("#media-pane  ul li").length;
    var mediaSlideDelay   = 500;
    var mediaSlideWidth   = 788;
    var maxMediaPaneSize  = numOfMediaItems * mediaSlideWidth;

    var stopClick = false;

    $('.scroll-button-recent').click(function() {
        if( stopClick ) { return; }

        var pos = $('#latest-kills ul').css("left").replace("px", "");
        var direction;

        if ( $(this).hasClass('left') ) {
            direction = 'left';
            pos       = parseInt(pos) + recentSlideWidth + 2;
        } else if ( $(this).hasClass('right') ) {
            direction = 'right';
            pos       = parseInt(pos) - recentSlideWidth - 2;
        }

        if ( (direction == 'left' && parseInt(pos) <= 0) 
             || (direction == 'right' && pos > (-1*maxRecentPaneSize) ) ) {
            stopClick = true; 
            $('#latest-kills ul').animate({ left: pos }, recentSlideDelay, function() {
                stopClick = false;
            });
        }
    });

    $('.scroll-button').click(function() {
        if( stopClick ) { return; }

        var pos = $('#media-pane ul').css("left").replace("px", "");
        var direction;

        if ( $(this).hasClass('left') ) {
            direction = 'left';
            pos       = parseInt(pos) + mediaSlideWidth;
        } else if ( $(this).hasClass('right') ) {
            direction = 'right';
            pos       = parseInt(pos) - mediaSlideWidth;
        }

        if ( (direction == 'left' && parseInt(pos) <= 0) 
             || (direction == 'right' && pos > (-1*maxMediaPaneSize) ) ) {
            stopClick = true; 
            $('#media-pane ul').animate({ left: pos }, mediaSlideDelay, function() {
                stopClick = false;
            });
        }
    });

    $(window).bind('scroll', function() {
        if ($(window).scrollTop() > 0) { //150
            $('#menu-wrapper').addClass('fixed');
        } else {
            $('#menu-wrapper').removeClass('fixed');
        }
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

    $(document).on('click', '.closePopup', function() {
        activePopup.fadeToggle('fast');
        activePopup.removeClass('centered');
        activePopup.html('');
        activePopup = '';
        $(".overlay").fadeToggle('fast');
    });
});