# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](http://semver.org/).

## [1.3.0] - 2018-12-17
### Added
- Logging User Profile creation events. (Tiago Bember Simeao)


## [1.2.0] - 2018-04-20
### Added
- Adding createProfile() static method. (Tiago Bember Simeao)

### Changed
- Forcing module to be enabled for all projects on the backend. (Tiago Bember Simeao)


## [1.1.1] - 2018-03-05
### Changed
- Updating README with modern language. (Tiago Bember Simeao)
- Set minimum REDCap versipon to 8.0.3 (Tiago Bember Simeao)
- Enable enable-every-page-hooks-on-system-pages in config.json (Tiago Bember Simeao)
- Fix regression in config.js. (Tiago Bember Simeao)


## [1.1] - 2017-11-29
### Added
- Add sample User Profile project (Philip Chase)

### Changed
- Giving up esoteric approaches in favor of creating a dedicated getAutoId() method. (Tiago Bember Simeao)
- Updated pre-reqs and installation instructions in README (Philip Chase)


## [1.0] - 2017-11-08
### Added
- Initial release of REDCap User Profile, a REDCap external module that extends user accounts information according to your needs using a REDCap project to enter, edit, and manage extended user attributes
- This module provides: an easy way to create and manage user profiles; an API to assist developers in accessing user profiles information
