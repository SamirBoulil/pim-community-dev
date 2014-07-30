<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\CatalogBundle\Manager\MediaManager;

class MediaDenormalizerSpec extends ObjectBehavior
{
    function let(MediaManager $manager)
    {
        $this->beConstructedWith(
            ['pim_catalog_image', 'pim_catalog_file'],
            $manager
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_csv_denormalization_of_image()
    {
        $this->supportsDenormalization('preview.jpg', 'pim_catalog_image', 'csv')->shouldBe(true);
    }

    function it_supports_csv_denormalization_of_file()
    {
        $this->supportsDenormalization('readme.md', 'pim_catalog_file', 'csv')->shouldBe(true);
    }

    function it_does_not_supports_csv_denormalization_of_metric()
    {
        $this->supportsDenormalization('readme.md', 'pim_catalog_metric', 'csv')->shouldBe(false);
    }

    function it_does_not_supports_xml_denormalization_of_image()
    {
        $this->supportsDenormalization('readme.md', 'pim_catalog_image', 'xml')->shouldBe(false);
    }

    function it_dernomalizes_media(
        $manager,
        $factory,
        Model\AbstractProductMedia $media
    ) {
        $manager->createFromFilename('preview.jpg')->willReturn($media);

        $this->denormalize('preview.jpg', 'pim_catalog_image')->shouldReturn($media);
    }

    function it_does_not_create_media_for_empty_filename(
        $manager,
        $factory,
        Model\AbstractProductMedia $media
    ) {
        $this->denormalize(null, 'pim_catalog_image')->shouldReturn(null);
        $this->denormalize('', 'pim_catalog_image')->shouldReturn(null);
    }
}
