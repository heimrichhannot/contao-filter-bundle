# Changelog
All notable changes to this project will be documented in this file.

## [1.0.0] - 2020-04-14
- added async form submit support to select fields
- updated import in filter bundle js 

## [1.0.0-beta130.2] - 2020-04-09
- fixed error if page id is not set in ajax controller 

## [1.0.0-beta130.1] - 2020-04-08
- added retrieve global objPage from current page when not initialized in ajax request

## [1.0.0-beta130.0] - 2020-04-07
- renamed tl_filter_config.action to tl_filter_config.filterFormAction to fix problems with contao 4.9 (field is automatically renamend by the bundle)

## [1.0.0-beta129.1] - 2020-04-06
- fixed an autowiring issue
- removed an dev leftover from composer.json
- removed unnecessary submitOnChange's in tl_filter_config

## [1.0.0-beta129.0] - 2020-04-06
- allow install within contao 4.9 and symfony 4

## [1.0.0-beta128.7] - 2020-03-04
- fixed yarn deps

## [1.0.0-beta128.6] - 2020-03-04
- fixed detect if form is resetted in async mode

## [1.0.0-beta128.5] - 2020-02-26
- fixed reset of form for async submit

## [1.0.0-beta128.4] - 2020-02-25
- fixed replacement of filter for async submit

## [1.0.0-beta128.3] - 2020-02-19
- fixed where query for cfg tag field when value is empty

## [1.0.0-beta128.2] - 2020-01-07
- fixed invalid redirect url exception when filter action is set as insert tag

## [1.0.0-beta128.1] - 2019-12-20
- fixed incorrect exception in FilterType

## [1.0.0-beta128] - 2019-12-10
- fixed redirect issue

## [1.0.0-beta127] - 2019-12-04
- added missing messages

## [1.0.0-beta126] - 2019-11-18
- added option to reset filter on page load/reload

