<?php

namespace PimEnterprise\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Enterprise Security extension
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PimEnterpriseSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('managers.yml');
        $loader->load('entities.yml');
        $loader->load('repositories.yml');
        $loader->load('voters.yml');
        $loader->load('subscribers.yml');
        $loader->load('event_listeners.yml');
        $loader->load('form_types.yml');
        $loader->load('context.yml');
    }
}
