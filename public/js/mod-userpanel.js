$(document).ready(function(){
    $("#userpanel-guild-logo").change(function(){
        changeGuildLogo(this);
    });

    $("#userpanel-screenshot").change(function(){
        changeScreenshot(this);
    });

    $("#userpanel-faction").change(function(){
        changeFactionLogo(this);
    });

    $("#userpanel-country").change(function(){
        changeCountryFlag(this);
    });

    function changeScreenshot(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var imgSrc = e.target.result;

                $('#screenshot-preview').html('<img class="screenshot-large" src="' + imgSrc + '">');
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    function changeGuildLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var imgSrc = e.target.result;

                $('#guild-logo-preview').html('<img id="guild-logo" src="' + imgSrc + '">');
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    function changeCountryFlag(input) {
        var country = input.value.toLowerCase().replace(' ', '_');

        if ( country != '' ) {
            var dir = getFlagLargeDirectory();
            var imgSrc = dir + country + '.png';

            $('#country-flag-preview').html('<img id="country-flag" src="' + imgSrc + '">');
        } else {
            $('#country-flag-preview').html('');
        }
    }

    function changeFactionLogo(input) {
        var faction = input.value.toLowerCase();

        $('#faction-logo-preview').children().fadeTo('fast', .3);
        
        if ( faction != '' ) {
            $('.' + faction).fadeTo('fast', 1);
        }
    }

    function getFlagLargeDirectory() {
        var href              = window.location.href;
        var fullAddressArray  = href.split('//');
        var paramAddressArray = fullAddressArray[1].split('/');
        var rootDir           = 'http://' + paramAddressArray[0];

        if ( paramAddressArray[1] == 'stage' ) {
            rootDir += '/' + paramAddressArray[1];
        }

        rootDir += '/public/images/flags/large/';

        return rootDir;
    }

    $('.option-footer.guild').click(function() {
        var guildId    = $(this).prop('id').replace('guild-pane-', '');
        var activePane = $(this).parent().find(".option-content.guild.active");
        var childPane  = $('#guild-content-'+guildId);


        if ( childPane.css('display') == 'none' ) {
            childPane.slideToggle();
            childPane.addClass('active');
        }

        activePane.slideToggle();
        activePane.removeClass('active');
    })
});