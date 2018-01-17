<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Form;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\InsertTags;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Exception\MissingFilterConfigException;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    const FILTER_ID_NAME = 'f_id';

    /**
     * @var FilterConfig|null
     */
    protected $config;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!isset($options['filter']) || !$options['filter'] instanceof FilterConfig) {
            throw new MissingFilterConfigException('Missing filter configuration.');
        }

        $this->config = $options['filter'];

        $this->framework = $this->config->getFramework();
        $this->framework->initialize();

        $filter = $this->config->getFilter();

        $builder->setAction($this->getAction());

        if (isset($filter['method'])) {
            $builder->setMethod($filter['method']);
        }

        // always add a hidden field with the filter id
        $builder->add(static::FILTER_ID_NAME, HiddenType::class, ['attr' => ['value' => $this->config->getId()]]);

        $this->buildElements($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'filter'    => null,
                'framework' => null,
            ]
        );
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
             * @var TypeInterface
             */
            $type = new $class($this->config);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class) || !is_subclass_of($type, TypeInterface::class)) {
                continue;
            }

            $type->buildForm($element, $builder);
        }
    }

    /**
     * Get the action based on current filter action
     *
     * @return string
     */
    protected function getAction()
    {
        $filter = $this->config->getFilter();

        if (!isset($filter['action'])) {
            return '';
        }

        /**
         * @var InsertTags $insertTagAdapter
         */
        $insertTagAdapter = $this->framework->createInstance(InsertTags::class);

        // while unit testing, the mock object cant be instantiated
        if (null === $insertTagAdapter) {
            $insertTagAdapter = $this->framework->getAdapter(InsertTags::class);
        }

        return urldecode($insertTagAdapter->replace($filter['action']));
    }
}
