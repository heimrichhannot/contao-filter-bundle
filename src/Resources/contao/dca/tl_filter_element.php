<?php

$GLOBALS['TL_DCA']['tl_filter_element'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_filter',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['tl_filter_element', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['HeimrichHannot\Haste\Dca\General', 'setDateAdded'],
            ['tl_filter_element', 'clearFilterRegistry'],
        ],
        'sql'               => [
            'keys' => [
                'id'                               => 'primary',
                'pid,start,stop,published,sorting' => 'index'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['id'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['sorting'],
            'headerFields'          => ['title', 'published', 'start', 'stop'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => ['tl_filter_element', 'listElements']
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
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_element']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_element']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_filter_element']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter_element']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_filter_element', 'toggleIcon']
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_element']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['type', 'customName', 'addPlaceholder', 'customLabel', 'published'],
        'default'      => '{general_legend},title,type;{publish_legend},published;',
        'text'         => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel;{expert_legend},cssClass;{publish_legend},published;',
        'choice'       => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel,expanded,multiple;{expert_legend},cssClass;{publish_legend},published;',
        'hidden'       => '{general_legend},title,type;{config_legend},field,customName;{expert_legend},cssClass;{publish_legend},published;',
        'button'       => '{general_legend},title,type;{config_legend},name,label;{expert_legend},cssClass;{publish_legend},published;',
        'reset'        => '{general_legend},title,type;{config_legend},customName,customLabel;{expert_legend},cssClass;{publish_legend},published;',
        'submit'       => '{general_legend},title,type;{config_legend},customName,customLabel;{expert_legend},cssClass;{publish_legend},published;',
    ],
    'subpalettes' => [
        'addPlaceholder' => 'placeholder',
        'customName'     => 'name',
        'customLabel'    => 'label',
        'published'      => 'start,stop'
    ],
    'fields'      => [
        'id'             => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'            => [
            'foreignKey' => 'tl_filter.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager']
        ],
        'sorting'        => [
            'sorting' => true,
            'flag'    => 2,
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'         => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_element']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'      => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'type'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_element']['type'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                $choices = \Contao\System::getContainer()->get('huh.filter.choice.type')->getCachedChoices($dc);
                return array_keys($choices);
            },
            'eval'             => ['chosen' => true, 'tl_class' => 'w50', 'submitOnChange' => true],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'title'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'field'          => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_element']['field'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => ['huh.filter.choice.field', 'getChoices'],
            'eval'             => ['chosen' => true, 'includeBlankOption' => true, 'doNotCopy' => true],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'customName'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['customName'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'name'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['name'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 128, 'doNotCopy' => true],
            'sql'       => "varchar(128) NOT NULL default ''"
        ],
        'addPlaceholder' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['addPlaceholder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'placeholder'    => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_element']['placeholder'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                $choices = \Contao\System::getContainer()->get('huh.filter.choice.message')->getCachedChoices('huh.filter.placeholder');
                return $choices;
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'doNotCopy' => true],
            'sql'              => "varchar(128) NOT NULL default ''"
        ],
        'customLabel'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['customLabel'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'label'          => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_element']['label'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                $choices = \Contao\System::getContainer()->get('huh.filter.choice.message')->getCachedChoices('huh.filter.label');
                return $choices;
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'doNotCopy' => true],
            'sql'              => "varchar(128) NOT NULL default ''"
        ],
        'expanded'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['expanded'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'multiple'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['expanded'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'parents'        => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_element']['parents'],
            'default'          => 'text',
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.filter.choice.parent', 'getChoices'],
            'eval'             => ['tl_class' => 'wizard', 'multiple' => true],
            'sql'              => "blob NULL"
        ],
        'cssClass'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 64],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'published'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'start'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'stop'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_element']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ]
    ]
];


class tl_filter_element extends \Backend
{

    public function listElements($arrRow)
    {
        return '<div class="tl_content_left">' . ($arrRow['title'] ?: $arrRow['id']) . ' <span style="color:#b3b3b3; padding-left:3px">[' .
            $arrRow['type'] . ']</span></div>';
    }

    public function checkPermission()
    {
        $user     = \BackendUser::getInstance();
        $database = \Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!is_array($user->filters) || empty($user->filters)) {
            $root = [0];
        } else {
            $root = $user->filters;
        }

        $id = strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(\Input::get('pid')) || !in_array(\Input::get('pid'), $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create filter_element items in filter_element archive ID ' . \Input::get('pid') . '.');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Input::get('pid'), $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' filter_element item ID ' . $id . ' to filter_element archive ID ' . \Input::get('pid') . '.');
                }
            // NO BREAK STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare("SELECT pid FROM tl_filter_element WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid filter_element item ID ' . $id . '.');
                }

                if (!in_array($objArchive->pid, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' filter_element item ID ' . $id . ' of filter_element archive ID ' . $objArchive->pid . '.');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access filter_element archive ID ' . $id . '.');
                }

                $objArchive = $database->prepare("SELECT id FROM tl_filter_element WHERE pid=?")
                    ->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid filter_element archive ID ' . $id . '.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = \System::getContainer()->get('session');

                $session                   = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);
                break;

            default:
                if (strlen(\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "' . \Input::get('act') . '".');
                } elseif (!in_array($id, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access filter_element archive ID ' . $id . '.');
                }
                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \BackendUser::getInstance();

        if (strlen(\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_filter_element::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user     = \BackendUser::getInstance();
        $database = \Database::getInstance();

        // Set the ID and action
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_filter_element']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_element']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_filter_element::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish filter_element item ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare("SELECT * FROM tl_filter_element WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_filter_element', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_filter_element']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_element']['fields']['published']['save_callback'] as $callback) {
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
        $database->prepare("UPDATE tl_filter_element SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_filter_element']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_filter_element']['config']['onsubmit_callback'] as $callback) {
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

    public function clearFilterRegistry(\Contao\DataContainer $dc)
    {
        System::getContainer()->get('huh.filter.registry')->clearCache([$dc->activeRecord->pid]);
    }
}
