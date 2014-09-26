var getVersions = function (){
    $.get("/versions/all", function (versions){
        var table = $(".repos");

        table.children('tbody').html('');
        if( !$.isEmptyObject(versions) ){
            $.each(versions, function(){
                table.children('tbody').append(tml.repo(this));
            });
        }
    });
}

$(document).ready(function (){
    $('#resources').load('../templates/repo.html');

    getVersions();

    /*setInterval(function(){
        getVersions();
    }, 15 * 1000);*/
});