<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
        $configuration   = new Configuration($container->getParameter('kernel.debug'));
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('huh.filter', $processedConfig);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('listener.yml');
        $loader->load('services.yml');
    }
}
