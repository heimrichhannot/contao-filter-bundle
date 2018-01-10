<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\FilterBundle\Choice\AbstractChoice;
use Symfony\Component\Filesystem\Filesystem;

class DataContainerChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        try {
            foreach (\System::getContainer()->get('contao.resource_finder')->findIn('dca')->name('tl_*.php') as $file) {
                /** @var \SplFileInfo $file */
                $name = $file->getBasename('.php');

                if (in_array($name, $choices)) {
                    continue;
                }

                $choices[] = $name;
            }
        } catch (\InvalidArgumentException $e) {
        }

        sort($choices);

        return $choices;
    }
}