<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;

class SubmitType extends AbstractType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function buildQuery(FilterQueryBuilderInterface $builder, array $data = [], $count = false)
    {
        // TODO: Implement buildQuery() method.
    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element, $builder, 'submit'), \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, $this->getOptions($element, $builder));
    }
}