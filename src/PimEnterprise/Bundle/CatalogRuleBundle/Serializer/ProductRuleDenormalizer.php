<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalize product rules.
 *
 * TODO: should be renamed rule denormalizer
 * TODO: should be moved in RuleEngine
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleDenormalizer implements DenormalizerInterface
{
    /** @var DenormalizerInterface */
    protected $contentDernomalizer;

    /** @var string */
    protected $ruleClass;

    /** @var string */
    protected $definitionClass;

    /**
     * @param DenormalizerInterface $contentDernomalizer
     * @param string                $ruleClass
     * @param string                $definitionClass
     */
    public function __construct(
        DenormalizerInterface $contentDernomalizer,
        $ruleClass,
        $definitionClass
    ) {
        $this->contentDernomalizer = $contentDernomalizer;
        $this->ruleClass = $ruleClass;
        $this->definitionClass = $definitionClass;
    }

    /**
     * {@inheritdoc}
     *
     * @return RuleDefinitionInterface
     *
     * @throws \LogicException
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var RuleInterface $rule */
        $rule = $this->getObject($context);
        $rule->setCode($data['code']);
        $rule->setType('product');

        if (isset($data['priority'])) {
            $rule->setPriority((int) $data['priority']);
        }

        $content = $this->contentDernomalizer->denormalize($rule->getContent(), $format, $context);

        foreach ($content['conditions'] as $condition) {
            $rule->addCondition($condition);
        }
        foreach ($content['actions'] as $action) {
            $rule->addAction($action);
        }

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->ruleClass === $type;
    }

    /**
     * @param array $context
     *
     * @return RuleDefinitionInterface
     */
    protected function getObject(array $context)
    {
        if (isset($context['object'])) {
            return $context['object'];
        }

        if (isset($context['definitionObject'])) {
            $definition = $context['definitionObject'];
        } else {
            $definition = new $this->definitionClass();
        }

        return new $this->ruleClass($definition);
    }
}
