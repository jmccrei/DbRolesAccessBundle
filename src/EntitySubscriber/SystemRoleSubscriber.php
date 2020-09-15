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
use Doctrine\ORM\Mapping\ClassMetadata;
use Jmccrei\UserManagement\Entity\AbstractSystemRole;
use Jmccrei\UserManagement\Entity\SystemRole;
use Jmccrei\UserManagement\Entity\SystemRoleInterface;
use Jmccrei\UserManagement\Manager\SystemRoleManager;
use Jmccrei\UserManagement\Traits\JmccreiUserManagementConfiguration;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class SystemRoleSubscriber
 * @package Jmccrei\UserManagement\EntitySubscriber
 */
class SystemRoleSubscriber implements EventSubscriberInterface
{
    use JmccreiUserManagementConfiguration;

    /**
     * @var SystemRoleManager
     */
    protected $systemRoleManager;

    /**
     * @var SystemRoleInterface|null
     */
    protected $systemRole;

    /**
     * SystemRoleSubscriber constructor.
     * @param SystemRoleManager $roleManager
     * @param KernelInterface   $kernel
     */
    public function __construct( SystemRoleManager $roleManager, KernelInterface $kernel )
    {
        $this->systemRoleManager = $roleManager;
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

        if ( is_subclass_of( $entityName, AbstractSystemRole::class ) ) {
            $this->mapParentField( $metadata );
            $this->mapChildrenField( $metadata );
            $this->mapUsersField( $metadata );
            $this->mapAccessField( $metadata );
        }
    }

    /**
     * @param ClassMetadata $metadata
     */
    protected function mapParentField( ClassMetadata $metadata ): void
    {
        $manyToOneMapping = array_merge(
            $this->getMappingConfiguration( 'role', 'parent' ),
            [
                'fieldName'    => 'parent',
                'targetEntity' => $this->config( 'role_class' ),
                'joinColumns'  => [
                    [
                        "name"                 => "parent_id",
                        "referencedColumnName" => "id",
                        "onDelete"             => "SET NULL"
                    ]
                ]
            ]
        );

        $metadata->mapManyToOne( $manyToOneMapping );
    }

    /**
     * @param ClassMetadata $metadata
     */
    protected function mapChildrenField( ClassMetadata $metadata ): void
    {
        $oneToMany = array_merge(
            $this->getMappingConfiguration( 'role', 'children' ),
            [
                'fieldName'    => 'children',
                'targetEntity' => $this->config( 'role_class' )
            ]
        );

        $metadata->mapOneToMany( $oneToMany );
    }

    /**
     * @param ClassMetadata $metadata
     */
    protected function mapUsersField( ClassMetadata $metadata ): void
    {
        $manyToManyMapping = array_merge(
            $this->getMappingConfiguration( 'role', 'users' ),
            [
                'fieldName'    => 'users',
                'targetEntity' => $this->config( 'user_class' )
            ]
        );

        $metadata->mapManyToMany( $manyToManyMapping );
    }

    /**
     * @param ClassMetadata $metadata
     */
    protected function mapAccessField( ClassMetadata $metadata ): void
    {
        $manyToManyMapping = array_merge(
            $this->getMappingConfiguration( 'role', 'access' ),
            [
                'fieldName'    => 'access',
                'targetEntity' => $this->config( 'access_class' )
            ]
        );

        $metadata->mapManyToMany( $manyToManyMapping );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist( LifecycleEventArgs $args ): void
    {
        if ( !$this->supports( $systemRole = $args->getObject() ) ) {
            return;
        }

        /** @var SystemRoleInterface $systemRole */
        $this->updateSystemRole( $systemRole );
    }

    /**
     * @param $entity
     * @return bool
     */
    public function supports( $entity ): bool
    {
        return $entity instanceof SystemRoleInterface;
    }

    /**
     * @param SystemRoleInterface $systemRole
     */
    protected function updateSystemRole( SystemRoleInterface $systemRole ): void
    {
        $this->systemRoleManager->updateRole( $systemRole, FALSE, FALSE );
        $this->systemRole = $systemRole;
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate( PreUpdateEventArgs $args ): void
    {
        if ( !$this->supports( $systemRole = $args->getObject() ) ) {
            return;
        }

        /** @var SystemRole $systemRole */
        $this->updateSystemRole( $systemRole );
    }

    /**
     * @param PostFlushEventArgs $args
     * @noinspection PhpUnusedParameterInspection
     */
    public function postFlush( PostFlushEventArgs $args ): void
    {
        if ( empty( $this->systemRole ) || !$this->supports( $this->systemRole ) ) {
            return;
        }

        $this->systemRoleManager->dumpSystemRoles();
    }
}