<?php

namespace Puzzle\Admin\ExpertiseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PuzzleAdminExpertiseExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('admin_expertise', $config);
        $container->setParameter('admin_expertise.title', $config['title']);
        $container->setParameter('admin_expertise.description', $config['description']);
        $container->setParameter('admin_expertise.icon', $config['icon']);
        $container->setParameter('admin_expertise.roles', $config['roles']);
        $container->setParameter('admin_expertise.dirname', $config['dirname']);
    }
}
