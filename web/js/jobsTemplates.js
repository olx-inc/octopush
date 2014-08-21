var tml = {
    fillCommonFields: function (html, job){
        var ticket = "",
            jenkins = "",
            deployment = "",
            testJob = "";

        if (!$.isEmptyObject(job._ticket)){
            ticket = job._ticket;
        }
        if (job._requestorJenkins != ""){
            jenkins = job._requestorJenkins;
        }
        if (job._deploymentJobId > 0){
            deployment = "{{ jenkins.getBuildUrl(queued_job) }}";
        }
        if (job._testJobUrl != ""){
            testJob = "{{ jenkins.getTestJobConsoleUrl(queued_job) }}";
        }

        html.find("[data-id]").text(job._id);
        html.find("[data-target-module]").text(job._targetModule);
        html.find("[data-target-version]").text(job._targetVersion);
        html.find("[data-status]").text(job._status);
        html.find(".label").addClass("label-" + job._status);
        html.find("[data-queued-date]").text(job._queued_at);
        html.find("[data-updated-date]").text(job._updated_at);
        // ---- Common actions
        tml.displayActions(ticket, html.find("[data-ticket]"));
        tml.displayActions(jenkins, html.find("[data-jenkins]"));
        tml.displayActions(deployment, html.find("[data-deployment]"));
        tml.displayActions(testJob, html.find("[data-test-job]"));
    },
    displayActions: function (data, selector) {
        if((selector.attr('class') == "go-live" ||
            selector.attr('class') == "rollback") && 
            data != "" ) {
            selector.data("job-id", data.id);
            selector.data("job-targetModule", data.targetModule);
            selector.data("job-targetVersion", data.targetVersion);
            selector.show();
        } else {
            if(selector.attr('class') == "ticket"){
                selector.attr("title", data);
            }
            if(data != "") {
                selector.attr("href", data);
                selector.show();
            }
        }
    },
    queued: function (job) {
        var newJob = $("#resources .job").clone();

        if (!$.isEmptyObject(job._ticket)){
            ticket = job._ticket;
        }
        if (job._requestorJenkins != ""){
            jenkins = job._requestorJenkins;
        }
        if (job._deploymentJobId > 0){
            deployment = "{{ jenkins.getBuildUrl(queued_job) }}";
        }
        if (job._testJobUrl != ""){
            testJob = "{{ jenkins.getTestJobConsoleUrl(queued_job) }}";
        }

        tml.fillCommonFields(newJob, job);

        return newJob;
    },

    inProgress: function (job) {
        var newJob = $("#resources .job").clone(),
            liveJob = "";

        if (job._liveJobId > 0){
            liveJob = "{{ jenkins.getLiveJobConsoleUrl(in_progress_job) }}";
        }

        tml.fillCommonFields(newJob, job);
        tml.displayActions(liveJob, newJob.find("[data-live-job]"));

        return newJob;
    },

    preprodProcessed: function (job) {
        var newJob = $("#resources .job").clone(),
            liveJob = "",
            goLive = "";

        if (job._liveJobId > 0){
            liveJob = "{{ jenkins.getLiveJobConsoleUrl(job) }}";
        }
        /*if (userdata.user and job.canGoLive and job.canBePushedLive(job)){
            goLive = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }*/

        tml.fillCommonFields(newJob, job);
        tml.displayActions(liveJob, newJob.find("[data-live-job]"));
        tml.displayActions(goLive, newJob.find("[data-go-live]"));

        return newJob;
    },

    prodProcessed: function (job) {
        var newJob = $("#resources .job").clone(),
            liveJob = "",
            wentLive = "",
            rollback = "";

        if (job._liveJobId > 0){
            liveJob = "{{ jenkins.getLiveJobConsoleUrl(job) }}";
        }
        /*if (processed_job.wentLive){
            wentLive = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }*/
        /*if (userdata.user and job.wentLive and job.canBePushedLive(job)){
            rollback = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        }*/

        tml.fillCommonFields(newJob, job);
        tml.displayActions(liveJob, newJob.find("[data-live-job]"));
        tml.displayActions(wentLive, newJob.find("[data-went-live]"));
        tml.displayActions(rollback, newJob.find("[data-rollback]"));

        return newJob;
    }
}