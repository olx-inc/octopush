---
layout: default
title: Authentication and authorization
---
 
User authentication is based on GitHub (OAuth), so for this to work you need to <a href="https://github.com/settings/applications/new">register the application in GitHub</a> and add your keys to the configuration file.

<pre><code>github_key: 'place_your_key_here'
github_secret: 'place_your_secret_here'
</code></pre>

Once the user is authenticated, authorization is performed by checking if the user belongs to a certain GitHub team. For this to work you need to set in the configuration file your GitHub management key (/settings/applications/Personal Access Token) and the id of the GitHub team that includes authorized users.</p>

<pre><code>github_management_key: 'your_management_key'
admin_team_id: 'your_admin_team_id'
</code></pre>