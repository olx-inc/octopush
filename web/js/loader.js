/* -------------
   --- Model ---
   ------------- */
var header = function (data){
    // Version
    $("#version").append(data.version);

    // Login Box
    var userdata = $("#userdata"),
        login = $("#login");
    if( !$.isEmptyObject(data.userdata) ){

        userdata
            .show()
            .html(function(){
            var admin = (data.userdata.is_admin_user) ? "admin" : "",
                logoutUrl = data.logoutUrl,
                myComponents = [
                    "<button type='button' class='btn-align btn' id='my-components'>",
                    "<span class='glyphicon glyphicon-filter'></span> My Components",
                    "</button>"
                ].join('');

            return  "<div class='userBox'>Hello " + 
                    data.userdata.user.username + 
                    admin +
                    " | <a href=" + logoutUrl + ">Logout</a></div>" +
                    myComponents;
        });

    } else {

        login
            .show()
            .html(function(){
                loginButton = [
                    "<a class='btn btn-block btn-social btn-github' href='/login'>",
                    "<i class='fa fa-github'></i>",
                    "Sign in with GitHub</a>"
                ].join('');

            return loginButton;
        });
    }
};

var paused = function (data){
    if( data.isPaused ){
        var paused = $("#paused"),
            contact = data.contact;

        paused
            .show()
            .html(function(){
            var pausedWarning = [
                "<h4>Octopush service is Paused right now due to a current incident</h4>",
                "<p>Please contact ",
                contact,
                ".</p>"
            ].join('');

            return pausedWarning;
        });
    }
};

/*var noPenedingJobs = function (data){
    //<div class="alert alert-info"><strong>EMPTY!</strong> No pending jobs</div>
    .preprod-queued
    .preprod-processed
    .prod-queue
    .prod-processed
    if( data.isPaused ){
        var paused = $("#paused"),
            contact = data.contact;

        paused
            .show()
            .html(function(){
            var pausedWarning = [
                "<h4>Octopush service is Paused right now due to a current incident</h4>",
                "<p>Please contact ",
                contact,
                ".</p>"
            ].join('');

            return pausedWarning;
        });
    }
};*/


/* -----------
   -- Jobs ---
   ----------- */
/* Pre-Production */
var queuedPreprodJobs = function (jobs){
    var empty = $("#preprod-queued-empty"),
        table = $("#preprod-queued");

    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(template.queuedPreprod(this));
        });

        table.show();
    } else {
        empty.show();
    }
};
var inProgressPreprodJobs = function (jobs){
    var table = $("#preprod-inprogress");

    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(template.inProgressPreprod(this));
        });

        table.show();
    }
};
var processedPreprodJobs = function (jobs){
    var empty = $("#preprod-processed-empty"),
        table = $("#preprod-processed");

    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(template.processedPreprod(this));
        });

        table.show();
    } else {
        empty.show();
    }
};


/* Production */
var queuedProdJobs = function (jobs){
    var empty = $("#prod-queued-empty"),
        table = $("#prod-queued");

    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(template.queuedProd(this));
        });

        table.show();
    } else {
        empty.show();
    }
};
var inProgressProdJobs = function (jobs){
    var table = $("#prod-inprogress");

    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(template.inProgressProd(this));
        });

        table.show();
    }
};
var processedProdJobs = function (jobs){
    var empty = $("#prod-processed-empty"),
        table = $("#prod-processed");

    if( !$.isEmptyObject(jobs) ){
        $.each(jobs, function(){
            table.children('tbody').append(template.processedProd(this));
        });

        table.show();
    } else {
        empty.show();
    }
};


/* ----------------
   --- Get Data ---
   ---------------- */
var model = function (){
    $.get("/modelData", function (modeldata){
        //var elements = $('[data-model]');
        console.log(modeldata)
        
        header(modeldata);
        paused(modeldata);
    });
}

var preprodJobs = function (){
    $.get("/staging/queued", function (jobs){
        queuedPreprodJobs(jobs);
    });

    $.get("/staging/inprogress", function (jobs){
        inProgressPreprodJobs(jobs);
    });

    $.get("/staging/deployed", function (jobs){
        processedPreprodJobs(jobs);
    })

    $.get("/prod/queued", function (jobs){
        queuedProdJobs(jobs);
    });

    $.get("/prod/inprogress", function (jobs){
        inProgressProdJobs(jobs);
    });

    $.get("/prod/deployed", function (jobs){
        processedProdJobs(jobs);
    })
}

$(document).ready(function (){
    $('.preprod-head').load('../templates/preprodHead.html');
    $('.prod-head').load('../templates/prodHead.html');
    model();
    preprodJobs();
});

/*var url = $(location).attr('href');

setInterval(function(){
    $.ajax({
        url: url + 'ajaxRequest',
        dataType: "html",
        success: function(ans) {
            var html = $(ans),
                section,
                selector;

            for (i=0; i<1; i++){
                section = html[i];
                selector = '.' + $(section).attr('class');
                $(selector).replaceWith(section);
            }
        },
        error: function(msg){
            console.log(msg);
        }
    });
}, 15 * 1000);*/