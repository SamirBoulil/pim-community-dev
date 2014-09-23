<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use PimEnterprise\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;

class ProductMassActionManagerSpec extends ObjectBehavior
{
    function let(
        ProductMassActionRepositoryInterface $massActionRepo,
        AttributeRepository $attRepo,
        AttributeGroupAccessRepository $attGroupAccessRepo,
        SecurityContext $securityContext,
        TokenInterface $token,
        User $user
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($massActionRepo, $attRepo, $attGroupAccessRepo, $securityContext);
    }

    function it_finds_attributes_with_groups_with_sub_query(
        $massActionRepo,
        $attRepo,
        $attGroupAccessRepo,
        $user,
        QueryBuilder $subQB
    ) {
        $productIds   = [1, 2];
        $attributeIds = [1, 2, 3, 4, 5];

        $massActionRepo->findCommonAttributeIds($productIds)->shouldBeCalled()->willReturn($attributeIds);

        $attGroupAccessRepo
            ->getGrantedAttributeGroupQB($user, Attributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn($subQB);

        $conditions = [
            'conditions' => ['unique' => 0],
            'filters'    => ['g.id'   => $subQB]
        ];
        $attRepo->findWithGroups($attributeIds, $conditions)->shouldBeCalled()->willReturn(['foo', 'bar']);

        $this->findCommonAttributes($productIds)->shouldReturn(['foo', 'bar']);
    }
}
