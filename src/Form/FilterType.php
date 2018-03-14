<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Form;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\InsertTags;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Exception\MissingFilterConfigException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
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

    /**
     * Build the form fields for the given elements.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildElements(FormBuilderInterface $builder, array $options)
    {
        $elements = $this->config->getElements();

        if (null === $elements) {
            return;
        }

        $wrappers = [];
        $types    = \System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!is_array($types) || empty($types)) {
            return;
        }

        /*
         * @var FilterConfigElementModel
         */
        foreach ($elements as $element) {
            if (!isset($types[$element->type])) {
                continue;
            }

            $config = $types[$element->type];
            $class  = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Filter\AbstractType $type */
            $type = new $class($this->config);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class)) {
                continue;
            }

            if (null === ($name = $type->getName($element))) {
                continue;
            }

            // collect wrappers and render afterwards
            if (isset($config['wrapper']) && true === $config['wrapper']) {
                $options                 = $type->getOptions($element, $builder);
                $options['inherit_data'] = false;
                $builder->add($builder->create($name, FormType::class, $options)); // add the group here to maintain correct form order
                $wrappers[] = $element;
                continue;
            }

            // as we build the form at every request (even in back end mode), catch errors that might be thrown from invalid options, formats etc
            try {
                if (!$element->isInitial) {
                    $type->buildForm($element, $builder);
                }
            } catch (InvalidOptionsException $e) {
                continue;
            }
        }

        $this->buildWrapperElements($wrappers, $builder, $options);
    }

    /**
     * Build the wrapper form elements.
     *
     * @param array $wrappers
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildWrapperElements(array $wrappers, FormBuilderInterface $builder, array $options)
    {
        $types = \System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!is_array($types) || empty($types)) {
            return;
        }

        /*
         * @var FilterConfigElementModel
         */
        foreach ($wrappers as $element) {
            if (!isset($types[$element->type])) {
                continue;
            }

            $type  = $types[$element->type];
            $class = $type['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Filter\AbstractType $type */
            $type = new $class($this->config);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class)) {
                continue;
            }

            // as we build the form at every request (even in back end mode), catch errors that might be thrown from invalid options, formats etc
            try {
                if (!$element->isInitial) {
                    $type->buildForm($element, $builder);
                }
            } catch (InvalidOptionsException $e) {
                continue;
            }
        }
    }

    /**
     * Get the action based on current filter action.
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
         * @var InsertTags
         */
        $insertTagAdapter = $this->framework->createInstance(InsertTags::class);

        // while unit testing, the mock object cant be instantiated
        if (null === $insertTagAdapter) {
            $insertTagAdapter = $this->framework->getAdapter(InsertTags::class);
        }

        return urldecode($insertTagAdapter->replace($filter['action']));
    }
}
