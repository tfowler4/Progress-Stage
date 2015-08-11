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

            $(this).parent().find('table').css('display', 'none');
            $(this).parent().find('table.' + systemId).css('display', 'table');
            stopClick = false;
        }
    });

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
        var numOfMediaItems   = $("#media-pane ul li").length;
        var mediaSlideDelay   = 400;
        var mediaSlideWidth   = 900;
        var maxMediaPaneSize  = numOfMediaItems * mediaSlideWidth;

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

            // media overlay bar
            $('.media-overlay').slideToggle(mediaSlideDelay).delay(mediaSlideDelay).slideToggle(mediaSlideDelay);

            // image slider
            $('#media-pane ul').delay(mediaSlideDelay).animate({ left: pos }, mediaSlideDelay, function() {
                stopClick = false;
            });
        }
    });
};