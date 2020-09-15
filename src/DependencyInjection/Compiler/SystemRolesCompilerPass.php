<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

/** @noinspection PhpUnused */
declare( strict_types = 1 );

namespace Jmccrei\UserManagement\DependencyInjection\Compiler;

use Jmccrei\UserManagement\DependencyInjection\Configuration;
use Jmccrei\UserManagement\Traits\DirStructure;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SystemRolesCompilerPass
 * @package Jmccrei\UserManagement\DependencyInjection\Compiler
 */
class SystemRolesCompilerPass implements CompilerPassInterface
{
    use  DirStructure;

    /**
     * Process the compiler pass
     *
     * Update the security.role_hierarchy.roles with our roles.yaml
     *
     * @param ContainerBuilder $container
     */
    public function process( ContainerBuilder $container ): void
    {
        $systemRoleFile = ( ( $env = $_SERVER[ 'APP_ENV' ] ?? 'prod' ) === 'test' )
            ? Configuration::SYSTEM_ROLE_FILE_TEST
            : ( $env === 'dev'
                ? Configuration::SYSTEM_ROLE_FILE_DEV
                : Configuration::SYSTEM_ROLE_FILE );

        if ( !file_exists( $file = $this->getConfigDir() . DIRECTORY_SEPARATOR . $systemRoleFile ) ) {
            return;
        }

        $yaml = Yaml::parse( file_get_contents( $file ) );
        $yaml = empty( $yaml ) ? [] : $yaml;

        $container->setParameter( 'security.role_hierarchy.roles', $yaml );
    }
}