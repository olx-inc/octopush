---
layout: default
title: Octopush
---
 
<section class="content">
<h2>Welcomen to Octopush project</h2>

Octopush is a project born out of the necessity to orchestrate Our Jenkins Deployment pipeline at OLX. We started up connecting Jenkins CI Servers with Jenkins RM Server in order to keep things separate, clean and scalable. 


<ul>
        {% for p in site.pages %}
        <li>
		<a href="{{ site.baseurl }}{{ p.url }}">{{ p.title }}</a>
        </li>
        {% endfor %}
    </ul>
</section>