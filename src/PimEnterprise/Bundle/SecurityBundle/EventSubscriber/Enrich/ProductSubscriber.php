<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;

/**
 * Product listener used to handle permissions.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductSubscriber implements EventSubscriberInterface
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param UserContext              $userContext
     */
    public function __construct(SecurityContextInterface $securityContext, UserContext $userContext)
    {
        $this->securityContext = $securityContext;
        $this->userContext     = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::PRE_EDIT => 'checkEditPermission',
        ];
    }

    /**
     * Throws an access denied exception if the user can not edit the product
     *
     * @param GenericEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkEditPermission(GenericEvent $event)
    {
        if (false === $this->securityContext->isGranted(Attributes::EDIT, $event->getSubject())) {
            throw new AccessDeniedException();
        }
        $locale = $this->userContext->getCurrentLocale();
        if (false === $this->securityContext->isGranted(Attributes::EDIT_PRODUCTS, $locale)) {
            throw new AccessDeniedException();
        }
    }
}