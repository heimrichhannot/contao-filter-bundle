<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Util;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;

class FilterPreselectUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Get preselected data based on given preselection
     *
     * @param int                    $id            The filter id
     * @param FilterPreselectModel[] $preselections list of preselections
     *
     * @return array
     */
    public function getPreselectData(int $id, array $preselections): array
    {
        $data = [];

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($id)) || null === ($elements = $filterConfig->getElements())) {
            return $data;
        }

        $types = \System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!is_array($types) || empty($types)) {
            return $data;
        }

        /** @var FilterPreselectModel $preselection */
        foreach ($preselections as $preselection) {
            $element = $filterConfig->getElementByValue($preselection->element);

            if (!isset($types[$element->type])) {
                continue;
            }

            $config = $types[$element->type];
            $class  = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Filter\AbstractType $type */
            $type = new $class($filterConfig);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class)) {
                continue;
            }

            if (null === ($name = $type->getName($element))) {
                continue;
            }

            $data[$name] = $this->getInitialValue($preselection);
        }

        return $data;
    }

    /**
     * Get the initial value based on preselection
     *
     * @param FilterPreselectModel $element
     *
     * @return array|mixed|null
     */
    public function getInitialValue(FilterPreselectModel $element)
    {
        $value = null;

        switch ($element->initialValueType) {
            case AbstractType::VALUE_TYPE_ARRAY:
                $value = array_map(
                    function ($val) {
                        return $val['value'];
                    },
                    StringUtil::deserialize($element->initialValueArray, true)
                );

                break;
            default:
                $value = $element->initialValue;
                break;
        }

        return $value;
    }
}