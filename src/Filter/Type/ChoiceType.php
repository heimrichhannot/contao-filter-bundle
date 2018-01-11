<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;


use Contao\System;
use Contao\Widget;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;

class ChoiceType extends AbstractType implements TypeInterface
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
        $builder->add($element['field'], \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, $this->getOptions($element, $builder));
    }

    /**
     * Collect the choices and return as array
     *
     * @param array $element
     * @return array
     */
    protected function getChoices(array $element)
    {
        $choices = [];
        $options = [];
        $filter  = $this->config->getFilter();

        \Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']])) {
            return $choices;
        }

        $data = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']];

        switch ($data['inputType']) {
            case 'cfgTags':
                if (!isset($data['eval']['tagsManager'])) {
                    break;
                }

                /**
                 * @var \Codefog\TagsBundle\Manager\ManagerInterface $tagsManager
                 */
                $tagsManager = System::getContainer()->get('codefog_tags.manager_registry')->get(
                    $data['eval']['tagsManager']
                );

                $tags = $tagsManager->findMultiple();

                /** @var \Codefog\TagsBundle\Tag $tag */
                foreach ($tags as $tag) {
                    $options[] = ['label' => $tag->getName(), 'value' => $tag->getValue()];
                }

                break;
            default:
                $class = $GLOBALS['TL_FFL'][$data['inputType']];

                if (!class_exists($class)) {
                    return $choices;
                }

                $attributes = Widget::getAttributesFromDca(
                    $data,
                    $element['field'],
                    '',
                    $element['field'],
                    $filter['dataContainer'],
                    null
                );

                if (is_array($attributes['options'])) {
                    $options = $attributes['options'];
                }
        }

        foreach ($options as $option) {
            if (!isset($option['label']) || !isset($option['value'])) {
                continue;
            }

            $choices[$option['label']] = $option['value'];
        }

        return $choices;
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
        return $options;
    }
}