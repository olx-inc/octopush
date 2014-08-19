var tml = {
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
        var newJob = $("#resources .job").clone(),
            ticket = "",
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

        newJob.find("[data-id]").text(job._id);
        newJob.find("[data-target-module]").text(job._targetModule);
        newJob.find("[data-target-version]").text(job._targetVersion);
        newJob.find("[data-status]").text(job._status);
        newJob.find(".label").addClass("label-" + job._status);
        newJob.find("[data-queued-date]").text(job._queued_at);
        newJob.find("[data-updated-date]").text(job._updated_at);
        tml.displayActions(ticket, newJob.find("[data-ticket]"));
        tml.displayActions(jenkins, newJob.find("[data-jenkins]"));
        tml.displayActions(deployment, newJob.find("[data-deployment]"));
        tml.displayActions(testJob, newJob.find("[data-test-job]"));

        return newJob;
    },

    inProgress: function (job) {
        var newJob = $("#resources .job").clone(),
            ticket = "",
            jenkins = "",
            deployment = "",
            testJob = "",
            liveJob = "";

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
        if (job._liveJobId > 0){
            liveJob = "{{ jenkins.getLiveJobConsoleUrl(in_progress_job) }}";
        }

        newJob.find("[data-id]").text(job._id);
        newJob.find("[data-target-module]").text(job._targetModule);
        newJob.find("[data-target-version]").text(job._targetVersion);
        newJob.find("[data-status]").text(job._status);
        newJob.find(".label").addClass("label-" + job._status);
        newJob.find("[data-queued-date]").text(job._queued_at);
        newJob.find("[data-updated-date]").text(job._updated_at);
        tml.displayActions(ticket, newJob.find("[data-ticket]"));
        tml.displayActions(jenkins, newJob.find("[data-jenkins]"));
        tml.displayActions(deployment, newJob.find("[data-deployment]"));
        tml.displayActions(testJob, newJob.find("[data-test-job]"));
        tml.displayActions(liveJob, newJob.find("[data-live-job]"));

        return newJob;
    },

    preprodProcessed: function (job) {
        var newJob = $("#resources .job").clone(),
            ticket = "",
            jenkins = "",
            deployment = "",
            testJob = "",
            liveJob = "",
            goLive = "";

        if (!$.isEmptyObject(job._ticket)){
            ticket = job._ticket;
        }
        if (job._requestorJenkins != ""){
            jenkins = job._requestorJenkins;
        }
        if (job._deploymentJobId > 0){
            deployment = "{{ jenkins.getBuildUrl(job) }}";
        }
        if (job._testJobUrl != ""){
            testJob = "{{ jenkins.getTestJobConsoleUrl(job) }}";
        }
        if (job._liveJobId > 0){
            liveJob = "{{ jenkins.getLiveJobConsoleUrl(job) }}";
        }
        /*if (userdata.user and job.canGoLive and job.canBePushedLive(job)){*/
            goLive = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        /*}*/

        newJob.find("[data-id]").text(job._id);
        newJob.find("[data-target-module]").text(job._targetModule);
        newJob.find("[data-target-version]").text(job._targetVersion);
        newJob.find("[data-status]").text(job._status);
        newJob.find(".label").addClass("label-" + job._status);
        newJob.find("[data-queued-date]").text(job._queued_at);
        newJob.find("[data-updated-date]").text(job._updated_at);
        tml.displayActions(ticket, newJob.find("[data-ticket]"));
        tml.displayActions(jenkins, newJob.find("[data-jenkins]"));
        tml.displayActions(deployment, newJob.find("[data-deployment]"));
        tml.displayActions(testJob, newJob.find("[data-test-job]"));
        tml.displayActions(liveJob, newJob.find("[data-live-job]"));
        tml.displayActions(goLive, newJob.find("[data-go-live]"));

        return newJob;
    },

    prodProcessed: function (job) {
        var newJob = $("#resources .job").clone(),
            ticket = "",
            jenkins = "",
            deployment = "",
            testJob = "",
            liveJob = "",
            wentLive = "",
            rollback = "";

        if (!$.isEmptyObject(job._ticket)){
            ticket = job._ticket;
        }
        if (job._requestorJenkins != ""){
            jenkins = job._requestorJenkins;
        }
        if (job._deploymentJobId > 0){
            deployment = "{{ jenkins.getBuildUrl(job) }}";
        }
        if (job._testJobUrl != ""){
            testJob = "{{ jenkins.getTestJobConsoleUrl(job) }}";
        }
        if (job._liveJobId > 0){
            liveJob = "{{ jenkins.getLiveJobConsoleUrl(job) }}";
        }
        /*if (processed_job.wentLive){*/
            wentLive = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        /*}*/
        /*if (userdata.user and job.wentLive and job.canBePushedLive(job)){*/
            rollback = {
                "id": job._id,
                "targetModule": job._targetModule,
                "targetVersion": job._targetVersion
            };
        /*}*/

        newJob.find("[data-id]").text(job._id);
        newJob.find("[data-target-module]").text(job._targetModule);
        newJob.find("[data-target-version]").text(job._targetVersion);
        newJob.find("[data-status]").text(job._status);
        newJob.find(".label").addClass("label-" + job._status);
        newJob.find("[data-queued-date]").text(job._queued_at);
        newJob.find("[data-updated-date]").text(job._updated_at);
        tml.displayActions(ticket, newJob.find("[data-ticket]"));
        tml.displayActions(jenkins, newJob.find("[data-jenkins]"));
        tml.displayActions(deployment, newJob.find("[data-deployment]"));
        tml.displayActions(testJob, newJob.find("[data-test-job]"));
        tml.displayActions(liveJob, newJob.find("[data-live-job]"));
        tml.displayActions(wentLive, newJob.find("[data-went-live]"));
        tml.displayActions(rollback, newJob.find("[data-rollback]"));

        return newJob;
    }
}