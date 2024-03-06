<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Form\FilterType;

class InsertTagsListener
{
    private array $supportedFilterTags = [
        'filter_reset_url',
    ];
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Replaces block insert tags.
     *
     * @param string $tag
     *
     * @return string|false
     */
    public function onReplaceInsertTags($tag)
    {
        $elements = explode('::', $tag);
        $key = strtolower($elements[0]);

        if (\in_array($key, $this->supportedFilterTags, true)) {
            if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById((int) $elements[1]))) {
                return '';
            }

            return $this->replaceFilterInsertTag($key, $filterConfig, \array_slice($elements, 2));
        }

        return false;
    }

    /**
     * Replaces an filter-related insert tag.
     *
     * @param string $insertTag The tag name
     * @param int    $id        The filter id
     * @param array  $elements  The insertTag parts
     *
     * @return string
     */
    private function replaceFilterInsertTag(string $insertTag, FilterConfig $filterConfig, array $elements = []): string
    {
        if ($insertTag !== 'filter_reset_url') {
            return '';
        }

        if (!isset($elements[0])) {
            return '';
        }

        /** @var PageModel $page */
        $page = $this->framework->createInstance(PageModel::class);

        if (null === ($page = $page->findByIdOrAlias($elements[0]))) {
            return '';
        }

        $url = $page->getFrontendUrl();
        $url .= '?' . FilterType::FILTER_RESET_URL_PARAMETER_NAME . '=' . $filterConfig->getId();

        return $url;
    }
}
