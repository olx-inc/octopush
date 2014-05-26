function goLive(el) {
    var $el = $(el);

    var jobId = $el.data('jobId');
    var moduleName = $el.data('jobTargetmodule');
    var moduleVersion = $el.data('jobTargetversion');

    var message = 'Are you sure you want to go live with [' + moduleName + '] version ' + moduleVersion + '?';
    var answer = confirm(message);


    if (answer == true) {
        var url = '/jobs/' + jobId + '/golive';

        var $icon = $el.find('i');
        $icon.removeAttr('class');
        $icon.addClass('fa').addClass('fa-spinner').addClass('fa-spin');

        $.get(url)
                .done(function() {
                    location.reload();
                })
                .fail(function() {
                    alert("error");
                });
    }
}

function rollback(jobId, moduleName, moduleVersion) {
    var message = 'Are you sure you want to rollback [' + moduleName + '] version ' + moduleVersion + '?';
    var answer = confirm(message);
    if (answer == true) {
        var url = '/jobs/' + jobId + '/rollback';
        $.get(url)
                .done(function() {
                    location.reload();
                })
                .fail(function() {
                    alert("error");
                });
    }
}


$(function() {
    $('[data-toggle="tooltip"]').tooltip();
    $("[data-job-go-live]").on('click', function(e) {
        goLive(this);
        return false;
    });
});