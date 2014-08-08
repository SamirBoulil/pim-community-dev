<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Category access manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryAccessManager
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $categoryAccessClass;

    /**
     * @var string
     */
    protected $categoryClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $categoryAccessClass
     * @param string          $categoryClass
     */
    public function __construct(ManagerRegistry $registry, $categoryAccessClass, $categoryClass)
    {
        $this->registry            = $registry;
        $this->categoryAccessClass = $categoryAccessClass;
        $this->categoryClass       = $categoryClass;
    }

    /**
     * Get user groups that have view access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Group[]
     */
    public function getViewUserGroups(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedUserGroups($category, Attributes::VIEW_PRODUCTS);
    }

    /**
     * Get user groups that have edit access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Group[]
     */
    public function getEditUserGroups(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedUserGroups($category, Attributes::EDIT_PRODUCTS);
    }

    /**
     * Get user groups that have own access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Group[]
     */
    public function getOwnUserGroups(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedUserGroups($category, Attributes::OWN_PRODUCTS);
    }

    /**
     * Check if a user is granted to an attribute on a given attribute
     *
     * @param UserInterface     $user
     * @param CategoryInterface $category
     * @param string            $attribute
     *
     * @return bool
     *
     * @throws \LogicException
     */
    public function isUserGranted(UserInterface $user, CategoryInterface $category, $attribute)
    {
        if (Attributes::EDIT_PRODUCTS === $attribute) {
            $grantedUserGroups = $this->getEditUserGroups($category);
        } elseif (Attributes::VIEW_PRODUCTS === $attribute) {
            $grantedUserGroups = $this->getViewUserGroups($category);
        } else {
            throw new \LogicException(sprintf('Attribute "%" is not supported.', $attribute));
        }

        foreach ($grantedUserGroups as $userGroup) {
            if ($user->hasGroup($userGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grant access on a category to specified user groups, own implies edit which implies read
     *
     * @param CategoryInterface $category   the category
     * @param Group[] $viewGroups the view user groups
     * @param Group[] $editGroups the edit user groups
     * @param Group[] $ownGroups  the own user groups
     */
    public function setAccess(CategoryInterface $category, $viewGroups, $editGroups, $ownGroups)
    {
        $grantedGroups = [];
        foreach ($ownGroups as $group) {
            $this->grantAccess($category, $group, Attributes::OWN_PRODUCTS);
            $grantedGroups[] = $group;
        }

        foreach ($editGroups as $group) {
            if (!in_array($group, $grantedGroups)) {
                $this->grantAccess($category, $group, Attributes::EDIT_PRODUCTS);
                $grantedGroups[] = $group;
            }
        }

        foreach ($viewGroups as $group) {
            if (!in_array($group, $grantedGroups)) {
                $this->grantAccess($category, $group, Attributes::VIEW_PRODUCTS);
                $grantedGroups[] = $group;
            }
        }

        $this->revokeAccess($category, $grantedGroups);
        $this->getObjectManager()->flush();
    }

    /**
     * Update accesses to all category children to specified user groups
     *
     * @param CategoryInterface $parent
     * @param Group[]           $addViewGroups
     * @param Group[]           $addEditGroups
     * @param Group[]           $addOwnGroups
     * @param Group[]           $removeViewGroups
     * @param Group[]           $removeEditGroups
     * @param Group[]           $removeOwnGroups
     */
    public function updateChildrenAccesses(
        CategoryInterface $parent,
        $addViewGroups,
        $addEditGroups,
        $addOwnGroups,
        $removeViewGroups,
        $removeEditGroups,
        $removeOwnGroups
    ) {
        $mergedPermissions = $this->getMergedPermissions(
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );

        /** @var Group[] $codeToGroups */
        $codeToGroups = [];

        /** @var Group[] $allGroups */
        $allGroups = array_merge(
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
        foreach ($allGroups as $group) {
            $codeToGroups[$group->getName()] = $group;
        }

        $categoryRepo = $this->getCategoryRepository();
        $childrenIds = $categoryRepo->getAllChildrenIds($parent);

        foreach ($codeToGroups as $group) {
            $groupCode = $group->getName();
            $view = $mergedPermissions[$groupCode]['view'];
            $edit = $mergedPermissions[$groupCode]['edit'];
            $own = $mergedPermissions[$groupCode]['own'];

            $accessRepo = $this->getAccessRepository();
            $toUpdateIds = $accessRepo->getCategoryIdsWithExistingAccess([$group], $childrenIds);
            $toAddIds = array_diff($childrenIds, $toUpdateIds);

            if ($view === false && $edit === false && $own === false) {
                $this->removeAccesses($toUpdateIds, $group);
            } else {
                if (count($toAddIds) > 0) {
                    $this->addAccesses($toAddIds, $group, $view, $edit, $own);
                }
                if (count($toUpdateIds) > 0) {
                    $this->updateAccesses($toUpdateIds, $group, $view, $edit, $own);
                }
            }
        }
    }

    /**
     * Get merged permissions
     *
     * @param Group[] $addViewGroups
     * @param Group[] $addEditGroups
     * @param Group[] $addOwnGroups
     * @param Group[] $removeViewGroups
     * @param Group[] $removeEditGroups
     * @param Group[] $removeOwnGroups
     *
     * @return array
     */
    protected function getMergedPermissions(
        $addViewGroups,
        $addEditGroups,
        $addOwnGroups,
        $removeViewGroups,
        $removeEditGroups,
        $removeOwnGroups
    ) {
        $mergedPermissions = [];

        /** @var Group[] $allGroups */
        $allGroups = array_merge(
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
        foreach ($allGroups as $group) {
            $mergedPermissions[$group->getName()] = ['view' => null, 'edit' => null, 'own' => null];
        }
        foreach ($addViewGroups as $group) {
            $mergedPermissions[$group->getName()]['view'] = true;
        }
        foreach ($addEditGroups as $group) {
            $mergedPermissions[$group->getName()]['edit'] = true;
            $mergedPermissions[$group->getName()]['view'] = true;
        }
        foreach ($addOwnGroups as $group) {
            $mergedPermissions[$group->getName()]['own']  = true;
            $mergedPermissions[$group->getName()]['edit'] = true;
            $mergedPermissions[$group->getName()]['view'] = true;
        }

        foreach ($removeViewGroups as $group) {
            $mergedPermissions[$group->getName()]['view'] = false;
        }
        foreach ($removeEditGroups as $group) {
            $mergedPermissions[$group->getName()]['edit'] = false;
        }
        foreach ($removeOwnGroups as $group) {
            $mergedPermissions[$group->getName()]['own'] = false;
        }

        return $mergedPermissions;
    }

    /**
     * Delete accesses on categories
     *
     * @param integer[] $categoryIds
     * @param Group     $group
     */
    protected function removeAccesses($categoryIds, Group $group)
    {
        $accesses = $this->getAccessRepository()->findBy(['category' => $categoryIds, 'userGroup' => $group]);

        foreach ($accesses as $access) {
            $this->getObjectManager()->remove($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Add accesses on categories, a null permission will be resolved as false
     *
     * @param integer[]    $categoryIds
     * @param Group        $group
     * @param boolean|null $view
     * @param boolean|null $edit
     * @param boolean|null $own
     */
    protected function addAccesses($categoryIds, Group $group, $view = false, $edit = false, $own = false)
    {
        $view = ($view === null) ? false : $view;
        $edit = ($edit === null) ? false : $edit;
        $own = ($own === null) ? false : $own;
        $categories = $this->getCategoryRepository()->findBy(['id' => $categoryIds]);

        foreach ($categories as $category) {
            /** @var CategoryAccessInterface $access */
            $access = new $this->categoryAccessClass();
            $access
                ->setCategory($category)
                ->setViewProducts($view)
                ->setEditProducts($edit)
                ->setOwnProducts($own)
                ->setUserGroup($group)
            ;
            $this->getObjectManager()->persist($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Update accesses on categories, if a permission is null we don't update
     *
     * @param integer[]    $categoryIds
     * @param Group        $group
     * @param boolean|null $view
     * @param boolean|null $edit
     * @param boolean|null $own
     */
    protected function updateAccesses($categoryIds, Group $group, $view = false, $edit = false, $own = false)
    {
        /** @var CategoryAccessInterface[] $accesses */
        $accesses = $this->getAccessRepository()->findBy(['category' => $categoryIds, 'userGroup' => $group]);

        foreach ($accesses as $access) {
            if ($view !== null) {
                $access->setViewProducts($view);
            }
            if ($edit !== null) {
                $access->setEditProducts($edit);
            }
            if ($own !== null) {
                $access->setOwnProducts($own);
            }
            $this->getObjectManager()->persist($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on a category for the provided user group
     *
     * @param CategoryInterface $category
     * @param Group             $group
     * @param string            $accessLevel
     */
    public function grantAccess(CategoryInterface $category, Group $group, $accessLevel)
    {
        $access = $this->getCategoryAccess($category, $group);
        $access
            ->setViewProducts(true)
            ->setEditProducts(in_array($accessLevel, [Attributes::EDIT_PRODUCTS, Attributes::OWN_PRODUCTS]))
            ->setOwnProducts($accessLevel === Attributes::OWN_PRODUCTS);

        $this->getObjectManager()->persist($access);
        $this->getObjectManager()->flush();
    }

    /**
     * Get CategoryAccess entity for a category and user group
     *
     * @param CategoryInterface $category
     * @param Group             $group
     *
     * @return CategoryAccessInterface
     */
    protected function getCategoryAccess(CategoryInterface $category, Group $group)
    {
        $access = $this->getAccessRepository()
            ->findOneBy(
                [
                    'category'  => $category,
                    'userGroup' => $group
                ]
            );

        if (!$access) {
            /** @var CategoryAccessInterface $access */
            $access = new $this->categoryAccessClass();
            $access
                ->setCategory($category)
                ->setUserGroup($group);
        }

        return $access;
    }

    /**
     * Revoke access to a category
     * If $excludedGroups are provided, access will not be revoked for user groups with them
     *
     * @param CategoryInterface $category
     * @param Group[]           $excludedGroups
     *
     * @return integer
     */
    protected function revokeAccess(CategoryInterface $category, array $excludedGroups = [])
    {
        return $this->getAccessRepository()->revokeAccess($category, $excludedGroups);
    }

    /**
     * Get category repository
     *
     * @return CategoryRepository
     */
    protected function getCategoryRepository()
    {
        return $this->registry->getRepository($this->categoryClass);
    }

    /**
     * Get category access repository
     *
     * @return CategoryAccessRepository
     */
    protected function getAccessRepository()
    {
        return $this->registry->getRepository($this->categoryAccessClass);
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->registry->getManagerForClass($this->categoryAccessClass);
    }
}
