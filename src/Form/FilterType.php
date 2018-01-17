<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Form;

use Contao\InsertTags;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    /**
     * @var FilterConfig|null
     */
    protected $config;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!isset($options['filter']) || !$options['filter'] instanceof FilterConfig) {
            return;
        }

        $this->config = $options['filter'];

        $filter = $this->config->getFilter();

        $builder->setAction(urldecode(InsertTags::replaceInsertTags($filter['action'])));
        $builder->setMethod($filter['method']);

        $this->buildElements($builder, $options);
    }


    protected function buildElements(FormBuilderInterface $builder, array $options)
    {
        $elements = $this->config->getElements();

        if (!is_array($elements)) {
            return;
        }

        $types = \System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!is_array($types) || empty($types)) {
            return;
        }

        foreach ($elements as $element) {

            if (!isset($types[$element['type']])) {
                continue;
            }

            $class = $types[$element['type']];

            if (!class_exists($class)) {
                continue;
            }

            /**
             * @var $type TypeInterface
             */
            $type = new $class($this->config);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class) || !is_subclass_of($type, TypeInterface::class)) {
                continue;
            }

            $type->buildForm($element, $builder);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'filter' => null,
        ]);
    }
}