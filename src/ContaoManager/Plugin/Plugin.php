<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle;
use Contao\ManagerPlugin\Config\ContainerBuilder;


class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoFilterBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
        ];
    }

    /**
     * Allows a plugin to override extension configuration.
     *
     * @param string $extensionName
     * @param array $extensionConfigs
     * @param ContainerBuilder $container
     *
     * @return
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
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

        return $extensionConfigs;
    }

}
