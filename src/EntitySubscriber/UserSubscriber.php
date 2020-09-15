<?php
/** @noinspection PhpUnused */

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\EntitySubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Jmccrei\UserManagement\Entity\AbstractUser;
use Jmccrei\UserManagement\Manager\UserManager;
use Jmccrei\UserManagement\Traits\JmccreiUserManagementConfiguration;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserSubscriber
 * @package Jmccrei\UserManagement\EntitySubscriber
 */
class UserSubscriber implements EventSubscriberInterface
{
    use JmccreiUserManagementConfiguration;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * UserSubscriber constructor.
     * @param UserManager     $userManager
     * @param KernelInterface $kernel
     */
    public function __construct( UserManager $userManager, KernelInterface $kernel )
    {
        $this->userManager = $userManager;
        $this->setConfigFromKernel( $kernel );
    }

    /**
     * Get UserManager
     * @return UserManager
     */
    public function getUserManager(): UserManager
    {
        return $this->userManager;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist,
            Events::preUpdate
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata( LoadClassMetadataEventArgs $args ): void
    {
        $metadata   = $args->getClassMetadata();
        $entityName = $metadata->getName();
        if ( is_subclass_of( $entityName, AbstractUser::class ) ) {
            $manyToManyMapping = array_merge(
                $this->getMappingConfiguration( 'user', 'roles' ) ?? [],
                [
                    "fieldName"    => "systemRoles",
                    "targetEntity" => $this->config( 'role_class' )
                ]
            );

            $metadata->mapManyToMany( $manyToManyMapping );
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist( LifecycleEventArgs $args ): void
    {
        if ( !$this->supports( $user = $args->getObject() ) ) {
            return;
        }

        /** @var AbstractUser $user */
        $this->userManager->updateUser( $user, FALSE, FALSE );
    }

    /**
     * @param $entity
     * @return bool
     */
    public function supports( $entity ): bool
    {
        return $entity instanceof UserInterface;
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate( PreUpdateEventArgs $args ): void
    {
        if ( !$this->supports( $user = $args->getObject() ) ) {
            return;
        }

        /** @var AbstractUser $user */
        $this->userManager->updateUser( $user, FALSE, FALSE );
    }
}