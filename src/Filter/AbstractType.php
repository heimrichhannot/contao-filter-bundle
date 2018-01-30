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
        $this->config     = $config;
        $this->translator = System::getContainer()->get('translator');
    }

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
        $label  = '';
        $filter = $this->config->getFilter();

        if (true === (bool)$element->customLabel && '' !== $element->label) {
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

        $options['label'] = (true === (bool)$element->hideLabel) ? false : ($this->getLabel($element, $builder) ?: $element->title);

        if (true === (bool)$element->addPlaceholder && '' !== $element->placeholder) {
            $options['attr']['placeholder'] = $this->translator->trans($element->placeholder, ['%label%' => $this->translator->trans($options['label'])]);
        }

        if ('' !== $element->cssClass) {
            $options['attr']['class'] = $element->cssClass;
        }

        $options['block_name'] = $this->getName($element);

        return $options;
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

        if (true === (bool)$element->customName && '' !== $element->name) {
            $name = $element->name;
        }

        if ('' === $name) {
            $name = StringUtil::standardize($element->title);
        }

        return $name;
    }
}
