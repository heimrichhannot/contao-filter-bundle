<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;


use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class ChoiceType extends AbstractType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function buildQuery(FilterQueryBuilder $builder, array $element)
    {
        $builder->whereElement($element, $this->getName($element), $this->config);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, $this->getOptions($element, $builder));
    }

    protected function getOptions(array $element, FormBuilderInterface $builder)
    {
        $options                              = parent::getOptions($element, $builder);
        $options['choices']                   = System::getContainer()->get('huh.filter.choice.field_options')->getCachedChoices([$element, $this->config->getFilter()]);
        $options['choice_translation_domain'] = false; // disable translation]

        if (isset($options['attr']['placeholder'])) {
            $options['attr']['data-placeholder'] = $options['attr']['placeholder'];
            $options['placeholder']              = $options['attr']['placeholder'];
            unset($options['attr']['placeholder']);
        }

        $options['expanded'] = (bool)$element['expanded'];
        $options['multiple'] = (bool)$element['multiple'];

        return $options;
    }
}