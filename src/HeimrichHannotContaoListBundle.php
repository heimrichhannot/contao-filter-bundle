<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle;

use HeimrichHannot\ListBundle\DependencyInjection\ListExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoListBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new ListExtension();
    }
}
