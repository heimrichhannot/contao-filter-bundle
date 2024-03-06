<?php

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\System;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;
use HeimrichHannot\FilterBundle\Util\Polyfill;

class MessageChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect(): array
    {
        $choices = [];

        $prefixes = $this->getContext();

        if (!\is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        $translator = System::getContainer()->get('translator');

        $catalog = $translator->getCatalogue();
        $all = $catalog->all();
        $messages = $all['messages'];

        if (!\is_array($messages)) {
            return $choices;
        }

        $choices = Polyfill::filterByPrefixes($messages, $prefixes);

        foreach ($choices as $key => $value) {
            $choices[$key] = $value.'['.$key.']';
        }

        return $choices;
    }
}