<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager as BaseCategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Category manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryManager extends BaseCategoryManager
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /* @var SecurityContextInterface */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param ObjectManager            $om
     * @param string                   $categoryClass
     * @param EventDispatcherInterface $eventDispatcher
     * @param CategoryAccessRepository $categoryAccessRepo
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ObjectManager $om,
        $categoryClass,
        EventDispatcherInterface $eventDispatcher,
        CategoryAccessRepository $categoryAccessRepo,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct($om, $categoryClass, $eventDispatcher);

        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->securityContext = $securityContext;
    }

    /**
     * Get the trees accessible by the current user.
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return array
     */
    public function getAccessibleTrees(UserInterface $user, $accessLevel = Attributes::VIEW_PRODUCTS)
    {
        $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, $accessLevel);

        $trees = [];
        foreach ($this->getTrees() as $tree) {
            if (in_array($tree->getId(), $grantedCategoryIds)) {
                $trees[] = $tree;
            }
        }

        return $trees;
    }

    /**
     * Get only the granted direct children for a parent category id.
     *
     * @param integer         $parentId
     * @param integer|boolean $selectNodeId
     *
     * @return ArrayCollection
     */
    public function getGrantedChildren($parentId, $selectNodeId = false)
    {
        $children = $this->getChildren($parentId, $selectNodeId);
        foreach ($children as $indChild => $child) {
            $category = (is_object($child)) ? $child : $child['item'];
            if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
                unset($children[$indChild]);
            }
        }

        return $children;
    }

    /**
     * Provides a tree filled up to the categories provided, with all their ancestors
     * and ancestors sibligns are filled too, in order to be able to display the tree
     * directly without loading other data
     * We apply permissions per category to hide not granted category or branch when
     * the path is not fully granted
     *
     * @param CategoryInterface $root       Tree root category
     * @param Collection        $categories Selected categories
     *
     * @return array Multi-dimensional array representing the tree
     */
    public function getGrantedFilledTree(CategoryInterface $root, Collection $categories)
    {
        $parentsIds = array();
        foreach ($categories as $category) {
            $categoryParentsIds = array();
            $path = $this->getEntityRepository()->getPath($category);
            if ($path[0]->getId() === $root->getId()) {
                foreach ($path as $pathItem) {
                    $categoryParentsIds[] = $pathItem->getId();
                    if (!$this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $pathItem)) {
                        $categoryParentsIds = [];
                        break;
                    }
                }
            }
            $parentsIds = array_merge($parentsIds, $categoryParentsIds);
        }
        $parentsIds = array_unique($parentsIds);
        $filledTree = $this->getEntityRepository()->getTreeFromParents($parentsIds);

        return $this->filterGrantedFilledTree($filledTree);
    }

    /**
     * Filter the filled tree to remove not granted category or branch of categories
     *
     * @param array &$filledTree the tree
     *
     * @return array Multi-dimensional array representing the tree
     */
    protected function filterGrantedFilledTree(&$filledTree)
    {
        foreach ($filledTree as $categoryIdx => &$categoryData) {

            $isLeaf = is_object($categoryData);
            $category = $isLeaf ? $categoryData : $categoryData['item'];

            if (!$this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
                unset($filledTree[$categoryIdx]);

            } elseif (!$isLeaf) {
                $this->filterGrantedFilledTree($categoryData['__children']);
            }
        }

        return $filledTree;
    }
}
