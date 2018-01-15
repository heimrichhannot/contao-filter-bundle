<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter;

use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use Nelmio\SecurityBundle\ContentSecurityPolicy\Violation\Filter\Filter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractType
{
    /**
     * @var Filter
     */
    protected $config;

    /**
     * @var $translator TranslatorInterface
     */
    protected $translator;

    public function __construct(FilterConfig $config)
    {
        $this->config     = $config;
        $this->translator = System::getContainer()->get('translator');
    }


    /**
     * Get the field label
     * @param array $element
     * @param FormBuilderInterface $builder
     * @return string
     */
    protected function getLabel(array $element, FormBuilderInterface $builder)
    {
        $label  = '';
        $filter = $this->config->getFilter();

        if (true === (bool)$element['customLabel'] && $element['label'] !== '') {
            return $element['label'];
        }

        \Controller::loadDataContainer($filter['dataContainer']);
        \Controller::loadLanguageFile($filter['dataContainer']);

        if (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']]['label'])) {
            return $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']]['label'][0];
        }

        return $label;
    }

    /**
     * Get field options
     * @param array $element
     * @param FormBuilderInterface $builder
     * @return array The field options
     */
    protected function getOptions(array $element, FormBuilderInterface $builder)
    {
        $options = [];

        $options['label'] = $this->getLabel($element, $builder) ?: $element['title'];

        if (true === (bool)$element['addPlaceholder'] && '' !== $element['placeholder']) {
            $options['attr']['placeholder'] = $this->translator->trans($element['placeholder'], ['%label%' => $this->translator->trans($options['label'])]);
        }

        if ('' !== $element['cssClass']) {
            $options['attr']['class'] = $element['cssClass'];
        }

        $options['block_name'] = $this->getName($element);

        return $options;
    }

    /**
     * Get the field name
     * @param array $element
     * @param string|null $default The default name
     * @return mixed
     */
    protected function getName(array $element, $default = null)
    {
        $name = $element['field'] ?: $default;

        if (true === (bool)$element['customName'] && $element['name'] !== '') {
            $name = $element['name'];
        }

        return $name;
    }
}