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
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class ButtonType extends AbstractType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function buildQuery(FilterQueryBuilder $builder, array $element)
    {

    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element, $element['name']), \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, $this->getOptions($element, $builder));
    }

    /**
     * Get the field label
     * @param array $element
     * @param FormBuilderInterface $builder
     * @return string
     */
    protected function getLabel(array $element, FormBuilderInterface $builder)
    {
        $label = parent::getLabel($element, $builder);

        if ($label === '' && $element['label'] !== '') {
            return $element['label'];
        }

        return $label;
    }
}