=== Completionist ===
Contributors: michelleblanchette
Tags: asana, project, task, management, manager, integration, api, work, business, collaboration, client, customer, support, portal, dashboard, widget, metabox, shortcodes
Requires at least: 5.0.0
Tested up to: 6.3
Stable tag: 4.0.0
Requires PHP: 7.1
License: GPL v3 or later
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

== Frequently Asked Questions ==

= Are you affiliated with Asana? =

No, I do not have a business relationship with Asana. I just happened to be looking for an Asana integration for WordPress and failed to find an affordable, specialized solution. That is why I created Completionist!

= Is an Asana account required? =

Yes, an Asana account is required to use Completionist. You can quickly [create an Asana account](https://asana.com/create-account) for **FREE**!

== Upgrade Notice ==

= 4.0.0 =
This plugin will now be hosted from the official WordPress.org Plugins directory. Please upgrade immediately so you can continue to receive the latest features, bug fixes, and security updates.

== Changelog ==

### 4.0.0 - 2023-09-03

#### Added

- New `readme.txt` file for WordPress.org plugins listing.
- New `Uninstaller` class to handle plugin data removal.

#### Changed

- Remote updates are now handled through WordPress.org and [Freemius](https://freemius.com/). This change also provides the option to upgrade to Completionist Pro.

#### Removed

- The `YahnisElsts/plugin-update-checker` Composer package which facilitated remote updates.
- The `uninstall.php` file. Data is now uninstalled by using the registered uninstall hook.

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

### 3.9.1 - 2023-07-28

#### Changed

- FontAwesome assets are now included locally to avoid third-party tracking and hosting.

#### Fixed

- Database compatibility for the Request Tokens database table primary key size. The database table would continuously fail to install due to max key length limits, such as 1000 bytes, making shortcodes unusable on some hosting providers.

### 3.9.0 - 2023-07-10

#### Added

- New filter hooks in JavaScript for adding custom ReactJS components after the task description in the `TaskListItem` component.
- New filter hooks in PHP for customizing retrieved Asana project data.
- New PHP class `Asana_Batch` to handle batching Asana API requests.

#### Changed

- Use `wp_remote_get()` instead of PHP's built-in cURL functions to proxy Asana attachments.
- Proxied attachment responses now include the `X-Robots-Tag: noindex` HTTP header.
- Task attachments and images now feature a loading animation.
- Updated Composer PHP dependencies.

#### Fixed

- Failure to detect and localize inline attachments where their HTML tag includes attributes.
- Failure to detect and insert oEmbeds within Asana tasks on WordPress <5.9.0.
- Disabled dragging of attachment images via the `draggable="false"` HTML attribute.
- Content layout shifting (CLS) of attachment images as they are loaded. Inline images now feature their intrinsic `width` and `height` to properly reserve space. Attachments and images with unknown dimensions reserve space for a 2:1 aspect ratio until loaded.

### 3.8.0 - 2023-06-26

#### Added

- New action hook in PHP `'ptc_completionist_deleted_stale_request_tokens'` fires when stale request tokens are deleted from the database.
- New action hook in PHP `'ptc_completionist_deleted_all_request_tokens'` fires when the request tokens database table is truncated (aka cleared).
- New filter hook in PHP `'ptc_completionist_shortcodes_meta_init'` filters the metadata definitions array of shortcodes registered and managed by Completionist.
- `'default_atts'` and `'render_callback'` keys in the metadata definitions array of shortcodes registered and managed by Completionist.

#### Changed

- The main admin page's submenu item is now labeled "Settings".
- Minor error check improvements when saving some plugin settings.
- Minor style updates for border-radius consistency.

#### Fixed

- Flash of an empty error message before the `[ptc_asana_project]` shortcode begins loading.
- Cache data for remote plugin update checks is now removed during uninstallation.

#### Security

- Request tokens could fail to become stale due to public access. Request tokens are now only refreshed when they are saved in a secure context. Note that HTML caching could now cause request tokens to become stale, depending on the interval and frequency that the HTML cache is refreshed. See the newly added action hooks to know when request tokens are deleted.
- Request tokens would use the Asana connection of WordPress user ID 1 (if available) to authenticate `[ptc_asana_project]` shortcodes when no `auth_user` or default frontend authentication user has been specified.

### 3.7.0 - 2023-04-21

#### Added

- New custom database table for the new request tokens architecture. The rearchitecture drastically improves performance, storage, and reliability for all frontend requests by batching database writes into a single transaction and using an atomic storage strategy.
- New action hook `ptc_completionist_shortcode_enqueue_assets` for users to easily and efficiently enqueue custom scipts and stylesheets for each rendered shortcode.
- JavaScript hooks are now available within the new frontend global `window.Completionist.hooks`. Only one filter hook is currently available to demonstrate this new architecture.

#### Changed

- Saving the "Frontend Authentication User" setting no longer forces the Asana request tokens cache entries to be deleted. Request tokens are now properly invalidated automatically.
- Minor performance improvement when rendering multiple shortcodes on a single page load.
- Minor performance improvement when `Database_Manager::init()` by preventing redundant initialization.

#### Deprecated

- The original `Request_Tokens` class and most of its methods. Methods related to data removal are still used for a clean migration to the new  `Request_Token` class.
- The `Options::REQUEST_TOKENS` postmeta key used by the original `Request_Tokens` class.
- The `$post_id` argument is deprecated and no longer used when localizing Asana attachment URLs. This is due to the request tokens rearchitecture.
- Filter hook `ptc_completionist_shortcode_asana_project_script_deps` is deprecated and replaced by the new action hook `ptc_completionist_shortcode_enqueue_assets`. Filtering the dependency array of each asset for each shortcode is not a scalable or performant approach. Please instead use the new, generic action hook for enqueueing custom assets. The new action hook also ensures custom assets are enqueued *after* included assets for proper sequencing of script and stylesheet overrides.

#### Fixed

- The `[ptc_asana_project]` shortcode now works in contexts where a post ID is not available, such as in widgets or complex page builders.
- The `[ptc_asana_project]` shortcode's associated data would be saved across unrelated posts if displayed in a global context, such as a site footer.
- Race conditions with database reads and writes related to request tokens would cause interruptions in functionality, such as Asana attachments failing to load.
- WordPress's `dbDelta()` compatibility with `FOREIGN KEY` declarations by ignoring its related `ADD COLUMN` table alter queries.
- WordPress's `dbDelta()` compatibility with uppercase `UNSIGNED` constraints by using lowercase instead.
- WordPress's `dbDelta()` compatibility with a zero fractional second precision `datetime` datatype by simply removing it altogether. That is, using `datetime` instead of `datetime(0)`.

#### Security

- An Asana authentication user must now be provided when request tokens are created. This *guarantees* request tokens are properly invalidated if the authentication user ever changes, though there were no known vulnerabilities related to the existing functionality.
- A unique "cache key" must now be provided when request tokens are created. This *guarantees* cache entries are specific to each request token's usage, though there were no known vulnerabilities related to the existing functionality.
