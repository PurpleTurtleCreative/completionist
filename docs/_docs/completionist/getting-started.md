---
title: Getting Started
parent: Completionist
nav_order: 1
---

# Getting Started
{: .no_toc }

Simply connect your Asana account and choose a tag to start managing Asana tasks on your WordPress site.
{: .text-beta .fw-300 .text-grey-dk-000}

## Table of Contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Install the Plugin

Completionist may be installed by extracting the zip file contents into your `wp-content/plugins/` directory or by using the built-in WordPress plugin installer:

1. Download the Completionist WordPress plugin for free from [the Purple Turtle Creative website](https://purpleturtlecreative.com/completionist/).
2. Log into your WordPress admin area and navigate to *Plugins > Add New*.
3. Click *Upload Plugin* at the top of the screen.
4. Upload the Completionist zip file and click *Install Now*.
5. Activate the plugin once the plugin is installed successfully.

### WordPress Multisite (wpmu) Considerations

Completionist is fully compatible with WordPress multisite networks. Feel free to activate the plugin at the network level or per blog/subsite. If you decide to uninstall Completionist, all plugin data will be properly removed across your network.


## Connect Your Asana Account

<div class="banner banner-danger">
  <h3>Asana Account Required</h3>
  <p>Completionist depends on an Asana account connection to function. <a href="https://asana.com/create-account" target="_blank">Create an Asana account</a> for free before continuing this setup guide.</p>
</div>

1. Navigate to the Completionist settings screen by clicking *Completionist* toward the bottom of your WordPress admin menu.
2. In a new browser window, sign into your Asana account and [visit your Asana developer console](https://app.asana.com/0/developer-console).
3. Click to generate a new access token at the bottom of your Asana developer console and follow the prompts.
4. Back in Completionist, paste your Personal Access Token into the Asana Connect form, agree to let Completionist perform actions in your Asana account on your behalf, and click *Authorize*.
5. Once successfully connected, you'll now be able to access Completionist's settings.

## Set a Site Tag

The final step to get started using Completionist's admin features is to set a *"site tag"*. A site tag is required to use Completionist.

1. In the Completionist settings screen, find the workspace settings section.
2. Choose an Asana workspace. After making your selection, the tag options will be retrieved for selection.
3. Once the tag options for your chosen workspace have loaded, choose the tag that Completionist will use to associate Asana tasks to your WordPress site.
4. Click *Save* to confirm your chosen workspace and site tag.

### What does this do?

Pulling Asana tasks into WordPress by a specific tag has many benefits, such as:

- Lets you organize your tasks however you need to within Asana since you aren't limited to a single project
- Improves performance on WordPress by limiting which tasks are pulled
- Helps you concentrate since you'll only see relevant tasks listed in your WordPress admin
- Establishes a two-way integration between Asana and Completionist—removing or adding the site tag to tasks within Asana will also remove or add the tasks to your WordPress admin!

## Set a Frontend Authentication User


If you'd like to use Completionist's shortcodes on your WordPress website, you should specify a default "frontend authentication user".

1. From the dropdown options, select a WordPress user. Only users that have connected their Asana account with Completionist will be listed.
2. Click *Save* to confirm your chosen user.
3. In a post or page on your WordPress website (can be any status, such as *Draft*), enter [a Completionist shortcode](/completionist/shortcodes/) in the content area. Save the post.
4. Preview the post or page to confirm that the shortcode works as expected.

### Who should I choose?

The authentication user determines which tasks will be displayed and is based on their visibility permissions within Asana.

If you'd like to be fully transparent on your website, then you should choose someone with access to all tasks within Asana. This is most often an Asana workspace owner or the project manager.

If you'd like to limit which tasks are viewable on your website, then you should choose someone with the same limited visibility within Asana.

<div class="banner banner-info">
  <h4 class="text-gamma">Pro Tip</h4>
  <p>Since Completionist's shortcodes help you reduce (or even eliminate) the need to onboard clients or users into your Asana workspace, you may choose to create a generic "client" or "website" user in Asana. You can then connect this Asana account to a generic WordPress user (eg. "Asana User") on your website.</p>
  <p>By using this "dummy" user, you can freely control what Asana content is visible or hidden on your WordPress website—without needing to actually disrupt your team members who need greater visibility permissions.</p>
</div>

### Where is this setting?

The "frontend authentication user" may be set within Completionist's settings screen in wp-admin.

**It is visible only to WordPress *Administrator* users.** There must also be at least 1 connected Asana user in WordPress that is a member of the chosen Asana workspace. This means that an Asana workspace and site tag also need to be saved.
