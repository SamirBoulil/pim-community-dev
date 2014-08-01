<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Enrich;

use Symfony\Component\Security\Core\User\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use Symfony\Component\EventDispatcher\GenericEvent;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;

class InjectCurrentUserProductDraftSubscriberSpec extends ObjectBehavior
{
    function let(
        UserContext $userContext,
        CatalogContext $catalogContext,
        ProductDraftRepositoryInterface $repository,
        ProductDraftChangesApplier $applier
    ) {
        $this->beConstructedWith($userContext, $catalogContext, $repository, $applier);
    }

    function it_applies_product_changes_when_finding_one(
        $userContext,
        $catalogContext,
        $repository,
        $applier,
        AbstractProduct $product,
        UserInterface $user,
        Proposition $productDraft,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $userContext->getUser()->willReturn($user);
        $catalogContext->getLocaleCode()->willReturn('en_US');
        $user->getUsername()->willReturn('julia');
        $repository->findUserProposition($product, 'julia', 'en_US')->willReturn($productDraft);
        $applier->apply($product, $productDraft)->shouldBeCalled();

        $this->inject($event);
    }

    function it_applies_nothing_if_there_is_no_product(
        $applier,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn(null);
        $applier->apply(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->inject($event);
    }

    function it_applies_nothing_if_there_is_no_user(
        $userContext,
        $applier,
        AbstractProduct $product,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $userContext->getUser()->willReturn(null);
        $applier->apply($product, Argument::any())->shouldNotBeCalled();

        $this->inject($event);
    }

    function it_applies_nothing_if_there_is_no_product_draft(
        $userContext,
        $catalogContext,
        $repository,
        $applier,
        AbstractProduct $product,
        UserInterface $user,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $userContext->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $catalogContext->getLocaleCode()->willReturn('en_US');
        $repository->findUserProposition($product, 'julia', 'en_US')->willReturn(null);
        $applier->apply($product, Argument::any())->shouldNotBeCalled();

        $this->inject($event);
    }
}
