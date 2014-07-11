<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CategoryManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager');
    }

    function let(
        CategoryAccessRepository $categoryAccessRepository,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        CategoryRepository $categoryRepository,
        SecurityContextInterface $context
    ) {
        $om->getRepository(Argument::any())->willReturn($categoryRepository);
        $this->beConstructedWith(
            $om,
            Argument::any(),
            $eventDispatcher,
            $categoryAccessRepository,
            $context
        );
    }

    function it_gets_accessible_trees_for_display(
        $categoryAccessRepository,
        $categoryRepository,
        Category $firstTree,
        Category $secondTree,
        Category $thirdTree,
        User $user
    ) {
        $firstTree->getId()->willReturn(1);
        $secondTree->getId()->willReturn(2);
        $thirdTree->getId()->willReturn(3);

        $categoryRepository
            ->getChildren(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willReturn([$firstTree, $secondTree, $thirdTree]);

        $accessibleCategoryIds = array(1, 3);

        $categoryAccessRepository
            ->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS)
            ->willReturn($accessibleCategoryIds);

        $this->getAccessibleTrees($user)->shouldReturn([$firstTree, $thirdTree]);
    }

    function it_gets_accessible_trees_for_edition(
        $categoryAccessRepository,
        $categoryRepository,
        Category $firstTree,
        Category $secondTree,
        Category $thirdTree,
        User $user
    ) {
        $firstTree->getId()->willReturn(1);
        $secondTree->getId()->willReturn(2);
        $thirdTree->getId()->willReturn(3);

        $categoryRepository
            ->getChildren(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willReturn([$firstTree, $secondTree, $thirdTree]);

        $accessibleCategoryIds = array(1);

        $categoryAccessRepository
            ->getGrantedCategoryIds($user, Attributes::EDIT_PRODUCTS)
            ->willReturn($accessibleCategoryIds);

        $this->getAccessibleTrees($user, Attributes::EDIT_PRODUCTS)->shouldReturn([$firstTree]);
    }
}
