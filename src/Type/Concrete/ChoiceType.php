<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Type\Concrete;

use Doctrine\DBAL\Driver\Connection;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartProcessor;
use HeimrichHannot\FilterBundle\Type\AbstractFilterType;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;
use HeimrichHannot\FilterBundle\Type\InitialFilterTypeInterface;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChoiceType extends AbstractFilterType implements InitialFilterTypeInterface
{
    const TYPE = 'choice_type';
    const GROUP = 'choice';

    /**
     * @var FieldOptionsChoice
     */
    protected $fieldOptionsChoice;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(
        FilterQueryPartProcessor $filterQueryPartProcessor,
        FilterQueryPartCollection $filterQueryPartCollection,
        TranslatorInterface $translator,
        FieldOptionsChoice $fieldOptionsChoice,
        ModelUtil $modelUtil,
        Connection $connection
    ) {
        parent::__construct($filterQueryPartProcessor, $filterQueryPartCollection, $translator);
        $this->fieldOptionsChoice = $fieldOptionsChoice;
        $this->modelUtil = $modelUtil;
        $this->connection = $connection;
    }

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildForm(FilterTypeContext $filterTypeContext)
    {
        $builder = $filterTypeContext->getFormBuilder();
        $builder->add($filterTypeContext->getElementConfig()->getElementName(), SymfonyChoiceType::class, $this->getOptions($filterTypeContext));
    }

    public function buildQuery(FilterTypeContext $filterTypeContext)
    {
        if ($filterTypeContext->getElementConfig()->isInitial && AbstractFilterType::VALUE_TYPE_ARRAY === $filterTypeContext->getElementConfig()->initialValueType) {
            $elementConfig = $filterTypeContext->getElementConfig();
            $elementConfig->initialValue = $filterTypeContext->getElementConfig()->initialValueArray;
            $filterTypeContext->setElementConfig($elementConfig);
        }

        $this->filterQueryPartCollection->addPart($this->filterQueryPartProcessor->composeQueryPart($filterTypeContext));
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator,customOptions,reviseOptions,dynamicOptions,sortOptionValues,adjustOptionLabels,submitOnChange,expanded,multiple,addGroupChoiceField,doNotCacheOptions;{visualization_legend},addPlaceholder,customLabel,hideLabel;'.$appendPalette;
    }

    public function getOperators(): array
    {
        //remove this operators from the DatabaseUtil::OPERATORS array
        $remove = [
            DatabaseUtil::OPERATOR_EQUAL,
            DatabaseUtil::OPERATOR_UNEQUAL,
            DatabaseUtil::OPERATOR_LIKE,
            DatabaseUtil::OPERATOR_UNLIKE,
            DatabaseUtil::OPERATOR_GREATER,
            DatabaseUtil::OPERATOR_GREATER_EQUAL,
            DatabaseUtil::OPERATOR_LOWER,
            DatabaseUtil::OPERATOR_LOWER_EQUAL,
        ];

        return array_values(array_diff(parent::getOperators(), $remove));
    }

    public function getOptions(FilterTypeContext $filterTypeContext): array
    {
        $elementConfig = $filterTypeContext->getElementConfig();
        $options = parent::getOptions($filterTypeContext);
        $options['choices'] = array_flip($this->collectChoices($filterTypeContext));
        $options['choice_translation_domain'] = false;
        $options['expanded'] = $elementConfig->expanded;

        if ((bool) $elementConfig->submitOnChange) {
            if ($filterTypeContext->getFilterConfig()->asyncFormSubmit) {
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

        if (isset($options['attr']['placeholder'])) {
            $options['attr']['data-placeholder'] = $options['attr']['placeholder'];
            $options['placeholder'] = $options['attr']['placeholder'];
            unset($options['attr']['placeholder']);

            $options['required'] = false;
            $options['empty_data'] = true === $elementConfig->multiple ? [] : '';
        }

        $options['multiple'] = $elementConfig->multiple;
        $options['data'] = $filterTypeContext->getValue();

        // forgiving array handling
        if ($elementConfig->addDefaultValue) {
            if (isset($options['multiple']) && true === (bool) $options['multiple'] && isset($options['data'])) {
                $options['data'] = !\is_array($options['data']) ? [$options['data']] : $options['data'];
            }
        }

        return $options;
    }

    /**
     * Get the list of available choices.
     */
    public function collectChoices(FilterTypeContext $filterTypeContext): array
    {
        if (null === $filterTypeContext->getElementConfig()) {
            return [];
        }

        return $this->fieldOptionsChoice->getCachedChoices([
            'element' => $filterTypeContext->getElementConfig(),
            'filter' => $filterTypeContext->getFilterConfig()->row(),
        ]);
    }

    public function getInitialPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator,initialValueType;'.$appendPalette;
    }

    public function getInitialValueChoices(FilterTypeContext $filterTypeContext): array
    {
        return $this->collectChoices($filterTypeContext);
    }

    public function getInitialValueTypes(array $types): array
    {
        $remove = [];

        return array_values(array_diff($types, $remove));
    }
}
