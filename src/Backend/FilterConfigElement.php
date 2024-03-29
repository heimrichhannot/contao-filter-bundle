<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Backend;

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\DataContainer\FilterConfigElementContainer;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\FilterCollection;
use HeimrichHannot\FilterBundle\Filter\Type\ChoiceType;
use HeimrichHannot\FilterBundle\Filter\Type\DateTimeType;
use HeimrichHannot\FilterBundle\Filter\Type\DateType;
use HeimrichHannot\FilterBundle\Filter\Type\ExternalEntityType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

class FilterConfigElement
{
    const INITIAL_PALETTE = FilterConfigElementContainer::PALETTE_PREFIX.'{config_legend},field,operator,alternativeValueSource,initialValueType,addMultilingualInitialValues;'.FilterConfigElementContainer::PALETTE_SUFFIX;

    protected ContaoFramework $contaoFramework;
    private FilterCollection  $filterCollection;
    private Utils             $utils;

    public function __construct(ContaoFramework $framework, FilterCollection $filterCollection, Utils $utils)
    {
        $this->contaoFramework = $framework;
        $this->filterCollection = $filterCollection;
        $this->utils = $utils;
    }

    public function modifyPalette(DataContainer $dc)
    {
        /** @var FilterConfigElementModel|null $filterConfigElement */
        if (null === ($filterConfigElement = $this->utils->model()->findModelInstanceByPk(
                'tl_filter_config_element',
                $dc->id
            ))) {
            return null;
        }

        /** @var class-string<AbstractType> $type */
        $type = $this->filterCollection->getClassByType($filterConfigElement->type);

        if (!$type) {
            return null;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];

        if ($filterConfigElement->isInitial && isset($dca['palettes'][$filterConfigElement->type]) && false !== strpos($dca['palettes'][$filterConfigElement->type], 'isInitial')) {
            if (null !== ($palette = $type::getInitialPalette(
                    FilterConfigElementContainer::PALETTE_PREFIX,
                    FilterConfigElementContainer::PALETTE_SUFFIX
                ))) {
                $dca['palettes'][$filterConfigElement->type] = $palette;
            } else {
                $dca['palettes'][$filterConfigElement->type] = static::INITIAL_PALETTE;
            }

            if ($filterConfigElement->alternativeValueSource) {
                $dca['palettes'][$filterConfigElement->type] = str_replace('initialValueType', '', $dca['palettes'][$filterConfigElement->type]);
            }

            if (\in_array($filterConfigElement->type, [DateTimeType::TYPE, DateType::TYPE, 'time'])) {
                PaletteManipulator::create()
                    ->removeField('operator')
                    ->applyToPalette($filterConfigElement->type, FilterConfigElementModel::getTable());
            }
        }

        if (ExternalEntityType::TYPE === $filterConfigElement->type && $filterConfigElement->sourceTable) {
            $dca['fields']['sourceEntityResolve']['eval']['multiColumnEditor']['table'] = $filterConfigElement->sourceTable;
        }
    }

    public function prepareChoiceTypes(DataContainer $dc)
    {
        /** @var FilterConfigElementModel $filterConfigElement */
        if (null === ($filterConfigElement = $this->utils->model()->findModelInstanceByPk(
                'tl_filter_config_element',
                $dc->id
            ))) {
            return null;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];

        $choiceType = $this->filterCollection->getClassByType($filterConfigElement->type);

        if (!($choiceType instanceof ChoiceType)) {
            return null;
        }

        $choices = $choiceType->getChoices($filterConfigElement);

        if (!\is_array($choices) || empty($choices)) {
            return null;
        }

        $options = $choices;

        // prepare scalar fields
        $dca['fields']['defaultValue']['inputType'] = 'select';
        $dca['fields']['defaultValue']['options'] = $options;
        $dca['fields']['defaultValue']['eval']['chosen'] = true;

        $dca['fields']['initialValue']['inputType'] = 'select';
        $dca['fields']['initialValue']['options'] = $options;
        $dca['fields']['initialValue']['eval']['chosen'] = true;

        $dca['fields']['multilingualInitialValues']['eval']['multiColumnEditor']['fields']['initialValue']['inputType'] = 'select';
        $dca['fields']['multilingualInitialValues']['eval']['multiColumnEditor']['fields']['initialValue']['options'] = $options;
        $dca['fields']['multilingualInitialValues']['eval']['multiColumnEditor']['fields']['initialValue']['eval']['chosen'] = true;

        // prepare array fields
        $dca['fields']['defaultValueArray']['eval']['multiColumnEditor']['fields']['value']['inputType'] = 'select';
        $dca['fields']['defaultValueArray']['eval']['multiColumnEditor']['fields']['value']['options'] = $options;
        $dca['fields']['defaultValueArray']['eval']['multiColumnEditor']['fields']['value']['eval']['chosen'] = true;

        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['inputType'] = 'select';
        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['options'] = $options;
        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['eval']['chosen'] = true;

        $dca['fields']['multilingualInitialValues']['eval']['multiColumnEditor']['fields']['initialValueArray']['inputType'] = 'select';
        $dca['fields']['multilingualInitialValues']['eval']['multiColumnEditor']['fields']['initialValueArray']['options'] = $options;
        $dca['fields']['multilingualInitialValues']['eval']['multiColumnEditor']['fields']['initialValueArray']['eval']['chosen'] = true;
    }

