# Changelog

All notable changes to this project will be documented in this file.

## [1.23.6] - 2023-01-10
- Fixed: allow '0' as valid value

## [1.23.5] - 2022-12-28
- Fixed: php8 and objPage issues

## [1.23.4] - 2022-12-23
- Fixed: context issue in TypeChoice

## [1.23.3] - 2022-12-19
- Fixed: translator got wrong language if ajax filer submit

## [1.23.2] - 2022-12-19
- Fixed: locale not kept on ajax filter submit

## [1.23.1] - 2022-11-30
- Changed: raised fieldpalette dependency
- Fixed: exception in filter preselect element if filter has no action set
- Fixed: issues with fieldpalette fields of other fieldpalette tables (use DcaGenerator of fieldpalette)

## [1.23.0] - 2022-10-07
- Changed: support haste 5
- Fixed: hard dependency on blocks module

## [1.22.6] - 2022-10-07
- Fixed: notice in php 8.1

## [1.22.5] - 2022-09-27
- Fixed: session started on every page with filter

## [1.22.4] - 2022-09-08
- Fixed: FilterQueryBuilderComposeEvent not dispatcher if value is empty

## [1.22.3] - 2022-09-08
- Fixed: FilterQueryBuilderComposeEvent not dispatched for tag filter types

## [1.22.2] - 2022-07-01
- Fixed: exception in filter backend

## [1.22.1] - 2022-06-27
- Fixed: merge issues

## [1.22.0] - 2022-06-27
- Added: FilterCollection class for single access to filter types ([#25])
- Changed: FilterPreselect options should now have same options as FilterConfigElement in the frontend ([#25])
- Changed: minimum php version is now 7.4 ([#25])
- Changed: minimum utils bundle version is now 2.217 ([#25])
- Changed: made FilterConfig::buildForm() configurable ([#25])
- Changed: refactored some code to modern coding standards ([#25])
- Fixed: some deprecations ([#25])

## [1.21.3] - 2022-05-18

- Fixed: symfony 5 compatibility

## [1.21.2] - 2022-05-10
- Fixed: symfony 5 compatiblity

## [1.21.1] - 2022-03-04
- Fixed: preselect link not working of no action is set in filter config. Now referrer parameter is also evaluated

## [1.21.0] - 2022-03-03
- Added: absoluteUrl option to FilterConfig::getPreselectAction()
- Added: filter config property to FilterBeforeRenderFilterFormEvent
- Added: preselectUrl variable to filter and frontend module templates
- Fixed: preselect values for checkbox types reset filter 


## [1.20.7] - 2022-02-14

- Fixed: array index issues in php 8+

## [1.20.6] - 2022-02-11

- Fixed: event_dispatcher call in ajax

## [1.20.5] - 2022-02-10

- Added: twig extension service definitions (the ones delivered by extra-bundle aren't working in symfony 4 out of the box)

## [1.20.4] - 2022-02-10

- Fixed: added concrete twig extension bundles for BC reasons

## [1.20.3] - 2022-02-10

- Fixed: `spaceless` usages in twig templates
- Fixed: querybuilder parameter colons for symfony 5+

## [1.20.2] - 2022-02-09

- Fixed: config for symfony 5+
- Removed: twig extension services from yml config

## [1.20.1] - 2022-02-08

- Fixed: controllers to symfony 4+
- Fixed: symfony dependencies

## [1.20.0] - 2022-02-07
- Added: FilterBeforeRenderFilterFormEvent ([#24])
- Changed: minimum contao version is not 4.9 ([#24])
- Changed: minimum php version is now 7.3 ([#24])

## [1.19.3] - 2022-02-07

- Fixed: exchanged abandoned `twig/extensions` by `twig/extra-bundle`

## [1.19.2] - 2022-02-07

- Fixed: dependencies for contao 4.13

## [1.19.1] - 2022-01-07
- Fixed: missing translations for hide filter on auto_item checkbox

## [1.19.0] - 2022-01-06
- Added: option to hide filter if auto_item is set
- Fixed: missing english placeholder translation

## [1.18.1] - 2021-12-16
- Fixed: async form reset not working correctly after change from 1.17

## [1.18.0] - 2021-12-13
- Added: FilterConfigInitEvent
- Added: FilterFormAdjustOptionsEvent

## [1.17.0] - 2021-12-09
- Added: FilterQueryBuilderComposeEvent 
- Changed: added multiple support to dynamic options option
- Fixed: checkbox type submitOnChange on ajax forms
- Fixed: outdated values when form value mapping fails on ajax requests

## [1.16.0] - 2021-12-02
- Added: buildQueryForModels() to YearType
- Fixed: YearType not correctly evaluated in DateChoice type
- Fixed: exception in FilterConfig when not sessionKey set
- Fixed: DateChoice type date period always empty

## [1.15.0] - 2021-11-25

- Changed: added name as class to form_row at bootstrap 5 template

## [1.14.0] - 2021-11-16

- Changed: default value array behavior -> if no options are found, backend palette fields stay text fields instead of empty selects
- Fixed: insert tag replacing for default values

## [1.13.2] - 2021-11-12

- Fixed: missing page_id parameter in ajax context

## [1.13.1] - 2021-10-29
- Fixed: custom options for ParentType not working

## [1.13.0] - 2021-10-19
- Added: bootstrap 5 form theme
- Changed: use twig support bundle for template loading and rendering

## [1.12.3] - 2021-10-11

- Fixed: static method types in `FilterConfigElementModel`

## [1.12.2] - 2021-10-01
- Fixed: hide label option is used if set in db even if FilterType does not support it

## [1.12.1] - 2021-09-15

- Fixed: preview mode for contao 4.9

## [1.12.0] - 2021-08-31

- Added: support for php 8

## [1.11.3] - 2021-08-11
- Fixed: sql, published and skip_parent types are not evaluated as initial types ([#21])

## [1.11.2] - 2021-07-26
- Fixed: datetype not working independent from daterange ([#20])

## [1.11.1] - 2021-07-23
- Changed: added check for existing joinAlias in FilterQueryBuilder

## [1.11.0] - 2021-06-23
- Added: support for multilingual initial filter field values

## [1.10.2] - 2021-06-07
- fixed FilterConfig service alias

## [1.10.1] - 2021-03-25
- fixed error in js code introduced in 1.9.2 ([#18])

## [1.10.0] - 2021-03-23
- added wildcardSuffix options to whereWidget ([#17])

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


[#25]: https://github.com/heimrichhannot/contao-filter-bundle/pull/25
[#24]: https://github.com/heimrichhannot/contao-filter-bundle/pull/24
[#21]: https://github.com/heimrichhannot/contao-filter-bundle/pull/21
[#20]: https://github.com/heimrichhannot/contao-filter-bundle/pull/20
[#18]: https://github.com/heimrichhannot/contao-filter-bundle/pull/18
[#17]: https://github.com/heimrichhannot/contao-filter-bundle/pull/17
[#16]: https://github.com/heimrichhannot/contao-filter-bundle/pull/16
