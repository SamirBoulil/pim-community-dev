<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Rule linked resource repository
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleLinkedResourceRepository extends EntityRepository implements RuleLinkedResourceRepositoryInterface
{
    /** @var string */
    protected $ruleLinkedResClass;

    /**
     * {@inheritdoc}
     */
    public function isResourceImpactedByRule($resourceId, $resourceName)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from($this->getClassName(), 'rlr')
            ->where('rlr.resourceName = :resource_name AND rlr.resourceId = :resource_id')
            ->setMaxResults(1)
            ->setParameters(
                array(
                    ':resource_name' => $resourceName,
                    ':resource_id'   => $resourceId
                )
            );

        $count = $qb->getQuery()->getScalarResult();

        return count($count) > 0;
    }
}
