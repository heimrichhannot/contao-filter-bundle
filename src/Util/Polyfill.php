<?php

namespace HeimrichHannot\FilterBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\System;
use Contao\ThemeModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

class Polyfill
{
    /*private Utils $utils;
    private ContaoFramework $framework;

    public function __construct(Utils $utils, ContaoFramework $framework) {
        $this->utils = $utils;
        $this->framework = $framework;
    }*/

    public static function retrieveGlobalPageFromCurrentPageId(int $id): ?PageModel
    {
        $page = PageModel::findByPk($id);
        if (null === $page) {
            return null;
        }

        if ($page->type === 'root') {
            return $page;
        }

        $parentPages = PageModel::findParentsById($id);
        if (null === $parentPages) {
            return $page;
        }

        // get inherited values from parent pages
        foreach ($parentPages as $parentPage)
        {
            $diffValues = array_diff_assoc($parentPage->row(), $page->row());

            if (empty($diffValues)) {
                continue;
            }

            foreach ($diffValues as $key => $value) {
                if ($page->{$key}) {
                    continue;
                }
                $page->{$key} = $value;
            }
        }

        // retrieve parameters which don't come from parent pages
        $page->dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
        $page->timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];
        $page->datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
        static::setParametersFromLayout($page);

        return $page;
    }

    protected static function setParametersFromLayout(PageModel &$page): void
    {
        if (!$page->layout) {
            return;
        }

        // get values from layout
        $layout = LayoutModel::findByPk($page->layout);
        if (null === $layout) {
            return;
        }

        $page->template = $layout->template;
        $page->outputFormat = $layout->doctype;

        // get values from theme
        $theme = ThemeModel::findByPk($layout->pid);
        if (null === $theme) {
            return;
        }

        $page->templateGroup = $theme->templates;
    }
}