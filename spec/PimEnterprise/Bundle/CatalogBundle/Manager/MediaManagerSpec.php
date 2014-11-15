<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Gaufrette\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;

class MediaManagerSpec extends ObjectBehavior
{
    function let(Filesystem $fs, MediaFactory $factory)
    {
        $this->beConstructedWith($fs, '/tmp/pim-ee', $factory);
    }

    function it_loads_file_into_a_media($fs, $factory, ProductMediaInterface $media)
    {
        $fs->has('preview.jpg')->willReturn(true);
        $fs->mimeType('preview.jpg')->willReturn('image/jpeg');

        $factory->createMedia()->willReturn($media);
        $media->setOriginalFilename('preview.jpg')->shouldBeCalled();
        $media->setFilename('preview.jpg')->shouldBeCalled();
        $media->setFilePath('/tmp/pim-ee/preview.jpg')->shouldBeCalled();
        $media->setMimeType('image/jpeg')->shouldBeCalled();

        $this->createFromFilename('preview.jpg')->shouldReturn($media);
    }

    function its_load_method_throw_exception_when_file_does_not_exist($fs, ProductMediaInterface $media)
    {
        $fs->has('readme.md')->willReturn(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('File "/tmp/pim-ee/readme.md" does not exist'))
            ->duringCreateFromFilename('readme.md');
    }
}
