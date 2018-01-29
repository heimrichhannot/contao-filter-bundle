<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;


use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ElementChoice extends AbstractChoice
{
    /**
     * @inheritDoc
     */
    protected function collect()
    {
        $choices = [];

        if (!is_array($this->getContext()) || empty($this->getContext())) {
            return $choices;
        }

        $context = $this->getContext();

        if (!isset($context['pid'])) {
            return $choices;
        }

        $context['types'] = is_array($context['types']) ? $context['types'] : [];

        /**
         * @var $adapter FilterConfigElementModel
         */
        $adapter = $this->framework->getAdapter(FilterConfigElementModel::class);

        if (null === ($elements = $adapter->findPublishedByPidAndTypes($context['pid'], $context['types']))) {
            return $choices;
        }

        while ($elements->next()) {
            $choices[$elements->id] = $elements->name.'['.$elements->type.']';
        }

        return $choices;
    }
}