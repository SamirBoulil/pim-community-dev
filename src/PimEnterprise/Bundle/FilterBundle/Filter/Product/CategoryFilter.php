<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\Product\CategoryFilter as PimCategoryFilter;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Override category filter to apply permissions on categories
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryFilter extends PimCategoryFilter
{
    /**
     * Override to apply category permissions
     *
     * {@inheritdoc}
     */
    protected function applyFilterByAll(FilterDatasourceAdapterInterface $ds, $data)
    {
        $ids = $this->manager->getGrantedCategoryIds();

        // TODO : Filter on granted categories + unclassified products
        // means unclassified in categories I can view
        $qb = $ds->getQueryBuilder();
        $rootAlias  = $qb->getRootAlias();
        $qb->leftJoin('p.categories', 'filterCategory');
        $qb->andWhere('filterCategory.id in(:filterCatIds) OR filterCategory.id is null');
        $qb->setParameter('filterCatIds', $categoryIds);

        return true;
    }

    /**
     * Override to apply category permissions (not for unclassified)
     *
     * {@inheritdoc}
     */
    protected function getProductIdsInCategory(CategoryInterface $category, $data)
    {
        if ($data['categoryId'] === self::UNCLASSIFIED_CATEGORY) {
            $productIds = $this->manager->getProductIdsInCategory($category, $data['includeSub']);
        } else {
            $productIds = $this->manager->getProductIdsInGrantedCategory($category, $data['includeSub']);
        }

        return (empty($productIds)) ? array(0) : $productIds;
    }
}
