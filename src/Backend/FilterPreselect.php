<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Backend;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\Type\ChoiceType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;

class FilterPreselect
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Adjust label of entries.
     *
     * @param array  $row
     * @param string $label
     *
     * @return string
     */
    public function adjustLabel($row, $label)
    {
        /** @var $filterConfigElement FilterConfigElementModel */
        if (null === ($filterConfigElement = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
                'tl_filter_config_element',
                $row['element']
            ))) {
            return $label;
        }

        $choices = $this->prepareElementChoices((int) $row['id']);

        switch ($row['initialValueType']) {
            case \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_SCALAR:
                $label = $choices[$row['initialValue']] ?? $row['initialValue'];
                break;
            case \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_ARRAY:
                $values = array_map(
                    function ($item) {
                        return $item['value'] ?? null;
                    },
                    StringUtil::deserialize($row['initialValueArray'], true)
                );

                $label = implode(',', array_intersect_key($choices, array_flip(array_filter($values))));
                break;
            case \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_CONTEXTUAL:
                $label = \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_CONTEXTUAL;
        }

        return sprintf('%s -> %s [ID: %s]', $filterConfigElement->title, $label, $filterConfigElement->id);
    }

    /**
     * Prepare initial choices.
     *
     * @param DataContainer $dc
     */
    public function prepareChoiceTypes(DataContainer $dc)
    {
        if ($dc->id < 1) {
            return;
        }

        $this->prepareElementChoices($dc->id);
    }

    /**
     * Prepare choices for given element id and return options.
     *
     * @param int $id
     *
     * @return array
     */
    protected function prepareElementChoices(int $id): array
    {
        if (null === ($filterPreselect = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_filter_preselect', $id))) {
            return [];
        }

        if (null === ($filterConfigElement = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
                'tl_filter_config_element',
                $filterPreselect->element
            ))) {
            return [];
        }

        $dca = &$GLOBALS['TL_DCA']['tl_filter_preselect'];
        $config = System::getContainer()->getParameter('huh.filter');
        $class = null;

        if (!isset($config['filter']['types']) || !is_array($config['filter']['types'])) {
            return [];
        }

        foreach ($config['filter']['types'] as $type) {
            if (isset($type['name']) && $type['name'] === $filterConfigElement->type && isset($type['class'])) {
                $class = $type['class'];
                break;
            }
        }

        // only choice types are supported
        if (null === $class) {
            return [];
        }

        if (null === ($filter = System::getContainer()->get('huh.filter.manager')->findById($filterConfigElement->pid))) {
            return [];
        }

        $choiceType = new $class($filter);

        if (!($choiceType instanceof ChoiceType)) {
            return [];
        }

        $options = array_flip($choiceType->getChoices($filterConfigElement));

        $dca['fields']['initialValue']['inputType'] = 'select';
        $dca['fields']['initialValue']['options'] = $options;
        $dca['fields']['initialValue']['eval']['chosen'] = true;

        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['inputType'] = 'select';
        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['options'] = $options;
        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['eval']['chosen'] = true;

        return $options;
    }
}
