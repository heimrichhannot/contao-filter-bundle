<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\FilterBundle\Util\AbstractChoice;

class FieldChoice extends AbstractChoice
{
    /**
     * {@inheritdoc}
     */
    protected function collect(): array
    {
        $context = $this->getContext();
        return $this->utils->dca()->getDcaFields($context['dataContainer'], $context);
    }
}