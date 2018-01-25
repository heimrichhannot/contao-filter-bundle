<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Module;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Environment;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\ListBundle\Backend\ListBundle;
use HeimrichHannot\ListBundle\Backend\ListConfig;
use HeimrichHannot\ListBundle\Backend\ListConfigElement;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Pagination\RandomPagination;
use HeimrichHannot\ListBundle\Util\ListConfigHelper;
use HeimrichHannot\Modal\ModalModel;
use HeimrichHannot\Request\Request;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use Patchwork\Utf8;

class ModuleList extends \Contao\Module
{
    protected $strTemplate = 'mod_list';

    /** @var ContaoFramework */
    protected $framework;

    /** @var ListConfigModel */
    protected $listConfig;

    /** @var FilterConfig */
    protected $filterConfig;

    /** @var object */
    protected $filter;

    /**
     * ModuleList constructor.
     *
     * @param ModuleModel $objModule
     * @param string      $strColumn
     */
    public function __construct(ModuleModel $objModule, $strColumn = 'main')
    {
        $this->framework = System::getContainer()->get('contao.framework');

        parent::__construct($objModule, $strColumn);
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    protected function compile()
    {
        Controller::loadDataContainer('tl_list_config');

        $this->listConfig = $listConfig = $this->getListConfig();
        $this->filterConfig = $this->getFilterConfig();
        $this->filter = (object) $this->filterConfig->getFilter();
        $this->filterRegistry = System::getContainer()->get('huh.filter.registry');

        Controller::loadDataContainer($this->filter->dataContainer);
        System::loadLanguageFile($this->filter->dataContainer);

        $this->handleShare();

        // apply module fields to template
        $this->Template->headline = $this->headline;
        $this->Template->hl = $this->hl;

        // add class to every list template
        $cssID = $this->cssID;
        $cssID[1] = $cssID[1].($cssID[1] ? ' ' : '').'huh-list';

        $this->cssID = $cssID;

        $this->Template->list = function (string $listTemplate = null, string $itemTemplate = null, array $data = []) {
            return $this->parseList($listTemplate, $itemTemplate, $data);
        };
    }

    protected function parseList(string $listTemplate = null, string $itemTemplate = null, array $data = [])
    {
        $templateData = [];
        $isSubmitted = $this->filterConfig->hasData();

        $templateData['wrapperId'] = 'huh-list-'.$this->id;

        // apply list config to template
        foreach ($this->listConfig->row() as $field => $value) {
            if (in_array($field, ['id', 'tstamp', 'dateAdded', 'title'], true)) {
                continue;
            }

            $templateData[$field] = $value;
        }

        $this->addDataAttributes($templateData);

        // sorting
        $templateData['currentSorting'] = $this->getCurrentSorting();

        if ($this->listConfig->isTableList) {
            $templateData['isTableList'] = $this->listConfig->isTableList;
            $templateData['tableFields'] = StringUtil::deserialize($this->listConfig->tableFields, true);

            if ($this->listConfig->hasHeader) {
                $templateData['header'] = $this->generateTableHeader();
            }
        }

        // apply filter
        $queryBuilder = $this->filterRegistry->getQueryBuilder($this->filter->id);

        $templateData['isSubmitted'] = $isSubmitted;

        if ($this->listConfig->limitFields) {
            $fieldsArray = \Contao\StringUtil::deserialize($this->listConfig->fields, true);

            // always add id
            if (!in_array('id', $fieldsArray, true)) {
                $fieldsArray = array_merge(['id'], $fieldsArray);
            }

            $fields = implode(', ', $fieldsArray);
        } else {
            $fields = '*';
        }

        if ($isSubmitted || $this->listConfig->showInitialResults) {
            $templateData['totalCount'] = $queryBuilder->select($fields)->execute()->rowCount();
        }

        // item count text
        $templateData['itemsFoundText'] = System::getContainer()->get('translator')->transChoice(
            $this->listConfig->itemCountText ?: 'huh.list.count.text.default',
            $templateData['totalCount'],
            ['%count%' => $templateData['totalCount']]
        );

        // no items text
        $templateData['noItemsText'] =
            System::getContainer()->get('translator')->trans($this->listConfig->noItemsText ?: 'huh.list.empty.text.default');

        $this->applyListConfigToQueryBuilder($queryBuilder, $templateData);

        if ($isSubmitted || $this->listConfig->showInitialResults) {
            $items = $queryBuilder->execute()->fetchAll();

            $preparedItems = $this->prepareItems($items);
            $templateData['items'] = $this->parseItems($preparedItems, $itemTemplate);
        }

        $listTemplate = $this->getListTemplateByName(($listTemplate ?: $this->listConfig->listTemplate) ?: 'default');
        $data = array_merge($templateData, $data);

        return System::getContainer()->get('twig')->render($listTemplate, $data);
    }

    protected function prepareItems(array $items): array
    {
        $preparedItems = [];

        foreach ($items as $item) {
            $preparedItem = $this->prepareItem($item);

            $preparedItems[] = $preparedItem;
        }

        return $preparedItems;
    }

    protected function prepareItem(array $item): array
    {
        $listConfig = $this->listConfig;
        $filter = $this->filter;
        $formUtil = System::getContainer()->get('huh.utils.form');

        $result = [];
        $dca = &$GLOBALS['TL_DCA'][$filter->dataContainer];

        $dc = DC_Table_Utils::createFromModelData($item, $filter->dataContainer);

        $fields = $listConfig->limitFields ? StringUtil::deserialize($listConfig->fields, true) : array_keys($dca['fields']);

        if ($listConfig->isTableList) {
            $result['tableFields'] = StringUtil::deserialize($listConfig->tableFields, true);
        }

        foreach ($fields as $field) {
            $dc->field = $field;
            $value = $item[$field];

            if (is_array($dca['fields'][$field]['load_callback'])) {
                foreach ($dca['fields'][$field]['load_callback'] as $callback) {
                    $obj = System::importStatic($callback[0]);
                    $value = $obj->{$callback[1]}($value, $dc);
                }
            }

            // add raw value
            $result['raw'][$field] = $value;

            $result['formatted'][$field] = $formUtil->prepareSpecialValueForOutput(
                $field,
                $value,
                $dc
            );

            // anti-xss: escape everything besides some tags
            $result['formatted'][$field] = $formUtil->escapeAllHtmlEntities(
                $filter->dataContainer,
                $field,
                $result['formatted'][$field]
            );
        }

        // add the missing field's raw values (these should always be inserted completely)
        foreach (array_keys($dca['fields']) as $field) {
            if (isset($result['raw'][$field])) {
                continue;
            }

            $value = $item[$field];

            if (is_array($dca['fields'][$field]['load_callback'])) {
                foreach ($dca['fields'][$field]['load_callback'] as $callback) {
                    $obj = System::importStatic($callback[0]);
                    $value = $obj->{$callback[1]}($value, $dc);
                }
            }

            // add raw value
            $result['raw'][$field] = $value;
        }

        return $result;
    }

    protected function parseItems(array $items, string $itemTemplate = null): array
    {
        $limit = count($items);

        if ($limit < 1) {
            return [];
        }

        $count = 0;
        $results = [];

        foreach ($items as $item) {
            ++$count;
            $first = 1 == $count ? ' first' : '';
            $last = $count == $limit ? ' last' : '';
            $oddEven = (0 == ($count % 2)) ? ' even' : ' odd';

            $class = 'item item_'.$count.$first.$last.$oddEven;

            $results[] = $this->parseItem(
                $item,
                $itemTemplate,
                $class,
                $count
            );
        }

        return $results;
    }

    protected function parseItem(array $item, string $itemTemplate = null, string $class = '', int $count = 0): string
    {
        $listConfig = $this->listConfig;
        $filter = $this->filter;

        $templateData = $item['formatted'];

        foreach ($item as $field => $value) {
            $templateData[$field] = $value;
        }

        $templateData['class'] = $class;
        $templateData['count'] = $count;
        $templateData['dataContainer'] = $filter->dataContainer;

        // id or alias
        $idOrAlias = $this->getIdOrAlias($item, $listConfig);

        $templateData['idOrAlias'] = $idOrAlias;
        $templateData['active'] = $idOrAlias && \Input::get('items') == $idOrAlias;

        // add images
        $this->addImagesToTemplate($item, $templateData, $listConfig);

        // details
        $this->addDetailsUrl($idOrAlias, $templateData, $listConfig);

        // share
        $this->addShareUrl($item, $templateData, $listConfig);

        $templateData['module'] = $this->arrData;

        $this->modifyItemTemplateData($templateData, $item);

        return System::getContainer()->get('twig')->render(
            $this->getItemTemplateByName(($itemTemplate ?: $listConfig->itemTemplate) ?: 'default'),
            $templateData
        );
    }

    protected function addImagesToTemplate(array $item, array &$templateData, ListConfigModel $listConfig)
    {
        $imageListConfigElements = System::getContainer()->get('huh.list.list-config-element-registry')->findBy(
            ['type=?', 'pid=?'],
            [ListConfigElement::TYPE_IMAGE, $listConfig->id]
        );

        if (null !== $imageListConfigElements) {
            while ($imageListConfigElements->next()) {
                $image = null;

                if ($item['raw'][$imageListConfigElements->imageSelectorField] && $item['raw'][$imageListConfigElements->imageField]) {
                    $imageSelectorField = $imageListConfigElements->imageSelectorField;
                    $image = $item['raw'][$imageListConfigElements->imageField];
                    $imageField = $imageListConfigElements->imageField;
                } elseif ($imageListConfigElements->placeholderImageMode) {
                    $imageSelectorField = $imageListConfigElements->imageSelectorField;
                    $imageField = $imageListConfigElements->imageField;

                    switch ($imageListConfigElements->placeholderImageMode) {
                        case ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED:
                            if ($item['raw'][$imageListConfigElements->genderField] == 'female') {
                                $image = $imageListConfigElements->placeholderImageFemale;
                            } else {
                                $image = $imageListConfigElements->placeholderImage;
                            }
                            break;
                        case ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE:
                            $image = $imageListConfigElements->placeholderImage;
                            break;
                    }
                } else {
                    continue;
                }

                $imageModel = FilesModel::findByUuid($image);

                if (null !== $imageModel
                    && is_file(System::getContainer()->get('huh.utils.container')->getProjectDir().'/'.$imageModel->path)
                ) {
                    $imageArray = $item['raw'];

                    // Override the default image size
                    if ('' != $imageListConfigElements->imgSize) {
                        $size = StringUtil::deserialize($imageListConfigElements->imgSize);

                        if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                            $imageArray['size'] = $imageListConfigElements->imgSize;
                        }
                    }

                    $imageArray[$imageField] = $imageModel->path;
                    $templateData['images'][$imageField] = [];

                    System::getContainer()->get('huh.utils.image')->addToTemplateData(
                        $imageField,
                        $imageSelectorField,
                        $templateData['images'][$imageField],
                        $imageArray,
                        null,
                        null,
                        null,
                        $imageModel
                    );
                }
            }
        }
    }

