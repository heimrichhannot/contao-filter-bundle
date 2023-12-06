# Developers

## Developers

### Events

| Event                             | Event ID                                       | Description                                              |
|-----------------------------------|------------------------------------------------|----------------------------------------------------------|
| Adjust filter options             | `huh.filter.event.adjust_filter_options_event` |                                                          |
| Adjust filter value               | `huh.filter.event.adjust_filter_value_event`   |                                                          |
| FilterConfigInitEvent             | FilterConfigInitEvent::class                   | Modify config on FilterConfig initialization.            |
| FilterBeforeRenderFilterFormEvent | FilterBeforeRenderFilterFormEvent:class        | Modify the filter form template context before rendering |
| FilterFormAdjustOptionsEvent      | FilterFormAdjustOptionsEvent::class            | Modify form options before building the form.            |
| FilterQueryBuilderComposeEvent    | FilterQueryBuilderComposeEvent::class          | Description provided below.                              |
| ModifyJsonResponseEvent           | `huh.filter.event.modify_json_response_event`  | Modify the JSON response of async form submits.          |

#### FilterQueryBuilderComposeEvent

In this event you can modify the data before the query for the current element is
created and added to the QueryBuilder. It is also possible to add a query within in the
event and skip the subsequent query creating.

```php
function __invoke(FilterQueryBuilderComposeEvent $event): void
{
    // modify values before creating the query
    if ($event->getName() === 'my_field') {
         if ("special_value" === $event->getValue()) {
             $event->setOperator(DatabaseUtil::OPERATOR_NOT_IN);
             $event->setValue([3,5]);
             return;
         }
     }
     // create a custom query and skip the default query creation pars.
     if ($event->getName() === 'totally_custom_field') {
        // do some magic
        $event->getQueryBuilder()->andWhere('custom_table_field REGEXP '.$magicValue);
        $event->setContinue(false);
     }
}
```

## Create filter types

1. Create a class inherit from `HeimrichHannot\FilterBundle\Filter\AbstractType`
2. Implement inherited methods
3. Optional: override following methods:
   - `getInitialPalette()`
   - `isEnabledForCurrentContext()`
   - `normalizeValue()`
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