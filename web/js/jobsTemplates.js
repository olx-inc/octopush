var template = {
    queuedPreprod: function (job) {
        var jenkinsAnchor = "",
            deploymentJob = "",
            testJobUrl = "",
            html;

        if (job._requestorJenkins != ""){
            jenkinsAnchor = [
                "<a href=",
                job._requestorJenkins,
                " target='_blank'><span class='glyphicon glyphicon-log-in'></span></a>"
            ].join('');
        }
        if (job._deploymentJobId > 0){
            deploymentJob = [
                "<a href=",
                "{{ jenkins.getBuildUrl(queued_job) }}",
                " target='_blank' title='view deployment job' data-toggle='tooltip' data-placement='top'><span class='glyphicon glyphicon-log-out' /></a>"
            ].join('');
        }
        if (job._testJobUrl != ""){
            testJobUrl = [
                "<a href=",
                "{{ jenkins.getTestJobConsoleUrl(queued_job) }}",
                " target='_blank' title='view test job' data-toggle='tooltip' data-placement='top'><span class='glyphicon glyphicon-ok' /></a>"
            ].join('');
        }

        html = [
            "<tr><td><small>",
            job._id,
            "</small></td><td><small>",
            job._targetModule,
            "</small></td><td><small>",
            job._targetVersion,
            "</small></td><td><span class='label label-",
            job._status,
            "'><small>",
            job._status,
            "</small></span></td><td><small>",
            job._queued_at,
            "</small></td><td><small>",
            job._updated_at,
            "</small></td><!-- Ticket column --><td><small>",
            "</small></td><td><small>",
            jenkinsAnchor,
            deploymentJob,
            testJobUrl,
            "</small></td></tr>"
        ].join('');

        return html;
    },

    inProgressPreprod: function (job) {
        var jenkinsAnchor = "",
            deploymentJob = "",
            testJobUrl = "",
            liveJob = "",
            html;

        if (job._requestorJenkins != ""){
            jenkinsAnchor = [
                "<a href=",
                job._requestorJenkins,
                " target='_blank'><span class='glyphicon glyphicon-log-in'></span></a>"
            ].join('');
        }
        if (job._deploymentJobId > 0){
            deploymentJob = [
                "<a href=",
                "{{ jenkins.getBuildUrl(queued_job) }}",
                " target='_blank' title='view deployment job' data-toggle='tooltip' ",
                "data-placement='top'><span class='glyphicon glyphicon-log-out' /></a>"
            ].join('');
        }
        if (job._testJobUrl != ""){
            testJobUrl = [
                "<a href=",
                "{{ jenkins.getTestJobConsoleUrl(queued_job) }}",
                " target='_blank' title='view test job' data-toggle='tooltip'",
                "data-placement='top'><span class='glyphicon glyphicon-ok' /></a>"
            ].join('');
        }
        /*if (job._liveJobId > 0){
            liveJob = [
                "<a href=",
                "{{ jenkins.getLiveJobConsoleUrl(in_progress_job) }}",
                " target='_blank' title='view live job' data-toggle='tooltip'",
                "data-placement='top'><span class='glyphicon glyphicon-new-window' /></a>"
            ].join('');
        }*/

        html = [
            "<tr><td><small>",
            job._id,
            "</small></td><td><small>",
            job._targetModule,
            "</small></td><td><small>",
            job._targetVersion,
            "</small></td><td><span class='label label-",
            job._status,
            "'><small>",
            job._status,
            "</small></span></td><td><small>",
            job._queued_at,
            "</small></td><td><small>",
            job._updated_at,
            "</small></td><!-- Ticket column --><td><small>",
            "</small></td><td><small>",
            jenkinsAnchor,
            deploymentJob,
            testJobUrl,
            liveJob,
            "</small></td></tr>"
        ].join('');
        
        return html;
    },

    processedPreprod: function (job) {
        var jenkinsAnchor = "",
            deploymentJob = "",
            testJobUrl = "",
            liveJob = "",
            goLive = "",
            html;

        if (job._requestorJenkins != ""){
            jenkinsAnchor = [
                "<a href=",
                job._requestorJenkins,
                " target='_blank'><span class='glyphicon glyphicon-log-in'></span></a>"
            ].join('');
        }
        if (job._deploymentJobId > 0){
            deploymentJob = [
                "<a href=",
                "{{ jenkins.getBuildUrl(queued_job) }}",
                " target='_blank' title='view deployment job' data-toggle='tooltip' ",
                "data-placement='top'><span class='glyphicon glyphicon-log-out' /></a>"
            ].join('');
        }
        if (job._testJobUrl != ""){
            testJobUrl = [
                "<a href=",
                "{{ jenkins.getTestJobConsoleUrl(queued_job) }}",
                " target='_blank' title='view test job' data-toggle='tooltip'",
                "data-placement='top'><span class='glyphicon glyphicon-ok' /></a>"
            ].join('');
        }
        /*if (job._wentLive){
            liveJob = [
                "<a href=",
                "{{ jenkins.getLiveJobConsoleUrl(processed_job) }}",
                " target='_blank' title='view live job' data-toggle='tooltip'",
                "data-placement='top'><span class='glyphicon glyphicon-new-window' /></a>"
            ].join('');
        }
        if (userdata.user and processed_job.canGoLive and jobsController.canBePushedLive(processed_job)){
            goLive = [
                "<a data-job-go-live data-job-id=",
                "{{ processed_job.id }}",
                " data-job-targetModule=",
                "{{ processed_job.targetModule }}",
                " data-job-targetVersion=",
                "{{ processed_job.targetVersion }}",
                " href='#' title='go live' data-toggle='tooltip' data-placement='top'>",
                "<i class='glyphicon glyphicon-new-window'></i></a>"
            ].join('');
        }*/

        html = [
            "<tr><td><small>",
            job._id,
            "</small></td><td><small>",
            job._targetModule,
            "</small></td><td><small>",
            job._targetVersion,
            "</small></td><td><span class='label label-",
            job._status,
            "'><small>",
            job._status,
            "</small></span></td><td><small>",
            job._queued_at,
            "</small></td><td><small>",
            job._updated_at,
            "</small></td><!-- Ticket column --><td><small>",
            "</small></td><td><small>",
            jenkinsAnchor,
            deploymentJob,
            testJobUrl,
            liveJob,
            goLive,
            "</small></td></tr>"
        ].join('');

        return html;
    },

    queuedProd: function (job) {
        var jenkinsAnchor = "",
            deploymentJob = "",
            testJobUrl = "",
            html;

        if (job._requestorJenkins != ""){
            jenkinsAnchor = [
                "<a href=",
                job._requestorJenkins,
                " target='_blank'><span class='glyphicon glyphicon-log-in'></span></a>"
            ].join('');
        }
        if (job._deploymentJobId > 0){
            deploymentJob = [
                "<a href=",
                "{{ jenkins.getBuildUrl(queued_job) }}",
                " target='_blank' title='view deployment job' data-toggle='tooltip' data-placement='top'><span class='glyphicon glyphicon-log-out' /></a>"
            ].join('');
        }
        if (job._testJobUrl != ""){
            testJobUrl = [
                "<a href=",
                "{{ jenkins.getTestJobConsoleUrl(queued_job) }}",
                " target='_blank' title='view test job' data-toggle='tooltip' data-placement='top'><span class='glyphicon glyphicon-ok' /></a>"
            ].join('');
        }

        html = [
            "<tr><td><small>",
            job._id,
            "</small></td><td><small>",
            job._targetModule,
            "</small></td><td><small>",
            job._targetVersion,
            "</small></td><td><span class='label label-",
            job._status,
            "'><small>",
            job._status,
            "</small></span></td><td><small>",
            job._queued_at,
            "</small></td><td><small>",
            job._updated_at,
            "</small></td><td class='text-center'><small><a href=",
            job._ticket,
            " target='_blank' title='",
            job._ticket,
            "' data-toggle='tooltip'><i class='fa fa-ticket'></i></a></small>",
            "</td><td><small>",
            jenkinsAnchor,
            deploymentJob,
            testJobUrl,
            "</small></td></tr>"
        ].join('');

        return html;
    },

    inProgressProd: function (job) {
        var jenkinsAnchor = "",
            deploymentJob = "",
            testJobUrl = "",
            liveJob = "",
            html;

        if (job._requestorJenkins != ""){
            jenkinsAnchor = [
                "<a href=",
                job._requestorJenkins,
                " target='_blank'><span class='glyphicon glyphicon-log-in'></span></a>"
            ].join('');
        }
        if (job._deploymentJobId > 0){
            deploymentJob = [
                "<a href=",
                "{{ jenkins.getBuildUrl(queued_job) }}",
                " target='_blank' title='view deployment job' data-toggle='tooltip' ",
                "data-placement='top'><span class='glyphicon glyphicon-log-out' /></a>"
            ].join('');
        }
        if (job._testJobUrl != ""){
            testJobUrl = [
                "<a href=",
                "{{ jenkins.getTestJobConsoleUrl(queued_job) }}",
                " target='_blank' title='view test job' data-toggle='tooltip'",
                "data-placement='top'><span class='glyphicon glyphicon-ok' /></a>"
            ].join('');
        }
        /*if (job._liveJobId > 0){
            liveJob = [
                "<a href=",
                "{{ jenkins.getLiveJobConsoleUrl(in_progress_job) }}",
                " target='_blank' title='view live job' data-toggle='tooltip'",
                "data-placement='top'><span class='glyphicon glyphicon-new-window' /></a>"
            ].join('');
        }*/

        html = [
            "<tr><td><small>",
            job._id,
            "</small></td><td><small>",
            job._targetModule,
            "</small></td><td><small>",
            job._targetVersion,
            "</small></td><td><span class='label label-",
            job._status,
            "'><small>",
            job._status,
            "</small></span></td><td><small>",
            job._queued_at,
            "</small></td><td><small>",
            job._updated_at,
            "</small></td><td class='text-center'><small><a href=",
            job._ticket,
            " target='_blank' title='",
            job._ticket,
            "' data-toggle='tooltip'><i class='fa fa-ticket'></i></a></small>",
            "</td><td><small>",
            jenkinsAnchor,
            deploymentJob,
            testJobUrl,
            liveJob,
            "</small></td></tr>"
        ].join('');
        
        return html;
    },

    processedPreprod: function (job) {
        var jenkinsAnchor = "",
            deploymentJob = "",
            testJobUrl = "",
            liveJob = "",
            goLive = "",
            html;

        if (job._requestorJenkins != ""){
            jenkinsAnchor = [
                "<a href=",
                job._requestorJenkins,
                " target='_blank'><span class='glyphicon glyphicon-log-in'></span></a>"
            ].join('');
        }
        if (job._deploymentJobId > 0){
            deploymentJob = [
                "<a href=",
                "{{ jenkins.getBuildUrl(queued_job) }}",
                " target='_blank' title='view deployment job' data-toggle='tooltip' ",
                "data-placement='top'><span class='glyphicon glyphicon-log-out' /></a>"
            ].join('');
        }
        if (job._testJobUrl != ""){
            testJobUrl = [
                "<a href=",
                "{{ jenkins.getTestJobConsoleUrl(queued_job) }}",
                " target='_blank' title='view test job' data-toggle='tooltip'",
                "data-placement='top'><span class='glyphicon glyphicon-ok' /></a>"
            ].join('');
        }
        /*if (job._wentLive){
            liveJob = [
                "<a href=",
                "{{ jenkins.getLiveJobConsoleUrl(processed_job) }}",
                " target='_blank' title='view live job' data-toggle='tooltip'",
                "data-placement='top'><span class='glyphicon glyphicon-new-window' /></a>"
            ].join('');
        }
        if (userdata.user and processed_job.canGoLive and jobsController.canBePushedLive(processed_job)){
            goLive = [
                "<a data-job-go-live data-job-id=",
                "{{ processed_job.id }}",
                " data-job-targetModule=",
                "{{ processed_job.targetModule }}",
                " data-job-targetVersion=",
                "{{ processed_job.targetVersion }}",
                " href='#' title='go live' data-toggle='tooltip' data-placement='top'>",
                "<i class='glyphicon glyphicon-new-window'></i></a>"
            ].join('');
        }*/

        html = [
            "<tr><td><small>",
            job._id,
            "</small></td><td><small>",
            job._targetModule,
            "</small></td><td><small>",
            job._targetVersion,
            "</small></td><td><span class='label label-",
            job._status,
            "'><small>",
            job._status,
            "</small></span></td><td><small>",
            job._queued_at,
            "</small></td><td><small>",
            job._updated_at,
            "</small></td><td class='text-center'><small><a href=",
            job._ticket,
            " target='_blank' title='",
            job._ticket,
            "' data-toggle='tooltip'><i class='fa fa-ticket'></i></a></small>",
            "</td><td><small>",
            jenkinsAnchor,
            deploymentJob,
            testJobUrl,
            liveJob,
            goLive,
            "</small></td></tr>"
        ].join('');

        return html;
    }
}

