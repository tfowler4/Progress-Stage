// user panel model event binder
var UserPanelEventBinder = function() {
    // clicking on guild name will slide up pane to show/hide details and options
    $(document).on('click touchstart', '.option-header.guild, .option-footer.guild', function() {
        var guildId    = $(this).prop('id').replace('guild-pane-', '');
        var activePane = $(this).parent().find(".option-content.guild.active");
        var childPane  = $('#guild-content-'+guildId);


        if ( childPane.css('display') == 'none' ) {
            childPane.slideToggle();
            childPane.addClass('active');
        }

        activePane.slideToggle();
        activePane.removeClass('active');
    });
};