# Completionist by Purple Turtle Creative - Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased - 1.1.0
### Added
- New "Automations" feature.
- Filter hook, 'ptc_cmp_create_task_comment', to filter the comment added to new Asana tasks.

### Changed
- Licensing is no longer required to use Completionist's features, but license activation is still required to check for and receive plugin updates.

### Fixed
- Authenticated users who could not be matched by email in Asana are now properly included in the site collaborators listing.
- Added existence checks for classes and functions used in uninstallation script.
- Corrected retrieval and typecasting on array of users returned when searching for WordPress user by Asana GID.

## 1.0.1 - 2020-04-21
### Fixed
- Malformed script enqueue condition causing excessive error logging.

## 1.0.0 - 2020-04-16
### Added
- Initial release featuring task-to-post pinning and the dashboard tasks overview widget.