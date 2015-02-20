<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\UnpublisherInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface;

/**
 * Product unpublisher
 *
 * @author    Julien Janvier <nicolas@akeneo.com>
 */
class ProductUnpublisher implements UnpublisherInterface
{
    /** @var PublishedAssociationRepositoryInterface */
    protected $publishedAssociationRepository;

    /** @var AssociationTypeRepository */
    protected $associationTypeRepository;

    /**
     * The constructor
     *
     * @param PublishedAssociationRepositoryInterface $publishedAssociationRepository
     * @param AssociationTypeRepository               $associationTypeRepository
     */
    public function __construct(
        PublishedAssociationRepositoryInterface $publishedAssociationRepository,
        AssociationTypeRepository $associationTypeRepository
    ) {
        $this->publishedAssociationRepository = $publishedAssociationRepository;
        $this->associationTypeRepository = $associationTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish($object, array $options = [])
    {
        $this->updateRelatedAssociations($object);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PublishedProductInterface;
    }

    /**
     * Update associations where the published product was referenced.
     *
     * @param PublishedProductInterface $published
     */
    protected function updateRelatedAssociations(PublishedProductInterface $published)
    {
        $nbAssociationsTypes = $this->associationTypeRepository->countAll();
        $this->publishedAssociationRepository->removePublishedProduct($published, $nbAssociationsTypes);
    }
}