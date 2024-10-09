### 4.4.1 - 2024-10-09

#### Fixed

- Updated minimum required PHP version to `8.1` to prevent plugin installation on incompatible systems. An unrecoverable fatal error would occur, caused by Composer dependencies in the Asana PHP SDK updated in Completionist v4.3.0.

### 4.4.0 - 2024-09-27

#### Added

- New `include_sections` attribute for the `[ptc_asana_project]` shortcode to specify which project sections should be displayed like `include_sections="First Section,Another Section"`. When using this attribute, all non-specified sections will be automatically excluded.

#### Changed

- Tested up to WordPress 6.6.2.
- Updated build script package versions.

### 4.3.0 - 2024-05-04

#### Added

- New shortcode `[ptc_asana_task]` to display an individual Asana task.
- Automation Actions now support dynamically pinning the newly created Asana task to the WordPress post which triggered its creation. Clear the "Pin to Post" input field value to see both available options, `{post.ID}` and `{post.post_parent}`.

#### Changed

- The "Pin to Post" field label in Automation Actions no longer shifts due to centered positioning.
- Improved error messaging when the "Pin to Post" field's post title could not be retrieved from the REST API.
- Prevent unnecessary Asana API calls when WordPress checks REST API route permissions.

#### Fixed

- Resolved log messages for PHP 8.1 by updating Asana's PHP SDK dependency packages.
- Resolved log messages for PHP 8.2 by forking Asana's PHP SDK and declaring missing class properties.
- REST API routes which require a logged in WordPress user would fail if no user is logged in and PHP `display_errors` is enabled.

### 4.2.1 - 2024-04-21

#### Changed

- Tested up to WordPress 6.5.2.
- Updated Asana color palette values.
- Updated build script package versions.
- Added "Support" to Helpful Links to direct feedback to the plugin support forum rather than personal email address. This promotes building a knowledgebase in public so users can quickly find answers.

#### Removed

