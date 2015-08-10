var UserPanel = function() {
    $(document).on('change', '#userpanel-guild-logo', function() {
        changeGuildLogo(this);
    });

    $(document).on('change', '#userpanel-screenshot', function() {
        changeScreenshot(this);
    });

    $(document).on('change', '#userpanel-faction', function() {
        changeFactionLogo(this);
    });

    $(document).on('change', '#userpanel-country', function() {
        changeCountryFlag(this);
    });

    $(document).on('click', '.option-header.guild, .option-footer.guild', function() {
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

    var changeScreenshot = function(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var imgSrc = e.target.result;

                $('#screenshot-preview').html('<img class="screenshot-large" src="' + imgSrc + '">');
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    var changeGuildLogo = function(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var imgSrc = e.target.result;

                $('#guild-logo-preview').html('<img id="guild-logo" src="' + imgSrc + '">');
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    var changeCountryFlag = function(input) {
        var country = input.value.toLowerCase().replace(' ', '_');

        if ( country != '' ) {
            var dir = getFlagLargeDirectory();
            var imgSrc = dir + country + '.png';

            $('#country-flag-preview').html('<img id="country-flag" src="' + imgSrc + '">');
        } else {
            $('#country-flag-preview').html('');
        }
    }

    var changeFactionLogo = function(input) {
        var faction = input.value.toLowerCase();

        $('#faction-logo-preview').children().fadeTo('fast', .3);
        
        if ( faction != '' ) {
            $('.' + faction).fadeTo('fast', 1);
        }
    }

    var getFlagLargeDirectory = function() {
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
};