// news model event binder
var NewsEventBinder = function() {
    var stopClickRankPanel = false;

    // side rankings system click to display different dungeons
    $(document).on('click touchstart', '.side-ranking-header.clickable', function() {
        var slideDelay      = 500;
        var blockRankHeight = '';

        if( stopClickRankPanel ) { return; }

        var paneTitleId = $(this).data('dungeon-id');
        var currentPane = $("div").find("[data-pane-dungeon-id='" + paneTitleId + "']");

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

        var systemId    = $(this).data('system-id');
        var numOfTables = $(document).find('.rank-panes').length;

        if ( !$(this).hasClass('highlight') && numOfTables > 0 ) {
            stopClickRankPanel = true;

            $(this).parent().children('.highlight').removeClass('highlight');
            $(this).addClass('highlight');

            hideAndShowSideRankings(this, 'rank-pane-details', systemId, 300, true);
        }
    });
    var hideAndShowSideRankings = function(me, detailsClass, systemId, delay, enableClick) {
        var identifier    = '.' + detailsClass + '.active-rank';
        var newIdentifier = '.' + systemId + '.' + detailsClass + '';

        me = $(me).parent().parent();

        $(me).parent().find(identifier).slideToggle(delay, 'linear', function() {
            $(this).removeClass('active-rank');
        });

        $(newIdentifier).slideToggle(delay, 'linear', function() {
            $(this).addClass('active-rank');

            if ( enableClick ) {
                stopClickRankPanel = false;
            }
        });
    };
};