<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Nelmio\SecurityBundle\ContentSecurityPolicy\Violation\Filter\Filter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractType
{
    const VALUE_TYPE_SCALAR = 'scalar';
    const VALUE_TYPE_ARRAY = 'array';

    const VALUE_TYPES = [
        self::VALUE_TYPE_SCALAR,
        self::VALUE_TYPE_ARRAY,
    ];

    /**
     * @var Filter
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
     * @param FilterConfigElementModel $element
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

        return $value;
    }

    public static function getInitialValue(FilterConfigElementModel $element)
    {
        switch ($element->initialValueType) {
            case static::VALUE_TYPE_ARRAY:
                $value = array_map(function ($val) {
                    return $val['value'];
                }, StringUtil::deserialize($element->initialValueArray, true));

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
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     *
     * @return string
     */
    protected function getLabel(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $label = '';
        $filter = $this->config->getFilter();

        if (true === (bool) $element->customLabel && '' !== $element->label) {
            return $element->label;
        }

        \Controller::loadDataContainer($filter['dataContainer']);
        \Controller::loadLanguageFile($filter['dataContainer']);

        if (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]['label'])) {
            return $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]['label'][0];
        }

        return $label;
    }

    /**
     * Get field options.
     *
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     *
     * @return array The field options
     */
    protected function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = [];

        $options['label'] = (true === (bool) $element->hideLabel) ? false : ($this->getLabel($element, $builder) ?: $element->title);

        if ($element->addDefaultValue) {
            $options['data'] = static::getDefaultValue($element);
        }

        if (true === (bool) $element->addPlaceholder && '' !== $element->placeholder) {
            $options['attr']['placeholder'] = $this->translator->trans($element->placeholder, ['%label%' => $this->translator->trans($options['label'])]);
        }

        if ('' !== $element->cssClass) {
            $options['attr']['class'] = $element->cssClass;
        }

        if (true === (bool) $element->inputGroup) {
            if ('' !== $element->inputGroupPrepend) {
                $prepend = $element->inputGroupPrepend;

                if ($this->translator->getCatalogue()->has($prepend)) {
                    $prepend = $this->translator->trans($prepend);
                }

                $options['input_group_prepend'] = $prepend;
            }

            if ('' !== $element->inputGroupAppend) {
                $append = $element->inputGroupAppend;

                if ($this->translator->getCatalogue()->has($append)) {
                    $append = $this->translator->trans($append);
                }

                $options['input_group_append'] = $append;
            }
        }

        $options['block_name'] = $this->getName($element);

        return $options;
    }
}
