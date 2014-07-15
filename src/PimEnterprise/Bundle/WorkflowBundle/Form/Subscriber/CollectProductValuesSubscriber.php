<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Media;
use PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\ChangesCollector;

/**
 * A collector of changes that a client is sending to a product edit form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CollectProductValuesSubscriber implements EventSubscriberInterface
{
    /** @var ChangesCollector */
    protected $collector;

    /** @var MediaManager */
    protected $mediaManager;

    /**
     * @param SecurityContextInterface $securityContext
     * @param MediaManager             $mediaManager
     */
    public function __construct(
        ChangesCollector $collector,
        MediaManager $mediaManager
    ) {
        $this->collector = $collector;
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'collect',
        ];
    }

    /**
     * Collect changes that client sent to the product values
     *
     * @param FormEvent $event
     */
    public function collect(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!array_key_exists('values', $data)) {
            return;
        }

        foreach ($data['values'] as $key => $changes) {
            if (isset($changes['media']['file']) && $changes['media']['file'] instanceof UploadedFile) {
                $media = new Media();
                $media->setFile($changes['media']['file']);
                $this->mediaManager->handle($media, 'proposition-' . md5(time() . uniqid()));

                $data['values'][$key]['media']['filename'] = $media->getFilename();
                $data['values'][$key]['media']['originalFilename'] = $media->getOriginalFilename();
                $data['values'][$key]['media']['filePath'] = $media->getFilePath();
                $data['values'][$key]['media']['mimeType'] = $media->getMimeType();
                $data['values'][$key]['media']['size'] = $changes['media']['file']->getClientSize();

                unset($data['values'][$key]['media']['file']);
            }
        }

        $this->collector->setData($data);
    }
}
