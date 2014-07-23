<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;

/**
 * Add parent permission when create a new category
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddCategoryPermissionsListener implements EventSubscriberInterface
{
    /** @var CategoryAccessManager */
    protected $accessManager;

    /**
     * Constructor
     *
     * @param CategoryAccessManager $accessManager
     */
    public function __construct(CategoryAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EnrichEvents::POST_CREATE_CATEGORY => 'addNewCategoryPermissions'
        ];
    }

    /**
     * Copy the parent's permissions to the new category
     *
     * @param GenericEvent $event
     */
    public function addNewCategoryPermissions(GenericEvent $event)
    {
        $category = $event->getSubject();
        $parent   = $category->getParent();
        if ($parent) {
            $this->accessManager->setAccess(
                $category,
                $this->accessManager->getViewUserGroups($parent),
                $this->accessManager->getEditUserGroups($parent),
                $this->accessManager->getOwnUserGroups($parent)
            );
        }
    }
}
