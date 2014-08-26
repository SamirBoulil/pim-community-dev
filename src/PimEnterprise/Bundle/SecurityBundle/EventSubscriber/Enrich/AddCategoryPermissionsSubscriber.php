<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;

/**
 * Add parent permission when create a new category
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddCategoryPermissionsSubscriber implements EventSubscriberInterface
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
            CategoryEvents::POST_CREATE => 'addNewCategoryPermissions'
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
        $this->accessManager->setAccessLikeParent($category);
    }
}
