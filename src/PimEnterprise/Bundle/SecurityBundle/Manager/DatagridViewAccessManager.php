<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PimEnterprise\Bundle\FilterBundle\Filter\Product\CategoryFilter;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Datagrid view access manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DatagridViewAccessManager
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var AttributeGroupAccessManager */
    protected $attributeGroupAccessManager;

    /** @var CategoryAccessManager */
    protected $categoryAccessManager;

    /**
     * @param AttributeRepository         $attributeRepository
     * @param CategoryRepository          $categoryRepository
     * @param AttributeGroupAccessManager $attributeGroupAccessManager
     * @param CategoryAccessManager       $categoryAccessManager
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        CategoryRepository $categoryRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        CategoryAccessManager $categoryAccessManager
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
        $this->attributeGroupAccessManager = $attributeGroupAccessManager;
        $this->categoryAccessManager = $categoryAccessManager;
    }

    /**
     * Chek is a user is granted on a datagrid view for the given attribute.
     *
     * @param UserInterface $user
     * @param DatagridView  $view
     * @param string        $attribute
     *
     * @return bool
     *
     * @throws \LogicException
     */
    public function isUserGranted(UserInterface $user, DatagridView $view, $attribute)
    {
        if (Attributes::VIEW !== $attribute) {
            throw new \LogicException(sprintf('Attribute "%" is not supported.', $attribute));
        }

        foreach ($view->getColumns() as $column) {
            if (false === $this->isAttributeGranted($user, $column)) {
                return false;
            }
        }

        foreach ($this->getViewFiltersAsArray($view) as $filter) {
            if (false === $this->isAttributeGranted($user, $filter)) {
                return false;
            }
        }

        if (null !== $categoryId = $this->getCategoryIdFromViewFilters($view)) {
            return $this->isCategoryGranted($user, $categoryId);
        }

        return true;
    }

    /**
     * Check if an attribute is granted for the current user (ie: if the attribute group is granted for view)
     *
     * @param UserInterface $user
     * @param string        $code
     *
     * @return bool
     */
    protected function isAttributeGranted(UserInterface $user, $code)
    {
        /** @var \Pim\Bundle\CatalogBundle\Entity\Attribute $attribute */
        if (null === $attribute = $this->attributeRepository->findOneBy(['code' => $code])) {
            return true;
        }

        return $this->attributeGroupAccessManager->isUserGranted(
            $user,
            $attribute->getGroup(),
            Attributes::VIEW_ATTRIBUTES
        );
    }

    /**
     * Check if a category is granted for the current user (ie: if the category is granted for view)
     *
     * @param UserInterface $user
     * @param int           $categoryId
     *
     * @return bool
     */
    protected function isCategoryGranted(UserInterface $user, $categoryId)
    {
        if (CategoryFilter::ALL_CATEGORY == $categoryId || CategoryFilter::UNCLASSIFIED_CATEGORY == $categoryId) {
            return true;
        }

        /** @var \Pim\Bundle\CatalogBundle\Model\CategoryInterface $category */
        if (null === $category = $this->categoryRepository->find($categoryId)) {
            return false;
        }

        return $this->categoryAccessManager->isUserGranted($user, $category, Attributes::VIEW_PRODUCTS);
    }

    /**
     * TODO:  change the way view filters are stored in the DB and remove this ugly hack...
     *
     * @param DatagridView $view
     *
     * @return int|null
     */
    protected function getCategoryIdFromViewFilters(DatagridView $view)
    {
        $matches = [];
        preg_match('/f\[category\]\[value\]\[categoryId\]=((?:-)?\d+)/', urldecode($view->getFilters()), $matches);

        // no filter on categories
        if (empty($matches[1])) {
            return null;
        }

        if (is_string($matches[1])) {
            return $matches[1];
        }

        return $matches[1][0];
    }

    /**
     * TODO:  change the way view filters are stored in the DB and remove this ugly hack...
     *
     * @param DatagridView $view
     *
     * @return array
     */
    protected function getViewFiltersAsArray(DatagridView $view)
    {
        $matches = [];
        preg_match_all('/f\[(.*?)\].*?=([\w\d]|\-\d)/', urldecode($view->getFilters()), $matches);

        $filters = array_unique($matches[1]);

        return array_map(
            function ($filter) {
                return str_replace('__', '', $filter);
            },
            $filters
        );
    }
}
