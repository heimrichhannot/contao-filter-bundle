<?php

namespace HeimrichHannot\FilterBundle\Backend;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class FilterConfig extends Backend
{
    public function checkPermission(): void
    {
        $user = BackendUser::getInstance();
        $database = Database::getInstance();

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

        $objSession = System::getContainer()->get('request_stack')->getSession();

        // Check current action
        switch (Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(Input::get('id'), $root)) {
                    /** @var AttributeBagInterface $sessionBag */
                    $sessionBag = $objSession->getBag('contao_backend');

                    $arrNew = $sessionBag->get('new_records');

                    if (is_array($arrNew['tl_filter_config']) && in_array(Input::get('id'), $arrNew['tl_filter_config'])) {
                        // Add the permissions on group level
                        if ('custom' != $user->inherit) {
                            $objGroup = $database->execute('SELECT id, filters, filterp FROM tl_user_group WHERE id IN('.implode(',', array_map('intval', $user->groups)).')');

                            while ($objGroup->next()) {
                                $arrModulep = StringUtil::deserialize($objGroup->filterp);

                                if (is_array($arrModulep) && in_array('create', $arrModulep)) {
                                    $arrModules = StringUtil::deserialize($objGroup->filters, true);
                                    $arrModules[] = Input::get('id');

                                    $database->prepare('UPDATE tl_user_group SET filters=? WHERE id=?')->execute(serialize($arrModules), $objGroup->id);
                                }
                            }
                        }

                        // Add the permissions on user level
                        if ('group' != $user->inherit) {
                            $user = $database->prepare('SELECT filters, filterp FROM tl_user WHERE id=?')->limit(1)->execute($user->id);

                            $arrModulep = StringUtil::deserialize($user->filterp);

                            if (is_array($arrModulep) && in_array('create', $arrModulep)) {
                                $arrModules = StringUtil::deserialize($user->filters, true);
                                $arrModules[] = Input::get('id');

                                $database->prepare('UPDATE tl_user SET filters=? WHERE id=?')->execute(serialize($arrModules), $user->id);
                            }
                        }

                        // Add the new element to the user object
                        $root[] = Input::get('id');
                        $user->filters = $root;
                    }
                }
            // no break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root) || ('delete' == Input::get('act') && !$user->hasAccess('delete', 'filterp'))) {
                    throw new AccessDeniedException('Not enough permissions to '.Input::get('act').' filter ID '.Input::get('id').'.');
                }

                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $objSession->all();

                if ('deleteAll' == Input::get('act') && !$user->hasAccess('delete', 'filterp')) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);

                break;

            default:
                if (strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Not enough permissions to '.Input::get('act').' filters.');
                }

                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = BackendUser::getInstance();

        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), ('1' === Input::get('state')), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_filter_config::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'. StringUtil::specialchars($title).'"'.$attributes.'>'. Image::getHtml($icon, $label, 'data-state="'.($row['published'] ? 1 : 0).'"').'</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        $user = BackendUser::getInstance();
        $database = Database::getInstance();

        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

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
            throw new AccessDeniedException('Not enough permissions to publish/unpublish filter item ID '.$intId.'.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare('SELECT * FROM tl_filter_config WHERE id=?')->limit(1)->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions('tl_filter_config_element', $intId);
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
        $database->prepare("UPDATE tl_filter_config SET tstamp=$time, published='".($blnVisible ? '1' : '')."' WHERE id=?")->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp = $time;
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
        return BackendUser::getInstance()->canEditFieldsOf('tl_filter_config') ? '<a href="'.$this->addToUrl($href.'&amp;id='. $row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'. Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    public function copyArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return BackendUser::getInstance()->hasAccess('create', 'filterp') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return BackendUser::getInstance()->hasAccess('delete', 'filterp') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }
}