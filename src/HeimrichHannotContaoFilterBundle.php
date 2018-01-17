<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle;

use HeimrichHannot\FilterBundle\DependencyInjection\Compiler\FilterElementManagerPass;
use HeimrichHannot\FilterBundle\DependencyInjection\HeimrichHannotContaoFilterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoFilterBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new HeimrichHannotContaoFilterExtension();
    }
}
