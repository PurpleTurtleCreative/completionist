### [unreleased]

#### Added

- By popular demand, the first frontend feature is now implemented! The shortcode, `[ptc_asana_project]`, displays an Asana project in list layout.
- First custom REST API endpoint, `/v1/projects`, with the new Request Keys architecture to securely perform requests from the website's public frontend.
- Settings option to define which WordPress user's Asana connection should be used to authenticate frontend requests by default.
- Many new ReactJS components and styles to render the `[ptc_asana_project]` shortcode.

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
