<?php
/** @noinspection PhpUnused */

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class JmccreiUserManagementExtension
 * @package Jmccrei\UserManagement\DependencyInjection
 */
class JmccreiUserManagementExtension extends Extension
{
    /**
     * Load configuration
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load( array $configs, ContainerBuilder $container ): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(
                implode( DIRECTORY_SEPARATOR, [
                    __DIR__,
                    '..',
                    'Resources',
                    'config'
                ] )
            )
        );

        // load our configuration settings
        $loader->load( 'services.yaml' );
        $loader->load( 'jmccrei.yaml' );

        // get and process the configuration
        $configuration = new Configuration();
        $finalConfig   = $this->processConfiguration( $configuration, $configs );

        // if the container has properly loaded the resources/config/jmccrei.yaml
        // then let's use that as our base config and overwrite with what was
        // given from the overrides
        if ( $container->hasParameter( 'jmccrei_user_management' ) ) {
            $finalConfig = array_replace_recursive(
                (array) $container->getParameter( 'jmccrei_user_management' ),
                $finalConfig
            );
        }

        $container->setParameter( 'jmccrei_user_management', $finalConfig );
    }
}