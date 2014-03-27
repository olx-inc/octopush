---
layout: default
title: A bit of history
---
 
Octopush is a project born out of the necessity to orchestrate Our Jenkins Deployment pipeline at OLX. We started up connecting Jenkins CI Servers with Jenkins RM Server in order to keep things separate, clean and scalable. We needed to be able to request a Deployment automatically from a Jenkins CI Server (managed by a certain Dev Team) to the Release Management Jenkins (managed by RM Team) once the builds where OK. There is no native or pretty way of doing this, we just curl from one Jenkins to the other like:

<pre><code>curl --user $JENKINS_USER:$API_TOKEN "http://jenkins-rm.olx.com/job/Deploy_Component/buildWithParameters?env=Staging&amp;repo=CompX&amp;revision=1.0.0"

</code></pre>

and checked out the response using Jenkins API like:

<pre><code>curl "http://jenkins-rm.olx.com/job/Deploy_Component/lastBuild/api/json"
</code></pre>

This was ok for a while, but then we needed better arbitration and visibility on how to handle a long queue of awaiting components to deploy.

That's how Octopush was born.

