---
title: Disconnect Asana
nav_order: 7
---

# Disconnecting Your Asana Account

To disconnect your Asana account from Completionist, click the red _Disconnect_ button in the last section of Completionist's _Settings_ screen.

This will delete the encrypted Personal Access Token and user GID associated with your Asana account, making it impossible for Completionist to take actions in Asana on your behalf.

After disconnecting your Asana account:

1. [Shortcodes]({{ site.baseurl }}/shortcodes/) where you are the authentication user will no longer render
1. [Automation Actions]({{ site.baseurl }}/automated-tasks/) where you are the task creator will no longer work
1. You will not be able to access Completionist's settings
1. You will not be able to see the [Dashboard Widget]({{ site.baseurl }}/dashboard-widget/)'s content
1. You will not be able to see or manage [Pinned Tasks]({{ site.baseurl }}/pinned-tasks/) on WordPress posts

**Be mindful of these consequences before disconnecting your Asana account!** You can always [reconnect your Asana account]({{ site.baseurl }}/getting-started/#connect-your-asana-account) again in case you need access to Completionist's settings and features again.

[![Settings section to disconnect your Asana account from the Completionist WordPress plugin]({{ site.baseurl }}/assets/images/settings-disconnect-asana.png)]({{ site.baseurl }}/assets/images/settings-disconnect-asana.png){:target="_blank"}
