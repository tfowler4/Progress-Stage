var blockRankHeight = '';

$(function() {
    var stopClick = false;
    var slideDelay = 500;

    $('.side-ranking-header.clickable').click(function() {
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
    })

    $('span.clickable').click(function() {
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
    })
});