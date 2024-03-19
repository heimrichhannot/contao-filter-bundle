<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FilterBundle\Choice\FieldChoice;
use HeimrichHannot\FilterBundle\Choice\FilterChoices;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Cache\InvalidArgumentException;

class FilterConfigElementUtil
{
    protected ContaoFramework $framework;
    protected Utils $utils;
    protected FieldChoice $fieldChoice;

    public function __construct(
        ContaoFramework $framework,
        Utils $utils,
        FieldChoice $fieldChoice
    ) {
        $this->framework = $framework;
        $this->utils = $utils;
        $this->fieldChoice = $fieldChoice;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getFields(DataContainer $dc)
    {
        $model = $this->utils->model()->findModelInstanceByPk($dc->table, $dc->id);
        if (null === $model) {
            return [];
        }

        $filterConfig = System::getContainer()->get('huh.filter.manager')->findById($model->pid);
        if (null === $filterConfig) {
            return [];
        }

        return $this->fieldChoice->getCachedChoices([
            'dataContainer' => $filterConfig->getFilter()['dataContainer'],
        ]);
    }

    public function getSortClasses(DataContainer $dc): array
    {
        $types = [];

        $config = System::getContainer()->getParameter('huh.sort');

        if (!isset($config['sort']['classes']) || !is_array($config['sort']['classes'])) {
            return $types;
        }

        foreach ($config['sort']['classes'] as $type) {
            $types[$type['name']] = $type['class'];
        }

        return $types;
    }

    public function getSortDirections(DataContainer $dc): array
    {
        $directions = [];

        $config = System::getContainer()->getParameter('huh.sort');

        if (!isset($config['sort']['directions']) || !is_array($config['sort']['directions'])) {
            return $directions;
        }

        $translator = System::getContainer()->get('translator');

        foreach ($config['sort']['directions'] as $type) {
            $directions[$type['value']] = $translator->trans('huh.sort.'.$type['value']);
        }

        return $directions;
    }

    public function getElements(DataContainer $dc, array $options = [])
    {
        $model = $this->utils->model()->findModelInstanceByPk($dc->table, $dc->id);
        if (null === $model) {
            return [];
        }

        $types = $options['types'] ?? null;

        return FilterChoices::getElementOptions($model->pid, $types);
    }
}