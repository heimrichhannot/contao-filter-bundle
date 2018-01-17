<?php

namespace HeimrichHannot\FilterBundle\DependencyInjection;

use HeimrichHannot\FilterBundle\Filter\Type\ButtonType;
use HeimrichHannot\FilterBundle\Filter\Type\CheckboxType;
use HeimrichHannot\FilterBundle\Filter\Type\ChoiceType;
use HeimrichHannot\FilterBundle\Filter\Type\ColorType;
use HeimrichHannot\FilterBundle\Filter\Type\CountryType;
use HeimrichHannot\FilterBundle\Filter\Type\EmailType;
use HeimrichHannot\FilterBundle\Filter\Type\HiddenType;
use HeimrichHannot\FilterBundle\Filter\Type\IntegerType;
use HeimrichHannot\FilterBundle\Filter\Type\LanguageType;
use HeimrichHannot\FilterBundle\Filter\Type\LocaleType;
use HeimrichHannot\FilterBundle\Filter\Type\MoneyType;
use HeimrichHannot\FilterBundle\Filter\Type\NumberType;
use HeimrichHannot\FilterBundle\Filter\Type\PasswordType;
use HeimrichHannot\FilterBundle\Filter\Type\PercentType;
use HeimrichHannot\FilterBundle\Filter\Type\RadioType;
use HeimrichHannot\FilterBundle\Filter\Type\RangeType;
use HeimrichHannot\FilterBundle\Filter\Type\ResetType;
use HeimrichHannot\FilterBundle\Filter\Type\SearchType;
use HeimrichHannot\FilterBundle\Filter\Type\SubmitType;
use HeimrichHannot\FilterBundle\Filter\Type\TelType;
use HeimrichHannot\FilterBundle\Filter\Type\TextareaType;
use HeimrichHannot\FilterBundle\Filter\Type\TextConcatType;
use HeimrichHannot\FilterBundle\Filter\Type\TextType;
use HeimrichHannot\FilterBundle\Filter\Type\UrlType;
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
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                    ->scalarNode('class')->cannotBeEmpty()->end()
                                    ->scalarNode('type')->defaultValue('other')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('templates')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                    ->scalarNode('template')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}