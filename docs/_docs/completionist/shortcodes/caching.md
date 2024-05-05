---
title: Caching
parent: Shortcodes
grand_parent: Completionist
nav_order: 1
---

# Shortcode Caching
{: .no_toc }

Understand and customize how Asana data is cached and used within WordPress.
{: .text-beta .fw-300 .text-grey-dk-000}

## Table of Contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

<div class="banner banner-warning">
  <p><strong>This page contains custom code snippets.</strong></p>
  <p>Please seek a WordPress developer for guidance if you're not familiar with adding custom code to WordPress websites!</p>
</div>
## What is Caching?

Completionist uses caching to reduce the number of unnecessary requests made to Asana. This drastically improves the performance of your website where Asana data is displayed via Completionist's shortcodes.

Basically, Completionist requests data from Asana once every 15 minutes. By remembering the data (caching it), Completionist then doesn't need to request new data from Asana. After 15 minutes, Completionist then considers the cached data to be expired and requests the latest data from Asana. This cycle of fetching, storing, and expiring data repeats endlessly, which saves countless requests every 15 minutes.

But what if you want your Asana changes to be reflected more often than just every 15 minutes? Or what if you only need Asana changes updated every hour? How can you force an important update even if the cache hasn't expired yet? These are common scenarios which the following settings help solve!

## Changing the Cache TTL

**Frontend requests are cached by default for 15 minutes (900 seconds).** This immensely improves website performance by reducing how often data is requested from Asana.

If you find that Asana data is not updated often enough, you can decrease the *Cache Duration (TTL)* setting in Completionist's settings. For example, to reflect new changes from Asana on your WordPress website every **5 minutes**, set the *Cache Duration (TTL)* setting to **300 seconds**. Note that you must be an administrator (have the `manage_options` capability) in WordPress to update this setting.

### Overriding in PHP

For a custom or dynamic solution, the following filter hook is also available in PHP.

This reduces the cache duration to 5 minutes:

```php
add_filter( 'ptc_completionist_request_tokens_ttl', 'ptc_get_request_tokens_ttl', 10, 1 );
function ptc_get_request_tokens_ttl( $ttl ) {
  return 5 * MINUTE_IN_SECONDS;
}
```

You may also increase the cache duration, such as using 1 hour:

```php
add_filter( 'ptc_completionist_request_tokens_ttl', 'ptc_get_request_tokens_ttl', 10, 1 );
function ptc_get_request_tokens_ttl( $ttl ) {
  return HOUR_IN_SECONDS;
}
```

Refer to [WordPress's PHP time constants](https://codex.wordpress.org/Easier_Expression_of_Time_Constants) for other duration expressions.

## Clearing the Cache

In Completionist's *Settings* screen, there is a *Clear Cache* button available in the *Asana Data Cache* settings section. You can click that button anytime you need changes from Asana immediately reflected on your WordPress website.

### Advanced Usage For Developers

Frontend caching and security is managed by the `Request_Token` PHP class within Completionist. You can think of request tokens as WordPress nonces. All related data is stored within the `wp_ptc_completionist_request_tokens` database table.

Request tokens are created in PHP as they are needed, including their associated cache records. Feel free to truncate the table at any time, but note that any frontend HTML caching of your WordPress website might become out-of-sync. For this reason, you should also clear the PHP/HTML cache for each page that contains a Completionist shortcode.

You can clear the request tokens database table by using PHP:

```php
// Load the Request_Token class file.
include_once PTC_Completionist\PLUGIN_PATH . 'src/public/class-request-token.php';

if (
    class_exists( 'PTC_Completionist\Request_Token' ) &&
    method_exists( 'PTC_Completionist\Request_Token', 'delete_all' )
) {
    // Attempt to delete all request token data, including cache records.
    if ( PTC_Completionist\Request_Token::delete_all() ) {
        // Successfully deleted all request tokens.
        // @TODO - Clear HTML cache records.
    }
}
```

