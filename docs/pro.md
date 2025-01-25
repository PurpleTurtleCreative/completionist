---
layout: default
title: ⭐️ Completionist PRO
nav_order: 1
permalink: /pro
---

# Completionist PRO
{: .no_toc }

Thank you for supporting the development of Completionist by purchasing a premium license!
{: .text-beta .fw-300 .text-grey-dk-000}

[Features & Pricing](https://purpleturtlecreative.com/completionist-pro/){: .btn .btn-purple .mr-1 }
[Changelog](https://purpleturtlecreative.com/completionist-pro/plugin-info/){: .btn }

## Table of Contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Download the Plugin File

To download your licensed copy of Completionist PRO for installation and activation, please follow the link labeled `completionist-pro.zip` in your order email, receipt, or customer portal.

Alternatively, visit [https://purpleturtlecreative.com/system/download](https://purpleturtlecreative.com/system/download){:target="_blank"} and manually enter the email address and license key associated with your purchase.

### Forgot your license key?

If you lose the license key associated with your purchase of Completionist PRO, please sign in to your account at [https://store.purpleturtlecreative.com/billing](https://store.purpleturtlecreative.com/billing){:target="_blank"}

You can also update your payment information, manage your license, and more within the customer portal.

## Installation & Activation

Completionist PRO may be installed by extracting the zip file contents into your `wp-content/plugins/` directory or by using the built-in WordPress plugin installer:

1. Log into your WordPress admin area and navigate to *Plugins > Add New*.
2. Click *Upload Plugin* at the top of the screen.
3. Upload the Completionist PRO plugin zip file and click *Install Now*.
4. Activate the plugin once installation completes.

### Already using the Free version?

Please install Completionist PRO alongside the free version. Completionist PRO will automatically detect and remove the free version without deleting/uninstalling any of your settings. The free version is automatically included within the Pro version, so you don't need to juggle version compatibilities between Free and Pro.

An admin notice will be displayed to alert whenever this happens which says:

> **Important: Completionist Pro deactivated and removed the free version of Completionist!** Enjoy all features, both free and premium, without the hassle of multiple plugins or version compatibility concerns. Thank you again for upgrading to Completionist Pro!

[![Notices are displayed at the top of the Plugins screen in WP Admin after activating Completionist Pro which removes Completionist Free]({{ site.baseurl }}/assets/images/pro-activation-replaces-free.png)]({{ site.baseurl }}/assets/images/pro-activation-replaces-free.png){:target="_blank"}

Additionally, Completionist PRO prevents installation of the free version while it is active with a notice which says:

> You are using Completionist Pro which already includes all free features!

[![Completionist in the Plugins screen of WP Admin will show as already active with a notice about Completionist Pro]({{ site.baseurl }}/assets/images/pro-cannot-install-free.png)]({{ site.baseurl }}/assets/images/pro-cannot-install-free.png){:target="_blank"}

### If needed, manually remove the Free version

Some hosting providers, such as WordPress VIP, enforce strict security rules which may prevent Completionist PRO from removing the free version's plugin files.

If the free version of Completionist is still present alongside Completionist PRO in your WordPress website's Plugins admin screen, please manually remove the free version's plugin directory `wp-content/plugins/completionist` via SSH or SFTP.

Manually removing the free version's files prevents the uninstallation process from running which would delete all of Completionist's plugin data, such as your settings, all Asana user connections, all configured Automations, all Pinned Tasks associations, and more.

## Enable Remote Updates & Premium Support

Once you've installed and activated Completionist PRO, visit the plugin's settings screen. You should now see the additional settings tabs that are unique to Completionist PRO, including the *License* tab.

Enter the email address and license key associated with your purchase of Completionist PRO to activate the installed instance.

[![The License tab in Completionist's settings page shows an activation form when a license is not yet associated with the website.]({{ site.baseurl }}/assets/images/completionist-pro_license-form.png)]({{ site.baseurl }}/assets/images/completionist-pro_license-form.png){:target="_blank"}

On success, the details of your licensed instance will be displayed.

[![The License tab in Completionist's settings page shows the activated license instance's details when a license is currently associated with the website.]({{ site.baseurl }}/assets/images/completionist-pro_license_activated-production.png)]({{ site.baseurl }}/assets/images/completionist-pro_license_activated-production.png){:target="_blank"}

License instance details are synced every time WordPress checks for plugin updates or when a user visits the Updates screen in wp-admin (ie. `/wp-admin/update-core.php`). You can see when the details were last updated at the bottom of the license instance details card.

### What about development or staging environments?

We highly encourage following best practices in WordPress website management and development by separating your live/production instance from your development/staging instance(s). Because of this, Completionist PRO's licensing was designed to be compatible with this professional workflow.

**Activate your license on your website's production environment.** When your production environment's database is synced down to your other environments, Completionist PRO will continue to refer to the license instance associated with your production environment's domain name.

[![The License tab in Completionist's settings page shows the activated license instance's details with an additional notice when a shared or synced license instance is associated with the current website.]({{ site.baseurl }}/assets/images/completionist-pro_license_activated-localhost.png)]({{ site.baseurl }}/assets/images/completionist-pro_license_activated-localhost.png){:target="_blank"}

When Completionist PRO detects a shared license instance is in-use, a notice is displayed in the details card which says:

> **Did you migrate this website?** If this is not a development environment for \{\{your production website domain\}\}, please deactivate this site's license and re-activate it on your live site's domain.

### What if I migrate my website to a new domain?

Depending on how the migration was handled, your original license instance may or may not continue to work. Either way, you should deactivate the old license instance associated with the old domain, and then activate a new license instance for the new domain.

**To deactivate a license instance,** click the *Deactivate* button at the bottom of *Completionist > Settings > License* in wp-admin of your WordPress website.

If this button is not displayed, please contact [michelle@purpleturtlecreative.com](mailto:michelle@purpleturtlecreative.com) from the email address associated with your purchase and include the license key and domain name associated with the license instance that you would like to be deactivated.

## Contact Premium Support

By maintaining an active license of Completionist PRO on your website(s), you gain access to priority email support which is accessible at *Completionist > Settings > Support* in wp-admin of your WordPress website.

Without an active or valid license, you will not be able to contact premium support:

[![The Support tab in Completionist's settings page will be locked with an error message if there is not an active and valid license instance associated with the website.]({{ site.baseurl }}/assets/images/completionist-pro_support-requires-license.png)]({{ site.baseurl }}/assets/images/completionist-pro_support-requires-license.png){:target="_blank"}

As long as your website has an active and valid license, you will be able to contact premium support by using the provided contact form:

[![Completionist's Support contact form includes options to specify recipient email addresses, the support topic, the message content, and whether basic system information should be included or not.]({{ site.baseurl }}/assets/images/completionist-pro_support-form.png)]({{ site.baseurl }}/assets/images/completionist-pro_support-form.png){:target="_blank"}

Please read the contact form's on-page instructions for further information.