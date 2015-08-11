// guild model event binder
var GuildEventBinder = function() {
    // change events to make ajax call to update guild sigs
    $(document).on('change', '.guild-sig-rank, .guild-sig-dungeon, .guild-sig-output-type, .guild-sig-view', function() {
        callAjaxToGuildDetails();
    });

    // ajax call to update guild sig
    var callAjaxToGuildDetails = function() {
        var txtAreaOutput = document.getElementById('textarea_output');
        var form          = $('#guild-sig-form');
        var rankSystem    = form.find('[name="guild-sig-rank"]').val();
        var guildId       = form.find('[name="guild-sig-guild-id"]').val();
        var dungeonId     = form.find('[name="guild-sig-dungeon"]').val();
        var view          = form.find('[name="guild-sig-view"]:checked').val();
        var outputType    = form.find('[name="guild-sig-output-type"]:checked').val();

        var urlParams = document.URL.split('guild/');
        var url       = urlParams[0] + 'guild/' + guildId + '/sig/' + guildId + '/' + dungeonId + '/' + rankSystem + '/' + view;

        document.getElementById('widget_display').innerHTML='<iframe src=' + url + ' height=50 width=610 scrolling=no style="border:0px solid #344E5B; overflow:hidden; border-radius:0px;" border="0"></iframe>';

        if ( outputType == "0" ) {
            txtAreaOutput.value = url;
        } else if ( outputType == "1" ) {
            txtAreaOutput.value = '<a href="' + url + '" target="_blank"><img src="' + url + '"></a>';
        } else if ( outputType == "2" ) {
            txtAreaOutput.value = '[url=' + url +'][img]' + url + '[/img][/url]';
        }
    };

    // update guild sig on page load
    $(document).ready(function(){
        $('.guild-sig-view').change();
    });
};