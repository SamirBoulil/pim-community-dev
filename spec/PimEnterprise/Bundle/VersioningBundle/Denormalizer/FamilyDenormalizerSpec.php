<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;

class FamilyDenormalizerSpec extends ObjectBehavior
{
    const ENTITY_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Family';
    const FORMAT_CSV   = 'csv';

    function let(ManagerRegistry $registry, FamilyRepository $repository)
    {
        $registry->getRepository(self::ENTITY_CLASS)->willReturn($repository);

        $this->beConstructedWith($registry, self::ENTITY_CLASS);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_in_csv_of_family()
    {
        $this->supportsDenormalization([], self::ENTITY_CLASS, self::FORMAT_CSV)->shouldBe(true);

        $this->supportsDenormalization(
            [],
            Argument::not(self::ENTITY_CLASS),
            self::FORMAT_CSV
        )->shouldBe(false);

        $this->supportsDenormalization(
            [],
            self::ENTITY_CLASS,
            Argument::not(self::FORMAT_CSV)
        )->shouldBe(false);

        $this->supportsDenormalization(
            [],
            Argument::not(self::ENTITY_CLASS),
            Argument::not(self::FORMAT_CSV)
        )->shouldBe(false);
    }

    function it_denormalizes_family($repository, Family $family)
    {
        $repository->findByReference('foo')->willReturn($family);

        $this->denormalize('foo', self::ENTITY_CLASS, self::FORMAT_CSV)->shouldReturn($family);
    }

    function it_throws_an_exception_if_family_is_unknown($repository)
    {
        $repository->findByReference('foo')->willReturn(false);

        $this->shouldThrow(
            new \Exception(
                sprintf('Entity "%s" with identifier "%s" not found', self::ENTITY_CLASS, 'foo')
            )
        )->during('denormalize', ['foo', self::ENTITY_CLASS, self::FORMAT_CSV]);
    }
}