- Removed latest version changes from being noted in the WordPress.org readme. To access the complete changelog history, please visit [https://purpleturtlecreative.com/completionist/plugin-info/](https://purpleturtlecreative.com/completionist/plugin-info/) or see `changelog.md` in Completionist's files.

#### Fixed

- Assignee name would be displayed as `(Not Connected)` in the admin widgets, even when the assignee's name is accessible from Asana. In the admin widgets, the WordPress user's display name is still prioritized if the assignee has connected their Asana account to their WordPress user profile.

### 4.2.0 - 2024-02-03

#### Added

- New JavaScript filter hook `TaskListItem_main_row_content` to modify the main header row of tasks listed in a project list. Helpful for injecting additional task data, such as custom fields.

### 4.1.1 - 2024-01-14

#### Fixed

- Typo in readme.

### 4.1.0 - 2024-01-14

#### Added

- New shortcode attribute `layout` to specify a custom layout for the `[ptc_asana_project]` shortcode. Note this is only useful for extenders and third-party customizations. Completionist always displays projects in `list` layout by default.
- New shortcode attribute `sort_tasks_by` to sort tasks by the specified field. Common values are `due_on`, `assignee`, `completed_at`, and `name`. Unlike Asana, subtasks are also sorted.
- `.pdf` attachments on tasks are now supported.
- External attachments (eg. Vimeo and YouTube embeds) are now displayed as their oEmbed HTML representations when available.

#### Changed

- Unsupported or otherwise non-displayable task attachments are now listed as error or warning notices for clarity. Previously, such attachments would simply be logged to the browser console and ignored.

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
- Resolve console errors and warnings in React scripts logged when `SCRIPT_DEBUG` is enabled.

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

### 3.6.0 - 2023-04-07

#### Added

- Project sections can now be excluded by name in the  `[ptc_asana_project]` shortcode using the new attribute like `exclude_sections="First Section,Another Section"`. No project sections are excluded by default.
- Tags on tasks are now displayed in the  `[ptc_asana_project]` shortcode with new attribute `show_tasks_tags="true"`. It is enabled by default.
- `.mp4` video attachments on tasks are now supported.
- YouTube and Vimeo video embeds within task descriptions are now supported. They are displayed responsively at a 16:9 aspect ratio.

#### Changed

- All default HTTP headers are now removed from proxied attachment requests to ensure only the desired headers are returned. For example, an `Expires` header would be present with a time that came before the desired `Cache-Control: max-age=...` duration.
- Updated the `@wordpress/scripts` package which is responsible for building ReactJS components and their styles.
- Added `untitled section` and `Untitled Section` to the default section names to be erased.
- Minor performance improvements to evaluating the `show_tasks_completed` option when retrieving tasks in an Asana project.
- The `'ptc-completionist-shortcode-asana-project'` stylesheet's version is now the stylesheet's build version rather than the Completionist plugin's version.

### 3.5.2 - 2023-03-27

#### Fixed

- Asana deprecations still being logged by some `\Asana\Client` instances that were loaded with different options than expected, particularly missing the `Asana-Enable` headers.

### 3.5.1 - 2023-03-22

#### Changed

- Remote plugin updates are now hooked by a named callback rather than an anonymous function.
- Plugin description and copyright year.
- Added "Project Embeds" as a feature listed in the README.
- Added documentation links for each feature in the README.

#### Fixed

- Fatal error when block editor assets are enqueued in a context with no current post ID, such as when using the classic theme Customizer.
- Enabled `new_goal_memberships` Asana update to resolve deprecation logs.

### 3.5.0 - 2023-03-19

#### Added

- Image attachments on tasks are now displayed in the  `[ptc_asana_project]` shortcode with new attribute `show_tasks_attachments="true"`. It is enabled by default.
- New REST API endpoint `/v1/attachments` to proxy Asana attachment requests.

#### Changed

- The `'ptc-completionist-shortcode-asana-project'` script's version is now the script's build version rather than the Completionist plugin's version.

#### Fixed

- Image attachments in task descriptions would break due to Amazon S3 source URLs expiring access after 2 minutes. This is now fixed in the  `[ptc_asana_project]` shortcode.
- Enabled `new_memberships` Asana update to resolve deprecation logs.

### 3.4.0 - 2022-11-04

#### Added

- By popular demand, the first frontend feature is now implemented! The shortcode, `[ptc_asana_project]`, displays an Asana project in list layout.
- First custom REST API endpoint, `/v1/projects`, with the new Request Keys architecture to securely perform requests from the website's public frontend.
- Settings option for which WordPress user's Asana connection should be used to authenticate frontend requests by default.
- Many new ReactJS components and styles to render the `[ptc_asana_project]` shortcode.
- Helpful links to the Settings screen to quickly access Asana, Completionist's documentation, Completionist's changelog, and where to send feedback.

#### Changed

- Minor style tweaks to the Settings admin screen.

#### Removed

- User task list links from the "site collaborators" list on the Settings screen. The Asana API returns a `Forbidden` error when trying to get the user task list link for other users, defeating the purpose of this feature and causing useless API requests.

#### Fixed

- Duplicate display of a user listed in the plugin settings screen when their connected Asana account email does not match their WordPress user account email.
- Repeated authentication to the Asana API when using the currently logged-in user's identity. This fix improves performance and reduces API calls for connected Asana users in wp-admin.

### 3.3.0 - 2022-10-15

#### Added

- Pinned Tasks post editor metabox has been converted to ReactJS, offering a better experience, and is now directly integrated into the Block Editor as a plugin panel.
- Several new ReactJS components to compose the new Pinned Tasks plugin panel in the Block Editor.
- Task request errors are now elegantly displayed on the frontend for the Dashboard Widget and Pinned Tasks panel ReactJS components. Before, errors were only logged to the browser console or rarely displayed in a browser window alert.

#### Changed

- The original, jQuery-based Pinned Tasks metabox is now marked as existing only for backwards compatibility with WordPress's Classic Editor.
- The `ptc_create_task` AJAX action now returns the full Asana task object in `response.data` instead of the generated HTML representation.
- The `ptc_pin_task` AJAX action now returns the full Asana task object in `response.data` instead of just the task GID. This also helps ensure proper task visibility permissions are enforced.
- `Asana_Interface::maybe_get_task_data()` and `Asana_Interface::create_task()` now also include the task's `action_link` for frontend component compatibility.
- The ReactJS Pinned Tasks panel and Dashboard Widget styles are now imported as SCSS modules for automatic compiling and composition ease.
- Simplified the Dashboard Widget's HTML `id` from `ptc-PTCCompletionistTasksDashboardWidget` to simply `ptc-DashboardWidget`.

#### Removed

- Distribution build files, such as webpacked JavaScript and processed CSS, from version control.

#### Fixed

- Task due dates being one day off in the Dashboard Widget, depending on your local timezone.
- Enabled `new_project_templates` Asana update to resolve deprecation logs.

### 3.2.0 - 2022-05-22

#### Added

- Per user request, Automations now supports entering a custom action or filter hook name as the Event Trigger.
- Helpful messaging in Automations. For example, when no Event Trigger is selected, the Conditions section will display an informative message rather than empty fields.

#### Changed

- Improved the UX of Automations' edit screens by tweaking some styles and messaging.

#### Fixed

- The Automations edit screen is now more reliable. In certain cases, for example, the deleted Action would not reflect as deleted. The underlying data would become out-of-sync with the frontend display.
- Removed usage of `FILTER_SANITIZE_STRING` PHP filter, which is deprecated in PHP 8.1.

### 3.1.1 - 2022-04-10

#### Changed

- Separated ReactJS build entrypoints to ensure only necessary scripts are enqueued for the Tasks dashboard widget and Automations screen.
- Display a message when there are no tasks to display for the selected filter in the Tasks dashboard widget.
- Hide the pagination navigation when there is only 1 page of tasks in the Tasks dashboard widget.
- Removed ReactJS source files from package bundling.

#### Fixed

- Task descriptions with long words (such as link URLs) causing layout breaks in the Tasks dashboard widget.
- Completed tasks showing in Tasks dashboard widget on initial load.
- Fatal error when an Automation triggered to create an Asana task with no description.

### 3.1.0 - 2021-12-25

#### Deprecated

- `$opt_fields` arguments in all relevant `Asana_Interface` methods, using new `Asana_Interface::TASK_OPT_FIELDS` member constant instead for consistency.

#### Added

- Many new ReactJS components to compose the new Tasks dashboard widget.

#### Changed

- Tasks dashboard widget has been converted to ReactJS, offering a better experience.
- Tasks dashboard widget style overhaul, offering a better experience.

#### Fixed

- `Asana_Interface::$wp_user_id` would not be properly set to the current user's ID when loading the Asana client where user ID is `0`, the default value.
- Newlines not rendering in the dashboard widget's task descriptions.

### 3.0.1 - 2021-11-09

#### Fixed

- Incorrect require file path in the admin dashboard widget.
- Minor style fixes.

### 3.0.0 - 2021-07-27

#### Changed
- Greatly improved source code organization—introduces breaking changes for source code users.
- Minor style tweaks due to better color organization and standardization.
- Task descriptions are now displayed on click instead of hover.
- Increased task title font-size in the dashboard widget and pinned tasks metabox task lists.
- Automation action buttons are now hidden until the automation row is hovered.
- Updated the Asana client library to `v0.10.2`.

#### Fixed
- Plugin "Docs" link refers to the new PTC Docs website!
- Character escaping visible in Automation description.
- Enabled `new_user_task_lists` Asana updated endpoint to resolve deprecation logs.
- Task actions no longer cause layout shifting when displayed on hover in the dashboard widget and pinned tasks metabox task lists.

### 2.0.0 - 2021-01-18
#### Added
- Remote updates via YahnisElsts/plugin-update-checker and custom resources API.

#### Removed
- Licensing and remote updates system via WCAM.

#### Changed
- Software is now licensed under GPL v3 or later.

#### Fixed
- Unnecessary files drastically increasing plugin size.
- Asana API outage causing fatal TypeError when attempting to identify the current Asana user.

### 1.1.0 - 2020-08-22
#### Added
- NEW "Automations" feature!

#### Changed
- Licensing is no longer required to use Completionist's features, but license activation is still required to check for and receive plugin updates.
- Improved design of licensing screens.

#### Fixed
- Authenticated users who could not be matched by email in Asana are now properly included in the site collaborators listing.
- Added existence checks for classes and functions used in uninstallation script.
- Corrected retrieval and typecasting on array of users returned when searching for WordPress user by Asana GID.

### 1.0.1 - 2020-04-21
#### Fixed
- Malformed script enqueue condition causing excessive error logging.

### 1.0.0 - 2020-04-16
#### Added
- Initial release featuring task-to-post pinning and the tasks overview dashboard widget.
