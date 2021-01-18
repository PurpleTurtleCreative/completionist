### 2.0.0 - [Unreleased]
#### Added
- Remote updates via YahnisElsts/plugin-update-checker and custom releases server.

#### Removed
- Licensing and remote updates system via WCAM.

#### Changed
- Software is now licensed under GPL v3 or later instead of proprietary terms and conditions.

#### Fixed
- Unnecessary files are now properly excluded from release package, drastically reducing plugin size.

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
