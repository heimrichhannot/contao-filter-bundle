<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle;

use HeimrichHannot\FilterBundle\DependencyInjection\HeimrichHannotContaoFilterExtension;
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
