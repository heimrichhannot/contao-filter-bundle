<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Type\Concrete;

use Contao\Date;
use Doctrine\DBAL\Types\Types;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartProcessor;
use HeimrichHannot\FilterBundle\Type\AbstractFilterType;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;
use HeimrichHannot\FilterBundle\Type\InitialFilterTypeInterface;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as SymfonyDateTimeType;
use Symfony\Contracts\Translation\TranslatorInterface;

class DateTimeType extends AbstractFilterType implements InitialFilterTypeInterface
{
    const TYPE = 'date_time_type';
    const GROUP = 'date';

    const WIDGET_TYPE_CHOICE = 'choice';
    const WIDGET_TYPE_SINGLE_TEXT = 'single_text';

    /**
     * @var DateUtil
     */
    protected $dateUtil;

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildQuery(FilterTypeContext $filterTypeContext)
    {
        if ($filterTypeContext->getElementConfig()->isInitial) {
            $filterTypeContext->setValue($this->container->get('dateUtil')->getTimeStamp($filterTypeContext->getElementConfig()->initialValue));
            $filterTypeContext->getElementConfig()->initialValueType = Types::INTEGER;
        } else {
            $filterTypeContext->setValue($this->container->get('dateUtil')->getTimeStamp($filterTypeContext->getValue()));
        }

        if (empty($filterTypeContext->getValue())) {
            return;
        }

        $filterTypeContext->setValueType(Types::INTEGER);
        $this->filterQueryPartCollection->addPart($this->filterQueryPartProcessor->composeQueryPart($filterTypeContext));
    }

    public function buildForm(FilterTypeContext $filterTypeContext)
    {
        $builder = $filterTypeContext->getFormBuilder();
        $builder->add($filterTypeContext->getElementConfig()->getElementName(), SymfonyDateTimeType::class, $this->getOptions($filterTypeContext));
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator,dateTimeFormat,minDateTime,maxDateTime;{visualization_legend},html5,dateWidget,customLabel,hideLabel,addPlaceholder;'.$appendPalette;
    }

    public function getInitialPalette(string $prependPalette, string $appendPalette)
    {
        $dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];
        $dca['fields']['initialValue']['eval']['rgxp'] = 'datim';
        $dca['fields']['initialValue']['eval']['datepicker'] = true;
        $dca['fields']['initialValue']['eval']['mandatory'] = true;

        return $prependPalette.'{config_legend},field,operator,dateTimeFormat,initialValue;'.$appendPalette;
    }

    public function getInitialValueTypes(array $types): array
    {
        return $types;
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
        $elementConfig = $filterTypeContext->getElementConfig();
        $options = parent::getOptions($filterTypeContext);
        $format = $elementConfig->dateTimeFormat ?: 'd.m.Y H:i';

        $options['widget'] = $elementConfig->dateWidget;

        switch ($elementConfig->dateWidget) {
            case static::WIDGET_TYPE_SINGLE_TEXT:
                if ($elementConfig->html5) {
                    $options['html5'] = $elementConfig->html5;
                    $options['date_format'] = $this->container->get('dateUtil')->transformPhpDateFormatToRFC3339($format);

                    if ('' !== $elementConfig->minDateTime) {
                        $options['attr']['min'] = Date::parse('Y-m-d\TH:i', $this->container->get('dateUtil')->getTimeStamp($elementConfig->minDateTime)); // valid rfc 3339 date `YYYY-MM-DD` format must be used
                    }

                    if ('' !== $elementConfig->maxDateTime) {
                        $options['attr']['max'] = Date::parse('Y-m-d\TH:i', $this->container->get('dateUtil')->getTimeStamp($elementConfig->maxDateTime)); // valid rfc 3339 date `YYYY-MM-DD` format must be used
                    }
                } else {
                    $options['format'] = $this->container->get('dateUtil')->transformPhpDateFormatToRFC3339($format);
                }

                $options['group_attr']['class'] = isset($options['group_attr']['class']) ? $options['group_attr']['class'].' datepicker timepicker' : 'datepicker timepicker';
                $options['attr']['data-iso8601-format'] = $this->container->get('dateUtil')->transformPhpDateFormatToISO8601($format);
                $options['attr']['data-enable-time'] = 'true';
                $options['attr']['data-date-format'] = $format;

                if ('' !== $elementConfig->minDateTime) {
                    $options['attr']['data-min-date'] = Date::parse($format, $this->container->get('dateUtil')->getTimeStamp($elementConfig->minDateTime));
                }

                if ('' !== $elementConfig->maxDateTime) {
                    $options['attr']['data-max-date'] = Date::parse($format, $this->container->get('dateUtil')->getTimeStamp($elementConfig->maxDateTime));
                }

                break;

            case static::WIDGET_TYPE_CHOICE:
                // months and days restriction cant be configured from min and max date

                $time = time();

                $minYear = Date::parse('Y', strtotime('-5 year', $time));
                $maxYear = Date::parse('Y', strtotime('+5 year', $time));

                if ('' !== $elementConfig->minDateTime) {
                    $minYear = Date::parse('Y', $this->container->get('dateUtil')->getTimeStamp($elementConfig->minDateTime));
                }

                if ('' !== $elementConfig->maxDateTime) {
                    $maxYear = Date::parse('Y', $this->container->get('dateUtil')->getTimeStamp($elementConfig->maxDateTime));
                }

                $options['years'] = range($minYear, $maxYear, 1);

                break;
        }

        if ('' === (string) $filterTypeContext->getValue()) {
            $options['data'] = null;
        } else {
            $options['data'] = date_create_from_format($format, $filterTypeContext->getValue());
        }

        return $options;
    }

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'dateUtil' => DateUtil::class,
        ]);
    }
}