/*"<tr>\
    <td><small>" + job._id + "</small></td>\
    <td><small>" + job._targetModule + "</small></td>\
    <td><small>" + job._targetVersion + "</small></td>\
    <td><span class='label label-'" + job._status + "'>\
            <small>" + job._status + "</small>\
        </span>\
    </td>\
    <td><small>" + job._queued_at + "</small></td>\
    <td><small>" + job._updated_at + "</small></td>\
    <!-- Ticket column -->\
    <td><small></small></td>\
    <td>\
        <small>" +
            if (job._requestorJenkins != ""){
                "<a href=" + job._requestorJenkins + " target='_blank'>
                    <span class='glyphicon glyphicon-log-in'></span>
                </a>"
            }
            if (job._deploymentJobId > 0){
                "<a href="{{ jenkins.getBuildUrl(queued_job) }}" target='_blank' title='view deployment job' data-toggle='tooltip' data-placement='top'>
                    <span class='glyphicon glyphicon-log-out' />
                </a>"
            }
            if (job._testJobUrl != ""){
                "<a href="{{ jenkins.getTestJobConsoleUrl(queued_job) }}" target='_blank' title='view test job' data-toggle='tooltip' data-placement='top'>
                    <span class='glyphicon glyphicon-ok' />
                </a>"
            }
        + "</small>
    </td>
</tr>"*/