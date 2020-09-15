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
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Jmccrei\UserManagement\Entity\AbstractAccess;
use Jmccrei\UserManagement\Entity\AccessInterface;
use Jmccrei\UserManagement\Manager\AccessManager;
use Jmccrei\UserManagement\Traits\JmccreiUserManagementConfiguration;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AccessSubscriber
 * @package Jmccrei\UserManagement\EntitySubscriber
 */
class AccessSubscriber implements EventSubscriberInterface
{

    use JmccreiUserManagementConfiguration;

    /**
     * @var AccessManager
     */
    protected $accessManager;

    /**
     * @var AccessInterface|null
     */
    protected $access;

    /**
     * AccessSubscriber constructor.
     * @param AccessManager   $accessManager
     * @param KernelInterface $kernel
     */
    public function __construct( AccessManager $accessManager, KernelInterface $kernel )
    {
        $this->accessManager = $accessManager;
        $this->setConfigFromKernel( $kernel );
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist,
            Events::preUpdate,
            Events::postFlush
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $args
     * @noinspection PhpUnused
     */
    public function loadClassMetadata( LoadClassMetadataEventArgs $args ): void
    {
        $metadata   = $args->getClassMetadata();
        $entityName = $metadata->getName();

        if ( is_subclass_of( $entityName, AbstractAccess::class ) ) {
            $manyToManyMapping = array_merge(
                $this->getMappingConfiguration( 'access', 'roles' ),
                [
                    "fieldName"    => "systemRoles",
                    "targetEntity" => $this->config( 'role_class' ),
                    "fetch"        => "EAGER"
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
        if ( !$this->supports( $access = $args->getObject() ) ) {
            return;
        }

        /** @var AccessInterface $access */
        $this->updateAccess( $access );
    }

    /**
     * @param $entity
     * @return bool
     */
    public function supports( $entity ): bool
    {
        return $entity instanceof AccessInterface;
    }

    /**
     * @param AccessInterface $access
     */
    protected function updateAccess( AccessInterface $access ): void
    {
        $this->access = $access;
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate( PreUpdateEventArgs $args ): void
    {
        if ( !$this->supports( $access = $args->getObject() ) ) {
            return;
        }

        /** @var AccessInterface $access */
        $this->updateAccess( $access );
    }

    /**
     * @param PostFlushEventArgs $args
     * @noinspection PhpUnusedParameterInspection
     */
    public function postFlush( PostFlushEventArgs $args ): void
    {
        if ( empty( $this->access ) || !$this->supports( $this->access ) ) {
            return;
        }

        $this->accessManager->dumpAccesses();
    }
}