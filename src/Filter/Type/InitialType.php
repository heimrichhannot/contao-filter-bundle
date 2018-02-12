<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class InitialType extends AbstractType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $builder->whereElement($element, $this->getName($element), $this->config);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        // TODO maybe not necessary
//        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\TextType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return null;
    }

    public static function getInitialValue(FilterConfigElementModel $element)
    {
        switch ($element->initialValueType) {
            case static::VALUE_TYPE_ARRAY:
                $value = array_map(function ($val) {
                    return $val['value'];
                }, StringUtil::deserialize($element->initialValueArray, true));

                break;
            default:
                $value = $element->initialValue;
                break;
        }

        return $value;
    }
}
