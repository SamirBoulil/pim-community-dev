<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Abstract compiler pass that helps to find and order tagged services by priority.
 * TODO: this is generic and could be put somewhere else
 *
 * @author Julien Janvier <julien.janvier@gmail.com>
 */
abstract class AbstractOrderedPass implements CompilerPassInterface
{
    /**
     * Returns an array of service references for a specified tag name
     *
     * @param ContainerBuilder $container
     * @param string           $tagName
     * @param int              $defaultPriority
     *
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function findAndSortTaggedServices(ContainerBuilder $container, $tagName, $defaultPriority = 0)
    {
        $services = $container->findTaggedServiceIds($tagName);

        $sortedServices = array();
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : $defaultPriority;
                $sortedServices[$priority][] = new Reference($serviceId);
            }
        }
        krsort($sortedServices);

        return count($sortedServices) > 0 ? call_user_func_array('array_merge', $sortedServices) : [];
    }
}
