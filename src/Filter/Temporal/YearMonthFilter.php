<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter\Temporal;

use HeimrichHannot\FilterBundle\Context\ContextInterface;
use HeimrichHannot\FilterBundle\Filter\FilterInterface;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilderInterface;
use HeimrichHannot\FilterBundle\Temporal\Choice\MonthChoice;
use HeimrichHannot\FilterBundle\Temporal\Choice\YearChoice;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class YearMonthFilter implements FilterInterface
{
    /**
     * Build the filter query
     * @param FilterQueryBuilderInterface $builder The query builder
     * @param array $data The form data
     * @param boolean $count Distinguish between count or fetch query
     */
    public function buildQuery(FilterQueryBuilderInterface $builder, array $data = [], $count = false)
    {
        $month = intval($data[YearMonthFilter::getName()]);
        $year  = intval($data[YearFilter::getName()]);

        if ($year > 0) {
            $start = mktime(0, 0, 0, $month ?: 0, 1, $year);
            $end   = mktime(0, 0, 0, $month ? $month + 1 : 12, 1, $year);

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
            YearFilter::getName(),
            ChoiceType::class,
            [
                'choices'                   => YearChoice::create()->setContext($context)->getChoices(),
                'choice_translation_domain' => false, // disable translation
                'required'                  => false,
                'placeholder'               => 'news.form.filter.placeholder.year',
                'label'                     => 'news.form.filter.label.year',
                'attr'                      => [
                    'onchange' => 'this.form.submit()'
                ]
            ]
        );

        // required to set choice data from request
        $builder->add(
            YearMonthFilter::getName(),
            HiddenType::class
        );


        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($context) {
                $form = $event->getForm();
                $year = $form->get(YearFilter::getName())->getData();

                if ($year !== null) {
                    $choices = MonthChoice::create()->setContext($context)->setYear($year)->getChoices();
                    $month   = $event->getData()[YearMonthFilter::getName()];

                    $form->add(
                        YearMonthFilter::getName(),
                        ChoiceType::class,
                        [
                            'choices'     => $choices,
                            'required'    => false,
                            'placeholder' => 'news.form.filter.placeholder.month',
                            'label'       => 'news.form.filter.label.month',
                            'attr'        => [
                                'onchange' => 'this.form.submit()'
                            ],
                            'data'        => in_array($month, $choices) ? $month : null
                        ]
                    );
                }
            }
        );
    }


    /**
     * Clarify the filter name
     * @return string The filter name
     */
    public static function getName()
    {
        return 'month';
    }


}