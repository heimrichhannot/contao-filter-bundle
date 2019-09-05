<?php


namespace HeimrichHannot\FilterBundle\Filter\Type;


use Contao\Controller;
use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class ExternalEntityType extends AbstractType
{
    const TYPE = 'external_entity';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        if (null === ($sourceEntity = $this->getSourceEntity($element))) {
            return;
        }

        if ('' == ($sourceValue = $this->getSourceValueForFilter($sourceEntity, $element))) {
            $sourceValue = -1;
        }

        $where = $this->getWhere($builder, $element, $sourceValue);
        $builder->andWhere($where);

    }

    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
    }

    /**
     * @param FilterQueryBuilder $builder
     * @param FilterConfigElementModel $element
     * @param $sourceValue
     * @return string
     */
    protected function getWhere(FilterQueryBuilder $builder, FilterConfigElementModel $element, $sourceValue): string
    {
        $filter = $this->config->getFilter();

        $field    = $filter['dataContainer'] . '.' . $element->field;
        $operator = $this->getOperator($element);
        $dca      = $this->getDca($filter, $element);

        return System::getContainer()->get('huh.utils.database')->composeWhereForQueryBuilder($builder, $field,
            $operator, $dca, $sourceValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_IN;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return null;
    }

    /**
     * @param FilterConfigElementModel $element
     * @return mixed|null
     */
    public function getSourceEntity(FilterConfigElementModel $element)
    {
        $sourceEntityConditions = StringUtil::deserialize($element->sourceEntityResolve, true);

        if (empty($sourceEntityConditions)) {
            return null;
        }

        list($where, $values) = System::getContainer()->get('huh.entity_filter.backend.entity_filter')->computeSqlCondition($sourceEntityConditions,
            $element->sourceTable);

        return System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy($element->sourceTable, [$where], $values);
    }

    /**
     * @param $entity
     * @param FilterConfigElementModel $element
     * @return string
     */
    protected function getSourceValueForFilter($entity, FilterConfigElementModel $element): string
    {
        if (!isset($entity->{$element->sourceField})) {
            return '';
        }

        $value = $entity->{$element->sourceField};

        if (false === strstr($GLOBALS['TL_DCA'][$element->sourceTable]['fields'][$element->sourceField]['sql'],
                'blob')) {
            return $value;
        }

        return implode(',', StringUtil::deserialize($value, true));
    }

    /**
     * @param array $filter
     * @param FilterConfigElementModel $element
     * @return array
     */
    protected function getDca(array $filter, FilterConfigElementModel $element): array
    {
        return $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];
    }

    /**
     * @param FilterConfigElementModel $element
     * @return string
     */
    protected function getOperator(FilterConfigElementModel $element): string
    {
        if(!$element->customOperator) {
            return $this->getDefaultOperator($element);
        }

        return $element->customOperator;
    }
}