function goLive(element) {
    var $el = $(element);

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
                    alert("An error occurred, if you don't see the job deploying, please try again");
                });
    }
}

function rollback(element) {
    var $el = $(element);
    
    var moduleName = $el.data('jobTargetmodule');
    var moduleVersion = $el.data('jobTargetversion');
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
                    location.reload();
                })
                .fail(function() {
                    alert("An error occurred, if you don't see the job rolling back, please try again");
                });
    }
}

function myComponents() {
    var url,
        btnState = localStorage.getItem('btnState'),
        btnClass = 'btn-off',
        btn = $('#my-components');

    if(btn.hasClass('btn-off')) {
        btnClass = 'btn-on';
    }

    url = '/my_components/' + btnClass;

    $.get(url)
        .done(function() {
            localStorage.setItem('btnState',btnClass);
            location.reload();
        })
        .fail(function() {
            alert("An error occurred, please try again");
        });

}

function setBtnState() {
    var btn = $('#my-components');
       
    if( btn.length > 0 ) {
        if(localStorage.getItem('btnState') <= 0 ) {
            localStorage.setItem('btnState','btn-on');
        }      
        $('#my-components').toggleClass( localStorage.getItem('btnState') );
    }    
}

$(function() {
    setBtnState();
    $('[data-toggle="tooltip"]').tooltip();
    $("[data-job-go-live]").on('click', function(e) {
        goLive(this);
        return false;
    });
    $("[data-job-rollback]").on('click', function(e) {
        rollback(this);
        return false;
    });

    $('#my-components').on('click', function (e) {
        e.preventDefault();
        myComponents();
    });
});