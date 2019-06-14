<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Util;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
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

    /**
     * @param FilterConfig $form
     */
    public function updateData(FilterConfig $form): void
    {
        if (empty($data = $this->getSubmittedData($form))) {
            return;
        }

        $updateData = [];

        foreach ($form->getData() as $key => $value) {
            if (!isset($data[$key])) {
                continue;
            }

            $updateData[$key] = $data[$key];
        }

        if (empty($updateData)) {
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

        if ('GET' == $filter['method']) {
            return $this->container->get('huh.request')->getGet($filter['name']);
        }

        return $this->container->get('huh.request')->getPost($filter['name']);
    }
}
