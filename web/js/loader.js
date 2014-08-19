
var isPaused = function (status){
    if( status == "ON" ){
        $("#paused").hide();
    } else {
        $("#paused").show();
    }
};


/* -----------
   -- Jobs ---
   ----------- */
var queuedJobs = function (selector, jobs){
    var empty = $(selector + " .queued-empty"),
        table = $(selector + " .queued");

    table.children('tbody').html('');
    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(tml.queued(this));
        });

        empty.hide();
        table.show();
    } else {
        empty.show();
        table.hide();
    }
};
var inProgressJobs = function (selector, jobs){
    var container = $(selector),
        table = $(selector + " .inprogress");

    table.children('tbody').html('');
    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(tml.inProgress(this));
        });

        container.show();
        table.show();
    } else {
        container.hide();
        table.hide();
    }
};
var preprodProcessed = function (selector, jobs){
    var empty = $(selector + " .processed-empty"),
        table = $(selector + " .processed");

    table.children('tbody').html('');
    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(tml.preprodProcessed(this));
        });

        empty.hide();
        table.show();
    } else {
        empty.show();
        table.hide();
    }
};
var prodProcessed = function (selector, jobs){
    var empty = $(selector + " .processed-empty"),
        table = $(selector + " .processed");

    table.children('tbody').html('');
    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(tml.prodProcessed(this));
        });

        empty.hide();
        table.show();
    } else {
        empty.show();
        table.hide();
    }
};


var getJobs = function (){
    $.get("/staging/queued", function (jobs){
        queuedJobs("#preprod-queued", jobs);
    });

    $.get("/staging/inprogress", function (jobs){
        inProgressJobs("#preprod-inprogress", jobs);
    });

    $.get("/staging/deployed", function (jobs){
        preprodProcessed("#preprod-processed", jobs);
    })

    $.get("/prod/queued", function (jobs){
        queuedJobs("#prod-queued", jobs);
    });

    $.get("/prod/inprogress", function (jobs){
        inProgressJobs("#prod-inprogress", jobs);
    });

    $.get("/prod/deployed", function (jobs){
        prodProcessed("#prod-processed", jobs);
    })
}


$(document).ready(function (){
    $('.preprod-head').load('../templates/preprodHead.html');
    $('.prod-head').load('../templates/prodHead.html');
    $('#resources').load('../templates/job.html');
    
    $.get("/status", function (status){
        isPaused(status);
    });

    getJobs();

    /*setInterval(function(){
        isPaused(status);
        getJobs();
    }, 15 * 1000);*/
});