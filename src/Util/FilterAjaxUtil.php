<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Util;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Form\FilterType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterAjaxUtil
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function updateData(FilterConfig &$form): void
    {
        if (empty($data = $this->getSubmittedData($form))) {
            return;
        }

        $updateData = array_merge($form->getData(), $data);

        if ($this->isDataEmpty($data)) {
            $updateData['reset'] = true;
        }

        if (empty($updateData)) {
            return;
        }

        if (isset($updateData['reset'])) {
            $form->resetData();

            return;
        }

        $form->setData($updateData);
    }

    /**
     * @param $builder
     *
     * @return array|null
     */
    public function getSubmittedData(FilterConfig $form)
    {
        $filter = $form->getFilter();
        $request = $this->container->get('huh.request');

        return 'GET' == $filter['method'] ? $request->getGet($filter['name']) : $request->getPost($filter['name']);
    }

    protected function isDataEmpty(array $data): bool
    {
        foreach ($data as $key => $value) {
            if ($this->isValueEmpty($value) || \in_array($key, [FilterType::FILTER_ID_NAME, FilterType::FILTER_REFERRER_NAME])) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * @param $value
     */
    protected function isValueEmpty($value): bool
    {
        if (\is_array($value)) {
            foreach ($value as $part) {
                if (!$part) {
                    continue;
                }

                return false;
            }
        } elseif ($value) {
            return false;
        }

        return true;
    }
}
