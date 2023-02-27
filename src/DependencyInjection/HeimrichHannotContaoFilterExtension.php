<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DependencyInjection;

use Codefog\NewsCategoriesBundle\CodefogNewsCategoriesBundle;
use HeimrichHannot\FilterBundle\Filter\Type\NewsCategoriesType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class HeimrichHannotContaoFilterExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'huh_filter';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $processedConfig = $this->processConfiguration($configuration, $configs);

        if (class_exists(CodefogNewsCategoriesBundle::class)) {
            $processedConfig['filter']['types'][] = [
                'name' => NewsCategoriesType::TYPE,
                'class' => NewsCategoriesType::class,
                'type' => 'choice',
                'wrapper' => false,
            ];
        }

        $container->setParameter('huh.filter', $processedConfig);
        $container->setParameter('huh.sort', $processedConfig);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('listener.yml');
        $loader->load('services.yml');
    }
}
