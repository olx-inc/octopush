function goLive(element) {
    var $el = $(element),
        jobId = $el.data('jobId'),
        moduleName = $el.data('jobTargetModule'),
        moduleVersion = $el.data('jobTargetVersion'),
        message = 'Are you sure you want to go live with [' + moduleName + '] version ' + moduleVersion + '?',
        answer = confirm(message);


    if (answer == true) {
        var url = '/jobs/' + jobId + '/golive',
            $icon = $el.find('i');
        
        $icon.removeAttr('class');
        $icon.addClass('fa').addClass('fa-spinner').addClass('fa-spin');

        $.get(url)
            .done(function() {
                getJobs();
            })
            .fail(function() {
                alert("An error occurred, if you don't see the job deploying, please try again");
            });
    }
}

function redeploy(element) {
    var $el = $(element),
        moduleName = $el.data('jobTargetModule'),
        moduleVersion = $el.data('jobTargetVersion'),
        jobId = $el.data('jobId'),
        message = 'Are you sure you want to re deploy [' + moduleName + '] version ' + moduleVersion + '?',
        answer = confirm(message);

    if (answer == true) {
        var url = '/jobs/' + jobId + '/rollback',
            $icon = $el.find('i');
        
        $icon.removeAttr('class');
        $icon.addClass('fa').addClass('fa-spinner').addClass('fa-spin');
        
        $.get(url)
                .done(function() {
                    getJobs();
                })
                .fail(function() {
                    alert("An error occurred, if you don't see the job re deployed, please try again");
                });
    }
}

function remove(element) {
    var $el = $(element),
        moduleName = $el.data('jobTargetModule'),
        moduleVersion = $el.data('jobTargetVersion'),
        jobId = $el.data('jobId'),
        message = 'Are you sure you want to remove [' + moduleName + '] version ' + moduleVersion + '?',
        answer = confirm(message);

    if (answer == true) {
        var url = '/jobs/' + jobId + '/cancel',
            $icon = $el.find('i');
        
        $icon.removeAttr('class');
        $icon.addClass('fa').addClass('fa-spinner').addClass('fa-spin');
        
        $.get(url)
                .done(function() {
                    getJobs();
                })
                .fail(function() {
                    alert("An error occurred, if you don't see the job removed, please try again");
                });
    }
}

$(function() {
    $(".container").on('click', "[data-job-go-live]", function (e) {
        goLive(this);
        return false;
    });
    $(".container").on('click', "[data-job-redeploy]", function (e) {
        redeploy(this);
        return false;
    });
    $(".container").on('click', "[data-remove]", function (e) {
        remove(this);
        return false;
    });

});