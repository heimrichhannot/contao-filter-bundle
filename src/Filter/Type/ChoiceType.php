<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Event\AdjustFilterOptionsEvent;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class ChoiceType extends AbstractType
{
    public const TYPE = 'choice';

    public const VALUE_TYPES_MULTIPLE = [
        self::VALUE_TYPE_ARRAY,
        self::VALUE_TYPE_CONTEXTUAL,
    ];

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $builder->whereElement($element, $this->getName($element), $this->config, $this->getDefaultOperator($element));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add(
            $this->getName($element),
            \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
            $this->getOptions($element, $builder)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_EQUAL;
    }

    /**
     * Get the list of available choices.
     *
     * @return array|mixed
     */
    public function getChoices(FilterConfigElementModel $element)
    {
        if (!System::getContainer()->has('huh.filter.choice.field_options')) {
            return [];
        }

        $method = $element->doNotCacheOptions ? 'getChoices' : 'getCachedChoices';

        return System::getContainer()->get('huh.filter.choice.field_options')->{$method}(
            ['element' => $element, 'filter' => $this->config->getFilter()]
        );
    }

    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $data = $this->config->getData();
        $name = $this->getName($element);

        $options = parent::getOptions($element, $builder, false);

        $options['choices'] = array_flip($this->getChoices($element));
        $options['choice_translation_domain'] = false; // disable translation
//        $options['choices'] = array_filter($options['choices'], 'strlen'); // remove empty elements (placeholders)

        if (isset($options['attr']['placeholder'])) {
            $options['attr']['data-placeholder'] = $options['attr']['placeholder'];
            $options['placeholder'] = $options['attr']['placeholder'];
            unset($options['attr']['placeholder']);

            $options['required'] = false;
            $options['empty_data'] = true === (bool) $element->multiple ? [] : '';
        }

        $options['expanded'] = (bool) $element->expanded;
        $options['multiple'] = (bool) $element->multiple;

        if ($element->submitOnChange) {
            if ($this->config->getFilter()['asyncFormSubmit']) {
                $options['attr']['data-submit-on-change'] = 1;
            } else {
                if ($options['expanded']) {
                    $options['choice_attr'] = function ($choiceValue, $key, $value) {
                        return ['onchange' => 'this.form.submit()'];
                    };
                } else {
                    $options['attr']['onchange'] = 'this.form.submit()';
                }
            }
        }

        // forgiving array handling
        if ($element->addDefaultValue && !isset($data[$name])) {
            if (isset($options['multiple']) && true === (bool) $options['multiple'] && isset($options['data'])) {
                $options['data'] = !\is_array($options['data']) ? [$options['data']] : $options['data'];
            }
        }

        if ($triggerEvent) {
            $event = System::getContainer()->get('event_dispatcher')->dispatch(
                new AdjustFilterOptionsEvent($name, $options, $element, $builder, $this->config),
                AdjustFilterOptionsEvent::NAME
            );

            return $event->getOptions();
        }

        return $options;
    }
}
