<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleRelationInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Repository\RuleRelationRepositoryInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Class RuleRelationManager
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleRelationManager
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var RuleRelationRepositoryInterface */
    protected $ruleRelationRepo;

    /** @var string */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param RuleRelationRepositoryInterface $ruleRelationRepo
     * @param AttributeRepository             $attributeRepository
     * @param string                          $attributeClass
     */
    public function __construct(
        RuleRelationRepositoryInterface $ruleRelationRepo,
        AttributeRepository $attributeRepository,
        $attributeClass
    ) {
        $this->ruleRelationRepo = $ruleRelationRepo;
        $this->attributeRepository = $attributeRepository;
        $this->attributeClass = $attributeClass;
    }

    /**
     * Returns all impacted attributes
     *
     * @param array $actions
     *
     * @return array
     */
    public function getImpactedAttributes(array $actions)
    {
        $fields = [];
        foreach ($actions as $action) {
            if ($action instanceof ProductCopyValueActionInterface) {
                $fields[] = $action->getToField();
            } elseif ($action instanceof ProductSetValueActionInterface) {
                $fields[] = $action->getField();
            }
        }

        // TODO : check memory leak (argument var is the sam&e than result)
        $fields = array_unique($fields);

        $impactedAttributes = [];
        foreach ($fields as $field) {
            $impactedAttributes[] = $this->attributeRepository->findByReference($field);
        }

        $impactedAttributes = array_filter($impactedAttributes);

        return $impactedAttributes;
    }

    /**
     * @param mixed $attributeId
     *
     * @return bool
     */
    public function isAttributeImpacted($attributeId)
    {
        return $this->isResourceImpacted($attributeId, $this->attributeClass);
    }

    /**
     * @param mixed  $resourceId
     * @param string $resourceName
     *
     * @return bool
     */
    public function isResourceImpacted($resourceId, $resourceName)
    {
        $resourceName = $this->resolveResourceName($resourceName);

        return $this->ruleRelationRepo->isResourceImpactedByRule($resourceId, $resourceName);
    }

    /**
     * @param int $attributeId
     *
     * @return RuleDefinitionInterface[]
     */
    public function getRulesForAttribute($attributeId)
    {
        return $this->getRulesForResource($attributeId, $this->attributeClass);
    }

    /**
     * Get rules related to a resource
     *
     * @param integer $resourceId
     * @param string  $resourceName
     *
     * @return RuleDefinitionInterface[]
     */
    public function getRulesForResource($resourceId, $resourceName)
    {
        $resourceName = $this->resolveResourceName($resourceName);
        $ruleRelations = $this->getRuleRelationsForResource($resourceId, $resourceName);

        $rules = [];
        foreach ($ruleRelations as $ruleRelation) {
            $rules[] = $ruleRelation->getRuleDefinition();
        }

        return $rules;
    }

    /**
     * Get rules relations
     *
     * @param string $resourceId
     * @param string $resourceName
     *
     * @return RuleRelationInterface[]
     */
    protected function getRuleRelationsForResource($resourceId, $resourceName)
    {
        return $this->ruleRelationRepo->findBy([
            'resourceId'   => $resourceId,
            'resourceName' => $resourceName
        ]);
    }

    /**
     * @param $resourceName
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function resolveResourceName($resourceName)
    {
        switch ($resourceName) {
            case 'attribute':
            case $this->attributeClass:
                $type = $this->attributeClass;
                break;
            default:
                $type = null;
        }

        if (null === $type) {
            throw new \InvalidArgumentException(sprintf('The resource name "%s" can not be resolved.', $resourceName));
        }

        return $type;
    }
}
