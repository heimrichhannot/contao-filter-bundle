# Changelog
All notable changes to this project will be documented in this file.

## [1.11.1] - 2021-07-23
- added check for existing joinAlias in FilterQueryBuilder

## [1.11.0] - 2021-06-23
- added support for multilingual initial filter field values

## [1.10.2] - 2021-06-07
- fixed FilterConfig service alias

## [1.10.1] - 2021-03-25
- fixed error in js code introduced in 1.9.2 (#18)

## [1.10.0] - 2021-03-23
- added wildcardSuffix options to whereWidget (#17)

## [1.9.3] - 2021-03-22
- adjust service definition for `FieldOptionsChoice`

## [1.9.2] - 2021-03-18
- Fix enter key behavios on async forms (#16)

## [1.9.1] - 2021-03-16
- fixed issue concerning different tags bundle versions

## [1.9.0] - 2021-03-16
- added initial value support for codefog-tags-based fields

## [1.8.1] - 2021-03-15
- fixed insert tag issue

## [1.8.0] - 2021-03-15
- added field "doNotCacheOptions" for `ChoiceType`

## [1.7.0] - 2021-03-04
- removed session interaction for initial filters
- fixed getWidgetOptions in FieldOptionsChoice to display correct options, according to element configuration

## [1.6.8] - 2021-02-11
- added insert tag support for default values

## [1.6.7] - 2021-02-09
- added service class aliases for FilterManager and FilterSession

## [1.6.6] - 2021-02-09
- added missing translation

## [1.6.5] - 2021-02-08
- made ajax list optional for async filter (a filter can also be used without a list)

## [1.6.4] - 2021-01-29
- fixed isInitial palettes from DateTimeType, DateType and time, removed attr_label from submit and reset fields (#14)

## [1.6.3] - 2021-01-22
- fixed attribute setting in asyncFormSubmit

## [1.6.2] - 2021-01-15
- fixed missing reference in `dca->options`-based choices

## [1.6.1] - 2020-11-05
- added check for empty request (e.g. in command situations)

## [1.6.0] - 2020-10-29
- added async submit for `TextConcatType` to update list while typing in field
- added async submit for `TextType` to update list while typing in field

## [1.5.4] - 2020-10-08
- fixed reviseOptions placeholder bug

## [1.5.3] - 2020-10-07
- fixed filter preselect

## [1.5.2] - 2020-10-01
- fixed reviseOptions -> now only respects the initial filter elements, since else inconsistent situations can happen
- added dynamicOptions to ChoiceType palette
- added translation
- fixed Intl bug in contao 4.9

## [1.5.1] - 2020-10-01
- added translation and sorting for sorting options

## [1.5.0] - 2020-09-17
- added bootstrap 3 input groups

## [1.4.1] - 2020-09-16
- fixed proximity search

## [1.4.0] - 2020-09-15
- added proximity search

## [1.3.1] - 2020-09-02
- fixed TextConcatType resulted in showing non published results

## [1.3.0] - 2020-07-03
- added new type `CurrentUserType`

## [1.2.10] - 2020-07-02
- fixed `options_callback` for `sourceTable`

## [1.2.9] - 2020-06-23
- fixed `PublishedType` fe preview

## [1.2.8] - 2020-06-23
- fixed bug for hidden filter config elements

## [1.2.7] - 2020-06-23
- fixed `PublishedType` -> now supports frontend preview correctly

## [1.2.6] - 2020-06-02
- fixed choice option bug in frontend for year filter (label and value were swapped)

## [1.2.5] - 2020-05-28
- fixed choice option bug in frontend (label and value were swapped)

## [1.2.4] - 2020-05-27
- fixed choice option bug (label and value were swapped)

## [1.2.3] - 2020-05-25
- fixed group by issue in `SqlType`

## [1.2.2] - 2020-05-20
- fixed full group by issue

## [1.2.1] - 2020-04-28
- fixed issue when hash is in form action

## [1.2.0] - 2020-04-28
- added new filter label `huh.filter.label.reset_filter`

## [1.1.2] - 2020-04-27
- added necessary label tag to inputs with hidden labels, by css class sr-only

## [1.1.1] - 2020-04-22
- fixed php_cs style

## [1.1.0] - 2020-04-22
- fixed handling for `resetFilterInitial` -> now it's based on referrer which prevents issues with the pagination

## [1.0.1] - 2020-04-14
- fixed resolving whether reset button has been clicked when `clickedButton` is not set for form

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
- removed a dev leftover from composer.json
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