    protected function getListTemplateByName($name)
    {
        $config = System::getContainer()->getParameter('huh.list');
        $templates = $config['list']['templates']['list'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return null;
    }

    protected function getItemTemplateByName($name)
    {
        $config = System::getContainer()->getParameter('huh.list');
        $templates = $config['list']['templates']['item'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return null;
    }

    protected function addDetailsUrl($idOrAlias, array &$templateData, ListConfigModel $listConfig)
    {
        $templateData['addDetails'] = $listConfig->addDetails;

        if ($listConfig->addDetails) {
            $templateData['useModal'] = $listConfig->useModal;
            $templateData['jumpToDetails'] = $listConfig->jumpToDetails;

            $pageJumpTo = System::getContainer()->get('huh.utils.url')->getJumpToPageObject(
                $listConfig->jumpToDetails
            );

            if (null !== $pageJumpTo) {
                if ($listConfig->useModal) {
                    if (null !== ($modal = ModalModel::findPublishedByTargetPage($pageJumpTo))) {
                        $templateData['modalUrl'] = Controller::replaceInsertTags(
                            sprintf(
                                '{{modal_url::%s::%s::%s}}',
                                $modal->id,
                                $listConfig->jumpToDetails,
                                $idOrAlias
                            ),
                            true
                        );
                    }
                } else {
                    $templateData['detailsUrl'] = Controller::generateFrontendUrl(
                        $pageJumpTo->row(),
                        '/'.$idOrAlias
                    );
                }
            }
        }
    }

    protected function addShareUrl($item, array &$templateData, ListConfigModel $listConfig)
    {
        $templateData['addShare'] = $listConfig->addShare;

        if ($listConfig->addShare) {
            $urlUtil = System::getContainer()->get('huh.utils.url');

            $pageJumpTo = $urlUtil->getJumpToPageObject(
                $listConfig->jumpToShare
            );

            if (null !== $pageJumpTo) {
                $shareUrl = Environment::get('url').'/'.\Controller::generateFrontendUrl($pageJumpTo->row());

                $url = $urlUtil->addQueryString(
                    'act='.ListBundle::ACTION_SHARE,
                    $urlUtil->getCurrentUrl(
                        [
                            'skipParams' => true,
                        ]
                    )
                );

                $url = $urlUtil->addQueryString('url='.urlencode($shareUrl), $url);

                if ($listConfig->useAlias && $item['raw'][$listConfig->aliasField]) {
                    $url = $urlUtil->addQueryString($listConfig->aliasField.'='.$item['raw'][$listConfig->aliasField], $url);
                } else {
                    $url = $urlUtil->addQueryString('id='.$item['raw']['id'], $url);
                }

                $templateData['shareUrl'] = $url;
            }
        }
    }

    protected function modifyItemTemplateData(array &$templateData, array $item): void
    {
    }

    protected function applyListConfigToQueryBuilder(FilterQueryBuilder $queryBuilder, array &$templateData)
    {
        $listConfig = $this->listConfig;

        // offset
        $offset = (int) ($listConfig->skipFirst);

        // limit
        $limit = null;

        if ($listConfig->numberOfItems > 0) {
            $limit = $listConfig->numberOfItems;
        }

        // total item number
        $totalCount = $templateData['totalCount'];

        // sorting
        $currentSorting = $this->getCurrentSorting();

        if (ListConfig::SORTING_MODE_RANDOM == $currentSorting['order']) {
            $randomSeed = Request::getGet(RandomPagination::PARAM_RANDOM) ?: rand(1, 500);
            $queryBuilder->orderBy('RAND("'.(int) $randomSeed.'")');
            list($offset, $limit) = $this->splitResults($templateData, $offset, $totalCount, $limit, $randomSeed);
        } else {
            if (!empty($currentSorting)) {
                $queryBuilder->orderBy($currentSorting['order'], $currentSorting['sort']);
            }

            list($offset, $limit) = $this->splitResults($templateData, $offset, $totalCount, $limit);
        }

        // split the results
        $queryBuilder->setFirstResult($offset)->setMaxResults($limit);
    }

    protected function splitResults(array &$templateData, $offset, $total, $limit, $randomSeed = null)
    {
        $listConfig = $this->listConfig;
        $offsettedTotal = $total - $offset;

        // Split the results
        if ($listConfig->perPage > 0 && (!isset($limit) || $listConfig->numberOfItems > $listConfig->perPage)) {
            // Adjust the overall limit
            if (isset($limit)) {
                $offsettedTotal = min($limit, $offsettedTotal);
            }

            // Get the current page
            $id = 'page_s'.$this->id;
            $page = Request::getGet($id) ?: 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($offsettedTotal / $listConfig->perPage), 1)) {
                global $objPage;
                $objPage->noSearch = 1;
                $objPage->cache = 0;

                // Send a 404 header
                header('HTTP/1.1 404 Not Found');

                return null;
            }

            // Set limit and offset
            $limit = $listConfig->perPage;
            $offset += (max($page, 1) - 1) * $listConfig->perPage;

            // Overall limit
            if ($offset + $limit > $offsettedTotal) {
                $limit = $offsettedTotal - $offset;
            }

            // Add the pagination menu
            if ($listConfig->addAjaxPagination) {
                $pagination = new RandomPagination(
                    $randomSeed,
                    $offsettedTotal,
                    $listConfig->perPage,
                    Config::get('maxPaginationLinks'),
                    $id,
                    new FrontendTemplate('pagination_ajax')
                );
            } else {
                $pagination = new RandomPagination(
                    $randomSeed, $offsettedTotal, $listConfig->perPage, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], $id
                );
            }

            $templateData['pagination'] = $pagination->generate("\n  ");
        }

