// news model event binder
var NewsEventBinder = function() {
    var stopClickRecent    = false;
    var stopClickMedia     = false;
    var stopClickRankPanel = false;
    var userClicked = false;
    var autoSliding = true;

    // side rankings system click to display different dungeons
    $(document).on('click touchstart', '.side-ranking-header.clickable', function() {
        var slideDelay      = 500;
        var blockRankHeight = '';

        if( stopClickRankPanel ) { return; }

        var paneTitleId = $(this).prop('id').replace('dungeon-rankings-clicker-', '');
        var currentPane = $('#dungeon-rankings-wrapper-' + paneTitleId);

        if ( currentPane.css('display') == 'none' ) {
            stopClickRankPanel = true;
            
            var activePane = $(document).find('.active-dungeon');
            activePane.slideToggle(slideDelay, 'linear', function() {
                activePane.removeClass('active-dungeon');

                currentPane.slideToggle(slideDelay, 'linear', function() {
                    stopClickRankPanel = false;
                    currentPane.addClass('active-dungeon');
                });
            });
        }
    });

    // side rankings system click to display different ranking systems data
    $(document).on('click touchstart', 'button.clickable', function() {
        if( stopClickRankPanel ) { return; }

        var systemId = $(this).data('system-id').replace('system-selector-', '');
        var numOfTables = $(document).find('.side-tables').length;

        if ( !$(this).hasClass('highlight') && numOfTables > 0 ) {
            stopClickRankPanel = true;

            $(this).parent().children('.highlight').removeClass('highlight');
            $(this).addClass('highlight');

            hideAndShowSideRankings(this, 'side-rankings-details', systemId, 300, false);
            hideAndShowSideRankings(this, 'side-rankings-details-small', systemId, 300, true);
        }
    });
    var hideAndShowSideRankings = function(me, detailsClass, systemId, delay, enableClick) {
        var identifier    = '.' + detailsClass + '.active-rank';
        var newIdentifier = '.' + systemId + '.' + detailsClass + '.hidden';
        me = $(me).parent().parent();

        $(me).parent().find(identifier).slideToggle(delay, 'linear', function() {
            $(this).addClass('hidden');
            $(this).removeClass('active-rank');
        });

        $(me).parent().find(newIdentifier).each(function() {
            $(this).addClass('active-rank');
            $(this).removeClass('hidden');
            $(this).css('display', 'block');
        });

        if ( enableClick ) {
            stopClickRankPanel = false;
        }
    };
};