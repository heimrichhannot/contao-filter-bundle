<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;


use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class PasswordType extends TextType
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
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, $this->getOptions($element, $builder));
    }

    /**
     * @inheritDoc
     */
    protected function getOptions(array $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);

        $options['always_empty'] = (bool)$element['alwaysEmpty'];

        return $options;
    }
}
