var getVersions = function (){
    $.get("/versions/all", function (versions){
        var repos = $(".repos");


        repos.html('');
        if( !$.isEmptyObject(versions) ){
            $.each(versions, function(){
                repos.append(tml.version(this));
            });
        }
    });
}

$(document).ready(function (){
    $('#versions-resources').load('../templates/repo.html');

    getVersions();

    /*setInterval(function(){
        getVersions();
    }, 15 * 1000);*/
});