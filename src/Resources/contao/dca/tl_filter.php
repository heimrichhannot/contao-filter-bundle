<?php

$GLOBALS['TL_DCA']['tl_filter'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ctable'            => ['tl_filter_element'],
        'switchToEdit'      => true,
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['tl_filter', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'        => 1,
            'fields'      => ['title'],
            'panelLayout' => 'filter;search,limit'
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter']['edit'],
                'href'  => 'table=tl_filter_element',
                'icon'  => 'edit.gif'
            ],
            'editheader' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter']['editheader'],
                'href'            => 'act=edit',
                'icon'            => 'header.gif',
                'button_callback' => ['tl_filter', 'editHeader']
            ],
            'copy'       => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter']['copy'],
                'href'            => 'act=copy',
                'icon'            => 'copy.gif',
                'button_callback' => ['tl_filter', 'copyArchive']
            ],
            'delete'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter']['copy'],
                'href'            => 'act=delete',
                'icon'            => 'delete.gif',
                'attributes'      => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
                'button_callback' => ['tl_filter', 'deleteArchive']
            ],
            'toggle'     => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter']['toggle'],
                'href'  => 'act=toggle',
                'icon'  => 'toggle.gif'
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['published'],
        'default'      => '{general_legend},title;{config_legend},name,dataContainer,method,action,renderEmpty;{template_legend},template;{publish_legend},published;{expert_legend},cssClass;'
    ],
    'subpalettes' => [
        'published' => 'start,stop'
    ],
    'fields'      => [
        'id'            => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'        => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'     => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'name'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['name'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 64],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'dataContainer' => [
            'inputType'        => 'select',
            'label'            => &$GLOBALS['TL_LANG']['tl_filter']['dataContainer'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['method'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['action'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'renderEmpty'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['renderEmpty'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'template'      => [
            'inputType'        => 'select',
            'label'            => &$GLOBALS['TL_LANG']['tl_filter']['template'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'start'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'stop'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'cssClass'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 64],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
    ]
];

class tl_filter extends \Backend
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

        $GLOBALS['TL_DCA']['tl_filter']['list']['sorting']['root'] = $root;

        // Check permissions to add archives
        if (!$user->hasAccess('create', 'filterp')) {
            $GLOBALS['TL_DCA']['tl_filter']['config']['closed'] = true;
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

                    if (is_array($arrNew['tl_filter']) && in_array(\Input::get('id'), $arrNew['tl_filter'])) {
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
                            $user = $database->prepare("SELECT filters, filterp FROM tl_user WHERE id=?")
                                ->limit(1)
                                ->execute($user->id);

                            $arrModulep = \StringUtil::deserialize($user->filterp);

                            if (is_array($arrModulep) && in_array('create', $arrModulep)) {
                                $arrModules   = \StringUtil::deserialize($user->filters, true);
                                $arrModules[] = \Input::get('id');

                                $database->prepare("UPDATE tl_user SET filters=? WHERE id=?")
                                    ->execute(serialize($arrModules), $user->id);
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

    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return \BackendUser::getInstance()->canEditFieldsOf('tl_filter') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
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
