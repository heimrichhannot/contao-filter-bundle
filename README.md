# Contao filter bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-filter-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-filter-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-filter-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-filter-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-filter-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-filter-bundle)

This bundle offers a generic filter module to use with arbitrary contao entities containing standard filter with initial filters and filter form types including [symfony form type representations](https://symfony.com/doc/current/reference/forms/types).


## Templates (filter)

There are two ways to define your templates. 

#### 1. By Prefix

The first one is to simply deploy twig templates inside any `templates` or bundles `views` directory with the following prefixes:

** filter template prefixes**

- `filter_`

**More prefixes can be defined, see 2nd way.**

#### 2. By config.yml

The second on is to extend the `config.yml` and define a strict template:

**Plugin.php**
```
<?php

class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        …
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        return ContainerUtil::mergeConfigFile(
            'huh_filter',
            $extensionName,
            $extensionConfigs,
            __DIR__ .'/../Resources/config/config.yml'
        );
    }
}
```

**config.yml**
```
huh:
  filter:
    templates:
      - {name: form_div_layout, template: '@HeimrichHannotContaoFilter/filter/filter_form_div_layout.html.twig'}
      - {name: form_table_layout, template: '@HeimrichHannotContaoFilter/filter/filter_form_table_layout.html.twig'}
      - {name: bootstrap_3_layout, template: '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_3_layout.html.twig'}
      - {name: bootstrap_3_horizontal_layout, template: '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_3_horizontal_layout.html.twig'}
      - {name: bootstrap_4_layout, template: '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_layout.html.twig'}
      - {name: bootstrap_4_horizontal_layout, template: '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_horizontal_layout.html.twig'}
      - {name: foundation_5_layout, template: '@HeimrichHannotContaoFilter/filter/filter_form_foundation_5_layout.html.twig'}
    template_prefixes:
      - filter_
```

## Bootstrap 4 form snippets

The following bootstrap 4 form theme snippets can be used to generate uncommon, but existing bootstrap 4 form widgets within your custom `filter_form_bootstrap4*.html.twig` template.

### [Radio buttons](https://getbootstrap.com/docs/4.0/components/buttons/#checkbox-and-radio-buttons)

Replace `categories` with the name of your custom field. Remove `onchange` handler if not required.
Select fallback can be used on small devices, if too many options, display/hide, using `@media` breakpoints. 

```
{% if(form.categories is defined) %}
    <div class="disable-hidden-inputs {{ ('form-group' ~ ' ' ~ form.categories.vars.id ~ ' ' ~ form.categories.vars.name ~ ' ' ~ form.categories.vars.attr.class)|trim }}">
        {{ form_label(form.categories) }}
        {% do form.categories.setRendered %}
        <div class="select-fallback">
            <label class="form-control-label" for="{{ form.categories.vars.id }}-select">{{ form.categories.vars.label|trans }}</label>
            <select id="{{ form.categories.vars.id }}-select" onchange="this.form.submit();" name="{{ form.categories.vars.full_name }}" class="form-control">
                {% for key,choice in form.categories.vars.choices %}
                    <option data-icon="category-{{ choice.value }}" value="{{ choice.value }}"{{ (choice.value == form.categories.vars.value ? ' selected' : '') }}>{{ choice.label }}</option>
                {% endfor %}
            </select>
        </div>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            {% for key,choice in form.categories.vars.choices %}
                <label class="{{ ('btn btn-link' ~ (choice.value == form.categories.vars.value ? ' active' : ''))|trim }}">
                    <input type="radio" id="{{ form.categories.vars.id }}_{{ key }}" {% if choice.value == form.categories.vars.value %}checked{% endif %}
                           autocomplete="off" onchange="this.form.submit();" name="{{ form.categories.vars.full_name }}" value="{{ choice.value }}">
                    {{ choice.label }}
                </label>
            {% endfor %}
        </div>
    </div>
{% endif %}
```

You must also set the non visible inputs to `disabled` in order to prevent them from being submitted and overwrite selected value due to same input name.
The following script can be used to achieve this behavior.

```
(function($) {
    function disableHiddenInputs () {
        var $selector = $('.disable-hidden-inputs');
        $selector.find(':hidden').children(':input').prop('disabled', true);
        $selector.find(':not(:hidden)').children(':input').prop('disabled', false);
    };
    
    $(function() {
        disableHiddenInputs();
    });

    $(window).resize(function() {
        disableHiddenInputs();
    });
})(jQuery);
```