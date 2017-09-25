<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter\Temporal;

use HeimrichHannot\FilterBundle\Context\ContextInterface;
use HeimrichHannot\FilterBundle\Filter\FilterInterface;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilderInterface;
use HeimrichHannot\FilterBundle\Temporal\Choice\YearChoice;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class YearFilter implements FilterInterface
{
    /**
     * Build the filter query
     * @param FilterQueryBuilderInterface $builder The query builder
     * @param array $data The form data
     * @param boolean $count Distinguish between count or fetch query
     */
    public function buildQuery(FilterQueryBuilderInterface $builder, array $data = [], $count = false)
    {
        $year = intval($data[YearFilter::getName()]);

        if ($year > 0) {
            $start = mktime(0, 0, 0, 0, 1, $year);
            $end   = mktime(0, 0, 0, 12, 1, $year);

            $builder->addColumns(["tl_news.date >= ? AND tl_news.date <= ?"]);
            $builder->addValues([$start, $end]);
        }
    }

    /**
     * Builds the form, add your filter fields here
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param FormBuilderInterface $builder The form builder
     * @param ContextInterface $context The current filter module
     */
    public function buildForm(FormBuilderInterface $builder, ContextInterface $context)
    {
        $builder->add(
            static::getName(),
            ChoiceType::class,
            [
                'choices'                   => YearChoice::create($context, $builder->getData())->getChoices(),
                'choice_translation_domain' => false, // disable translation
                'required'                  => false,
                'placeholder'               => 'news.form.filter.placeholder.year',
                'label'                     => 'news.form.filter.label.year',
                'attr'                      => [
                    'onchange' => 'this.form.submit()'
                ]
            ]
        );
    }


    /**
     * Clarify the filter name
     * @return string The filter name
     */
    public static function getName()
    {
        return 'year';
    }


}