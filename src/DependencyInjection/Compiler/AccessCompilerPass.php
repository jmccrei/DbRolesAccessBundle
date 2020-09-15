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
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AccessCompilerPass
 * @package Jmccrei\UserManagement\DependencyInjection\Compiler
 */
class AccessCompilerPass implements CompilerPassInterface
{
    use DirStructure;

    /**
     * Process the compiler
     *
     * Let's get the access yaml file and add our entries to the security.access_map
     *
     * @param ContainerBuilder $container
     */
    public function process( ContainerBuilder $container ): void
    {

        $accessFilename = ( ( $env = $_SERVER[ 'APP_ENV' ] ?? 'prod' ) === 'test' )
            ? Configuration::ACCESS_FILE_TEST
            : ( $env === 'dev'
                ? Configuration::ACCESS_FILE_DEV
                : Configuration::ACCESS_FILE );

        $accessMapDefinition = $container->getDefinition( 'security.access_map' );
        if ( !file_exists( $accessFile = $this->getConfigDir() . DIRECTORY_SEPARATOR . $accessFilename ) ) {
            return;
        }

        $yaml = Yaml::parse( file_get_contents( $accessFile ) );
        $yaml = empty( $yaml ) ? [] : $yaml;

        // lets loop through the yaml data and add access privileges
        foreach ( (array) $yaml as $idx => $access ) {
            $path    = $ip = $roles = $host = $channel = NULL;
            $methods = [];
            foreach ( [ 'path', 'ip', 'roles', 'host', 'channel' ] as $key ) {
                if ( isset( $access[ $key ] ) ) {
                    $$key = $access[ $key ];
                }
            }

            $matcher = $this->createRequestMatcher( $container, $path, $host, $methods ?? [], $ip );
            $accessMapDefinition->addMethodCall( 'add', [ $matcher, $roles, $channel ] );
        }
    }

    /**
     * Create a new request matcher
     *
     * @param ContainerBuilder $container
     * @param string|null      $path
     * @param string|null      $host
     * @param array|null       $methods
     * @param string|null      $ip
     * @param array|null       $attributes
     * @return Reference
     */
    protected function createRequestMatcher( ContainerBuilder $container, ?string $path = NULL, ?string $host = NULL, ?array $methods = [], ?string $ip = NULL, ?array $attributes = [] ): Reference
    {
        $methods   = array_map( 'strtoupper', (array) $methods );
        $arguments = [ $path, $host, $methods, $ip, $attributes ];
        while ( count( $arguments ) > 0 && !end( $arguments ) ) {
            array_pop( $arguments );
        }

        // get the unique id
        $id = 'security.request_matcher' . ContainerBuilder::hash( $arguments );

        // register the request matcher so we can actually use it
        $container->register( $id, 'Symfony\Component\HttpFoundation\RequestMatcher' )
            ->setPublic( FALSE )
            ->setArguments( $arguments );

        return new Reference( $id );
    }
}