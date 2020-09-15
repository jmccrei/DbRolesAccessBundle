<?php

namespace Jmccrei\UserManagement\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Jmccrei\UserManagement\Entity\AbstractUser;
use Jmccrei\UserManagement\Entity\AccessInterface;
use Jmccrei\UserManagement\Entity\SystemRoleInterface;
use Jmccrei\UserManagement\Traits\JmccreiUserManagementConfiguration;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AppFixtures
 * @package Jmccrei\UserManagement\DataFixtures
 */
class AppFixtures extends Fixture implements FixtureGroupInterface
{
    const SYSTEM_ROLES = [
        [ 'name' => 'User' ],
        [ 'name' => 'Member', 'parent' => 'User' ],
        [ 'name' => 'Api', 'parent' => 'Member' ],
        [ 'name' => 'Administrator', 'parent' => 'Member', 'alias' => 'Admin' ],
        [ 'name' => 'Super Administrator', 'parent' => 'Administrator', 'alias' => 'Super Admin' ],
        [ 'name' => 'Developer', 'parent' => 'Member', 'alias' => 'Dev' ]
    ];

    const ACCESS = [
        [ 'path' => '^/', 'anonymous' => TRUE ],
        [ 'path' => '^/login', 'anonymous' => TRUE, 'methods' => [ 'GET', 'POST' ] ],
        [ 'path' => '^/logout', 'anonymous' => TRUE, 'methods' => [ 'GET' ] ],
        [ 'path' => '^/api', 'anonymous' => FALSE, 'roles' => [ 'ROLE_API' ] ],
        [ 'path' => '^/api/login', 'anonymous' => TRUE, 'methods' => [ 'GET', 'POST' ] ],
        [ 'path' => '^/api/login_check', 'anonymous' => TRUE, 'methods' => [ 'GET', 'POST' ] ],
        [ 'path' => '^/dev', 'anonymous' => FALSE, 'roles' => [ 'ROLE_DEVELOPER' ] ],
        [ 'path' => '^/admin', 'anonymous' => FALSE, 'roles' => [ 'ROLE_ADMINISTRATOR' ] ]
    ];

    /**
     * UserFixtures constructor.
     * @param KernelInterface $kernel
     */
    public function __construct( KernelInterface $kernel )
    {
        $this->setConfigFromKernel( $kernel );
    }

    use JmccreiUserManagementConfiguration;

    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return [ 'jmccrei_user_management' ];
    }

    /**
     * Load a default admin user
     *
     * @param ObjectManager $manager
     */
    public function load( ObjectManager $manager ): void
    {
        $this->loadSystemRoles( $manager );
        $this->loadUsers( $manager );
        $this->loadAccess( $manager );
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadSystemRoles( ObjectManager $manager ): void
    {
        $systemRoleClass = $this->config( 'role_class' );
        $systemRolesData = self::SYSTEM_ROLES;

        foreach ( $systemRolesData as &$roleArray ) {
            if ( empty( $this->getSystemRole( $manager, $roleArray[ 'name' ] ) ) ) {
                /** @var SystemRoleInterface $systemRole */
                $systemRole                = new $systemRoleClass();
                $roleArray[ 'systemRole' ] = $systemRole;

                foreach ( $roleArray as $key => $value ) {
                    if ( method_exists( $systemRole, $method = 'set' . ucfirst( $key ) ) && $key !== 'parent' ) {
                        $systemRole->$method( $value );
                    } else if ( $key === 'parent' ) {
                        foreach ( $systemRolesData as $rData ) {
                            if ( $rData[ 'name' ] === $value && !empty( $rData[ 'systemRole' ] ) && $rData[ 'systemRole' ] instanceof SystemRoleInterface ) {
                                $systemRole->setParent( $rData[ 'systemRole' ] );
                            }
                        }
                    }
                }

                $manager->persist( $systemRole );
                $manager->flush();
            }
        }
    }

    /**
     * @param ObjectManager $manager
     * @param string|null   $name
     * @param string|null   $roleName
     * @return SystemRoleInterface|null
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    private function getSystemRole( ObjectManager $manager, string $name = NULL, string $roleName = NULL ): ?SystemRoleInterface
    {
        if ( !empty( $roleName ) ) {
            return $manager->getRepository( $this->config( 'role_class' ) )->findOneBy( [ 'roleName' => $roleName ] );
        }

        /** @return SystemRoleInterface|null */
        return $manager->getRepository( $this->config( 'role_class' ) )->findOneBy( [ 'name' => $name ] );
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadUsers( ObjectManager $manager ): void
    {
        if ( empty( $admin = $this->getAdminUser( $manager ) ) ) {
            // no admin user
            $userClass = $this->config( 'user_class' );
            /** @var AbstractUser $admin */
            $admin = new $userClass();
            $admin->setUsername( 'admin@domain.com' )
                ->setEmail( 'admin@domain.com' )
                ->addRole( 'ROLE_SUPER_ADMINISTRATOR' )
                ->setPlainPassword( 'AdminPassword' );

            $manager->persist( $admin );
            $manager->flush();
        }
    }

    /**
     * Get the admin user
     *
     * @param ObjectManager $manager
     * @return AbstractUser|null
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getAdminUser( ObjectManager $manager ): ?AbstractUser
    {
        return $manager->getRepository( $this->config( 'user_class' ) )->findOneBy( [ 'email' => 'admin@domain.com' ] );
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadAccess( ObjectManager $manager ): void
    {
        $accessClass = $this->config( 'access_class' );
        $accessData  = self::ACCESS;

        foreach ( $accessData as $accessArray ) {
            if ( empty( $this->getAccess( $manager, $accessArray[ 'path' ] ) ) ) {
                /** @var AccessInterface $access */
                $access                = new $accessClass();
                $roleArray[ 'access' ] = $access;

                foreach ( $accessArray as $key => $value ) {
                    if ( method_exists( $access, $method = 'set' . ucfirst( $key ) ) && $key !== 'roles' ) {
                        $access->$method( $value );
                    } else if ( $key === 'roles' && is_array( $value ) ) {
                        foreach ( $value as $k => $roleName ) {
                            /** @var SystemRoleInterface $systemRole */
                            $systemRole = $this->getSystemRole( $manager, NULL, $roleName );
                            if ( !empty( $systemRole ) ) {
                                $access->addSystemRole( $systemRole );
                            }
                        }
                    }
                }

                $manager->persist( $access );
                $manager->flush();
            }
        }
    }

    /**
     * @param ObjectManager $manager
     * @param string        $path
     * @return AccessInterface|null
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getAccess( ObjectManager $manager, string $path ): ?AccessInterface
    {
        return $manager->getRepository( $this->config( 'access_class' ) )->findOneBy( [ 'path' => $path ] );
    }
}