    public function checkPermission()
    {
        $user = \BackendUser::getInstance();
        $database = \Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!\is_array($user->filters) || empty($user->filters)) {
            $root = [0];
        } else {
            $root = $user->filters;
        }

        $id = \strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!\strlen(\Input::get('pid')) || !\in_array(\Input::get('pid'), $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create filter_element items in filter_element archive ID '.\Input::get('pid').'.');
                }

                break;

            case 'cut':
            case 'copy':
                if (!\in_array(\Input::get('pid'), $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Input::get('act').' filter_element item ID '.$id.' to filter_element archive ID '.\Input::get('pid').'.');
                }
            // no break STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare('SELECT pid FROM tl_filter_config_element WHERE id=?')->limit(1)->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid filter_element item ID '.$id.'.');
                }

                if (!\in_array($objArchive->pid, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Input::get('act').' filter_element item ID '.$id.' of filter_element archive ID '.$objArchive->pid.'.');
                }

                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!\in_array($id, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access filter_element archive ID '.$id.'.');
                }

                $objArchive = $database->prepare('SELECT id FROM tl_filter_config_element WHERE pid=?')->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid filter_element archive ID '.$id.'.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = \System::getContainer()->get('session');

                $session = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);

                break;

            default:
                if (\strlen(\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "'.\Input::get('act').'".');
                } elseif (!\in_array($id, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access filter_element archive ID '.$id.'.');
                }

                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \BackendUser::getInstance();

        if (\strlen(\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), ('1' === \Input::get('state')), (@func_get_arg(12) ?: null));
            Controller::redirect(Controller::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_filter_config_element::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="'.Controller::addToUrl($href).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml(
                $icon,
                $label,
                'data-state="'.($row['published'] ? 1 : 0).'"'
            ).'</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user = \BackendUser::getInstance();
        $database = \Database::getInstance();

        // Set the ID and action
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    $callbackObj = System::importStatic($callback[0]);
                    $callbackObj->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_filter_config_element::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish filter_element item ID '.$intId.'.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare('SELECT * FROM tl_filter_config_element WHERE id=?')->limit(1)->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_filter_config_element', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (($GLOBALS['TL_DCA']['tl_filter_config_element']['fields']['published']['save_callback'] ?? null) &&
            \is_array($GLOBALS['TL_DCA']['tl_filter_config_element']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_config_element']['fields']['published']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $callbackObj = System::importStatic($callback[0]);
                    $blnVisible = $callbackObj->{$callback[1]}($blnVisible, $dc);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE tl_filter_config_element SET tstamp=$time, published='".($blnVisible ? '1' : '')."' WHERE id=?")->execute(
            $intId
        );

        if ($dc) {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onsubmit_callback'] ?? null) &&
            \is_array($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onsubmit_callback'] as $callback) {
                if (\is_array($callback)) {
                    $callbackObj = System::importStatic($callback[0]);
                    $callbackObj->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }

    /**
     * Add a link to the option items import wizard.
     *
     * @return string
     */
    public function optionImportWizard()
    {
        return ' <a href="'.Controller::addToUrl('key=option').'" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['ow_import'][1]).'" onclick="Backend.getScrollOffset()">'.Image::getHtml('tablewizard.gif', $GLOBALS['TL_LANG']['MSC']['ow_import'][0]).'</a>';
    }

    public function getOptions(DataContainer $dc)
    {
        if ($dc->activeRecord->customOptions) {
            $options = [];

            foreach (StringUtil::deserialize($dc->activeRecord->options) as $option) {
                $options[$option['value']] = $option['label'];
            }

            return $options;
        }
    }

    public function getSourceFields(DataContainer $dc): array
    {
        if (!$dc->activeRecord->sourceTable) {
            return [];
        }

        return System::getContainer()->get('huh.utils.dca')->getFields($dc->activeRecord->sourceTable);
    }
}
