<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Enterprise DataGrid extension
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimEnterpriseDataGridExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('configurators.yml');
        $loader->load('event_listeners.yml');
        $loader->load('mass_actions.yml');
        $loader->load('hydrators.yml');
        $loader->load('data_sources.yml');
        $loader->load('hydrators.yml');
        $loader->load('extensions.yml');
        $loader->load('controllers.yml');
        $loader->load('helpers.yml');
        $loader->load('managers.yml');

        $storageDriver = $container->getParameter('pim_catalog_product_storage_driver');
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load(sprintf('storage_driver/%s.yml', $storageDriver));
    }
}