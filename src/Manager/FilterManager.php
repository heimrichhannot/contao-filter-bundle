<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Manager;


use HeimrichHannot\FilterBundle\Context\ContextInterface;
use HeimrichHannot\FilterBundle\Filter\FilterInterface;
use HeimrichHannot\FilterBundle\Form\FilterForm;
use HeimrichHannot\FilterBundle\Model\FilterElementModel;
use Symfony\Component\Form\Forms;

class FilterManager
{

    /**
     * Build the form for a given filter
     * @param int $filter
     * @param ContextInterface $context
     */
    public function buildForm($filter, ContextInterface $context)
    {
        $factory = Forms::createFormFactoryBuilder()->addExtensions([])->getFormFactory();

        $options = [];

        $builder = $factory->createBuilder(FilterForm::class, [], $options);

        foreach ($this->getFilters($filter) as $alias) {
            /**
             * @var $filter FilterInterface
             */
            $filter = \System::getContainer()->get('huh.filter.registry')->get($alias);
            $filter->buildForm($builder, $context);
        }

        $form = $builder->getForm()->handleRequest();

        /**
         * @var \Twig_Environment $twig
         */
        $twig = \System::getContainer()->get('twig');

        return $twig->render(
            '@HeimrichHannotContaoNews/forms/filter_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function getFilters($filter)
    {
        if (($elements = FilterElementModel::findPublishedByPid($filter)) === null) {
            return [];
        }

        return array_intersect($elements->fetchEach('type'), \System::getContainer()->get('huh.filter.registry')->getAliases());
    }

    /**
     * Build the query for a given filter
     * @param int $filter
     */
    public function buildQuery($filter)
    {

    }
}