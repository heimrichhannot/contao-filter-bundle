<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Util;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;

class FilterConfigElementUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function getFields(DataContainer $dc)
    {
        if (null === ($model = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
            return [];
        }

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($model->pid))) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices([
            'dataContainer' => $filterConfig->getFilter()['dataContainer'],
        ]);
    }

    public function getSortClasses(DataContainer $dc)
    {
        $types = [];

        $config = System::getContainer()->getParameter('huh.sort');

        if (!isset($config['sort']['classes']) || !\is_array($config['sort']['classes'])) {
            return $types;
        }

        foreach ($config['sort']['classes'] as $type) {
            $types[$type['name']] = $type['class'];
        }

        return $types;
    }

    public function getSortDirections(DataContainer $dc)
    {
        $directions = [];

        $config = System::getContainer()->getParameter('huh.sort');

        if (!isset($config['sort']['directions']) || !\is_array($config['sort']['directions'])) {
            return $directions;
        }

        $translator = System::getContainer()->get('translator');

        foreach ($config['sort']['directions'] as $type) {
            $directions[$type['value']] = $translator->trans('huh.sort.'.$type['value']);
        }

        return $directions;
    }
}
