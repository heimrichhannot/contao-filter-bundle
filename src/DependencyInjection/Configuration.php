<?php

namespace HeimrichHannot\FilterBundle\DependencyInjection;

use HeimrichHannot\FilterBundle\Filter\Type\ChoiceType;
use HeimrichHannot\FilterBundle\Filter\Type\HiddenType;
use HeimrichHannot\FilterBundle\Filter\Type\ResetType;
use HeimrichHannot\FilterBundle\Filter\Type\SubmitType;
use HeimrichHannot\FilterBundle\Filter\Type\TextType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor.
     *
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }


    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode    = $treeBuilder->root('huh');

        $rootNode
            ->children()
                ->arrayNode('filter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('types')
                            ->addDefaultsIfNotSet()
                            ->children()
                                // parent filter
//                                ->scalarNode('parent')->cannotBeEmpty()->defaultValue(TextType::class)->end()
                                // text fields
                                ->scalarNode('text')->cannotBeEmpty()->defaultValue(TextType::class)->end()
//                                ->scalarNode('textarea')->cannotBeEmpty()->defaultValue(TextareaType::class)->end()
//                                ->scalarNode('email')->cannotBeEmpty()->defaultValue(EmailType::class)->end()
//                                ->scalarNode('integer')->cannotBeEmpty()->defaultValue(IntegerType::class)->end()
//                                ->scalarNode('money')->cannotBeEmpty()->defaultValue(MoneyType::class)->end()
//                                ->scalarNode('number')->cannotBeEmpty()->defaultValue(NumberType::class)->end()
//                                ->scalarNode('password')->cannotBeEmpty()->defaultValue(PasswordType::class)->end()
//                                ->scalarNode('percent')->cannotBeEmpty()->defaultValue(PercentType::class)->end()
//                                ->scalarNode('search')->cannotBeEmpty()->defaultValue(SearchType::class)->end()
//                                ->scalarNode('url')->cannotBeEmpty()->defaultValue(UrlType::class)->end()
//                                ->scalarNode('range')->cannotBeEmpty()->defaultValue(RangeType::class)->end()
//                                ->scalarNode('tel')->cannotBeEmpty()->defaultValue(TelType::class)->end()
//                                ->scalarNode('color')->cannotBeEmpty()->defaultValue(ColorType::class)->end()
                                // choice fields
                                ->scalarNode('choice')->cannotBeEmpty()->defaultValue(ChoiceType::class)->end()
//                                ->scalarNode('entity')->cannotBeEmpty()->defaultValue(EntityType::class)->end()
//                                ->scalarNode('country')->cannotBeEmpty()->defaultValue(CountryType::class)->end()
//                                ->scalarNode('language')->cannotBeEmpty()->defaultValue(LanguageType::class)->end()
//                                ->scalarNode('locale')->cannotBeEmpty()->defaultValue(LocaleType::class)->end()
//                                ->scalarNode('timezone')->cannotBeEmpty()->defaultValue(TimezoneType::class)->end()
//                                ->scalarNode('currency')->cannotBeEmpty()->defaultValue(CurrencyType::class)->end()
                                // date and time fields
//                                ->scalarNode('date')->cannotBeEmpty()->defaultValue(DateType::class)->end()
//                                ->scalarNode('dateInterval')->cannotBeEmpty()->defaultValue(DateIntervalType::class)->end()
//                                ->scalarNode('dateTime')->cannotBeEmpty()->defaultValue(DateTimeType::class)->end()
//                                ->scalarNode('time')->cannotBeEmpty()->defaultValue(TimeType::class)->end()
//                                ->scalarNode('birthday')->cannotBeEmpty()->defaultValue(BirthdayType::class)->end()
                                // field groups
//                                ->scalarNode('collection')->cannotBeEmpty()->defaultValue(CollectionType::class)->end()
//                                ->scalarNode('repeated')->cannotBeEmpty()->defaultValue(RepeatedType::class)->end()
                                // hidden field
                                ->scalarNode('hidden')->cannotBeEmpty()->defaultValue(HiddenType::class)->end()
                                // buttons
//                                ->scalarNode('button')->cannotBeEmpty()->defaultValue(ButtonType::class)->end()
                                ->scalarNode('reset')->cannotBeEmpty()->defaultValue(ResetType::class)->end()
                                ->scalarNode('submit')->cannotBeEmpty()->defaultValue(SubmitType::class)->end()
                            ->end()
                        ->end()
                        ->arrayNode('templates')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('form_div_layout')->cannotBeEmpty()->defaultValue('@HeimrichHannotContaoFilter/forms/filter_form_div_layout.html.twig')->end()
                                ->scalarNode('form_table_layout')->cannotBeEmpty()->defaultValue('@HeimrichHannotContaoFilter/forms/filter_form_table_layout.html.twig')->end()
                                ->scalarNode('bootstrap_3_layout')->cannotBeEmpty()->defaultValue('@HeimrichHannotContaoFilter/forms/filter_form_bootstrap_3_layout.html.twig')->end()
                                ->scalarNode('bootstrap_3_horizontal_layout')->cannotBeEmpty()->defaultValue('@HeimrichHannotContaoFilter/forms/filter_form_bootstrap_3_horizontal_layout.html.twig')->end()
                                ->scalarNode('bootstrap_4_layout')->cannotBeEmpty()->defaultValue('@HeimrichHannotContaoFilter/forms/filter_form_bootstrap_4_layout.html.twig')->end()
                                ->scalarNode('bootstrap_4_horizontal_layout')->cannotBeEmpty()->defaultValue('@HeimrichHannotContaoFilter/forms/filter_form_bootstrap_4_horizontal_layout.html.twig')->end()
                                ->scalarNode('foundation_5_layout')->cannotBeEmpty()->defaultValue('@HeimrichHannotContaoFilter/forms/filter_form_foundation_5_layout.html.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}