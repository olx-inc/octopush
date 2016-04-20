var getVersions = function (){
    $.get("/versions/all", function (versions){
        var table = $(".repos");

        table.children('tbody').html('');
        if( !$.isEmptyObject(versions) ){
            $.each(versions, function(){
                table.children('tbody').append(tml.version(this));
            });
        }
    });
}

function enqueueConf(element) {
    var $el = $(element),
        moduleName = $el.data('jobTargetModule'),
        moduleVersion = $el.data('jobTargetVersion'),
        message = 'Are you sure you want to deploy [' + moduleName + '] version ' + moduleVersion + '?',
        answer = confirm(message);

    if (answer == true) {
        var url = '/environments/production/modules/' + moduleName+ '/versions/' + moduleVersion + '/push', //'?access_token=5a48662dd72bb88eac09815f671461b23adab428',
            $icon = $el.find('i');

        $icon.removeAttr('class');
        $icon.addClass('fa').addClass('fa-spinner').addClass('fa-spin');

        $.get(url)
                .done(function() {
                    //getVersions();
                    window.location.href='/#production';
                })
                .fail(function() {
                    alert("An error occurred, if you don't see the job deployed, please try again");
                });
    }
}

$(document).ready(function (){
    $('#resources').load('../templates/repo.html');
    $(".container").on('click', "[data-conf-live]", function (e) {
        enqueueConf(this);
        return false;
    });

    getVersions();

    setInterval(function(){
        getVersions();
    }, 15 * 1000);
});
