<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use Ausi\SlugGenerator\SlugGenerator;
use Ausi\SlugGenerator\SlugOptions;
use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Event\AdjustFilterOptionsEvent;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractType
{
    const VALUE_TYPE_SCALAR = 'scalar';
    const VALUE_TYPE_ARRAY = 'array';
    const VALUE_TYPE_CONTEXTUAL = 'contextual';
    const VALUE_TYPE_LATEST = 'latest';

    const VALUE_TYPES = [
        self::VALUE_TYPE_SCALAR,
        self::VALUE_TYPE_ARRAY,
        self::VALUE_TYPE_CONTEXTUAL,
    ];

    /**
     * @var FilterConfig
     */
    protected $config;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(FilterConfig $config)
    {
        $this->config = $config;
        $this->translator = System::getContainer()->get('translator');
    }

    /**
     * Get the field name.
     *
     * @return mixed
     */
    public function getName(FilterConfigElementModel $element)
    {
        $name = $this->getDefaultName($element) ?: $element->field;

        if (true === (bool) $element->customName && '' !== $element->name) {
            $name = $element->name;
        }

        if ('' === $name) {
            $name = (new SlugGenerator((new SlugOptions)->setValidChars('a-z0-9_')->setDelimiter('_')))->generate($element->title);
        }

        return $name;
    }

    public static function getDefaultValue(FilterConfigElementModel $element)
    {
        switch ($element->defaultValueType) {
            case static::VALUE_TYPE_ARRAY:
                $value = array_map(function ($val) {
                    return System::getContainer()->get(\HeimrichHannot\UtilsBundle\String\StringUtil::class)->replaceInsertTags($val['value']);
                }, StringUtil::deserialize($element->defaultValueArray, true));

                break;

            default:
                $value = System::getContainer()->get(\HeimrichHannot\UtilsBundle\String\StringUtil::class)->replaceInsertTags($element->defaultValue, false);

                break;
        }

        return $value;
    }

    public static function getInitialValue(FilterConfigElementModel $element, array $contextualValues = [])
    {
        $value = null;

        switch ($element->initialValueType) {
            case static::VALUE_TYPE_ARRAY:
                $value = array_map(function ($val) {
                    return $val['value'];
                }, StringUtil::deserialize($element->initialValueArray, true));

                break;

            case static::VALUE_TYPE_CONTEXTUAL:
                if (isset($contextualValues[$element->field])) {
                    $value = $contextualValues[$element->field];
                }

                break;

            default:
                $value = $element->initialValue;

                break;
        }

        // multilingual initial values
        if ($element->addMultilingualInitialValues) {
            foreach (StringUtil::deserialize($element->multilingualInitialValues, true) as $row) {
                if ($GLOBALS['TL_LANGUAGE'] === $row['language']) {
                    switch ($row['initialValueType']) {
                        case static::VALUE_TYPE_ARRAY:
                            $value = $row['initialValueArray'];

                            break;

                        case static::VALUE_TYPE_CONTEXTUAL:
                            if (isset($contextualValues[$element->field])) {
                                $value = $contextualValues[$element->field];
                            }

                            break;

                        default:
                            $value = $row['initialValue'];

                            break;
                    }

                    break;
                }
            }
        }

        return $value;
    }

    /**
     * Build the filter query.
     *
     * @param FilterQueryBuilder       $builder The query builder
     * @param FilterConfigElementModel $element The element data
     */
    abstract public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element);

    /**
     * Builds the form, add your filter fields here.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param FilterConfigElementModel $element The element data
     * @param FormBuilderInterface     $builder The form builder
     */
    abstract public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder);

    /**
     * Get the default operator for this type.
     *
     * @param FilterConfigElementModel $element The element data
     *
     * @return string|null The returned string must be an operator defined in \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATORS
     */
    abstract public function getDefaultOperator(FilterConfigElementModel $element);

    /**
     * Get the default form element name.
     *
     * @param FilterConfigElementModel $element The element data
     *
     * @return string|null
     */
    abstract public function getDefaultName(FilterConfigElementModel $element);

    /**
     * Get the field label.
     *
     * @return string
     */
    public function getLabel(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $label = '';
        $filter = $this->config->getFilter();

        if (true === (bool) $element->customLabel && '' !== $element->label) {
            return $element->label;
        }

        Controller::loadDataContainer($filter['dataContainer']);
        Controller::loadLanguageFile($filter['dataContainer']);

        if (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]['label'])) {
            return $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]['label'][0];
        }

        return $label;
    }

    /**
     * Get field options.
     *
     * @return array The field options
     */
    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $data = $this->config->getData();
        $name = $this->getName($element);
        $options = [];

        $options['label'] = $this->getLabel($element, $builder) ?: $element->title;

        // sr-only style for non-bootstrap projects is shipped within the filter_form_* templates
        if ($this->getHideLabel($element)) {
            $options['label_attr'] = ['class' => 'sr-only'];
        }

        // always label for screen readers
        $options['attr']['aria-label'] = $this->translator->trans($this->getLabel($element, $builder) ?: $element->title);

        if ($element->addDefaultValue && !isset($data[$name])) {
            $options['data'] = static::getDefaultValue($element);
        }

        if (true === (bool) $element->addPlaceholder && '' !== $element->placeholder) {
            $options['attr']['placeholder'] = $this->translator->trans($element->placeholder, ['%label%' => $this->translator->trans($options['label']) ?: $element->title]);
        }

        if ('' !== $element->cssClass) {
            $options['attr']['class'] = $element->cssClass;
        }

        if (true === (bool) $element->inputGroup) {
            if ('' !== $element->inputGroupPrepend) {
                $prepend = $element->inputGroupPrepend;

                if ($this->translator->getCatalogue()->has($prepend)) {
                    $prepend = $this->translator->trans($prepend, ['%label%' => $this->translator->trans($options['label']) ?: $element->title]);
                }

                $options['input_group_prepend'] = $prepend;
            }

            if ('' !== $element->inputGroupAppend) {
                $append = $element->inputGroupAppend;

                if ($this->translator->getCatalogue()->has($append)) {
                    $append = $this->translator->trans($append, ['%label%' => $this->translator->trans($options['label']) ?: $element->title]);
                }

                $options['input_group_append'] = $append;
            }
        }

        $options['block_name'] = $this->getName($element);

        if ($triggerEvent) {
            $event = System::getContainer()->get('event_dispatcher')->dispatch(
                new AdjustFilterOptionsEvent($name, $options, $element, $builder, $this->config),
                AdjustFilterOptionsEvent::NAME
            );

            return $event->getOptions();
        }

        return $options;
    }

    public function getHideLabel(FilterConfigElementModel $element): bool
    {
        return (bool) $element->hideLabel;
    }

    public static function getInitialPalette(string $prepend, string $append): ?string
    {
        return null;
    }

    /**
     * Check if element is enabled in current context.
     *
     * Following options may be passed:
     * - table (string)
     * - filterConfigElementModel (FilterConfigElementModel)
     */
    public static function isEnabledForCurrentContext(array $context = []): bool
    {
        return true;
    }

    /**
     * Add logic to normalize data.
     *
     * @example CheckboxType Example implementation
     *
     * @param $value
     *
     * @return mixed
     */
    public static function normalizeValue($value)
    {
        return $value;
    }

    /**
     * Get min date for given element and type.
     *
     * @return int The min date as timestamp
     */
    protected function getMinDate(FilterConfigElementModel $element)
    {
        $field = null;

        switch ($element->type) {
            case 'time':
                $field = 'minTime';

                break;

            case 'date':
                $field = 'minDate';

                break;

            case 'date_time':
                $field = 'minDateTime';

                break;
        }

        if (null === $field || !isset($element->{$field}) || '' === $element->{$field}) {
            return 0;
        }

        return System::getContainer()->get('huh.utils.date')->getTimeStamp($element->{$field});
    }

    /**
     * Get max date for given element and type.
     *
     * @return int The max date as timestamp
     */
    protected function getMaxDate(FilterConfigElementModel $element)
    {
        $field = null;

        switch ($element->type) {
            case 'time':
                $field = 'maxTime';

                break;

            case 'date':
                $field = 'maxDate';

                break;

            case 'date_time':
                $field = 'maxDateTime';

                break;
        }

        if (null === $field || !isset($element->{$field}) || '' === $element->{$field}) {
            return 9999999999999;
        }

        return System::getContainer()->get('huh.utils.date')->getTimeStamp($element->{$field});
    }

    /**
     * Return custom options if custom options config is set. Otherwise return null.
     */
    protected function getCustomOptions(FilterConfigElementModel $element): ?array
    {
        if (false === (bool) $element->customOptions) {
            return null;
        }

        if (null === $element->options) {
            return [];
        }

        return StringUtil::deserialize($element->options, true);
    }
}
