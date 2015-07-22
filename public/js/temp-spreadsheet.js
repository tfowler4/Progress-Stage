$(document).ready(function(){
    $('.spreadsheet').click(function(event){
        event.preventDefault();

        var currentPageUrl = document.URL;
        var dungeonId      = $(this).prop('id');

        $(".overlay").fadeToggle('fast');

        // Ajax call to retrieve spreadsheet html
         $.ajax({
            url: currentPageUrl,
            type: 'POST',
            data: { request: 'spreadsheet', dungeon: dungeonId},
            success: function(data) {
                var spreadsheetDiv = $('#popup-wrapper');

                spreadsheetDiv.toggleClass('centered');
                spreadsheetDiv.fadeToggle('fast');
                spreadsheetDiv.html(data);
            },
            error: function(xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    });
});