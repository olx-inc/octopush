var tml = {
    fillCommonFields: function (html, job){
        var user = job._user,
            ticket = job._ticket,
            jenkins = job._buildJobUrl,
            deployJobUrl = job._deployJobUrl,
            viewTestJob = job._testJobUrl;

        html.find("[data-id]").text(job._id);
        html.find("[data-target-module]").text(job._targetModule);
        html.find("[data-target-version]").text(job._targetVersion);
        html.find("[data-status]").text(job._status);
        html.find(".label").addClass("label-" + job._status);
        html.find("[data-queued-date]").text(job._queued_at);
        html.find("[data-updated-date]").text(job._updated_at);
        // ---- User
        tml.displayUser(user, html.find("[data-user]"));
        // ---- Ticket
        tml.displayTicket(ticket, html.find("[data-ticket]"));
        // ---- Common actions
        tml.displayActions(jenkins, html.find("[data-jenkins]"));
        tml.displayActions(deployJobUrl, html.find("[data-deployment]"));
        tml.displayActions(viewTestJob, html.find("[data-test-job]"));
        // ---- Start tooltip
        html.find("[data-toggle='tooltip']").tooltip();
    },
    displayUser: function (data, selector){
        if (data != "") {
            selector.attr("title", data);
            selector.show();
        }
    },
    displayTicket: function (data, selector){
        if (data != "") {
            selector.attr("title", data);
            selector.attr("href", data);
            selector.show();
        }

    },
    displayActions: function (data, selector, toJson) {
        var toJson = toJson || false;
        if(toJson && data != "") {
            selector.data("job-id", data.id);
            selector.data("job-targetModule", data.targetModule);
            selector.data("job-targetVersion", data.targetVersion);
            selector.show();
        } else {
            if(data != "") {
                selector.attr("href", data);
                selector.show();
            }
        }
    },

    preprodQueue: function (job) {
        var newJob = $("#resources .job").clone(),
            remove = "";

        if(job._canCancel) {
            remove = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }

        tml.fillCommonFields(newJob, job);
        tml.displayActions(remove, newJob.find("[data-remove]"), true);

        return newJob;
    },

    preprodInProgress: function (job) {
        var newJob = $("#resources .job").clone();

        tml.fillCommonFields(newJob, job);

        return newJob;
    },

    preprodDeployed: function (job) {
        var newJob = $("#resources .job").clone(),
            canGoLive = job._canGoLive,
            goLive = "";

        if (canGoLive){
            goLive = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }

        tml.fillCommonFields(newJob, job);
        tml.displayActions(goLive, newJob.find("[data-go-live]"), true);

        return newJob;
    },

    prodQueue: function (job) {
        var newJob = $("#resources .job").clone(),
            remove = "";

        if(job._canCancel) {
            remove = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }

        tml.fillCommonFields(newJob, job);
        tml.displayActions(remove, newJob.find("[data-remove]"), true);

        return newJob;
    },

    prodInProgress: function (job) {
        var newJob = $("#resources .job").clone(),
            viewLiveJob = job._deployLiveJobUrl;

        tml.fillCommonFields(newJob, job);
        tml.displayActions(viewLiveJob, newJob.find("[data-live-job]"));

        return newJob;
    },

    prodDeployed: function (job) {
        var newJob = $("#resources .job").clone(),
            viewLiveJob = job._deployLiveJobUrl,
            wentLive = job._canRollback,
            redeploy = "";

        if (wentLive){
            redeploy = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }

        tml.fillCommonFields(newJob, job);
        tml.displayActions(viewLiveJob, newJob.find("[data-live-job]"));
        tml.displayActions(redeploy, newJob.find("[data-redeploy]"), true);

        return newJob;
    }
}