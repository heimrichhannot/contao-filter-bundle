# Contao filter bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-filter-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-filter-bundle.svg)

This bundle offers a generic filter module to use with arbitrary contao entities containing standard filter with initial filters and filter form types including [symfony form type representations](https://symfony.com/doc/current/reference/forms/types).

## Features
- Form handling using symfony form component 
- Form rendering by using symfony form templates (currently available: bootstrap 3-5, foundation, div, table)
- Numerous [symfony form types](https://symfony.com/doc/4.4/reference/forms/types.html) supported
- Highly customizable and detached from tl_module table
- Label/Message handling using symfony translations
- Render form always empty (without user selection)
- Merge data over multiple filter forms with same form name
- Default Values (can be overwritten by user)
- Initial Values (can`t be overwritten by user)
- Stores filter data in session (no GET parameter URL remnant)
- Content element "Filter-Preselect" with optional redirect functionality to preselect filter on given page
- Content element "Filter-Hyperlink" with filter preselect feature
- Integrations:
   - `codefog/contao-news_categories`
   - `codefog/tags-bundle`
   - `heimrichhannot/contao-categories-bundle`
   - `heimrichhannot/contao-encore-bundle`

## Usage

### Install 
1. Install with composer or contao manager

    ```
    composer require heimrichhannot/contao-filter-bundle
    ```
   
1. Update database

We recommend to use this bundle toghether with [List Bundle](https://github.com/heimrichhannot/contao-list-bundle) and [Reader Bundle](https://github.com/heimrichhannot/contao-reader-bundle).

### Setup

1. Create a filter configuration within System -> Filter & sort configuration
1. Add filter elements to the filter config.
1. If you want to show the filter somewhere (for example to filter a list), create a filter/sort frontend module.

### Wrapper elements (DateRange, ProximitySearch, ...)

The Wrapper element has to be places **before** the fields associated with them. 
For example the date_range wrapper element needs to be placed before the two associated date fields.

### Preselect

Filter Bundle Forms are not typical GET-Forms, so it is not possible to simple 
copy the filter urls to share or bookmark
a filtered list. To overcome this limitation, preselect urls can be generated.
Preselect urls for the current filter can be found within template variabled, 
you can create a preselect content element or get the url programmatically from 
the FilterConfig.

#### Template variables
If a filter is set, the variable `preselectUrl` contains the preselection url for
the current filter. It's available in the filter templates and the frontend module
template.

You can for example create a copy preselect url button:

```twig
{% if preselectUrl is defined and preselectUrl is not empty %}
   <div class="col-xd-12 col-md-3">
   <a class="btn btn-primary" onclick="navigator.clipboard.writeText('{{ preselectUrl }}');alert('Copied preselect link!');return false;">Filtervorauswahllink kopieren</a>
   </div>
{% endif %}
```

#### Content element
You can use one of the following content elements:

- "Filter-Preselect" with optional redirect functionality to preselect filter on given page
- "Filter-Hyperlink" with filter preselect feature

#### FilterConfig

You can generate the preselect link from the FilterConfig instance

```php
<?php 
use HeimrichHannot\FilterBundle\Manager\FilterManager;

class CustomController {
   private FilterManager $filterManager;
   
   public function invoke(): string
   {
       $filterConfig = $this->filterManager->findById($this->objModel->filter);
       return !empty($filterConfig->getData()) ? $filterConfig->getPreselectAction($filterConfig->getData(), true) : ''
   }
}
```


## Inserttags

Insert tag | Arguments | Description
--- | --------- | ------- 
`{{filter_reset_url::*::*}}` | filter ID :: page ID or alias | This tag will be replaced with a reset filter link to an internal page with (replace 1st * with the filter ID, replace 2nd * with the page ID or alias)

## Further documentation

[Developer introductions](docs/developers.md)
[Templates](docs/templates.md)