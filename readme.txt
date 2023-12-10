=== Completionist – Asana Integration Suite ===
Contributors: michelleblanchette
Tags: asana, project, task, management, manager, integration, api, work, business, collaboration, client, customer, support, portal, dashboard, widget, metabox, shortcodes
Requires at least: 5.0.0
Tested up to: 6.4.2
Stable tag: 4.0.0
Requires PHP: 7.2
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Integrates Asana project management with WordPress content management.

== Description ==

Completionist is an integration WordPress plugin for Asana. It helps you establish a complete project management workflow between your Asana workspace and WordPress website.

## Features

There are many ways to improve your productivity by using Completionist.

### Pin and create Asana tasks directly on WordPress content.

Provide crystal-clear clarity and reduce tiresome context-switching by creating and listing Asana tasks on any type of content within the WordPress editor.

- Pin existing tasks that were created in Asana
- Add new Asana tasks from within WordPress
- Pin to any post type such as products or shop orders

[**Read about Pinned Tasks →**](https://docs.purpleturtlecreative.com/completionist/pinned-tasks/)

### Track relevant Asana tasks in your WordPress dashboard.

Review progress and access all outstanding Asana tasks associated with WordPress to stay focused on all current content initiatives and website maintenance.

- Check on productivity with a simple progress overview
- Prioritize quickly with 4 task category filters
- Access pinned tasks to get straight to work

[**Read about the Dashboard Widget →**](https://docs.purpleturtlecreative.com/completionist/dashboard-widget/)

### Automatically create Asana tasks from WordPress activity.

Standardize your content management workflow and never miss a beat by automatically creating Asana tasks as changes happen on WordPress.

- Choose from 6 common WordPress event triggers
- Specify any custom action or filter hook event trigger for endless possibilities and integrations
- Control context with custom conditions
- Compose Asana tasks with dynamic values from WordPress for effortless clarity

[**Read about Automations →**](https://docs.purpleturtlecreative.com/completionist/automated-tasks/)

### Display Asana projects on your WordPress website.

Share real-time progress on WordPress posts and pages to boost engagement from clients and stakeholders while emphasizing your own brand.

- Customize project display with 13+ shortcode attributes
- Keep clients out of your Asana workspace by providing your own branded experience
- Stop writing tedious emails by providing stakeholders self-serviced, real-time progress updates

[**Read about Shortcodes →**](https://docs.purpleturtlecreative.com/completionist/shortcodes/)

---

_Completionist by Purple Turtle Creative is not associated with Asana. Asana is a trademark and service mark of Asana, Inc., registered in the U.S. and in other countries. [Learn more.](https://asana.com/)_

_All source code is available on [GitHub](https://github.com/PurpleTurtleCreative/completionist)._

== Frequently Asked Questions ==

= Is an Asana account required? =

Yes, an Asana account is required to use Completionist. You can quickly [create an Asana account](https://asana.com/create-account) for **FREE**!

= Are you affiliated with Asana? =

No, I do not have a business relationship with Asana. I just happened to be looking for an Asana integration for WordPress and failed to find an affordable, specialized solution. After noticing many others with the same problem, I decided to create Completionist!

= Is this WordPress multisite (wpmu) compatible? =

Yes! Feel free to activate the plugin at the network level or per blog/subsite. If you decide to uninstall Completionist, all plugin data will be properly removed across your network.

= What languages are supported? =

The Completionist plugin is written in **American English (en-US)** and currently doesn’t support translation. However, your Asana projects and tasks will display their original content which may be in another language. All text labels and messages surrounding the Asana data, though, are in American English.

== Upgrade Notice ==

= 4.0.0 =
This plugin is now hosted from the official WordPress.org Plugins directory. Please upgrade immediately so you can continue to receive the latest features, bug fixes, and security updates!

== Changelog ==

_Here's what has changed in the past 3 releases. To access the complete changelog history, please visit [https://purpleturtlecreative.com/completionist/plugin-info/](https://purpleturtlecreative.com/completionist/plugin-info/) or see `changelog.md` in Completionist's files._

### 4.0.0 - 2023-12-10

#### Added

- New `readme.txt` file for WordPress.org plugins listing.
- New `Uninstaller` class to handle plugin data removal.
- New `Upgrader` class to handle plugin version updates. This also offers support assistance when a version rollback is detected, which usually indicates that the user is experiencing issues with a newer version of the plugin.
- New `Admin_Notices` class to handle displaying of admin notices. All notices are respectful in that they are either displayed once or dismissible.
- New `Errors\No_Authorization` exception type class to fix class name and file inconsistency.
- New `Autoloader` class to autoload class files.
- New REST API endpoints to replace all WP Admin AJAX actions.

#### Changed

- Remote updates are now handled through WordPress.org. See the official plugin listing at [https://wordpress.org/plugins/completionist/](https://wordpress.org/plugins/completionist/)
- Class declarations are no longer wrapped in `if ( class_exists( ... ) )` checks. All classes are properly namespaced and should not normally cause collisions.
- Upgraded the legacy Tasks metabox within the Classic Editor. This offers great UX/UI and performance improvements, matching the Pinned Tasks panel in the Block Editor. This also removes script dependencies on jQuery.
- All admin scripts are now loaded in the document footer.
- The `global $submenu` is no longer modified in wp-admin to change the main menu page's submenu title to "Settings". Instead, it's now explicitly added as a duplicate submenu page with the overridden title.
- Refactored `Automation::to_stdClass()` to `Automation::to_std_class()` for proper snake casing per WordPress Coding Standards.

#### Removed

- The `YahnisElsts/plugin-update-checker` Composer package which facilitated remote updates. Remote updates are now hosted by WordPress.org.
- The `uninstall.php` file. Data is now uninstalled by using the registered uninstall hook.
- The deprecated `Request_Tokens` class file, options, and other references.
- The `Errors\NoAuthorization` class due to inconsistent naming and class file.
- All `require_once` calls which manually included class files. The new `Autoloader` class now handles this.
- All WP Admin AJAX actions to instead use the new REST API endpoints.
- The `HTML_Builder::format_task_row()` function. It was only used by the legacy Tasks metabox within the Classic Editor, which is now replaced by the upgraded ReactJS-based components.
- The `Task_Categorizer` class, all child classes, and the `Task_Categorizer` namespace. These PHP classes have not been used since this functionality was moved to ReactJS on the frontend.
- Non-class files within the `src/admin` directory. All PHP+HTML template code has been moved to methods within the related PHP classes, either `Admin_Pages` or `Admin_Widgets`.

#### Fixed

- Unpinning a task from the post editor would unpin the task across the entire site.
- Some edge-case oddities with the WP Admin AJAX actions for managing tasks. The new REST API endpoints are now more robust after a thorough code review and refactor.
- Searching for posts in an Automation Action's "Pin to Post" field would include WordPress's internal types such as `wp_navigation` and `wp_global_styles`.
- The Asana Data Cache Duration (TTL) could not be set to 0 seconds.

#### Security

- Various improvements with the new REST API endpoints which replace the original WP Admin AJAX actions.
- Unique nonces to authorize different requests to the new REST API endpoints which replace the original WP Admin AJAX actions.
- Searching for posts in an Automation Action's "Pin to Post" field would include posts that the current user did not have permission to read.
- Improve sanitization of nonce values before validation.

### 3.11.0 - 2023-11-19

#### Added

- New PHP filter hook `ptc_completionist_project_task_fields` to edit the task fields that will be retrieved for each task in an Asana project.

### 3.10.2 - 2023-10-10

#### Changed

- Clearing the Asana Data Cache no longer completely deletes all request tokens, so it's now compatible with frontend page caching.

#### Fixed

- Media attachments with uppercase file suffix, such as `JPG` or `PNG`, would not be displayed in Project Embeds.
- Style issues on the Settings screen when using Chrome with the Loom browser extension.
- Error 404 when using the new Asana project URL as the `src` in Project Embeds.
