<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

use Contao\Date;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartProcessor;
use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as SymfonyDateTimeType;
use Symfony\Contracts\Translation\TranslatorInterface;

class DateTimeType extends AbstractFilterType
{
    const TYPE = 'date_time_type';

    /**
     * @var DateUtil
     */
    protected $dateUtil;

    public function __construct(
        FilterQueryPartProcessor $filterQueryPartProcessor,
        FilterQueryPartCollection $filterQueryPartCollection,
        TranslatorInterface $translator,
        DateUtil $dateUtil
    ) {
        parent::__construct($filterQueryPartProcessor, $filterQueryPartCollection, $translator);
        $this->dateUtil = $dateUtil;
    }

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildQuery(FilterTypeContext $filterTypeContext)
    {
        $this->filterQueryPartCollection->addPart($this->filterQueryPartProcessor->composeQueryPart($filterTypeContext));
    }

    public function buildForm($filterTypeContext)
    {
        $builder = $filterTypeContext->getFormBuilder();

        $builder->add($filterTypeContext->getName(), SymfonyDateTimeType::class, $this->getOptions($filterTypeContext));
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator,dateTimeFormat,minDateTime,maxDateTime;{visualization_legend},html5,dateWidget,customLabel,hideLabel,addPlaceholder;'.$appendPalette;
    }

    public function getOperators(): array
    {
        //remove this operators from the DatabaseUtil::OPERATORS array
        $remove = [
            DatabaseUtil::OPERATOR_IN,
            DatabaseUtil::OPERATOR_NOT_IN,
            DatabaseUtil::OPERATOR_LIKE,
            DatabaseUtil::OPERATOR_UNLIKE,
            DatabaseUtil::OPERATOR_REGEXP,
            DatabaseUtil::OPERATOR_NOT_REGEXP,
            DatabaseUtil::OPERATOR_IS_NULL,
            DatabaseUtil::OPERATOR_IS_NOT_NULL,
            DatabaseUtil::OPERATOR_IS_EMPTY,
            DatabaseUtil::OPERATOR_IS_NOT_EMPTY,
        ];

        return array_values(array_diff(parent::getOperators(), $remove));
    }

    public function getOptions(FilterTypeContext $filterTypeContext): array
    {
        $options = parent::getOptions($filterTypeContext);
        $format = $filterTypeContext->getDateTimeFormat() ?: 'd.m.Y H:i';
        $options['html5'] = $filterTypeContext->isHtml5();

        if (true === $options['html5']) {
            $options['date_format'] = $this->dateUtil->transformPhpDateFormatToRFC3339($format);

            if ($filterTypeContext->getMinDateTime()) {
                $options['attr']['min'] = Date::parse('Y-m-d\TH:i', $this->dateUtil->getTimeStamp($filterTypeContext->getMinDateTime())); // valid rfc 3339 date `YYYY-MM-DD` format must be used
            }

            if ($filterTypeContext->getMaxDateTime()) {
                $options['attr']['max'] = Date::parse('Y-m-d\TH:i', $this->dateUtil->getTimeStamp($filterTypeContext->getMaxDateTime())); // valid rfc 3339 date `YYYY-MM-DD` format must be used
            }
        } else {
            $options['format'] = $this->dateUtil->transformPhpDateFormatToRFC3339($format);
        }

        $options['group_attr']['class'] = isset($options['group_attr']['class']) ? $options['group_attr']['class'].' datepicker timepicker' : 'datepicker timepicker';
        $options['attr']['data-iso8601-format'] = $this->dateUtil->transformPhpDateFormatToISO8601($format);
        $options['attr']['data-enable-time'] = 'true';
        $options['attr']['data-date-format'] = $format;

        if ($filterTypeContext->getMinDateTime()) {
            $options['attr']['data-min-date'] = Date::parse($format, $this->dateUtil->getTimeStamp($filterTypeContext->getMinDateTime()));
        }

        if ($filterTypeContext->getMaxDateTime()) {
            $options['attr']['data-max-date'] = Date::parse($format, $this->dateUtil->getTimeStamp($filterTypeContext->getMaxDateTime()));
        }

        $options['widget'] = $filterTypeContext->getWidget();

        return $options;
    }
}
