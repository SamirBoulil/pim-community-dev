<?php

namespace PimEnterprise\Bundle\CatalogBundle\Persistence;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use PimEnterprise\Bundle\CatalogBundle\Factory\RevisionFactory;

/**
 * Store product through revisions
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RevisionPersister implements ProductPersister
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var RevisionFactory */
    protected $factory;

    /** @var ProductChangesProvider */
    protected $changesProvider;

    /**
     * @param ManagerRegistry          $registry
     * @param CompletenessManager      $completenessManager
     * @param SecurityContextInterface $securityContext
     * @param RevisionFactory          $factory
     * @param ProductChangesProvider   $changesProvider
     *
     */
    public function __construct(
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        SecurityContextInterface $securityContext,
        RevisionFactory $factory,
        ProductChangesProvider $changesProvider
    ) {
        $this->registry = $registry;
        $this->completenessManager = $completenessManager;
        $this->securityContext = $securityContext;
        $this->factory = $factory;
        $this->changesProvider = $changesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(ProductInterface $product, array $options)
    {
        if (true /** Condition based on user right to edit the product */) {
            $this->persistProduct($product, $options);
        } else {
            $this->persistRevision($product);
        }
    }

    /**
     * Persist the product
     *
     * @param ProductInterface $product
     * @param array            $options
     */
    private function persistProduct(ProductInterface $product, array $options)
    {
        $options = array_merge(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
            ],
            $options
        );

        $manager = $this->registry->getManagerForClass(get_class($product));
        $manager->persist($product);

        if ($options['schedule'] || $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if ($options['recalculate'] || $options['flush']) {
            $manager->flush();
        }

        if ($options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
    }

    /**
     * Persist a revision of the product
     *
     * @param ProductInterface $product
     */
    private function persistRevision(ProductInterface $product)
    {
        $revision = $this->factory->createRevision(
            $product,
            $this->getUser(),
            $this->changesProvider->computeNewValues($product)
        );

        // TODO	(2014-05-05 14:35 by Gildas): Find a way to prevent product modification saving
        // $this->registry->getManagerForClass(get_class($product))->getUnitOfWork()->clearEntityChangeSet(spl_object_hash($product));

        $manager = $this->registry->getManagerForClass(get_class($revision));
        $manager->persist($revision);
        $manager->flush();
    }

    /**
     * Get user from the security context
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws LogicException
     */
    private function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            throw new \LogicException('No user logged in');
        }

        if (!is_object($user = $token->getUser())) {
            throw new \LogicException('No user logged in');
        }

        return $user;
    }
}
