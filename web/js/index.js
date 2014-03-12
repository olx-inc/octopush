function goLive(jobId, moduleName, moduleVersion) {
	var message = 'Are you sure you want to go live with [' + moduleName + '] version ' + moduleVersion + '?';
	var answer = confirm(message);
	if (answer == true) {
		var url = '/jobs/' + jobId + '/golive';
		$.get( url)
		  .done(function() {
		    location.reload();
		  })
		  .fail(function() {
		    alert( "error" );
		  });
	}
}