=== Completionist – Asana Integration Suite ===
Contributors: michelleblanchette
Tags: asana, project, task, management, manager, integration, api, work, business, collaboration, client, customer, support, portal, dashboard, widget, metabox, shortcodes
Requires at least: 5.0.0
Tested up to: 6.4.1
Stable tag: 4.0.0
Requires PHP: 7.2
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Integrates Asana project management with WordPress content management.

== Description ==

Completionist is an integration WordPress plugin for Asana. It helps you establish a complete project management workflow between your Asana workspace and WordPress website.

## Features

There are many ways to improve your productivity by using Completionist.

### Pinned Tasks

Pin an existing Asana task or create a new task right on relevant content in WordPress. You can pin tasks to content of any post type. Reduce frustrating context-switching by having all relevant tasks listed in one place—where the work needs to get done.

[**Read the Docs**](https://docs.purpleturtlecreative.com/completionist/pinned-tasks/)

### Dashboard Widget

See task progress and quickly access all outstanding tasks on your WordPress dashboard. Upon logging in to your WordPress site, you'll know immediately what work needs to get done. If you have pinned tasks, you'll immediately be able to access the content with outstanding tasks. Filter tasks by *Pinned*, *General*, *Critical*, or *My Tasks*.

[**Read the Docs**](https://docs.purpleturtlecreative.com/completionist/dashboard-widget/)

### Automated Tasks

Create Asana tasks automatically as events occur on your WordPress site to standardize task workflows. Set your own custom automation conditions, and compose tasks using dynamic merge fields. Standardize your processes, remove busy work, and never miss a beat.

[**Read the Docs**](https://docs.purpleturtlecreative.com/completionist/automated-tasks/)

### Project Embeds (Shortcodes)

Display Asana project information and tasks on your WordPress site. Automatically share work progress with stakeholders and followers without on-boarding read-only users into your private workspace in Asana.

[**Read the Docs**](https://docs.purpleturtlecreative.com/completionist/shortcodes/)

---

_Completionist by Purple Turtle Creative is not associated with Asana. Asana is a trademark and service mark of Asana, Inc., registered in the U.S. and in other countries. [Learn more.](https://asana.com/)_

_Premium features are sold and distributed via Freemius. [Learn more.](https://freemius.com/)_

_All source code is available on [GitHub](https://github.com/PurpleTurtleCreative/completionist)._

== Frequently Asked Questions ==

= Are you affiliated with Asana? =

No, I do not have a business relationship with Asana. I just happened to be looking for an Asana integration for WordPress and failed to find an affordable, specialized solution. That is why I created Completionist!

= Is an Asana account required? =

Yes, an Asana account is required to use Completionist. You can quickly [create an Asana account](https://asana.com/create-account) for **FREE**!

== Upgrade Notice ==

= 4.0.0 =
This plugin will now be hosted from the official WordPress.org Plugins directory. Please upgrade immediately so you can continue to receive the latest features, bug fixes, and security updates.

== Changelog ==

_Here are the latest changes. You can access the complete changelog history at [https://purpleturtlecreative.com/completionist/plugin-info/](https://purpleturtlecreative.com/completionist/plugin-info/)_

### 4.0.0 - [unreleased]

#### Added

- New submenu page to upgrade to Completionist Pro for premium features via [Freemius](https://freemius.com/).
- New `readme.txt` file for WordPress.org plugins listing.
- New `Uninstaller` class to handle plugin data removal.
- New `Upgrader` class to handle plugin version updates. This also offers support assistance when a version rollback is detected, which usually indicates that the user is experiencing issues with a newer version of the plugin.
- New `Admin_Notices` class to handle displaying of admin notices. All notices are respectful in that they are either displayed once or dismissible.
- New `Errors\No_Authorization` exception type class to fix class name and file inconsistency.
- New `Autoloader` class to autoload class files.
- New REST API endpoints to replace all WP Admin AJAX actions.

#### Changed

- Remote updates are now handled through WordPress.org.
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

#### Security

- Various improvements with the new REST API endpoints which replace the original WP Admin AJAX actions.
- Unique nonces to authorize different requests to the new REST API endpoints which replace the original WP Admin AJAX actions.
- Searching for posts in an Automation Action's "Pin to Post" field would include posts that the current user did not have permission to read.

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

### 3.10.1 - 2023-09-15

#### Fixed

- PHP error when creating or displaying Asana tasks assigned to an Asana user connection that's being used by multiple WordPress users in Completionist.
- PHP 8.2 compatibility for handling dates which was causing fatal errors when trying to create and display tasks in the Pinned Tasks metabox.
- Increased minimum version requirement to PHP 7.2 due to Composer dependencies.

### 3.10.0 - 2023-08-15

#### Added

- Settings to clear the Asana data cache and set the cache duration (TTL).

#### Changed

- Improved the styling and language clarity of the Asana authorization screen.

#### Fixed

- Automation actions firing multiple times or never at all for the *Post is Created* event. The `'transition_post_status'` action hook is now used instead of `'wp_insert_post'`.
- Large images that failed to load would overflow the container in Project Embeds.
- Special characters would be encoded to HTML entities or completely stripped in automations and Asana tasks created by automations.
- Minor style fix on the Settings admin page.
