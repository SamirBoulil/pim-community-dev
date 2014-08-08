<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group as UserGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Model\AttributeGroupAccessInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Attribute group access manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccessManager
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $attGroupAccessClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $attGroupAccessClass
     */
    public function __construct(ManagerRegistry $registry, $attGroupAccessClass)
    {
        $this->registry                  = $registry;
        $this->attGroupAccessClass = $attGroupAccessClass;
    }

    /**
     * Check if a user is granted to an attribute on a given permission
     *
     * @param UserInterface  $user
     * @param AttributeGroup $group
     * @param string         $permission
     *
     * @return bool
     *
     * @throws \LogicException
     */
    public function isUserGranted(UserInterface $user, AttributeGroup $group, $permission)
    {
        if (Attributes::EDIT_ATTRIBUTES === $permission) {
            $grantedUserGroups = $this->getEditUserGroups($group);
        } elseif (Attributes::VIEW_ATTRIBUTES === $permission) {
            $grantedUserGroups = $this->getViewUserGroups($group);
        } else {
            throw new \LogicException(sprintf('Attribute "%" is not supported.', $permission));
        }

        foreach ($grantedUserGroups as $userGroup) {
            if ($user->hasGroup($userGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user groups that have view access to an attribute group
     *
     * @param AttributeGroup $group
     *
     * @return UserGroup[]
     */
    public function getViewUserGroups(AttributeGroup $group)
    {
        return $this->getRepository()->getGrantedUserGroups($group, Attributes::VIEW_ATTRIBUTES);
    }

    /**
     * Get user groups that have edit access to an attribute group
     *
     * @param AttributeGroup $group
     *
     * @return UserGroup[]
     */
    public function getEditUserGroups(AttributeGroup $group)
    {
        return $this->getRepository()->getGrantedUserGroups($group, Attributes::EDIT_ATTRIBUTES);
    }

    /**
     * Grant access on an attribute group to specified user group
     *
     * @param AttributeGroup $attributeGroup
     * @param UserGroup[]    $viewUserGroups
     * @param UserGroup[]    $editGroups
     */
    public function setAccess(AttributeGroup $attributeGroup, $viewUserGroups, $editGroups)
    {
        $grantedUserGroups = [];
        foreach ($editGroups as $userGroup) {
            $this->grantAccess($attributeGroup, $userGroup, Attributes::EDIT_ATTRIBUTES);
            $grantedUserGroups[] = $userGroup;
        }

        foreach ($viewUserGroups as $userGroup) {
            if (!in_array($userGroup, $grantedUserGroups)) {
                $this->grantAccess($attributeGroup, $userGroup, Attributes::VIEW_ATTRIBUTES);
                $grantedUserGroups[] = $userGroup;
            }
        }

        $this->revokeAccess($attributeGroup, $grantedUserGroups);
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on an attribute group for the provided user group
     *
     * @param AttributeGroup $attributeGroup
     * @param UserGroup      $userGroup
     * @param string         $accessLevel
     */
    public function grantAccess(AttributeGroup $attributeGroup, UserGroup $userGroup, $accessLevel)
    {
        $access = $this->getAttributeGroupAccess($attributeGroup, $userGroup);
        $access
            ->setViewAttributes(true)
            ->setEditAttributes($accessLevel === Attributes::EDIT_ATTRIBUTES);

        $this->getObjectManager()->persist($access);
        $this->getObjectManager()->flush();
    }

    /**
     * Get AttributeGroupAccess entity for an attribute group and user group
     *
    * @param AttributeGroup $attributeGroup
    * @param UserGroup      $userGroup
    *
    * @return AttributeGroupAccess
     */
    protected function getAttributeGroupAccess(AttributeGroup $attributeGroup, UserGroup $userGroup)
    {
        $access = $this->getRepository()
            ->findOneBy(
                [
                    'attributeGroup' => $attributeGroup,
                    'userGroup'      => $userGroup
                ]
            );

        if (!$access) {
            /** @var AttributeGroupAccessInterface $access */
            $access = new $this->attGroupAccessClass();
            $access
                ->setAttributeGroup($attributeGroup)
                ->setUserGroup($userGroup);
        }

        return $access;
    }

    /**
     * Revoke access to an attribute group
     * If $excludedUserGroups are provided, access will not be revoked for groups with them
     *
     * @param AttributeGroup $attributeGroup
     * @param UserGroup[]    $excludedUserGroups
     *
     * @return integer
     */
    protected function revokeAccess(AttributeGroup $attributeGroup, array $excludedUserGroups = [])
    {
        return $this->getRepository()->revokeAccess($attributeGroup, $excludedUserGroups);
    }

    /**
     * Get repository
     *
     * @return AttributeGroupAccessRepository
     */
    protected function getRepository()
    {
        return $this->registry->getRepository($this->attGroupAccessClass);
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->registry->getManagerForClass($this->attGroupAccessClass);
    }
}
