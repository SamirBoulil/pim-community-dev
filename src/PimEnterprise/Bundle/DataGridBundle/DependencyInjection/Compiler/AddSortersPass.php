<?php

namespace PimEnterprise\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Add grid sorter types
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddSortersPass implements CompilerPassInterface
{
    /** @staticvar string */
    const SORTER_PROPOSITION_EXTENSION_ID = 'pimee_datagrid.extension.sorter.proposition_sorter';

    /** @staticvar string */
    const TAG_NAME = 'pimee_datagrid.extension.sorter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $propositionExtension = $container->getDefinition(self::SORTER_PROPOSITION_EXTENSION_ID);

        $filters = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($filters as $serviceId => $tags) {
            $tagAttrs = reset($tags);
            if (isset($tagAttrs['type']) === false) {
                throw new \InvalidArgumentException(
                    sprintf('The service %s must be configured with a type attribute', $serviceId)
                );
            }
            if ($propositionExtension) {
                $propositionExtension->addMethodCall('addSorter', array($tagAttrs['type'], new Reference($serviceId)));
            }
        }
    }
}
