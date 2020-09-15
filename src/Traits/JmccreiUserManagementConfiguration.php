<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Traits;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Trait JmccreiUserManagementConfiguration
 * @package Jmccrei\UserManagement\Traits
 */
trait JmccreiUserManagementConfiguration
{
    /**
     * @var ParameterBag
     */
    protected $configuration;

    /**
     * @param KernelInterface $kernel
     * @return $this
     */
    protected function setConfigFromKernel( KernelInterface $kernel )
    {
        $this->configuration = new ParameterBag(
            $kernel->getContainer()
                ->getParameter( 'jmccrei_user_management' )
        );

        return $this;
    }

    /**
     * @param array $configuration
     * @return $this
     */
    protected function setConfigFromArray( array $configuration )
    {
        $this->configuration = new ParameterBag( $configuration );

        return $this;
    }

    /**
     * Get a specified configuration mapping
     *
     * @param string      $key
     * @param string|null $subKey
     * @return mixed|null
     */
    protected function getMappingConfiguration( string $key, string $subKey = NULL )
    {
        $mappings = $this->config( 'mappings' );

        if ( empty( $subKey ) || NULL === $mappings[ $key ] ?? NULL ) {
            return $mappings[ $key ] ?? NULL;
        }

        return $mappings[ $key ][ $subKey ] ?? NULL;
    }

    /**
     * @param string $key
     * @param null   $defaultValue
     * @return mixed|null
     */
    protected function config( string $key, $defaultValue = NULL )
    {
        return $this->configuration->get( $key, $defaultValue );
    }
}