<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class ProximitySearchType extends AbstractType
{
    const TYPE = 'proximity_search';

    const FIELD_RADIUS = 'proxRadius';
    const FIELD_USE_LOCATION = 'proxUseLocation';
    const FIELD_LOCATION = 'proxLocation';

    const COORDINATES_MODE_COMPOUND = 'compound';
    const COORDINATES_MODE_SEPARATED = 'separated';
    const COORDINATES_MODES = [
        self::COORDINATES_MODE_COMPOUND,
        self::COORDINATES_MODE_SEPARATED,
    ];

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $data = $this->config->getData();
        $filter = $this->config->getFilter();
        $name = $this->getName($element);

        $t = $filter['dataContainer'];

        Controller::loadDataContainer($filter['dataContainer']);

        $postalElementName = ($formElement = $this->config->getElementByValue($element->postalElement)) ? $formElement->getFormName($this->config) : null;
        $cityElementName = ($formElement = $this->config->getElementByValue($element->cityElement)) ? $formElement->getFormName($this->config) : null;
        $stateElementName = ($formElement = $this->config->getElementByValue($element->stateElement)) ? $formElement->getFormName($this->config) : null;
        $countryElementName = ($formElement = $this->config->getElementByValue($element->countryElement)) ? $formElement->getFormName($this->config) : null;
        $currentLocationHiddenElementName = ($formElement = $this->config->getElementByValue($element->currentLocationHiddenElement)) ? $formElement->getFormName($this->config) : null;
        $radiusElementName = ($formElement = $this->config->getElementByValue($element->radiusElement)) ? $formElement->getFormName($this->config) : null;

        if ($radiusElementName && isset($data[$name][$radiusElementName]) && $data[$name][$radiusElementName]) {
            $radiusValue = str_replace('km', '', $data[$name][$radiusElementName]);
        } else {
            $radiusValue = null;
        }

        if ($currentLocationHiddenElementName && isset($data[$name][$currentLocationHiddenElementName]) && $data[$name][$currentLocationHiddenElementName]) {
            $locationValue = $data[$name][$currentLocationHiddenElementName];
        } else {
            $locationValue = null;
        }

        if ($postalElementName && isset($data[$name][$postalElementName]) && $data[$name][$postalElementName]) {
            $postalValue = $data[$name][$postalElementName];
        } else {
            $postalValue = null;
        }

        if ($cityElementName && isset($data[$name][$cityElementName]) && $data[$name][$cityElementName]) {
            $cityValue = $data[$name][$cityElementName];
        } else {
            $cityValue = null;
        }

        if ($stateElementName && isset($data[$name][$stateElementName]) && $data[$name][$stateElementName]) {
            $stateValue = $data[$name][$stateElementName];
        } else {
            $stateValue = null;
        }

        if ($countryElementName && isset($data[$name][$countryElementName]) && $data[$name][$countryElementName]) {
            $countryValue = $data[$name][$countryElementName];
        } else {
            $countryValue = null;
        }

        if (!$locationValue && !$postalValue && !$cityValue) {
            return;
        }

        // get queried coordinates
        $queryLat = '';
        $queryLong = '';

        if ($locationValue) {
            [$queryLat, $queryLong] = explode(',', $locationValue);
        } elseif ($postalValue || $cityValue || $stateValue) {
            $query = [];

            if ($postalValue) {
                $query['postal'] = $postalValue;
            } else {
                if ($cityValue) {
                    $query['city'] = $cityValue;
                }

                if ($stateValue) {
                    $query['state'] = $stateValue;
                }
            }

            // add country
            $countries = System::getCountries();
            $query['country'] = $countries[$countryValue];

            $coordinates = System::getContainer()->get('huh.utils.location')->computeCoordinatesByArray($query);

            if (false !== $coordinates) {
                $queryLat = $coordinates['lat'];
                $queryLong = $coordinates['lng'];
            }
        }

        // compose WHERE clause
        $latField = $longField = '';
        $searchCoordinatesField = $element->coordinatesField;

        switch ($element->coordinatesMode) {
            case static::COORDINATES_MODE_SEPARATED:
                $latField = $element->latField;
                $longField = $element->longField;

                break;

            case static::COORDINATES_MODE_COMPOUND:
                $latField = "LEFT($t.$searchCoordinatesField,INSTR($t.$searchCoordinatesField,',')-1)";
                $longField = "SUBSTRING_INDEX($t.$searchCoordinatesField,',',-1)";

                break;
        }

        if (!$queryLat && !$queryLong) {
            return;
        }
        $builder->andWhere($builder->expr()->lt("(
                6371 * acos(
                    cos(
                        radians($queryLat)
                    ) * cos(
                        radians($latField)
                    ) * cos(
                        radians($longField) - radians($queryLong)
                    ) + sin(
                        radians($queryLat)
                    ) * sin(
                        radians($latField)
                    )
                ))", ':radius'));
        $builder->setParameter(':radius', $radiusValue);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $name = $this->getName($element);
        $data = $this->config->getData();

        $elements = [
            'postal',
            'city',
            'state',
            'country',
            'useCurrentLocation',
            'currentLocationHidden',
            'radius',
        ];

        // proximity_search is a wrapper form, group should already exist
        if (null === $name || !$builder->has($name)) {
            return;
        }

        $group = $builder->get($this->getName($element));

        foreach ($elements as $elementName) {
            if (!$element->{$elementName.'Element'}) {
                continue;
            }

            $elementObject = $this->config->getElementByValue($element->{$elementName.'Element'});
            $elementFormChild = $builder->get($elementObject->getFormName($this->config));
            $builder->remove($elementObject->getFormName($this->config));
            $group->add($elementObject->getFormName($this->config), \get_class($elementFormChild->getType()->getInnerType()), $elementFormChild->getOptions());
            $group->get($elementObject->getFormName($this->config))->setData($data[$name][$elementObject->getFormName($this->config)] ?? $elementFormChild->getData());
            $builder->remove($elementFormChild->getName());
        }

        $builder->add($group);
    }

    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $options = parent::getOptions($element, $builder, $triggerEvent);
        $config = [];

        $prefix = $this->config->getFilter()['name'].'['.$this->config->getElementByValue($element->id)->getFormName($this->config).']';

        $elements = [
            'postal',
            'city',
            'state',
            'country',
            'useCurrentLocation',
            'currentLocationHidden',
            'radius',
        ];

        foreach ($elements as $elementName) {
            if ($element->{$elementName.'Element'}) {
                $config[$elementName.'Field'] = $prefix.'['.$this->config->getElementByValue($element->{$elementName.'Element'})->getFormName($this->config).']';
            }
        }

        $options['attr']['data-proximity-search-config'] = json_encode($config);

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return $element->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_LIKE;
    }
}
