<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use Ausi\SlugGenerator\SlugGenerator;
use Ausi\SlugGenerator\SlugOptions;
use Contao\Controller;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\StringUtil;
use Contao\System;
use DateTime;
use Exception;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Event\AdjustFilterOptionsEvent;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractType
{
    const VALUE_TYPE_SCALAR = 'scalar';
    const VALUE_TYPE_ARRAY = 'array';
    const VALUE_TYPE_CONTEXTUAL = 'contextual';
    const VALUE_TYPE_LATEST = 'latest';

    const VALUE_TYPES = [
        self::VALUE_TYPE_SCALAR,
        self::VALUE_TYPE_ARRAY,
        self::VALUE_TYPE_CONTEXTUAL,
    ];

    /**
     * @var FilterConfig
     */
    protected $config;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(FilterConfig $config)
    {
        $this->config = $config;
        $this->translator = System::getContainer()->get('translator');
    }

    /**
     * Get the field name.
     *
     * @return mixed
     */
    public function getName(FilterConfigElementModel $element)
    {
        $name = $this->getDefaultName($element) ?: $element->field;

        if ($element->customName && '' !== $element->name) {
            $name = $element->name;
        }

        if ('' === $name) {
            $slugOptions = new SlugOptions();
            $slugOptions->setValidChars('a-z0-9_')->setDelimiter('_');
            $slugGenerator = new SlugGenerator($slugOptions);
            $name = $slugGenerator->generate($element->title);
        }

        return $name;
    }

    public static function getDefaultValue(FilterConfigElementModel $element): array|string
    {
        /** @var InsertTagParser $insertTagParser */
        $insertTagParser = System::getContainer()->get('contao.insert_tag.parser');

        return match ($element->defaultValueType) {
            static::VALUE_TYPE_ARRAY => array_map(function ($val) use ($insertTagParser) {
                return $insertTagParser->replace($val['value']);
            }, StringUtil::deserialize($element->defaultValueArray, true)),
            default => $insertTagParser->replace($element->defaultValue),
        };
    }

    public static function getInitialValue(FilterConfigElementModel $element, array $contextualValues = [])
    {
        $value = match ($element->initialValueType) {
            static::VALUE_TYPE_ARRAY => array_map(function ($val) {
                return $val['value'];
            }, StringUtil::deserialize($element->initialValueArray, true)),
            static::VALUE_TYPE_CONTEXTUAL => $contextualValues[$element->field] ?? null,
            default => $element->initialValue,
        };

        // multilingual initial values
        if ($element->addMultilingualInitialValues)
        {
            $rows = StringUtil::deserialize($element->multilingualInitialValues, true);
            foreach ($rows as $row)
            {
                if ($GLOBALS['TL_LANGUAGE'] !== $row['language']) {
                    continue;
                }

                $value = match ($row['initialValueType']) {
                    static::VALUE_TYPE_ARRAY => $row['initialValueArray'],
                    static::VALUE_TYPE_CONTEXTUAL => $contextualValues[$element->field] ?? $value,
                    default => $row['initialValue'],
                };

                break;
            }
        }

        return $value;
    }

    /**
     * Build the filter query.
     *
     * @param FilterQueryBuilder       $builder The query builder
     * @param FilterConfigElementModel $element The element data
     */
    abstract public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element);

    /**
     * Builds the form, add your filter fields here.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param FilterConfigElementModel $element The element data
     * @param FormBuilderInterface     $builder The form builder
     */
    abstract public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder);

    /**
     * Get the default operator for this type.
     *
     * @param FilterConfigElementModel $element The element data
     *
     * @return string|null The returned string must be an operator defined in
     *     \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATORS
     */
    abstract public function getDefaultOperator(FilterConfigElementModel $element);

    /**
     * Get the default form element name.
     *
     * @param FilterConfigElementModel $element The element data
     *
     * @return string|null
     */
    abstract public function getDefaultName(FilterConfigElementModel $element);

    /**
     * Get the field label.
     *
     * @return string
     */
    public function getLabel(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $label = '';
        $filter = $this->config->getFilter();

        if (true === (bool) $element->customLabel && '' !== $element->label) {
            return $element->label;
        }

        Controller::loadDataContainer($filter['dataContainer']);
        Controller::loadLanguageFile($filter['dataContainer']);

        if (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]['label'])) {
            return $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]['label'][0];
        }

        return $label;
    }

    /**
     * Get field options.
     *
     * @return array The field options
     */
    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $data = $this->config->getData();
        $name = $this->getName($element);
        $options = [];

        $options['label'] = $this->getLabel($element, $builder) ?: $element->title;

        // sr-only style for non-bootstrap projects is shipped within the filter_form_* templates
        if ($this->getHideLabel($element)) {
            $options['label_attr'] = ['class' => 'sr-only'];
        }

        // always label for screen readers
        $options['attr']['aria-label'] = $this->translator->trans($this->getLabel($element, $builder) ?: $element->title);

        if ($element->addDefaultValue && !isset($data[$name])) {
            $options['data'] = static::getDefaultValue($element);
        }

        if ($element->addPlaceholder && $element->placeholder) {
            $options['attr']['placeholder'] = $this->translator->trans($element->placeholder, ['%label%' => $this->translator->trans($options['label']) ?: $element->title]);
        }

        if ($element->cssClass) {
            $options['attr']['class'] = $element->cssClass;
        }

        if ($element->inputGroup) {
            if ($element->inputGroupPrepend) {
                $prepend = $element->inputGroupPrepend;

                if ($this->translator->getCatalogue()->has($prepend)) {
                    $prepend = $this->translator->trans($prepend, ['%label%' => $this->translator->trans($options['label']) ?: $element->title]);
                }

                $options['input_group_prepend'] = $prepend;
            }

            if ($element->inputGroupAppend) {
                $append = $element->inputGroupAppend;

                if ($this->translator->getCatalogue()->has($append)) {
                    $append = $this->translator->trans($append, ['%label%' => $this->translator->trans($options['label']) ?: $element->title]);
                }

                $options['input_group_append'] = $append;
            }
        }

        $options['block_name'] = $this->getName($element);

        if ($triggerEvent) {
            $event = System::getContainer()->get('event_dispatcher')->dispatch(
                new AdjustFilterOptionsEvent($name, $options, $element, $builder, $this->config),
                AdjustFilterOptionsEvent::NAME
            );

            return $event->getOptions();
        }

        return $options;
    }

    public function getHideLabel(FilterConfigElementModel $element): bool
    {
        return (bool) $element->hideLabel;
    }

    public static function getInitialPalette(string $prepend, string $append): ?string
    {
        return null;
    }

    /**
     * Check if element is enabled in current context.
     *
     * Following options may be passed:
     * - table (string)
     * - filterConfigElementModel (FilterConfigElementModel)
     */
    public static function isEnabledForCurrentContext(array $context = []): bool
    {
        return true;
    }

    /**
     * Add logic to normalize data.
     *
     * @example CheckboxType Example implementation
     *
     * @param $value
     *
     * @return mixed
     */
    public static function normalizeValue($value)
    {
        return $value;
    }

    /**
     * Get min date for given element and type.
     *
     * @return int The min date as timestamp
     */
    protected function getMinDate(FilterConfigElementModel $element): int
    {
        $field = match ($element->type) {
            'time' => 'minTime',
            'date' => 'minDate',
            'date_time' => 'minDateTime',
            default => null,
        };

        $date = $element->{$field} ?? null;
        return $this->getTimeStamp($date) ?? 0;
    }

    /**
     * Get max date for given element and type.
     *
     * @return int The max date as timestamp
     */
    protected function getMaxDate(FilterConfigElementModel $element): int
    {
        $field = match ($element->type) {
            'time' => 'maxTime',
            'date' => 'maxDate',
            'date_time' => 'maxDateTime',
            default => null,
        };

        $date = $element->{$field} ?? null;
        return $this->getTimeStamp($date) ?? 9999999999999;
    }

    /**
     * @internal For the transferred polyfill below, see:
     *   {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Date/DateUtil.php#L45}
     */
    protected function getTimeStamp(mixed $date): ?int
    {
        if (empty($date)) {
            return null;
        }

        /** @var InsertTagParser $insertTagParser */
        $insertTagParser = System::getContainer()->get('contao.insert_tag.parser');
        $date = $insertTagParser->replace(strval($date));

        if (is_numeric($date)) {
            $dateTime = new DateTime(null, null);
            $dateTime->setTimestamp($date);
            return $dateTime->getTimestamp();
        }

        $timeStr = strtotime($date);
        if ($timeStr !== false) {
            try {
                $dateTime = new DateTime($timeStr, null);
                return $dateTime->getTimestamp();
            } catch (Exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * Return custom options if custom options config is set. Otherwise return null.
     */
    protected function getCustomOptions(FilterConfigElementModel $element): ?array
    {
        if (!$element->customOptions) {
            return null;
        }

        if (null === $element->options) {
            return [];
        }

        return StringUtil::deserialize($element->options, true);
    }
}
