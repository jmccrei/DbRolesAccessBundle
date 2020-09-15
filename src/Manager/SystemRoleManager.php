<?php
/** @noinspection PhpUnused */

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Jmccrei\UserManagement\DependencyInjection\Configuration;
use Jmccrei\UserManagement\Entity\AbstractSystemRole;
use Jmccrei\UserManagement\Entity\SystemRoleInterface;
use Jmccrei\UserManagement\Form\SystemRoleType;
use Jmccrei\UserManagement\Manager\Traits\CreateForm;
use Jmccrei\UserManagement\Manager\Traits\Doctrine;
use Jmccrei\UserManagement\Traits\JmccreiUserManagementConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SystemRoleManager
 * @package Jmccrei\UserManagement\Manager
 */
class SystemRoleManager
{
    use Doctrine;
    use CreateForm;
    use JmccreiUserManagementConfiguration;

    /**
     * Static variable for reference in the KernelSubscriber
     *
     * @var bool
     */
    public static $reload = FALSE;

    /**
     * @var SystemRoleManager|null
     */
    public static $instance = NULL;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * RoleManager constructor.
     * @param KernelInterface $kernel
     */
    public function __construct( KernelInterface $kernel )
    {
        $this->kernel = $kernel;
        $this->setConfigFromKernel( $kernel );
        self::$instance = $this;
    }

    static public function checkSystemRolesFile( KernelInterface $kernel )
    {
        if ( !file_exists( $filename = self::getSystemRolesYamlFile( $kernel ) ) ) {
            // let's build it
            if ( self::$instance ) {
                self::$instance->dumpSystemRoles();
                self::$reload = TRUE;
            }
        }
    }

    /**
     * @param KernelInterface $kernel
     * @return string
     */
    static public function getSystemRolesYamlFile( KernelInterface $kernel )
    {
        $systemRoleFile = ( ( $env = $kernel->getEnvironment() ) === 'test' )
            ? Configuration::SYSTEM_ROLE_FILE_TEST
            : ( $env === 'dev'
                ? Configuration::SYSTEM_ROLE_FILE_DEV
                : Configuration::SYSTEM_ROLE_FILE );

        return implode( DIRECTORY_SEPARATOR, [
            $kernel->getProjectDir(),
            'config',
            $systemRoleFile
        ] );
    }

    /**
     * @return $this
     */
    public function dumpSystemRoles(): SystemRoleManager
    {
        $this->writeSystemRolesFile();

        return $this;
    }

    /**
     * Write roles file
     */
    protected function writeSystemRolesFile(): void
    {
        $file = $this->getSystemRolesFile();
        $data = $this->getDataStructure( TRUE );
        $fs   = new Filesystem();
        $fs->dumpFile( $file, $data );
    }

    /**
     * @return string|null
     */
    protected function getSystemRolesFile(): ?string
    {
        return self::getSystemRolesYamlFile( $this->kernel );
    }

    /**
     * @param bool $returnYaml
     * @return array|string|null
     */
    protected function getDataStructure( bool $returnYaml = FALSE )
    {
        $hier = [];

        /** @var SystemRoleInterface $systemRole */
        foreach ( $this->getAllSystemRoles() as $systemRole ) {
            $rhier           = [];
            $parent          = $systemRole->getParent();
            $allowedToSwitch = $systemRole->isSwitch();
            if ( !empty( $parent ) ) {
                $parentAlias    = AbstractSystemRole::nameToRole( $parent->getAlias() );
                $parentRoleName = $parent->getRoleName();
                $rhier[]        = $parentRoleName;
                if ( !empty( $parentAlias ) ) {
                    $rhier[] = $parentAlias;
                }
            }

            if ( $allowedToSwitch ) {
                $rhier[] = 'ROLE_ALLOWED_TO_SWITCH';
            }

            if ( $systemRole->isActive() ) {
                $alias = AbstractSystemRole::nameToRole( $systemRole->getAlias() );
                if ( !empty( $alias ) ) {
                    $hier[ $alias ] = json_decode( json_encode( $rhier ), TRUE );
                    $rhier[]        = $alias;
                }
                $hier[ $systemRole->getRoleName() ] = $rhier;
            }

        }

        if ( count( $hier ) === 0 ) {
            return NULL;
        }

        return $returnYaml ? Yaml::dump( $hier, 1 ) : $hier;
    }

    /**
     * @return array|object[]
     */
    public function getAllSystemRoles(): ?array
    {
        return $this->getRepository( $this->config( 'role_class' ) )
            ->findAll();
    }

    /**
     * @param SystemRoleInterface $systemRole
     * @return FormInterface
     */
    public function getSystemRoleForm( SystemRoleInterface $systemRole ): FormInterface
    {
        $method = 'PUT';

        if ( $systemRole === NULL ) {
            $method     = 'POST';
            $roleClass  = $this->config( 'role_class' );
            $systemRole = new $roleClass();
        }

        return $this->createForm( SystemRoleType::class,
            $systemRole,
            [ 'method' => $method ] );
    }

    /**
     * Submit the system role form
     *
     * @param Form    $form
     * @param Request $request
     * @return bool
     */
    public function submitSystemRoleForm( Form $form, Request $request ): bool
    {
        if ( $request->isMethod( 'POST' ) || $request->isMethod( 'PUT' ) ) {
            $form->handleRequest( $request );
            if ( !$form->isSubmitted() || !$form->isValid() ) {
                return FALSE;
            }

            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param SystemRoleInterface $systemRole
     * @param bool                $persist
     * @param bool                $flush
     * @return $this
     */
    public function updateRole( SystemRoleInterface $systemRole, bool $persist = TRUE, bool $flush = TRUE ): SystemRoleManager
    {
        $name = $systemRole->getName();
        $systemRole->setRoleName( $roleName = AbstractSystemRole::nameToRole( $name ) );

        $this->matchExistingUserSystemRoles( $systemRole );

        if ( !!$persist ) {
            $this->getObjectManager()->persist( $systemRole );

            if ( !!$flush ) {
                $this->getObjectManager()->flush();
            }
        }

        return $this;
    }

    /**
     * @param SystemRoleInterface $systemRole
     * @param bool                $persist
     * @return ArrayCollection
     */
    protected function matchExistingUserSystemRoles( SystemRoleInterface $systemRole, bool $persist = TRUE ): ArrayCollection
    {
        $roleName = $systemRole->getRoleName();

        $repository = $this->getRepository( $this->config( 'user_class' ) );
        $query      = $repository->createQueryBuilder( 'u' )
            ->where( 'u.roles LIKE :roleName' )
            ->setParameter( 'roleName', '%' . $roleName . '%' )
            ->getQuery();
        $users      = $query->getResult();
        $touched    = new ArrayCollection();

        if ( is_iterable( $users ) && count( $users ) > 0 ) {
            foreach ( $users as $user ) {
                $roles = $user->getRoles();
                if ( is_iterable( $roles ) && in_array( $roleName, $roles ) ) {
                    if ( !$user->getSystemRoles()->contains( $systemRole ) ) {
                        $user->addSystemRole( $systemRole );
                        $touched->add( $user );

                        if ( !!$persist && !$this->getObjectManager()->contains( $user ) ) {
                            $this->getObjectManager()->persist( $user );
                        }
                    }
                }
            }
        }

        return $touched;
    }

    /**
     * Get the container
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }
}
