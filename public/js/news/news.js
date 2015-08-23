// news model event binder
var NewsEventBinder = function() {
    var stopClick = false;

    // side rankings system click to display different dungeons
    $(document).on('click touchstart', '.side-ranking-header.clickable', function() {
        var slideDelay      = 500;
        var blockRankHeight = '';

        if( stopClick ) { return; }

        var paneTitleId = $(this).prop('id').replace('dungeon-rankings-clicker-', '');
        var currentPane = $('#dungeon-rankings-wrapper-' + paneTitleId);

        if ( currentPane.hasClass('hidden') ) {
            stopClick = true;

            var activePane = $(this).parent().children('.active');

            activePane.slideToggle(slideDelay, 'linear', function() {
                activePane.addClass('hidden');
                activePane.removeClass('active');
                activePane.css('background-color', '#D9D9D9');

                blockRankHeight = $("#dungeon-slider").css('height');
            });

            currentPane.slideToggle(slideDelay, 'linear', function() {
                stopClick = false;
                currentPane.removeClass('hidden');
                currentPane.addClass('active');
                activePane.css('background-color', '#D9D9D9');
                
                blockRankHeight = $("#dungeon-slider").css('height');
            });

            $("#dungeon-slider").css('height', 'auto');
        }
    });

    // side rankings system click to display different ranking systems data
    $(document).on('click touchstart', 'span.clickable', function() {
        if( stopClick ) { return; }

        var systemId = $(this).prop('id').replace('system-selector-', '');
        
        if ( !$(this).hasClass('highlight') ) {
            stopClick = true;
            $(this).parent().children('.highlight').removeClass('highlight');
            $(this).addClass('highlight');

            hideAndShowSideRankings(this, 'side-rankings-details', systemId, 300, false);
            hideAndShowSideRankings(this, 'side-rankings-details-small', systemId, 300, true);
        }
    });
    var hideAndShowSideRankings = function(me, detailsClass, systemId, delay, enableClick) {
        var identifier = '.' + detailsClass + '.active';
        $(me).parent().find(identifier).parent().css('height', $(me).parent().find(identifier).parent().css('height'));
        $(me).parent().find(identifier).slideToggle(delay, 'linear', function() {
            $(me).parent().find(identifier).addClass('hidden');
            $(me).parent().find(identifier).css('display', 'none');
            $(me).parent().find(identifier).removeClass('active');
        });

        var newIdentifier = '.' + systemId + '.' + detailsClass + '.hidden';
        $(me).parent().find(newIdentifier).delay(delay).slideToggle(delay, 'linear', function() {
            $(me).parent().find(newIdentifier).addClass('active');
            $(me).parent().find(newIdentifier).css('display', 'block');
            $(me).parent().find(newIdentifier).removeClass('hidden');

            if ( enableClick ) {
                stopClick = false;
            }
        });
    };

    // recent raid buttons click to scroll through different list panes
    $(document).on('click touchstart', '.scroll-button-recent', function() {
        var numOfDisplayItems = 8;
        var numOfRecentItems  = Math.ceil($("#latest-kills  ul li").length / numOfDisplayItems);
        var recentSlideDelay  = 500;
        var recentSlideWidth  = 1206;
        var maxRecentPaneSize = numOfRecentItems * recentSlideWidth;

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

    // media viewer buttons click to scroll through different video/streams
    $(document).on('click touchstart', '.scroll-button-media', function() {
        var numOfMediaItems  = $("#media-pane ul li").length;
        var mediaSlideDelay  = 350;
        var mediaSlideWidth  = 900;
        var maxMediaPaneSize = numOfMediaItems * mediaSlideWidth;

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

            var navigationNumber = -1 * (pos / mediaSlideWidth);
            $('.circle').not('.clickable.faded').addClass('clickable faded');

            $('.circle.'+navigationNumber).removeClass('clickable faded');

            // media overlay top bar logo
            $('.media-overlay-top img').fadeToggle(mediaSlideDelay).delay(mediaSlideDelay).fadeToggle(mediaSlideDelay);

            // media overlay bottom bar
            $('.media-overlay-bottom').slideToggle(mediaSlideDelay).delay(mediaSlideDelay).slideToggle(mediaSlideDelay);

            // image slider
            $('#media-pane ul').delay(mediaSlideDelay).animate({ left: pos }, mediaSlideDelay);

            // place guild logo back to its original place by fading out and resetting
            if ( $('.media-guild-logo').css('margin-right') == '5px' ) {
                $('.media-guild-logo').fadeToggle(mediaSlideDelay, function() {
                    $('.media-guild-logo').css('margin-right', '500px');
                    $('.media-guild-logo').css('display', 'block');
                })
            }

            // animate guild logo 
            $('.media-guild-logo').delay(mediaSlideDelay).animate({ 'margin-right': 5 }, mediaSlideDelay, function() {
                stopClick = false;
            });
        }
    });

    // media overlay navigation button click to scroll to exact stream
    $(document).on('click touchstart', '.circle.clickable.faded', function() {
        var mediaSlideDelay = 350;
        var mediaSlideWidth = 900;
        var currentPosition = parseInt($('#media-pane ul').css("left").replace("px", ""));

        if( stopClick ) { return; }

        stopClick = true;

        $('.circle').not('.clickable.faded').addClass('clickable faded');

        $(this).removeClass('clickable faded');

        var navigationNumber = parseInt($(this).attr('class').replace('circle', ''));
        var mediaPosition    = navigationNumber * mediaSlideWidth;
        var newPosition      = -1 * mediaPosition;

        // media overlay top bar logo
        $('.media-overlay-top img').fadeToggle(mediaSlideDelay).delay(mediaSlideDelay).fadeToggle(mediaSlideDelay);

        // media overlay bottom bar
        $('.media-overlay-bottom').slideToggle(mediaSlideDelay).delay(mediaSlideDelay).slideToggle(mediaSlideDelay);

        // image slider
        $('#media-pane ul').delay(mediaSlideDelay).animate({ left: newPosition }, mediaSlideDelay);

        // place guild logo back to its original place by fading out and resetting
        if ( $('.media-guild-logo').css('margin-right') == '5px' ) {
            $('.media-guild-logo').fadeToggle(mediaSlideDelay, function() {
                $('.media-guild-logo').css('margin-right', '500px');
                $('.media-guild-logo').css('display', 'block');
            })
        }

        // animate guild logo 
        $('.media-guild-logo').delay(mediaSlideDelay).animate({ 'margin-right': 5 }, mediaSlideDelay, function() {
            stopClick = false;
        });
    });

    // when page loads, re-adjust guild logos on media overlay to be centered based on image height
    $(document).ready(function(){
        $('.media-guild-logo img, .media-guild-flag img').each(function() {
            var parentHeight = parseInt($(this).parent().parent().css('height').replace('px', ''));
            var height       = parseInt($(this).css('height').replace('px', ''));
            var marginTop    = -1 *(height - parentHeight) / 2;

            $(this).css('margin-top', marginTop);
        });
    });
};