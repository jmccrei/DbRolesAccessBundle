<?php
/** @noinspection PhpUnused */

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\DependencyInjection;

use Jmccrei\UserManagement\Entity\Access;
use Jmccrei\UserManagement\Entity\SystemRole;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Jmccrei\UserManagement\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    const SYSTEM_ROLE_FILE      = 'system_roles.yaml';
    const SYSTEM_ROLE_FILE_TEST = 'system_roles_test.yaml';
    const SYSTEM_ROLE_FILE_DEV  = 'system_roles_dev.yaml';
    const ACCESS_FILE           = 'access.yaml';
    const ACCESS_FILE_TEST      = 'access_test.yaml';
    const ACCESS_FILE_DEV       = 'access_dev.yaml';

    /**
     * Get the configuration tree builder
     *
     * @return TreeBuilder|void
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder( 'jmccrei_user_management' );

        $tree->getRootNode()
            ->children()
            ->scalarNode( 'login_route' )->defaultValue( 'app_login' )->end()
            ->booleanNode( 'referrer_redirect' )->defaultTrue()->end()
            ->scalarNode( 'successful_redirect_route' )->defaultValue( 'site_index' )->end()
            ->scalarNode( 'user_class' )->isRequired()->end()
            ->scalarNode( 'role_class' )->defaultValue( SystemRole::class )->end()
            ->scalarNode( 'access_class' )->defaultValue( Access::class )->end()
            ->scalarNode( 'invalid_credentials_message' )->defaultValue( 'Invalid security credentials supplied' )->end();

        $this->registerEntityMappings( $tree );

        return $tree;
    }

    /**
     * @param TreeBuilder $tree
     * @formatter:off
     * @noinspection PhpPossiblePolymorphicInvocationInspection*/
    protected function registerEntityMappings( TreeBuilder $tree )
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $tree->getRootNode()
            ->children()
                ->arrayNode( 'mappings' )
                    ->children()
                        ->arrayNode( 'user' )
                            ->children()
                                ->arrayNode( 'roles' )
                                    ->children()
                                        ->arrayNode( 'cascade' )
                                            ->scalarPrototype()->defaultNull()->end()
                                        ->end()
                                        ->scalarNode( 'inversedBy' )->defaultValue( 'users' )->end()
                                        ->arrayNode( 'joinTable' )
                                            ->children()
                                                ->scalarNode( 'name' )->defaultNull()->end()
                                                ->arrayNode( 'joinColumns' )
                                                    ->arrayPrototype()
                                                        ->children()
                                                            ->scalarNode( 'name' )->defaultValue( 'user_id' )->end()
                                                            ->scalarNode( 'referencedColumnName' )->defaultValue( 'id' )->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                                ->arrayNode( 'inverseJoinColumns' )
                                                    ->arrayPrototype()
                                                        ->children()
                                                            ->scalarNode( 'name' )->defaultValue( 'role_id' )->end()
                                                            ->scalarNode( 'referencedColumnName' )->defaultValue( 'id' )->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode( 'access' )
                        ->children()
                            ->arrayNode( 'roles' )
                                ->children()
                                    ->arrayNode( 'cascade' )->scalarPrototype()->defaultValue( 'persist' )->end()->end()
                                    ->scalarNode( 'inversedBy' )->defaultValue( 'access' )->end()
                                    ->arrayNode( 'joinTable' )
                                        ->children()
                                            ->scalarNode( 'name' )->defaultNull()->end()
                                            ->arrayNode( 'joinColumns' )
                                                ->arrayPrototype()
                                                    ->children()
                                                        ->scalarNode( 'name' )->defaultValue( 'access_id' )->end()
                                                        ->scalarNode( 'referencedColumnName' )->defaultValue( 'id' )->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode( 'inverseJoinColumns' )
                                                ->arrayPrototype()
                                                    ->children()
                                                        ->scalarNode( 'name' )->defaultValue( 'role_id' )->end()
                                                        ->scalarNode( 'referencedColumnName' )->defaultValue( 'id' )->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode( 'role' )
                        ->children()
                            ->arrayNode( 'parent' )
                                ->children()
                                    ->arrayNode( 'cascade' )->scalarPrototype()->defaultValue( 'persist' )->end()->end()
                                    ->scalarNode( 'inversedBy' )->defaultValue( 'children' )->end()
                                ->end()
                            ->end()
                            ->arrayNode( 'children' )
                                ->children()
                                    ->scalarNode( 'mappedBy' )->defaultValue( 'parent' )->end()
                                ->end()
                            ->end()
                            ->arrayNode( 'users' )
                                ->children()
                                    ->arrayNode( 'cascade' )->scalarPrototype()->defaultValue( 'persist' )->end()->end()
                                    ->scalarNode( 'mappedBy' )->defaultValue( 'systemRoles' )->end()
                                ->end()
                            ->end()
                            ->arrayNode( 'access' )
                                ->children()
                                    ->arrayNode( 'cascade' )->scalarPrototype()->defaultValue( 'persist' )->end()->end()
                                    ->scalarNode( 'mappedBy' )->defaultValue( 'systemRoles' )->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}