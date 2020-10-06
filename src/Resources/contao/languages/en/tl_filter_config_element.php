<?php

$lang = &$GLOBALS['TL_LANG']['tl_filter_config_element'];

/**
 * Fields
 */
$lang['title']              = ['Title', 'Please enter a title.'];
$lang['type']               = ['Type', 'Select a field type.'];
$lang['field']              = ['DCA field', 'Select a mapping dca field.'];
$lang['fields']             = ['Fields', 'Select multiple fields that should be combined in given order.'];
$lang['customOptions']      = ['Custom options', 'Use choices.'];
$lang['reviseOptions']      = ['Revise options', 'Select this option to clean up option values (options without occurrences in the result set based on the current filter). Supported only if the option values are determined using the foreign key (foreignKey) of the field.'];
$lang['adjustOptionLabels'] = ['Customize option labels', 'Select this option if you want to customize the labels of the options.'];
$lang['optionLabelPattern'] = ['Options label', 'Select an options label whose template you want to customize the option labels.'];
$lang['options']            = ['Options', 'If JavaScript is disabled, make sure to save your changes before modifying the order.'];
$lang['customName']         = ['Custom name', 'Set a custom field name.'];
$lang['name']               = ['Form field name', 'Enter the name for the field in the HTML form.'];
$lang['customOperator']     = ['Custom operator', 'Set a custom operator.'];
$lang['operator']           = ['Operator', 'Choose a custom operator.'];
$lang['addPlaceholder']     = ['Add placeholder', 'Add a placeholder value.'];
$lang['placeholder']        = ['Placeholder', 'Select a placeholder.'];
$lang['hideLabel']          = ['Hide label', 'Hide the label.'];
$lang['customLabel']        = ['Custom label', 'Set a custom label.'];
$lang['label']              = ['Label', 'Select a custom label.'];
$lang['expanded']           = ['Expanded (Radio/Checkboxes)', 'Enable to render choices as radio buttons or checkboxes.'];
$lang['multiple']           = ['Multiple', 'Enable, if the user should be able to select multiple options.'];
$lang['currency']           = ['Currency', 'Select a currency that the money is being specified in.'];
$lang['divisor']            = ['Divisor', 'Enter divisor as integer. If, for some reason, you need to divide your starting value by a number before rendering it to the user, you can use the divisor option.'];
$lang['grouping']           = ['Group numbers', 'Enable, if numbers should be grouped based on your locale. (12345.123 would display as 12,345.123.)'];
$lang['scale']              = ['Scale numbers', 'This number specifies how many decimals will be allowed until the field rounds the submitted value (via rounding_mode). For example, if scale is set to 2, a submitted value of 20.123 will be rounded to, for example, 20.12 (depending on your rounding_mode).'];
$lang['roundingMode']       = ['Round numbers', 'Select a rounding method. By default, if the user enters a non-integer number, it will be rounded down.'];
$lang['alwaysEmpty']        = ['Always render blank', 'Set to true, if the field should always render blank, even if the corresponding field has a value. '];
$lang['percentType']        = ['Percent type', 'This controls how your data is stored on your object. For example, a percentage corresponding to "55%", might be stored as .55 or 55 on your object.'];
$lang['defaultProtocol']    = ['Default protocol', 'If a value is submitted that doesn\'t begin with some protocol (e.g. http://, ftp://, etc), this protocol will be prepended to the string when the data is submitted to the form.'];
$lang['customCountries']    = ['Custom countries', 'Use custom country choices.'];
$lang['countries']          = ['Countries', 'Select some selectable countries.'];
$lang['customLanguages']    = ['Custom languages', 'Use custom language choices.'];
$lang['languages']          = ['Languages', 'Select some selectable languages.'];
$lang['customLocales']      = ['Custom locales', 'Use custom locale choices.'];
$lang['locales']            = ['Locales', 'Select some selectable locales.'];
$lang['customValue']        = ['Custom value', 'Use custom value.'];
$lang['value']              = ['Value', 'Enter a custom value.'];
$lang['isInitial']          = ['Initial filter element', 'Select this option to mark this filter element as "initial". Thus it won\'t be displayed in frontend, but nevertheless will get processed. Ordinary filter elements override any initial filter elements.'];
$lang['initialValueType']   = ['Initial value type', 'Choose the initial type\'s value.'];
$lang['initialValue']       = ['Initial value', 'Specify the initial value here.'];
$lang['initialValue_value'] = ['Value', ''];
$lang['addDefaultValue']    = ['Add default value', 'HINT: The value is not processed initially, but is only set as a default value for the filter field. If you want to specify an initial *filter*, please mark this filter element as "initial".'];
$lang['defaultValueType']   = ['Default value type', 'Choose the default type\'s value.'];
$lang['defaultValue']       = ['Default value', 'Specify the default value here.'];
$lang['defaultValue_value'] = ['Value', ''];
$lang['startElement']       = ['Start element', 'Select the start element for the date range.'];
$lang['stopElement']        = ['Stop element', 'Select the stop element for the date range.'];
$lang['timeFormat']         = ['Time format', 'Enter a valid time format.'];
$lang['dateFormat']         = ['Date format', 'Enter a valid date format.'];
$lang['dateTimeFormat']     = ['Datetime format', 'Enter a valid datetime format.'];
$lang['minDate']            = ['Minimum date', 'Enter a minimum date.'];
$lang['maxDate']            = ['Maximum date', 'Enter a maximum date.'];
$lang['minTime']            = ['Minimum time', 'Enter a minimum time.'];
$lang['maxTime']            = ['Maximum time', 'Enter a maximum time.'];
$lang['dateWidget']         = ['Date widget render mode', 'Select the basic way in which the date field should be rendered.'];
$lang['timeWidget']         = ['Time widget render mode', 'Select the basic way in which the time field should be rendered.'];
$lang['html5']              = ['Render as an HTML5 field ', 'This will render the field as HTML5, which means that some - but not all - browsers will add nice date picker functionality to the field.'];
$lang['inputGroup']         = ['Render as input group', 'Prepend or append icons, text and more.'];
$lang['inputGroupPrepend']  = ['Prepended content', 'Select content that should be prepended to the input.'];
$lang['inputGroupAppend']   = ['Appended content', 'Select content that should be appended to the input.'];
$lang['invertField']        = ['Invert field value', 'Select this option if a "true" in the "published" field equals to an unpublished state.'];
$lang['ignoreFePreview']    = ['Ignore frontend preview', 'Select this option to ignore the frontend preview for this filter element.'];
$lang['addStartAndStop']    = ['Add start and stop field', 'Select this option if the filter element should take start and stop fields into account.'];
$lang['startField']         = ['Start field', 'Select a field here.'];
$lang['stopField']          = ['Stop field', 'Select a field here.'];
$lang['cssClass']           = ['CSS class', 'Here you can enter one or more classes.'];
$lang['published']          = ['Publish filter element', 'Make the filter element publicly visible on the website.'];
$lang['start']              = ['Show from', 'Do not publish the filter element on the website before this date.'];
$lang['stop']               = ['Show until', 'Unpublish the filter element on the website after this date.'];
$lang['tstamp']             = ['Revision date', ''];
$lang['whereSql']           = ['Additional WHERE-SQL', 'Enter SQL, which will be added to the WHERE statement.'];
$lang['submitOnChange']     = ['Submit form on change (submitOnChange)', 'Select this option if you want the user to submit the form on change.'];
$lang['alwaysShow']         = ['Show always', 'Always show the filter element.'];
$lang['dynamicOptions']     = ['Dynamic options', 'Use this option to get option values based on the underlaying data. Initial filters with skalar or array values are respected.'];
$lang['addOptionCount']     = ['Show option entries count', 'Output the count of elements for each option. Works only if dynamic options are enabled.'];
$lang['optionCountLabel']   = ['Label for options with count', 'Choose the label format for options with count.'];

