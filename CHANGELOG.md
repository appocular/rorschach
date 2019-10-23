# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased
### Changed
- Steps is now checkpoints.
- Don't print stack traces on errors like missing environment variables.

## 0.5.1 - 2019-10-21
### Changed
- Prints URL being screenshotted in debug output.

## 0.5.0 - 2019-10-15
### Added
- `wait_script` for delaying screenshot until the given JavaScript
  returns true.
- `dont_kill_animations` to disable the new animation disabling routine.

### Changes
- Now disables all transitions, translations and animations when
  taking screenshot.

### Fixed
- Print proper amount of seconds in debug output.

## 0.4.1 - 2019-10-11
### Changed
- Exit with non-zero code if any errors.

## 0.4.0 - 2019-10-09
### Added
- Debugging output added.
- `workers` option to set number of parallel processes. Defaults to 4.

### Chonged
- Output handling changed to properly support -d/-v/-vv/-vvv.

## 0.3.2 - 2019-10-04
### Added
- `wait` option for steps, to wait after navigation before taking the
  screenshot.
- `stitch_delay` option to wait after moving the viewport before
  taking the screenshot, when stitching together full page
  screenshots. This allows animations time to settle.

### Fixed
- Fixed the Stopgap URL printed after completing snapshot.

## 0.3.1 - 2019-09-28
### Added
- Support for Github Actions.

## 0.3.0 - 2019-09-19
### Added
- Fetches history from git, or environment var.
- Ability to hide CSS selectors before taking screenshot.
- Write-out/read-in options for testing without WebDriver.
- Setting browser size per step.

### Changed
- Retool to submit images to Appocular instead of Applitools Eyes.
- Config file format has changed.
- Require at least PHP 7.1.

### Removed
- Applitools Eyes support.

## 0.2.0 - 2018-05-17
### Changed
- Make full height screenshots.

## 0.1.0 - 2018-05-09
### Added
- Initial implementation.
