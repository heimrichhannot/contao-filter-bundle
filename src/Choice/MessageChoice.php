<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\Haste\Util\Arrays;

class MessageChoice extends AbstractChoice
{

    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $prefixes = $this->getContext();
        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }
        $translator = \System::getContainer()->get('translator');

        $catalog  = $translator->getCatalogue();
        $all      = $catalog->all();
        $messages = $all['messages'];

        if (!is_array($messages)) {
            return $choices;
        }

        $choices = Arrays::filterByPrefixes($messages, $prefixes);

        foreach ($choices as $key => $value) {
            $choices[$key] = $value . '[' . $key . ']';
        }

        return $choices;
    }
}