/**
 * Legends
 */
$lang['general_legend']       = 'General settings';
$lang['config_legend']        = 'Configuration';
$lang['visualization_legend'] = 'Visualization';
$lang['expert_legend']        = 'Expert settings';
$lang['publish_legend']       = 'Publish settings';

/**
 * Buttons
 */
$lang['new']    = ['New filter element', 'Create a new filter element'];
$lang['edit']   = ['Edit filter element', 'Edit filter element ID %s'];
$lang['copy']   = ['Copy filter element', 'Copy filter element ID %s'];
$lang['cut']    = ['Cut filter element', 'Cut filter element ID %s'];
$lang['delete'] = ['Delete filter element', 'Delete filter element ID %s'];
$lang['toggle'] = ['Publish/unpublish filter element', 'Publish/unpublish filter element ID %s'];
$lang['show']   = ['Filter element details', 'Show the details of filter element ID %s'];

/**
 * References
 */
$lang['reference'] = [
    'type'                                                              => [
        'text'                                                        => 'Text',
        'text_concat'                                                 => 'Text combined',
        'textarea'                                                    => 'Textarea',
        \HeimrichHannot\FilterBundle\Filter\Type\EmailType::TYPE      => 'Email',
        'integer'                                                     => 'Integer',
        'money'                                                       => 'Money',
        'number'                                                      => 'Number',
        'password'                                                    => 'Password',
        'percent'                                                     => 'Percent',
        'search'                                                      => 'Search',
        'url'                                                         => 'Url',
        'range'                                                       => 'Range',
        'tel'                                                         => 'Tel',
        'color'                                                       => 'Color',
        'choice'                                                      => 'Choice',
        'country'                                                     => 'Country',
        'language'                                                    => 'Language',
        'locale'                                                      => 'Locale',
        'parent'                                                      => 'Parent entity',
        'published'                                                   => 'Published',
        'button'                                                      => 'Button',
        'reset'                                                       => 'Reset',
        'submit'                                                      => 'Submit',
        'hidden'                                                      => 'Hidden',
        'checkbox'                                                    => 'Checkbox',
        'radio'                                                       => 'Radio',
        'other'                                                       => 'Other',
        'initial'                                                     => 'Initial',
        \HeimrichHannot\FilterBundle\Filter\Type\DateTimeType::TYPE   => 'Date & Time',
        \HeimrichHannot\FilterBundle\Filter\Type\DateType::TYPE       => 'Date',
        'time'                                                        => 'Time',
        \HeimrichHannot\FilterBundle\Filter\Type\DateRangeType::TYPE  => 'Date range',
        \HeimrichHannot\FilterBundle\Filter\Type\SqlType::TYPE        => 'SQL',
        \HeimrichHannot\FilterBundle\Filter\Type\SortType::TYPE       => 'Sorting',
        \HeimrichHannot\FilterBundle\Filter\Type\YearType::TYPE       => 'Year',
        \HeimrichHannot\FilterBundle\Filter\Type\DateChoiceType::TYPE => 'Date choice',
    ],
    'roundingMode'                                                      => [
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_DOWN      => 'Down (round towards zero)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_FLOOR     => 'Floor (round towards negative infinity)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_UP        => 'Up (round away from zero)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_CEILING   => 'Ceiling (round towards positive infinity)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_DOWN => 'Half down (round towards the "nearest neighbor". If both neighbors are equidistant, round down.)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN => 'Half even (round towards the "nearest neighbor". If both neighbors are equidistant, round towards the even neighbor.)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_UP   => 'Half up (round towards the "nearest neighbor". If both neighbors are equidistant, round up.)',
    ],
    'percentType'                                                       => [
        'fractional' => 'fractional (e.g .55)',
        'integer'    => 'integer (e.g. 55)',
    ],
    \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_SCALAR => 'Scalar',
    \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_ARRAY  => 'Array',
    \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_LATEST => 'Latest',
];
