<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager as BaseProductCategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Product category manager
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductCategoryManager extends BaseProductCategoryManager
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * Constructor
     *
     * @param ProductCategoryRepositoryInterface $productRepo        Product repository
     * @param CategoryRepositoryInterface        $categoryRepo       Category repository
     * @param SecurityContextInterface           $securityContext    Security context
     * @param CategoryAccessRepository           $categoryAccessRepo Category Access repository
     */
    public function __construct(
        ProductCategoryRepositoryInterface $productRepo,
        CategoryRepositoryInterface $categoryRepo,
        SecurityContextInterface $securityContext,
        CategoryAccessRepository $categoryAccessRepo
    ) {
        parent::__construct($productRepo, $categoryRepo);

        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     * @see getProductCountByTree same logic but here we apply permisions and count only visible category (full path)
     */
    public function getProductCountByGrantedTree(ProductInterface $product)
    {
        $count     = $this->getProductCountWithFullGrantedPath($product);
        $trees     = $this->categoryRepository->getChildren(null, true, 'created', 'DESC');
        $treeCount = [];
        foreach ($trees as $tree) {
            if ($this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $tree)) {
                $treeCount[] = [
                    'tree' => $tree,
                    'productCount' => isset($count[$tree->getId()]) ? $count[$tree->getId()] : 0
                ];
            }
        }

        return $treeCount;
    }

    /**
     * {@inheritdoc}
     * @see getProductsCountInCategory same logic with applying permissions
     */
    public function getProductsCountInGrantedCategory(
        CategoryInterface $category,
        $inChildren = false,
        $inProvided = true
    ) {
        if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
            return 0;
        }

        $grantedQb = null;
        if ($inChildren) {
            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, $inProvided);
            $grantedQb = $this->getAllGrantedChildrenQueryBuilder($categoryQb);
        }

        return $this->productRepository->getProductsCountInCategory($category, $grantedQb);
    }

    /**
     * Count only product with a full accessible path
     *
     * @param ProductInterface $product
     *
     * @return array with format [treeId => productCount]
     */
    protected function getProductCountWithFullGrantedPath(ProductInterface $product)
    {
        $categories = $product->getCategories();
        $treesCount = [];
        foreach ($categories as $category) {
            $path = $this->categoryRepository->getPath($category);
            $fullPathGranted = true;
            foreach ($path as $pathItem) {
                if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $pathItem)) {
                    $fullPathGranted = false;
                    break;
                }
            }
            if ($fullPathGranted) {
                $treeId = $category->getRoot();
                if (!isset($treesCount[$treeId])) {
                    $treesCount[$treeId] = 0;
                }
                $treesCount[$treeId]++;
            }
        }

        return $treesCount;
    }

    /**
     * Build a new query builder based on children QB to let only granted children
     *
     * @param QueryBuilder $childrenQb
     *
     * @return QueryBuilder
     */
    protected function getAllGrantedChildrenQueryBuilder(QueryBuilder $childrenQb)
    {
        $categoryIds = $this->categoryAccessRepo->getGrantedCategoryIdsFromQB(
            $childrenQb,
            $this->securityContext->getToken()->getUser(),
            Attributes::VIEW_PRODUCTS
        );

        $rootAlias  = current($childrenQb->getRootAliases());
        $grantedQb = $this->categoryRepository->createQueryBuilder($rootAlias);
        $grantedQb->select($rootAlias.'.id');
        $grantedQb->where($grantedQb->expr()->in($rootAlias.'.id', ':categoryIds'));
        $grantedQb->setParameter('categoryIds', $categoryIds);

        return $grantedQb;
    }
}