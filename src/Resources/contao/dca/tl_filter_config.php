<?php

$GLOBALS['TL_DCA']['tl_filter_config'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ctable'            => ['tl_filter_config_element'],
        'switchToEdit'      => true,
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['tl_filter_config', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id'                   => 'primary',
                'start,stop,published' => 'index',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'         => 2,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;sort,search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['edit'],
                'href'  => 'table=tl_filter_config_element',
                'icon'  => 'edit.gif',
            ],
            'editheader' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter_config']['editheader'],
                'href'            => 'act=edit',
                'icon'            => 'header.gif',
                'button_callback' => ['tl_filter_config', 'editHeader'],
            ],
            'copy'       => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter_config']['copy'],
                'href'            => 'act=copy',
                'icon'            => 'copy.gif',
                'button_callback' => ['tl_filter_config', 'copyArchive'],
            ],
            'delete'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter_config']['copy'],
                'href'            => 'act=delete',
                'icon'            => 'delete.gif',
                'attributes'      => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
                'button_callback' => ['tl_filter_config', 'deleteArchive'],
            ],
            'toggle'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter_config']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_filter_config', 'toggleIcon'],
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['published', 'type'],
        'default'      => '{general_legend},title,type;',
        'filter'       => '{general_legend},title,type;{config_legend},authorType,author,name,dataContainer,method,action,renderEmpty,mergeData;{template_legend},template;{expert_legend},cssClass;{publish_legend},published;',
        'sort'         => '{general_legend},title,type;{config_legend},parentFilter;{template_legend},template;{expert_legend},cssClass;{publish_legend},published;',
    ],
    'subpalettes' => [
        'published' => 'start,stop',
    ],
    'fields'      => [
        'id'            => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'        => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'     => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'name'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['name'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 64, 'rgxp' => 'fieldname'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dataContainer' => [
            'inputType'        => 'select',
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config']['dataContainer'],
            'options_callback' => ['huh.utils.choice.data_container', 'getChoices'],
            'eval'             => [
                'chosen'             => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'mandatory'          => true,
            ],
            'exclude'          => true,
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        'method'        => [
            'inputType' => 'select',
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['method'],
            'options'   => ['GET', 'POST'],
            'default'   => 'GET',
            'eval'      => [
                'mandatory' => true,
                'tl_class'  => 'w50',
            ],
            'exclude'   => true,
            'sql'       => "varchar(4) NOT NULL default ''",
        ],
        'action'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['action'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'renderEmpty'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['renderEmpty'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'template'      => [
            'inputType'        => 'select',
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config']['template'],
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.filter.choice.template')->getCachedChoices($dc);
            },
            'eval'             => [
                'mandatory' => true,
                'tl_class'  => 'w50',
            ],
            'exclude'          => true,
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'published'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'cssClass'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 64],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'mergeData'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['mergeData'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'type'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config']['type'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\FilterBundle\Config\FilterConfig::FILTER_TYPES,
            'eval'      => [
                'tl_class'       => 'w50',
                'submitOnChange' => true,
                'mandatory'      => true,
            ],
            'sql'       => "varchar(64) NOT NULL default 'filter'",
        ],
        'parentFilter'  => [
            'label'      => &$GLOBALS['TL_LANG']['tl_filter_config']['parentFilter'],
            'exclude'    => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_filter_config.title',
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
            'eval'       => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'chosen' => true, 'mandatory' => true],
            'sql'        => "int(10) NOT NULL default '0'",
        ],
    ],
];

class tl_filter_config extends \Backend
{
    public function checkPermission()
    {
        $user     = \BackendUser::getInstance();
        $database = \Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array($user->filters) || empty($user->filters)) {
            $root = [0];
        } else {
            $root = $user->filters;
        }

        $GLOBALS['TL_DCA']['tl_filter_config']['list']['sorting']['root'] = $root;

        // Check permissions to add archives
        if (!$user->hasAccess('create', 'filterp')) {
            $GLOBALS['TL_DCA']['tl_filter_config']['config']['closed'] = true;
        }

        /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
        $objSession = \System::getContainer()->get('session');

        // Check current action
        switch (\Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root)) {
                    /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $sessionBag */
                    $sessionBag = $objSession->getBag('contao_backend');

                    $arrNew = $sessionBag->get('new_records');

                    if (is_array($arrNew['tl_filter_config']) && in_array(\Input::get('id'), $arrNew['tl_filter_config'])) {
                        // Add the permissions on group level
                        if ($user->inherit != 'custom') {
                            $objGroup = $database->execute("SELECT id, filters, filterp FROM tl_user_group WHERE id IN(" . implode(',', array_map('intval', $user->groups)) . ")");

                            while ($objGroup->next()) {
                                $arrModulep = \StringUtil::deserialize($objGroup->filterp);

                                if (is_array($arrModulep) && in_array('create', $arrModulep)) {
                                    $arrModules   = \StringUtil::deserialize($objGroup->filters, true);
                                    $arrModules[] = \Input::get('id');

                                    $database->prepare("UPDATE tl_user_group SET filters=? WHERE id=?")->execute(serialize($arrModules), $objGroup->id);
                                }
                            }
                        }

                        // Add the permissions on user level
                        if ($user->inherit != 'group') {
                            $user = $database->prepare("SELECT filters, filterp FROM tl_user WHERE id=?")->limit(1)->execute($user->id);

                            $arrModulep = \StringUtil::deserialize($user->filterp);

                            if (is_array($arrModulep) && in_array('create', $arrModulep)) {
                                $arrModules   = \StringUtil::deserialize($user->filters, true);
                                $arrModules[] = \Input::get('id');

                                $database->prepare("UPDATE tl_user SET filters=? WHERE id=?")->execute(serialize($arrModules), $user->id);
                            }
                        }

                        // Add the new element to the user object
                        $root[]        = \Input::get('id');
                        $user->filters = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$user->hasAccess('delete', 'filterp'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' filter ID ' . \Input::get('id') . '.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $objSession->all();
                if (\Input::get('act') == 'deleteAll' && !$user->hasAccess('delete', 'filterp')) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;

            default:
                if (strlen(\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' filters.');
                }
                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();

        if (strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') === '1'), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_filter_config::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . \Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . \Contao\Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user     = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        // Set the ID and action
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_filter_config']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_config']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_filter_config::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish filter item ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare("SELECT * FROM tl_filter_config WHERE id=?")->limit(1)->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_filter_config_element', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_filter_config']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_config']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE tl_filter_config SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_filter_config']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_config']['config']['onsubmit_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }

    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return \BackendUser::getInstance()->canEditFieldsOf('tl_filter_config') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    public function copyArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return \BackendUser::getInstance()->hasAccess('create', 'filterp') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return \BackendUser::getInstance()->hasAccess('delete', 'filterp') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }
}
