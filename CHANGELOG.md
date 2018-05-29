# Linkit Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.0.9- 2018-05-09

### Added

*   Added `isElementLink()` check to link models to quickly determine if the link is an element type
*   Added `isAvailable()` check to link models to quickly determine if the link is an element type

### Fixed

*   Linkit fields are now previewable table colums in table view
*   Fixed issue with the Craft 2 normailise function when a type is not specified

## 1.0.8 - 2018-04-24

### Fixed

*   Fixed element select - now respects selected site in the cp

## 1.0.7.1 - 2018-04-24

### Added

*   Fixed version issue

## 1.0.7 - 2018-04-24

### Added

*   Added Craft 2 migration scripts
*   Added product link type (requires the awesome Craft Commerce)

### Fixed

*   Fixed email & phone links text value returning the full url
*   Fixed documentation link

## 1.0.6 - 2018-04-16

### Fixed

*   Fixed the select toggle where there is multiple Linkit field's on the same layout
*   Fixed the default text displaying incorrectly for element links

### Added

*   Added `getLinkAttributes()` to access any addional attributes set by Linkit
*   Added `getTargetString()` to access the current target setting `_self` or `_blank`

## 1.0.5 - 2018-03-31

*   Case fix - thanks Brad!

## 1.0.4 - 2018-03-30

*   Updated naming - thanks Brandon!

## 1.0.3 - 2018-03-27

*   Updated settings page to group link types.
*   Enabling link types is now done with lightswitches instead of checkboxes

## 1.0.2 - 2018-03-27

*   Updated icon and license.

## 1.0.0 - 2018-03-27

*   Initial release
