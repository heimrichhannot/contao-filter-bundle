<?php

$lang = &$GLOBALS['TL_LANG']['tl_filter_element'];

/**
 * Fields
 */
$lang['title']           = ['Title', 'Please enter a title.'];
$lang['type']            = ['Type', 'Select a field type.'];
$lang['field']           = ['Field', 'Select a mapping table field.'];
$lang['fields']          = ['Fields', 'Select multiple fields that should be combined in given order.'];
$lang['customOptions']   = ['Custom options', 'Use choices.'];
$lang['options']         = ['Options', 'If JavaScript is disabled, make sure to save your changes before modifying the order.'];
$lang['customName']      = ['Custom name', 'Set a custom field name.'];
$lang['name']            = ['Name', 'Enter a custom field name.'];
$lang['addPlaceholder']  = ['Add placeholder', 'Add a placeholder value.'];
$lang['placeholder']     = ['Placeholder', 'Select a placeholder.'];
$lang['customLabel']     = ['Custom label', 'Set a custom label.'];
$lang['label']           = ['Label', 'Select a custom label.'];
$lang['expanded']        = ['Expanded (Radio/Checkboxes)', 'Enable to render choices as radio buttons or checkboxes.'];
$lang['multiple']        = ['Multiple', 'Enable, if the user should be able to select multiple options.'];
$lang['currency']        = ['Currency', 'Select a currency that the money is being specified in.'];
$lang['divisor']         = ['Divisor', 'Enter divisor as integer. If, for some reason, you need to divide your starting value by a number before rendering it to the user, you can use the divisor option.'];
$lang['grouping']        = ['Group numbers', 'Enable, if numbers should be grouped based on your locale. (12345.123 would display as 12,345.123.)'];
$lang['scale']           = ['Scale numbers', 'This number specifies how many decimals will be allowed until the field rounds the submitted value (via rounding_mode). For example, if scale is set to 2, a submitted value of 20.123 will be rounded to, for example, 20.12 (depending on your rounding_mode).'];
$lang['roundingMode']    = ['Round numbers', 'Select a rounding method. By default, if the user enters a non-integer number, it will be rounded down.'];
$lang['alwaysEmpty']     = ['Always render blank', 'Set to true, if the field should always render blank, even if the corresponding field has a value. '];
$lang['percentType']     = ['Percent type', 'This controls how your data is stored on your object. For example, a percentage corresponding to "55%", might be stored as .55 or 55 on your object.'];
$lang['defaultProtocol'] = ['Default protocol', 'If a value is submitted that doesn\'t begin with some protocol (e.g. http://, ftp://, etc), this protocol will be prepended to the string when the data is submitted to the form.'];
$lang['customCountries'] = ['Custom countries', 'Use custom country choices.'];
$lang['countries']       = ['Countries', 'Select some selectable countries.'];
$lang['customLanguages'] = ['Custom languages', 'Use custom language choices.'];
$lang['languages']       = ['Languages', 'Select some selectable languages.'];
$lang['customLocales']   = ['Custom locales', 'Use custom locale choices.'];
$lang['locales']         = ['Locales', 'Select some selectable locales.'];
$lang['customValue']     = ['Custom value', 'Use custom value.'];
$lang['value']           = ['Value', 'Enter a custom value.'];
$lang['cssClass']        = ['CSS class', 'Here you can enter one or more classes.'];
$lang['published']       = ['Publish Filterelement', 'Make the Filterelement publicly visible on the website.'];
$lang['start']           = ['Show from', 'Do not publish the Filterelement on the website before this date.'];
$lang['stop']            = ['Show until', 'Unpublish the Filterelement on the website after this date.'];
$lang['tstamp']          = ['Revision date', ''];

/**
 * Legends
 */
$lang['general_legend'] = 'General settings';
$lang['config_legend']  = 'Configuration';
$lang['expert_legend']  = 'Expert settings';
$lang['publish_legend'] = 'Publish settings';

/**
 * Buttons
 */
$lang['new']    = ['New Filterelement', 'Filterelement create'];
$lang['edit']   = ['Edit Filterelement', 'Edit Filterelement ID %s'];
$lang['copy']   = ['Duplicate Filterelement', 'Duplicate Filterelement ID %s'];
$lang['delete'] = ['Delete Filterelement', 'Delete Filterelement ID %s'];
$lang['toggle'] = ['Publish/unpublish Filterelement', 'Publish/unpublish Filterelement ID %s'];
$lang['show']   = ['Filterelement details', 'Show the details of Filterelement ID %s'];

/**
 * References
 */
$lang['reference']['roundingMode'][\Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_DOWN]      = 'Down (round towards zero)';
$lang['reference']['roundingMode'][\Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_FLOOR]     = 'Floor (round towards negative infinity)';
$lang['reference']['roundingMode'][\Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_UP]        = 'Up (round away from zero)';
$lang['reference']['roundingMode'][\Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_CEILING]   = 'Ceiling (round towards positive infinity)';
$lang['reference']['roundingMode'][\Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_DOWN] = 'Half down (round towards the "nearest neighbor". If both neighbors are equidistant, round down.)';
$lang['reference']['roundingMode'][\Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN] = 'Half even (round towards the "nearest neighbor". If both neighbors are equidistant, round towards the even neighbor.)';
$lang['reference']['roundingMode'][\Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_UP]   = 'Half up (round towards the "nearest neighbor". If both neighbors are equidistant, round up.)';


$lang['reference']['percentType']['fractional'] = 'fractional (e.g .55)';
$lang['reference']['percentType']['integer']    = 'integer (e.g. 55)';
