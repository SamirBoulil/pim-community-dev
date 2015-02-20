<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Pim\Bundle\CatalogBundle\Event;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

class CheckPublishedProductOnRemovalSubscriberSpec extends ObjectBehavior
{
    function let(PublishedProductRepositoryInterface $publishedRepository, CategoryRepository $categoryRepository)
    {
        $this->beConstructedWith($publishedRepository, $categoryRepository);
    }

    function it_subscribes_to_pre_remove_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            Event\ProductEvents::PRE_REMOVE           => 'checkProductHasBeenPublished',
            Event\FamilyEvents::PRE_REMOVE            => 'checkFamilyLinkedToPublishedProduct',
            Event\AttributeEvents::PRE_REMOVE         => 'checkAttributeLinkedToPublishedProduct',
            Event\CategoryEvents::PRE_REMOVE_CATEGORY => 'checkCategoryLinkedToPublishedProduct',
            Event\CategoryEvents::PRE_REMOVE_TREE     => 'checkCategoryLinkedToPublishedProduct',
            Event\AssociationTypeEvents::PRE_REMOVE   => 'checkAssociationTypeLinkedToPublishedProduct',
            Event\GroupEvents::PRE_REMOVE             => 'checkGroupLinkedToPublishedProduct'
        ]);
    }

    function it_checks_if_a_product_is_not_published(
        $publishedRepository,
        AbstractProduct $product,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $publishedRepository->findOneByOriginalProduct($product)->willReturn(null);

        $this->checkProductHasBeenPublished($event);
    }

    function it_throws_an_exception_if_the_product_is_published(
        $publishedRepository,
        AbstractProduct $product,
        PublishedProductInterface $published,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $publishedRepository->findOneByOriginalProduct($product)->willReturn($published);

        $this
            ->shouldThrow(new PublishedProductConsistencyException('Impossible to remove a published product'))
            ->duringCheckProductHasBeenPublished($event);
    }

    function it_checks_if_the_family_is_linked_to_a_published_product(
        $publishedRepository,
        Family $family,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($family);
        $publishedRepository->countPublishedProductsForFamily($family)->willReturn(0);

        $this->checkFamilyLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_family_is_linked_to_a_published_product(
        $publishedRepository,
        Family $family,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($family);
        $publishedRepository->countPublishedProductsForFamily($family)->willReturn(1);

        $this
            ->shouldThrow(new PublishedProductConsistencyException('Impossible to remove family linked to a published product'))
            ->duringCheckFamilyLinkedToPublishedProduct($event);
    }

    function it_checks_if_the_attribute_is_linked_to_a_published_product(
        $publishedRepository,
        AbstractAttribute $attribute,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($attribute);
        $publishedRepository->countPublishedProductsForAttribute($attribute)->willReturn(0);

        $this->checkAttributeLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_attribute_is_linked_to_a_published_product(
        $publishedRepository,
        AbstractAttribute $attribute,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($attribute);
        $publishedRepository->countPublishedProductsForAttribute($attribute)->willReturn(1);

        $this
            ->shouldThrow(new PublishedProductConsistencyException('Impossible to remove attribute linked to a published product'))
            ->duringCheckAttributeLinkedToPublishedProduct($event);
    }

    function it_checks_if_the_category_is_linked_to_a_published_product(
        $publishedRepository,
        $categoryRepository,
        CategoryInterface $category,
        GenericEvent $event
    ) {
        $category->getId()->willReturn(1);
        $event->getSubject()->willReturn($category);
        $categoryRepository->getAllChildrenIds($category)->willReturn([2, 3]);
        $publishedRepository->countPublishedProductsForCategoryAndChildren([2, 3, 1])->willReturn(0);

        $this->checkCategoryLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_category_is_linked_to_a_published_product(
        $publishedRepository,
        $categoryRepository,
        CategoryInterface $category,
        GenericEvent $event
    ) {

        $category->getId()->willReturn(1);
        $event->getSubject()->willReturn($category);
        $categoryRepository->getAllChildrenIds($category)->willReturn([2, 3]);
        $publishedRepository->countPublishedProductsForCategoryAndChildren([2, 3, 1])->willReturn(2);

        $this
            ->shouldThrow(new PublishedProductConsistencyException('Impossible to remove category linked to a published product'))
            ->duringCheckCategoryLinkedToPublishedProduct($event);
    }

    function it_checks_if_the_group_is_linked_to_a_published_product(
        $publishedRepository,
        Group $group,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($group);
        $publishedRepository->countPublishedProductsForGroup($group)->willReturn(0);

        $this->checkGroupLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_group_is_linked_to_a_published_product(
        $publishedRepository,
        Group $group,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($group);
        $publishedRepository->countPublishedProductsForGroup($group)->willReturn(1);

        $this
            ->shouldThrow(new PublishedProductConsistencyException('Impossible to remove group linked to a published product'))
            ->duringCheckGroupLinkedToPublishedProduct($event);
    }

    function it_checks_if_the_association_type_is_linked_to_a_published_product(
        $publishedRepository,
        AssociationType $associationType,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($associationType);
        $publishedRepository->countPublishedProductsForAssociationType($associationType)->willReturn(0);

        $this->checkAssociationTypeLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_association_type_is_linked_to_a_published_product(
        $publishedRepository,
        AssociationType $associationType,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($associationType);
        $publishedRepository->countPublishedProductsForAssociationType($associationType)->willReturn(1);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException('Impossible to remove association type linked to a published product')
            )
            ->duringCheckAssociationTypeLinkedToPublishedProduct($event);
    }
}