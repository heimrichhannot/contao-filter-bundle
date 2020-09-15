<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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

        $postalElementName = $this->config->getElementByValue($element->postalElement)->getFormName($this->config);
        $cityElementName = $this->config->getElementByValue($element->cityElement)->getFormName($this->config);
        $stateElementName = $this->config->getElementByValue($element->stateElement)->getFormName($this->config);
        $countryElementName = $this->config->getElementByValue($element->countryElement)->getFormName($this->config);
        $currentLocationHiddenElementName = $this->config->getElementByValue($element->currentLocationHiddenElement)->getFormName($this->config);
        $radiusElementName = $this->config->getElementByValue($element->radiusElement)->getFormName($this->config);

        if (isset($data[$name][$radiusElementName]) && $data[$name][$radiusElementName]) {
            $radiusValue = str_replace('km', '', $data[$name][$radiusElementName]);
        } else {
            $radiusValue = null;
        }

        if (isset($data[$name][$currentLocationHiddenElementName]) && $data[$name][$currentLocationHiddenElementName]) {
            $locationValue = $data[$name][$currentLocationHiddenElementName];
        } else {
            $locationValue = null;
        }

        if (isset($data[$name][$postalElementName]) && $data[$name][$postalElementName]) {
            $postalValue = $data[$name][$postalElementName];
        } else {
            $postalValue = null;
        }

        if (isset($data[$name][$cityElementName]) && $data[$name][$cityElementName]) {
            $cityValue = $data[$name][$cityElementName];
        } else {
            $cityValue = null;
        }

        if (isset($data[$name][$stateElementName]) && $data[$name][$stateElementName]) {
            $stateValue = $data[$name][$stateElementName];
        } else {
            $stateValue = null;
        }

        if (isset($data[$name][$countryElementName]) && $data[$name][$countryElementName]) {
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

        // proximity_search is a wrapper form, group should already exist
        if (null === $name || !$builder->has($name)) {
            return;
        }

        $postalElement = $this->config->getElementByValue($element->postalElement);
        $cityElement = $this->config->getElementByValue($element->cityElement);
        $stateElement = $this->config->getElementByValue($element->stateElement);
        $countryElement = $this->config->getElementByValue($element->countryElement);
        $useCurrentLocationElement = $this->config->getElementByValue($element->useCurrentLocationElement);
        $currentLocationHiddenElement = $this->config->getElementByValue($element->currentLocationHiddenElement);
        $radiusElement = $this->config->getElementByValue($element->radiusElement);

        $postal = $builder->get($postalElement->getFormName($this->config));
        $city = $builder->get($cityElement->getFormName($this->config));
        $state = $builder->get($stateElement->getFormName($this->config));
        $country = $builder->get($countryElement->getFormName($this->config));
        $useCurrentLocation = $builder->get($useCurrentLocationElement->getFormName($this->config));
        $currentLocationHidden = $builder->get($currentLocationHiddenElement->getFormName($this->config));
        $radius = $builder->get($radiusElement->getFormName($this->config));

        $builder->remove($postalElement->getFormName($this->config));
        $builder->remove($cityElement->getFormName($this->config));
        $builder->remove($stateElement->getFormName($this->config));
        $builder->remove($countryElement->getFormName($this->config));
        $builder->remove($useCurrentLocationElement->getFormName($this->config));
        $builder->remove($currentLocationHiddenElement->getFormName($this->config));
        $builder->remove($radiusElement->getFormName($this->config));

        $group = $builder->get($this->getName($element));

        $group->add($postalElement->getFormName($this->config), \get_class($postal->getType()->getInnerType()), $postal->getOptions());
        $group->add($cityElement->getFormName($this->config), \get_class($city->getType()->getInnerType()), $city->getOptions());
        $group->add($stateElement->getFormName($this->config), \get_class($state->getType()->getInnerType()), $state->getOptions());
        $group->add($countryElement->getFormName($this->config), \get_class($country->getType()->getInnerType()), $country->getOptions());
        $group->add($useCurrentLocationElement->getFormName($this->config), \get_class($useCurrentLocation->getType()->getInnerType()), $useCurrentLocation->getOptions());
        $group->add($currentLocationHiddenElement->getFormName($this->config), \get_class($currentLocationHidden->getType()->getInnerType()), $currentLocationHidden->getOptions());
        $group->add($radiusElement->getFormName($this->config), \get_class($radius->getType()->getInnerType()), $radius->getOptions());

        $group->get($postalElement->getFormName($this->config))->setData($data[$name][$postalElement->getFormName($this->config)] ?? $postal->getData());
        $group->get($cityElement->getFormName($this->config))->setData($data[$name][$cityElement->getFormName($this->config)] ?? $city->getData());
        $group->get($stateElement->getFormName($this->config))->setData($data[$name][$stateElement->getFormName($this->config)] ?? $state->getData());
        $group->get($countryElement->getFormName($this->config))->setData($data[$name][$countryElement->getFormName($this->config)] ?? $country->getData());
        $group->get($useCurrentLocationElement->getFormName($this->config))->setData($data[$name][$useCurrentLocationElement->getFormName($this->config)] ?? $useCurrentLocation->getData());
        $group->get($currentLocationHiddenElement->getFormName($this->config))->setData($data[$name][$currentLocationHiddenElement->getFormName($this->config)] ?? $currentLocationHidden->getData());
        $group->get($radiusElement->getFormName($this->config))->setData($data[$name][$radiusElement->getFormName($this->config)] ?? $radius->getData());

        $builder->remove($postal->getName());
        $builder->remove($city->getName());
        $builder->remove($state->getName());
        $builder->remove($country->getName());
        $builder->remove($useCurrentLocation->getName());
        $builder->remove($currentLocationHidden->getName());
        $builder->remove($radius->getName());

        $builder->add($group);
    }

    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $options = parent::getOptions($element, $builder, $triggerEvent);
        $config = [];

        $prefix = $this->config->getFilter()['name'].'['.$this->config->getElementByValue($element->id)->getFormName($this->config).']';

        if ($element->postalElement) {
            $config['postalField'] = $prefix.'['.$this->config->getElementByValue($element->postalElement)->getFormName($this->config).']';
        }

        if ($element->cityElement) {
            $config['cityField'] = $prefix.'['.$this->config->getElementByValue($element->cityElement)->getFormName($this->config).']';
        }

        if ($element->stateElement) {
            $config['stateField'] = $prefix.'['.$this->config->getElementByValue($element->stateElement)->getFormName($this->config).']';
        }

        if ($element->countryElement) {
            $config['countryField'] = $prefix.'['.$this->config->getElementByValue($element->countryElement)->getFormName($this->config).']';
        }

        if ($element->useCurrentLocationElement) {
            $config['useCurrentLocationField'] = $prefix.'['.$this->config->getElementByValue($element->useCurrentLocationElement)->getFormName($this->config).']';
        }

        if ($element->currentLocationHiddenElement) {
            $config['currentLocationHiddenField'] = $prefix.'['.$this->config->getElementByValue($element->currentLocationHiddenElement)->getFormName($this->config).']';
        }

        if ($element->radiusElement) {
            $config['radiusField'] = $prefix.'['.$this->config->getElementByValue($element->radiusElement)->getFormName($this->config).']';
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
