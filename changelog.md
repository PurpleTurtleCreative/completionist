### [Unreleased] - 2022-03-06

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
