<?php

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ObjectRepository;
use Jmccrei\UserManagement\Entity\AbstractUser;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use function is_subclass_of;

/**
 * Class UserProvider
 * @package Jmccrei\UserManagement\Provider
 */
class UserProviderOld implements UserProviderInterface, UserLoaderInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * UserProvider constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct( EntityManagerInterface $entityManager )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UserInterface $user
     * @return mixed|null|UserInterface
     * @throws NoResultException
     */
    public function refreshUser( UserInterface $user )
    {
        $class = get_class( $user );

        if ( !$this->supportsClass( $class ) ) {
            throw new UnsupportedUserException( sprintf( 'Instance of "%s" is not supported.', $class ) );
        }

        /** @var AbstractUser $output */
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $output = $this->loadUserByUsername( $user->getEmail() );
        if ( $output === NULL ) {
            throw new NoResultException();
        }

        $output->addRole( 'ROLE_USER' );

        return $output;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass( string $class )
    {
        return AbstractUser::class == $class
            || is_subclass_of( $class, AbstractUser::class );
    }

    /**
     * @param string $slug
     * @return mixed|null|UserInterface
     */
    public function loadUserByUsernameOrEmail( string $slug )
    {
        $repository = $this->getUserRepository();

        foreach ( [ 'username', 'email' ] as $which ) {
            if ( !empty( $user = $repository->findOneBy( [ $which => $slug ] ) ) ) {
                return $user;
            }
        }

        return NULL;
    }

    /**
     * @param string $username
     * @return mixed|UserInterface|null
     */
    public function loadUserByUsername( string $username )
    {
        return $this->loadUserByUsernameOrEmail( $username );
    }

    /**
     * @param null|string $className
     * @return ObjectRepository
     */

    public function getUserRepository( ?string $className = NULL )
    {
        return $this->entityManager->getRepository( $className ?? AbstractUser::class );
    }
}