<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface, RoutingPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(HeimrichHannotContaoFilterBundle::class)->setLoadAfter([ContaoCoreBundle::class, 'blocks']),
        ];
    }

    /**
     * Allows a plugin to override extension configuration.
     *
     * @param string $extensionName
     *
     * @return
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container): array
    {
        if ('framework' === $extensionName) {
            foreach ($extensionConfigs as &$extensionConfig) {
                // enable form plugin
                if (!isset($extensionConfig['form'])) {
                    $extensionConfig['form']['enabled'] = true;

                    break;
                }
            }
        }

        # todo: is this available in utils?
        $mergeConfigFile = function (
            string $activeExtensionName,
            string $extensionName,
            array $extensionConfigs,
            string $configFile
        ): array {
            if ($activeExtensionName === $extensionName && file_exists($configFile))
            {
                $config = Yaml::parseFile($configFile);
                $extensionConfigs = array_merge_recursive($extensionConfigs, is_array($config) ? $config : []);
            }
            return $extensionConfigs;
        };

        return $mergeConfigFile(
            'huh_filter',
            $extensionName,
            $extensionConfigs,
            __DIR__.'/../Resources/config/config.yml'
        );
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): ?RouteCollection
    {
        return $resolver
            ->resolve(__DIR__.'/../Resources/config/routing.yml')
            ->load(__DIR__.'/../Resources/config/routing.yml');
    }
}
