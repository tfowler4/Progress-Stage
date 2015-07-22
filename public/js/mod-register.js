$(document).ready(function(){
    $("#register-guild-logo").change(function(){
        changeGuildLogo(this);
    });

    $("#register-faction").change(function(){
        changeFactionLogo(this);
    });

    $("#register-country").change(function(){
        changeCountryFlag(this);
    });

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

        $('#faction-logo-preview').children().fadeTo('fast', .3); //addClass('faded');
        
        if ( faction != '' ) {
            $('.' + faction).fadeTo('fast', 1); //removeClass('faded');
        }
    }

    function getFlagLargeDirectory() {
        var href = window.location.href;
        var addressArray = href.split('/');

        addressArray.pop();
        addressArray.pop();
        
        var rootDir = addressArray.join('/');
        rootDir += '/public/images/flags/large/';

        return rootDir;
    }
});