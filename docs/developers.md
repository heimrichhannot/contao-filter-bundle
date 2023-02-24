# Developers

## Create filter types

1. Create a class inherit from `HeimrichHannot\FilterBundle\Filter\AbstractType`
2. Add `TYPE` constant (e.g. `public const TYPE = 'choices';`)
3. Register your type within `huh.filter.types`
    
    ```yaml
    huh:
      filter:
        types:
          - { name: text, class: HeimrichHannot\FilterBundle\Filter\Type\TextType, type: text , wrapper: false }
    ```
4. Register you type palette withing `$GLOBALS['TL_DCA']['tl_filter_config_element']['palettes']`
5. Add translation within `$GLOBALS['TL_LANG']['tl_filter_config_element']`