<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Form;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Exception\MissingFilterConfigException;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeInterface;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    const FILTER_ID_NAME = 'f_id';
    const FILTER_REFERRER_NAME = 'f_ref';
    const FILTER_RESET_URL_PARAMETER_NAME = 'f_reset';
    const FILTER_FORM_SUBMITTED = 'f_submitted';
    const FILTER_PAGE_ID_NAME = 'f_pageId';

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

        $builder->setAction($this->config->getAction());

        if (isset($filter['method'])) {
            $builder->setMethod($filter['method']);
        }

        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        // always add a hidden field with the filter id
        $builder->add(static::FILTER_ID_NAME, HiddenType::class, ['attr' => ['value' => $this->config->getId()]]);

        // always add a hidden field with the page id
        global $objPage;
        $builder->add(static::FILTER_PAGE_ID_NAME, HiddenType::class, ['attr' => ['value' => $objPage->id]]);

        // always add a hidden field with the referrer url (required by reset for example to redirect back to user action page) -> use request query string when in esi _ fragment sub-request
        if ($request->query->has('request')) {
            $referrerUrl = $request->getSchemeAndHttpHost().'/'.$request->query->get('request');
        } else {
            // Check if referrer is set to set again
            if (Environment::get('isAjaxRequest') && $request->get($filter['name']) && isset($request->get($filter['name'])[static::FILTER_REFERRER_NAME])) {
                if (parse_url($request->get($filter['name'])[static::FILTER_REFERRER_NAME], PHP_URL_HOST) !== parse_url(Environment::get('url'), PHP_URL_HOST)) {
                    throw new \Exception('Invalid redirect url.');
                }

                $referrerUrl = $request->get($filter['name'])[static::FILTER_REFERRER_NAME];
            } else {
                $referrerUrl = $request->getUri();
            }
        }

        $builder->add(static::FILTER_REFERRER_NAME, HiddenType::class, [
            'attr' => [
                'value' => $referrerUrl,
            ],
        ]);

        $this->buildElements($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'filter' => null,
            'framework' => null,
        ]);
    }

    /**
     * Build the form fields for the given elements.
     */
    protected function buildElements(FormBuilderInterface $builder, array $options)
    {
        $elements = $this->config->getElements();

        if (null === $elements) {
            return;
        }

        $wrappers = [];
        $types = \System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        $newTypes = \System::getContainer()->get('huh.filter.filter_type.collection')->getTypes();
        $types = array_merge($types, $newTypes);

        if (!\is_array($types) || empty($types)) {
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

            if (!\is_array($config)) {
                $this->buildFilterTypeElement($element, $config, $builder);

                continue;
            }

            $class = $config['class'];

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
                $options = $type->getOptions($element, $builder);
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

    protected function buildFilterTypeElement(FilterConfigElementModel $element, FilterTypeInterface $filterType, FormBuilderInterface $builder)
    {
        $context = new FilterTypeContext();

        if ($element->isInitial) {
            return;
        }

        $context->setId($element->id);
        $context->setName($element->type.'_'.$element->id);
        $context->setValue($element->value);
        $context->setDefaultValue($element->defaultValue);
        $context->setPlaceholder($element->placeholder);
        $context->setFormBuilder($builder);
        $context->setTitle($element->title);
        $context->setLabel($element->label);
        $context->setParent($element->getRelated('pid'));
        $context->setSubmitOnChange($element->submitOnChange);
        $context->setExpanded($element->expanded);
        $context->setMultiple($element->multiple);
        $context->setDateTimeFormat($element->dateTimeFormat);
        $context->setMinDateTime($element->minDateTime);
        $context->setMaxDateTime($element->maxDateTime);

        if ($element->hideLabel) {
            $context->hideLabel();
        }

        try {
            $filterType->buildForm($context);
        } catch (InvalidOptionException $e) {
            return;
        }
    }

    /**
     * Build the wrapper form elements.
     */
    protected function buildWrapperElements(array $wrappers, FormBuilderInterface $builder, array $options)
    {
        $types = \System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!\is_array($types) || empty($types)) {
            return;
        }

        /*
         * @var FilterConfigElementModel
         */
        foreach ($wrappers as $element) {
            if (!isset($types[$element->type])) {
                continue;
            }

            $type = $types[$element->type];
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
}
