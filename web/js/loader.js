var getParameters = function (){
    var parameters = $(location).attr('search');

    return parameters;
};

var isPaused = function (){
    $.get("/status", function (status){
        if( status == "ON" ){
            $("#paused").hide();
        } else {
            $("#paused").show();
        }
    });
};

var filterHandler = function (){
    var parameters = getParameters();
    parameters = parameters.split("=");
    parameters = parameters[1];
    
    $('.filters .text').text(parameters);
};


/* -----------
   -- Jobs ---
   ----------- */
var queuedJobs = function (selector, jobs, tml){
    var empty = $(selector + " .queued-empty"),
        table = $(selector + " .queued");

    table.children('tbody').html('');
    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(tml(this));
        });

        empty.hide();
        table.show();
    } else {
        empty.show();
        table.hide();
    }
};
var inProgressJobs = function (selector, jobs, tml){
    var container = $(selector),
        table = $(selector + " .inprogress");

    table.children('tbody').html('');
    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(tml(this));
        });

        container.show();
        table.show();
    } else {
        container.hide();
        table.hide();
    }
};
var deployedJobs = function (selector, jobs, tml){
    var empty = $(selector + " .processed-empty"),
        table = $(selector + " .processed");

    table.children('tbody').html('');
    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(tml(this));
        });

        empty.hide();
        table.show();
    } else {
        empty.show();
        table.hide();
    }
};


var getJobs = function (){
    var parameters = getParameters(),
        url = "/all" + parameters;

    $.get(url, function (jobs){
        queuedJobs("#preprod-queued", jobs.preprodQueue, tml.preprodQueue);
        inProgressJobs("#preprod-inprogress", jobs.preprodInprogress, tml.preprodInProgress);
        deployedJobs("#preprod-processed", jobs.preprodDeployed, tml.preprodDeployed);
        queuedJobs("#prod-queued", jobs.prodQueue, tml.prodQueue);
        inProgressJobs("#prod-inprogress", jobs.prodInprogress, tml.prodInProgress);
        deployedJobs("#prod-processed", jobs.prodDeployed, tml.prodDeployed);
    });
}


$(document).ready(function (){
    $('.preprod-head').load('../templates/preprodHead.html');
    $('.prod-head').load('../templates/prodHead.html');
    $('#resources').load('../templates/job.html');

    isPaused();
    filterHandler();
    getJobs();

    setInterval(function(){
        isPaused();
        getJobs();
    }, 15 * 1000);
});