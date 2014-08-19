function goLive(element) {
    var $el = $(element);
    var jobId = $el.data('jobId');
    var moduleName = $el.data('jobTargetModule');
    var moduleVersion = $el.data('jobTargetVersion');

    var message = 'Are you sure you want to go live with [' + moduleName + '] version ' + moduleVersion + '?';
    var answer = confirm(message);


    if (answer == true) {
        var url = '/jobs/' + jobId + '/golive';

        var $icon = $el.find('i');
        $icon.removeAttr('class');
        $icon.addClass('fa').addClass('fa-spinner').addClass('fa-spin');

        $.get(url, function(){
                alert("Success");
            })
            .done(function() {
                getJobs();
            })
            .fail(function() {
                alert("An error occurred, if you don't see the job deploying, please try again");
            });
    }
}

function rollback(element) {
    var $el = $(element);
    
    console.log($el.data())
    var moduleName = $el.data('jobTargetModule');
    var moduleVersion = $el.data('jobTargetVersion');
    var jobId = $el.data('jobId');
    
    var message = 'Are you sure you want to rollback [' + moduleName + '] version ' + moduleVersion + '?';
    var answer = confirm(message);
    if (answer == true) {
        var url = '/jobs/' + jobId + '/rollback';
        
        var $icon = $el.find('i');
        $icon.removeAttr('class');
        $icon.addClass('fa').addClass('fa-spinner').addClass('fa-spin');
        
        $.get(url)
                .done(function() {
                    getJobs();
                })
                .fail(function() {
                    alert("An error occurred, if you don't see the job rolling back, please try again");
                });
    }
}

function myComponents() {
    var url,
        btnClass = $('#my-components').attr('class');

    if(btnClass.indexOf('btn-on') >= 0){
        url = '/mycomponents/btn-off';
    }else{
        url = '/mycomponents/btn-on';
    }
    
    $.get(url)
        .done(function() {
            location.reload();
        })
        .fail(function() {
            alert("An error occurred, please try again");
        });

}

$(function() {
    $('[data-toggle="tooltip"]').tooltip();
    $(".container").on('click', "[data-job-go-live]", function (e) {
        goLive(this);
        return false;
    });
    $(".container").on('click', "[data-job-rollback]", function (e) {
        rollback(this);
        return false;
    });

    $('.container').on('click', "#my-components", function (e) {
        e.preventDefault();
        myComponents();
    });
});