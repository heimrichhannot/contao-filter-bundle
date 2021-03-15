<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Event\AdjustFilterOptionsEvent;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Nelmio\SecurityBundle\ContentSecurityPolicy\Violation\Filter\Filter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
            $name = StringUtil::standardize($element->title);
        }

        return $name;
    }

    public static function getDefaultValue(FilterConfigElementModel $element)
    {
        switch ($element->defaultValueType) {
            case static::VALUE_TYPE_ARRAY:
                $value = array_map(function ($val) {
                    return $val['value'];
                }, StringUtil::deserialize($element->defaultValueArray, true));

                break;

            default:
                $value = $element->defaultValue;

                break;
        }

        return System::getContainer()->get(\HeimrichHannot\UtilsBundle\String\StringUtil::class)->replaceInsertTags($value, false);
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
        if (true === (bool) $element->hideLabel) {
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
            $event = System::getContainer()->get('event_dispatcher')->dispatch(AdjustFilterOptionsEvent::NAME, new AdjustFilterOptionsEvent(
                $name, $options, $element, $builder, $this->config
            ));

            return $event->getOptions();
        }

        return $options;
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
}
