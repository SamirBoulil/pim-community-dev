<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Loader;

use PimEnterprise\Bundle\ProductRuleBundle\Model\ProductRule;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInstanceInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Loader\LoaderInterface;

class ProductRuleLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(RuleInstanceInterface $instance)
    {
        $rule = new ProductRule();

        // load expression from content
        $jsonContent = $instance->getContent();
        $content = json_decode($jsonContent, true);
        $expression = $content['expression'];

        // TODO : load/transform from expression to QB
        $rule->setExpression($expression);

        // TODO : load actions, they may be in expression too
        $actions = $content['actions'];

        // use a ProductRuleBuilder

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInstanceInterface $instance)
    {
        return $instance->getRuleFQCN() === 'PimEnterprise\Bundle\ProductRuleBundle\Model\ProductRule';
    }
}
