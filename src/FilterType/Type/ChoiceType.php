<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

use Doctrine\DBAL\Driver\Connection;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\Filter\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\Filter\FilterQueryPartProcessor;
use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChoiceType extends AbstractFilterType
{
    const TYPE = 'choice_type';
    protected FieldOptionsChoice $fieldOptionsChoice;
    protected ModelUtil          $modelUtil;
    protected Connection         $connection;

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

    public function buildForm($filterTypeContext)
    {
        $builder = $filterTypeContext->getFormBuilder();

        $builder->add($filterTypeContext->getName(), SymfonyChoiceType::class, $this->getOptions($filterTypeContext));
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator,submitOnChange;{visualization_legend},addPlaceholder,customLabel,hideLabel;{expert_legend},cssClass;'.$appendPalette;
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
        $options = parent::getOptions($filterTypeContext);
        $options['choices'] = array_flip($this->collectChoices($filterTypeContext));

        if ($filterTypeContext->isSubmitOnChange()) {
            if ($filterTypeContext->getParent()->asyncFormSubmit) {
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

        return $options;
    }

    /**
     * Get the list of available choices.
     */
    public function collectChoices(FilterTypeContext $context): array
    {
        if (null === ($element = $this->modelUtil->findModelInstanceByPk('tl_filter_config_element', $context->getId()))) {
            return [];
        }

        return $this->fieldOptionsChoice->getCachedChoices([
            'element' => $element,
            'filter' => $context->getParent()->row(),
        ]);
    }
}
