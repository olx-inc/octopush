---
layout: default
title: Welcome to Octopush project
---

Octopush is a project born out of the necessity to orchestrate Our Jenkins Deployment pipeline at OLX. We started up connecting Jenkins CI Servers with Jenkins RM Server in order to keep things separate, clean and scalable.

Octopush handles a Queue were every request waits for its turn to be deployed, available components to be deployed are configured on src/config/config.yml where you also specify a groupId. This groupId works as a tier identification, front-end components use one, backend use another. This is used by Octopush to identify dependecies and be able to parallelize non-dependant components (on the same group) and serialize dependant ones (this is an improvement point, dependency graph would be much better).

{% for p in site.pages %}
* [{{ p.title }}]({{ site.baseurl }}{{ p.url }})
{% endfor %}