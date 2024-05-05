---
title: Customizations
parent: Shortcodes
grand_parent: Completionist
nav_order: 1
---

# Shortcode Customizations
{: .no_toc }

Use PHP and JavaScript hooks to customize shortcode content, functionality, and styles to suite your needs.
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
## Enqueueing Custom Assets

In the `'wp_footer'` action hook of WordPress, Completionist enqueues the scripts and styles for each of its shortcodes that have rendered for the current page load. As Completionist processes each detected shortcode tag, the `'ptc_completionist_shortcode_enqueue_assets'` action hook in PHP is executed for third-party customizations.

Note that this action hook only runs for shortcodes that have been executed. This ensures assets are enqueued only once per page load and only when they are needed.

This sample code enqueues a custom JavaScript file and CSS stylesheet whenever [the `[ptc_asana_project]` shortcode](/completionist/shortcodes/#ptc_asana_project) has been rendered at least once for the current page load:

```php
add_action(
  'ptc_completionist_shortcode_enqueue_assets',
  'custom_completionist_shortcode_enqueue_assets',
  10,
  1
);

function custom_completionist_shortcode_enqueue_assets( string $shortcode_tag ) {
  if ( 'ptc_asana_project' === $shortcode_tag ) {
    
    wp_enqueue_script(
      'custom-asana-project-script',
      plugins_url( '/assets/js/script.js' , __FILE__ ),
      array( 'ptc-completionist-shortcode-asana-project' ),
      '1.0.0',
      true
    );
    
    wp_enqueue_style(
      'custom-asana-project-styles',
      plugins_url( '/assets/css/styles.css' , __FILE__ ),
      array( 'ptc-completionist-shortcode-asana-project' ),
      '1.0.0'
    );
  }
}
```

*See WordPress's documentation for usage of [`wp_enqueue_script()`](https://developer.wordpress.org/reference/functions/wp_enqueue_script/) and [`wp_enqueue_style()`](https://developer.wordpress.org/reference/functions/wp_enqueue_style/).*

## JavaScript Hooks

Completionist uses [WordPress's `@wordpress/hooks` npm package](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-hooks/) to implement custom action and filter hooks in JavaScript.

Using [the method described above](#enqueueing-custom-assets), enqueue a custom JavaScript file that registers your modifications.

This example customizes the project section "Section" label text in Completionist Pro:

```js
window.Completionist.hooks.addFilter(
  'task_modal_project_section_label',
  'my-custom-plugin',
  ( label, task ) => {

    // Project section names acting as statuses.
    const statuses = [
      'To Do',
      'In Progress',
      'Needs Review',
      'Done',
    ];
    
    if ( statuses.includes( task.project_section_name ) ) {
      // Label the project section as a "Status".
      label = 'Status';
    }

    return label;
  }
);
```

### Finding Available JavaScript Hooks

The best way to find action and filter events that you can hook customizations into is by examining the following JavaScript global variables in your browser console:

```js
// Contains the action hooks that have fired for the given client session.
window.console.log( window.Completionist.hooks.actions );
// Contains the filter hooks that have fired for the given client session.
window.console.log( window.Completionist.hooks.filters );
```

Note that these global variables only contain hooks that have executed at least once before you log their contents. This means you should interact with Completionist's elements until a behavior happens or a view is displayed that you want to hook into.

If you need me to add more action or filter hooks, please [let me know](/#support)!

## Untitled Project Sections

Despite an Asana project seeming to have untitled sections or no sections at all, the Asana API provides these sections with placeholder names. To display the same experience, Completionist ignores those placeholder names.

**By default, Completionist does not display these section titles:**

- `(no section)`
- `untitled section`
- `Untitled section`
- `Untitled Section`

You may choose to override which project section names are ignored by filtering the list of erased names. The Asana project's GID and request configuration arguments are also provided for context.

This will allow all section names to be displayed, including Asana's placeholder names:

```php
add_filter( 'ptc_completionist_project_section_names_to_erase', 'ptc_get_project_section_names_to_erase', 10, 3 );
function ptc_get_project_section_names_to_erase( $names, $project_gid, $args ) {
  return array();
}
```

This will add another section name to be erased:

```php
add_filter( 'ptc_completionist_project_section_names_to_erase', 'ptc_get_project_section_names_to_erase', 10, 3 );
function ptc_get_project_section_names_to_erase( $names, $project_gid, $args ) {
  $names[] = 'Section Name';
  return $names;
}
```

## Custom Add-On Plugin Example

A common request from users of Completionist is to add support for Asana Business features such as [*Custom Fields*](https://asana.com/features/project-management/custom-fields). While Completionist does not currently support premium Asana features at this time, we make hooks available to help you implement these features as needed.

You can access a free add-on plugin on GitHub to get started at [https://github.com/PurpleTurtleCreative/completionist-custom-fields](https://github.com/PurpleTurtleCreative/completionist-custom-fields)

Downloading this plugin from GitHub and installing it alongside Completionist on your WordPress website will cause custom fields to be displayed on tasks in [Project Embeds](/completionist/shortcodes/).
