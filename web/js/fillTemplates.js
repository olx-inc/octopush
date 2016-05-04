var tml = {
    fillJobCommonFields: function (html, job){

        html.find(".id").addClass("row-" + job._status);

        html.find("[data-id]").text(job._id);
        html.find("[data-target-module]").text(job._targetModule);
        html.find("[data-target-version]").text(job._targetVersion);
        html.find("[data-status]").text(job._status);
        html.find(".label").addClass("label-" + job._status);
        html.find("[data-queued-date]").text(job._queued_ago);
        html.find("[data-updated-date]").text(job._updated_ago);
        // ---- User
        tml.displayTooltip(job._user, html.find("[data-user]"));
        tml.displayTooltip(job._queued_at, html.find("[data-queue]"));
        tml.displayTooltip(job._updated_at, html.find("[data-update]"));

        // ---- Ticket
        tml.displayTicket(job._ticket, html.find("[data-ticket]"));
        // ---- Common actions
        tml.displayActions(job._buildJobUrl, html.find("[data-jenkins]"));
        tml.displayActions(job._deployJobUrl, html.find("[data-deployment]"));
        tml.displayActions(job._testJobUrl, html.find("[data-test-job]"));
        // ---- Start tooltip
        html.find("[data-toggle='tooltip']").tooltip();
    },

    fillRepoFields: function (html, repo){
        html.find("[data-repo]").text(repo._module).attr("href", repo._module_link);
        html.find("[data-testing]").text(repo._testing).attr("href", repo._testing_link);
        html.find("[data-testing-time]").text(repo._testing_time);
        html.find("[data-staging]").text(repo._staging).attr("href", repo._staging_link);
        html.find("[data-staging-time]").text(repo._staging_time);
        html.find("[data-production]").text(repo._production).attr("href", repo._production_link);
        html.find("[data-production-time]").text(repo._production_time);
        // ---- Ticket
        tml.displayTicket(repo._ticket, html.find("[data-ticket]"));
        // ---- Start tooltip
        html.find("[data-toggle='tooltip']").tooltip();
    },

    handleDate: function (jobDate, currentDate){
        var current = currentDate,
            job = new Date(jobDate),
            days,
            hours,
            minutes;

        minutes = (Math.abs(current - job) / 1000) / 60;
        hours = minutes / 60;
        days = hours / 24;

        minutes = parseInt(minutes);
        hours = parseInt(hours);
        days = parseInt(days);

        if (minutes < 60) {
            return minutes + " min ago";
        } else if (hours < 24) {
            return hours + " hours ago";
        } else if (!isNaN(days)) {
            return days + " days ago";
        } else {
            return "-";
        }
    },

    displayTooltip: function (data, selector){
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

    /* -------------------------
       -- Fill job template ---
       ------------------------- */
    preprodQueue: function (job) {
        var newJob = $("#resources .job").clone(),
            remove = "",
            date = new Date(job._serverTime);

        if(job._canCancel) {
            remove = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }

        // convert date to time
        job._queued_ago = tml.handleDate(job._queued_at, date);
        job._updated_ago = tml.handleDate(job._updated_at, date);

        tml.fillJobCommonFields(newJob, job);
        tml.displayActions(remove, newJob.find("[data-remove]"), true);

        return newJob;
    },

    preprodInProgress: function (job) {
        var newJob = $("#resources .job").clone(),
            date = new Date(job._serverTime);

        // convert date to time
        job._queued_ago = tml.handleDate(job._queued_at, date);
        job._updated_ago = tml.handleDate(job._updated_at, date);

        tml.fillJobCommonFields(newJob, job);

        return newJob;
    },

    preprodDeployed: function (job) {
        var newJob = $("#resources .job").clone(),
            canGoLive = job._canGoLive,
            goLive = "",
            date = new Date(job._serverTime);

        if (canGoLive){
            goLive = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }
        // convert date to time
        job._queued_ago = tml.handleDate(job._queued_at, date);
        job._updated_ago = tml.handleDate(job._updated_at, date);

        tml.fillJobCommonFields(newJob, job);
        tml.displayActions(goLive, newJob.find("[data-go-live]"), true);

        return newJob;
    },

    prodQueue: function (job) {
        var newJob = $("#resources .job").clone(),
            remove = "",
            date = new Date(job._serverTime);

        if(job._canCancel) {
            remove = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }

        // convert date to time
        job._queued_ago = tml.handleDate(job._queued_at, date);
        job._updated_ago = tml.handleDate(job._updated_at, date);

        tml.fillJobCommonFields(newJob, job);
        tml.displayActions(remove, newJob.find("[data-remove]"), true);

        return newJob;
    },

    prodInProgress: function (job) {
        var newJob = $("#resources .job").clone(),
            viewLiveJob = job._deployLiveJobUrl,
            date = new Date(job._serverTime);

        // convert date to time
        job._queued_ago = tml.handleDate(job._queued_at, date);
        job._updated_ago = tml.handleDate(job._updated_at, date);

        tml.fillJobCommonFields(newJob, job);
        tml.displayActions(viewLiveJob, newJob.find("[data-live-job]"));

        return newJob;
    },

    prodDeployed: function (job) {
        var newJob = $("#resources .job").clone(),
            viewLiveJob = job._deployLiveJobUrl,
            wentLive = job._canRollback,
            redeploy = "",
            date = new Date(job._serverTime);

        if (wentLive){
            redeploy = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }

        // convert date to time
        job._queued_ago = tml.handleDate(job._queued_at, date);
        job._updated_ago = tml.handleDate(job._updated_at, date);

        tml.fillJobCommonFields(newJob, job);
        tml.displayActions(viewLiveJob, newJob.find("[data-live-job]"));
        tml.displayActions(redeploy, newJob.find("[data-redeploy]"), true);

        return newJob;
    },

    /* -------------------------
       -- Fill repo template ---
       ------------------------- */
    version: function (version) {
        var newRepo = $("#resources .repo").clone(),
            canRollback = false, //repo._canRollback
            goLive = "",
            rollback = "";

        if (canRollback){
            rollback = {
                "id": version._id,
                "targetModule": version._module,
            }
        }
        tml.fillRepoFields(newRepo, version);

        if (typeof version._prod_ready != 'undefined'){
          if (version._prod_ready != version._production){
            if (version._canGoLive) {
              goLive = {
                  "targetModule": version._module,
                  "targetVersion": version._prod_ready
              };
            }
            prod_label = newRepo.find("[data-production]");
            prod_label.attr("href", version._prod_ready_link);
            prod_label.css("color", "red");
            tml.displayTooltip("Production ready", prod_label);
          }
        }
        tml.displayActions(goLive, newRepo.find("[data-conf-live]"), true);

        //tml.displayActions(rollback, newRepo.find("[data-rollback]"), true);

        return newRepo;
    }
}
