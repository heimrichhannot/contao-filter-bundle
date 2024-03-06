<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;

class ElementChoice extends AbstractChoice
{
    /**
     * {@inheritdoc}
     */
    protected function collect(): array
    {
        $choices = [];

        if (!is_array($this->getContext()) || empty($this->getContext())) {
            return $choices;
        }

        $context = $this->getContext();

        if (!isset($context['pid']) || !is_numeric($context['pid']) || $context['pid'] < 1) {
            return $choices;
        }

        $context['types'] = isset($context['types']) && is_array($context['types']) ? $context['types'] : [];

        /**
         * @var FilterConfigElementModel
         */
        $adapter = $this->framework->getAdapter(FilterConfigElementModel::class);
        $elements = $adapter->findPublishedByPidAndTypes($context['pid'], $context['types']);

        if (null === $elements) {
            return $choices;
        }

        foreach ($elements as $element) {
            $choices[$element->id] = $element->title.' ['.$element->type.']';
        }

        return $choices;
    }
}
