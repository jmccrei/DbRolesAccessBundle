<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Manager\Traits;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use LogicException;

/**
 * Trait Doctrine
 * @package Jmccrei\UserManagement\Manager\Traits
 */
trait Doctrine
{
    /**
     * Get a repository
     * @param string      $repositoryName
     * @param string|null $managerName
     * @return ObjectRepository
     */
    public function getRepository( string $repositoryName, string $managerName = NULL ): ObjectRepository
    {
        return $this->getObjectManager( $managerName )
            ->getRepository( $repositoryName );
    }

    /**
     * @param string|null $name
     * @return ObjectManager
     */
    public function getObjectManager( ?string $name = NULL ): ObjectManager
    {
        return $this->getDoctrine()->getManager( $name );
    }

    /**
     * Get doctrine
     * Assume methods exist:
     *  $this->getContainer()
     *
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        if ( !$this->getContainer()->has( 'doctrine' ) ) {
            throw new LogicException( 'The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".' );
        }

        return $this->getContainer()->get( 'doctrine' );
    }
}