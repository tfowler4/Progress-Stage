// news model event binder
var NewsEventBinder = function() {
    var stopClickRecent    = false;
    var stopClickMedia     = false;
    var stopClickRankPanel = false;
    var userClicked = false;
    var autoSliding = true;

    var numOfMediaItems;
    var mediaSlideDelay;
    var mediaSlideWidth;
    var maxMediaPaneSize;
    var mediaAutoSliderId;
    var mediaSliderPosition;

    // side rankings system click to display different dungeons
    $(document).on('click touchstart', '.side-ranking-header.clickable', function() {
        var slideDelay      = 500;
        var blockRankHeight = '';

        if( stopClickRankPanel ) { return; }

        var paneTitleId = $(this).prop('id').replace('dungeon-rankings-clicker-', '');
        var currentPane = $('#dungeon-rankings-wrapper-' + paneTitleId);

        if ( currentPane.hasClass('hidden') ) {
            stopClickRankPanel = true;

            var activePane = $(this).parent().children('.active');

            activePane.slideToggle(slideDelay, 'linear', function() {
                activePane.addClass('hidden');
                activePane.removeClass('active');
                activePane.css('background-color', '#D9D9D9');

                blockRankHeight = $("#dungeon-slider").css('height');
            });

            currentPane.slideToggle(slideDelay, 'linear', function() {
                stopClickRankPanel = false;
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
        if( stopClickRankPanel ) { return; }

        var systemId = $(this).prop('id').replace('system-selector-', '');
        
        if ( !$(this).hasClass('highlight') ) {
            stopClickRankPanel = true;
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
                stopClickRankPanel = false;
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

        if( stopClickRecent ) { return; }

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
            stopClickRecent = true; 
            $('#latest-kills ul').animate({ left: pos }, recentSlideDelay, function() {
                stopClickRecent = false;
            });
        }
    });

    // media viewer buttons click to scroll through different video/streams
    $(document).on('click touchstart', '.scroll-button-media', function() {
        if( stopClickMedia || numOfMediaItems <= 1) { return; }

        if ( !userClicked ) {
            userClicked = true;
            autoSliding = false;

            if ( mediaAutoSliderId ) {
                clearInterval(mediaAutoSliderId);
            }
        }

        mediaSliderPosition = $('#media-pane ul').css("left").replace("px", "");
        var direction;

        if ( $(this).hasClass('left') ) {
            direction           = 'left';
            mediaSliderPosition = parseInt(mediaSliderPosition) + mediaSlideWidth;
        }

        if ( $(this).hasClass('right') && (mediaSliderPosition-mediaSlideWidth) == (-1*maxMediaPaneSize) ) {
            direction           = 'right';
            mediaSliderPosition = 0;
        } else if ( $(this).hasClass('right') ) {
            direction           = 'right';
            mediaSliderPosition = parseInt(mediaSliderPosition) - mediaSlideWidth;
        }

        scrollMediaSlider(direction, mediaSliderPosition);
    });
    var scrollMediaSlider = function(direction, pos) {
        if ( (direction == 'left' && parseInt(pos) <= 0) 
             || (direction == 'right' && pos > (-1*maxMediaPaneSize) ) ) {
            stopClickMedia = true;

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

                    $('.media-guild-flag img, media-guild-logo img').css('margin-top', '0px');

                    $('.media-guild-flag img').css('margin-top', '-15px');
                })
            }

            // animate guild logo 
            $('.media-guild-logo').delay(mediaSlideDelay).animate({ 'margin-right': 5 }, mediaSlideDelay, function() {
                stopClickMedia = false;
            });
        }
    };

    // media overlay navigation button click to scroll to exact stream
    $(document).on('click touchstart', '.circle.clickable.faded', function() {
        var mediaSlideDelay = 350;
        var mediaSlideWidth = 900;
        var currentPosition = parseInt($('#media-pane ul').css("left").replace("px", ""));

        if( stopClickMedia ) { return; }

        stopClickMedia = true;

        if ( !userClicked ) {
            userClicked = true;
            autoSliding = false;

            if ( mediaAutoSliderId ) {
                clearInterval(mediaAutoSliderId);
            }
        }

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
            stopClickMedia = false;
        });
    });

    // when page finishes loading
    $(window).load(function(){
        // when page loads, re-adjust guild logos on media overlay to be centered based on image height
        $('.media-guild-logo img').each(function() {
            var parentHeight = parseInt($(this).parent().parent().css('height').replace('px', ''));
            var height       = parseInt($(this).css('height').replace('px', ''));
            var marginTop    = -1 *(height - parentHeight) / 2;

            $(this).css('margin-top', marginTop +'px');
        });

        $('.media-guild-flag img').each(function() {
            var parentHeight = parseInt($(this).parent().parent().css('height').replace('px', ''));
            var height       = parseInt($(this).css('height').replace('px', ''));
            var marginTop    = -1 *(height - parentHeight) / 2;

            $(this).css('margin-top', '-15px');
        });

        numOfMediaItems     = $("#media-pane ul li").length;
        mediaSlideDelay     = 350;
        mediaSlideWidth     = 900;
        maxMediaPaneSize    = numOfMediaItems * mediaSlideWidth;
        mediaSliderPosition = $('#media-pane ul').css("left").replace("px", "");

        // automatically move slider to right until user clicks
        if ( !userClicked && autoSliding && numOfMediaItems > 1 ) {
            mediaAutoSliderId = setInterval(function(){
                if ( (mediaSliderPosition-mediaSlideWidth) == (-1*maxMediaPaneSize) ) {
                    mediaSliderPosition = 0;
                } else {
                    mediaSliderPosition = parseInt(mediaSliderPosition) - mediaSlideWidth;
                }

                scrollMediaSlider('right', mediaSliderPosition);
            }, 1000*10 );
        }
    });
};