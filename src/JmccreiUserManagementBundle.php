<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement;

use Jmccrei\UserManagement\DependencyInjection\Compiler\AccessCompilerPass;
use Jmccrei\UserManagement\DependencyInjection\Compiler\SystemRolesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class JmccreiUserManagementBundle
 * @package Jmccrei\UserManagement
 */
class JmccreiUserManagementBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );

        $container->addCompilerPass( new SystemRolesCompilerPass() );
        $container->addCompilerPass( new AccessCompilerPass() );
    }
}