        return [$offset, $limit];
    }

    protected function generateTableHeader()
    {
        $headerFields = [];
        $currentSorting = $this->getCurrentSorting();
        $listConfig = $this->listConfig;
        $filter = $this->filter;
        $urlUtil = System::getContainer()->get('huh.utils.url');
        $dca = &$GLOBALS['TL_DCA'][$filter->dataContainer];
        $tableFields = \Contao\StringUtil::deserialize($listConfig->tableFields, true);

        foreach ($tableFields as $i => $name) {
            $isCurrentOrderField = ($name == $currentSorting['order']);

            $field = [
                'label' => $dca['fields'][$name]['label'][0] ?: $name,
                'class' => System::getContainer()->get('huh.utils.string')->camelCaseToDashed($name),
            ];

            if ($isCurrentOrderField) {
                $field['sortingClass'] = (ListConfig::SORTING_DIRECTION_ASC
                                          == $currentSorting['sort'] ? ListConfig::SORTING_DIRECTION_ASC : ListConfig::SORTING_DIRECTION_DESC);

                $field['link'] = $urlUtil->addQueryString(
                    'order='.$name.'&sort='.(ListConfig::SORTING_DIRECTION_ASC
                                                   == $currentSorting['sort'] ? ListConfig::SORTING_DIRECTION_DESC : ListConfig::SORTING_DIRECTION_ASC)
                );
            } else {
                $field['link'] = $urlUtil->addQueryString('order='.$name.'&sort='.ListConfig::SORTING_DIRECTION_ASC);
            }

            $headerFields[] = $field;
        }

        return $headerFields;
    }

    protected function addDataAttributes(array &$templateData)
    {
        $dataAttributes = [];
        $stringUtil = System::getContainer()->get('huh.utils.string');
        $listConfig = $this->listConfig;

        foreach ($GLOBALS['TL_DCA']['tl_list_config']['fields'] as $field => $data) {
            if ($data['eval']['addAsDataAttribute'] && $listConfig->{$field}) {
                $dataAttributes[] = 'data-'.$stringUtil->camelCaseToDashed($field).'="'.$listConfig->{$field}.'"';
            }
        }

        if (!empty($dataAttributes)) {
            $templateData['dataAttributes'] = implode(' ', $dataAttributes);
        }
    }

    protected function getListConfig()
    {
        $listConfigId = $this->arrData['listConfig'];

        if (!$listConfigId || null === ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($listConfigId))) {
            throw new \Exception(sprintf('The module %s has no valid list config. Please set one.', $this->id));
        }

        return $listConfig;
    }

    protected function getFilterConfig()
    {
        $filterId = $this->listConfig->filter;

        if (!$filterId || null === ($filterConfig = System::getContainer()->get('huh.filter.registry')->findById($filterId))) {
            throw new \Exception(sprintf('The module %s has no valid filter. Please set one.', $this->id));
        }

        return $filterConfig;
    }

    protected function handleShare()
    {
        $listConfig = $this->listConfig;
        $filter = $this->filter;
        $action = Request::getGet('act');

        if (ListBundle::ACTION_SHARE == $action && $listConfig->addShare) {
            $url = Request::getGet('url');
            $id = Request::getGet($listConfig->useAlias ? $listConfig->aliasField : 'id');

            if (null !== ($entity =
                    System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($this->framework, $filter->dataContainer, $id))
            ) {
                $now = time();

                if (ListConfigHelper::shareTokenExpiredOrEmpty($entity, $now)) {
                    $shareToken = str_replace('.', '', uniqid('', true));
                    $entity->shareToken = $shareToken;
                    $entity->shareTokenTime = $now;
                    $entity->save();
                }

                if ($listConfig->shareAutoItem) {
                    $shareUrl = $url.'/'.$entity->shareToken;
                } else {
                    $shareUrl = System::getContainer()->get('huh.utils.url')->addQueryString('share='.$entity->shareToken, $url);
                }

                die($shareUrl);
            }
        }
    }

    protected function getCurrentSorting()
    {
        $listConfig = $this->listConfig;
        $filter = $this->filter;
        $sortingAllowed = $listConfig->isTableList && $listConfig->hasHeader && $listConfig->sortingHeader;

        // GET parameter
        if ($sortingAllowed && ($orderField = Request::getGet('order')) && ($sort = Request::getGet('sort'))) {
            // anti sql injection: check if field exists
            if (Database::getInstance()->fieldExists($orderField, $filter->dataContainer)
                && in_array($sort, ListConfig::SORTING_DIRECTIONS, true)
            ) {
                $currentSorting = [
                    'order' => Request::getGet('order'),
                    'sort' => Request::getGet('sort'),
                ];
            } else {
                $currentSorting = [];
            }
        }
        // initial
        else {
            switch ($listConfig->sortingMode) {
                case ListConfig::SORTING_MODE_TEXT:
                    $currentSorting = [
                        'order' => $listConfig->sortingText,
                    ];
                    break;
                case ListConfig::SORTING_MODE_RANDOM:
                    $currentSorting = [
                        'order' => ListConfig::SORTING_MODE_RANDOM,
                    ];
                    break;
                default:
                    $currentSorting = [
                        'order' => $listConfig->sortingField,
                        'sort' => $listConfig->sortingDirection,
                    ];
                    break;
            }
        }

        return $currentSorting;
    }

    protected function getIdOrAlias(array $item, ListConfigModel $listConfig)
    {
        $idOrAlias = $item['raw']['id'];

        if ($listConfig->useAlias && $item['raw'][$listConfig->aliasField]) {
            $idOrAlias = $item['raw'][$listConfig->aliasField];
        }

        return $idOrAlias;
    }
}
