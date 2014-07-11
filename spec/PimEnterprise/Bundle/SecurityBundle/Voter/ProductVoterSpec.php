<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Voter\ProductVoter;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ProductVoterSpec extends ObjectBehavior
{
    protected $attributes = [ Attributes::VIEW_PRODUCT, Attributes::EDIT_PRODUCT ];

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\Voter\ProductVoter');
    }

    function let(CategoryAccessRepository $categoryAccessRepository, TokenInterface $token, User $user)
    {
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($categoryAccessRepository);
    }

    function it_returns_abstain_access_if_non_attribute_group_entity($token)
    {
        $this
            ->vote($token, 'foo', array('bar', 'baz'))
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, ProductVoter $wrongClass)
    {
        $this
            ->vote($token, $wrongClass, [Attributes::VIEW_PRODUCT])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $categoryAccessRepository,
        $token,
        $user,
        AbstractProduct $product,
        CategoryInterface $categoryFive,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->getGrantedCategoryIds($user, Attributes::EDIT_PRODUCTS)->willReturn([1, 3]);
        $product->getCategories()->willReturn([$categoryFive, $categorySix]);
        $categoryFive->getId()->willReturn(5);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $product, [Attributes::EDIT_PRODUCT])
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $categoryAccessRepository,
        $token,
        $user,
        AbstractProduct $product,
        CategoryInterface $categoryOne,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->getGrantedCategoryIds($user, Attributes::EDIT_PRODUCTS)->willReturn([1, 3]);
        $product->getCategories()->willReturn([$categoryOne, $categorySix]);
        $categoryOne->getId()->willReturn(1);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $product, [Attributes::EDIT_PRODUCT])
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }
}
