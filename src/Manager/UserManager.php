<?php /** @noinspection PhpUnused */

/** @noinspection PhpUnused */

/** @noinspection PhpUnused */

/** @noinspection PhpUnused */

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Manager;

use Jmccrei\UserManagement\Entity\AbstractUser;
use Jmccrei\UserManagement\Entity\SystemRole;
use Jmccrei\UserManagement\Form\AdminUserType;
use Jmccrei\UserManagement\Form\UserType;
use Jmccrei\UserManagement\Manager\Traits\CreateForm;
use Jmccrei\UserManagement\Manager\Traits\Doctrine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserManager
 * @package Jmccrei\UserManagement\Manager
 */
class UserManager
{
    use Doctrine;
    use CreateForm;

    /**
     * @var PasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var ParameterBag
     */
    protected $configuration;

    /**
     * UserManager constructor.
     * @param KernelInterface              $kernel
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param array                        $jmccreiUserManagementConfiguration
     */
    public function __construct( KernelInterface $kernel,
                                 UserPasswordEncoderInterface $userPasswordEncoder,
                                 array $jmccreiUserManagementConfiguration )
    {
        $this->kernel          = $kernel;
        $this->passwordEncoder = $userPasswordEncoder;
        $this->configuration   = new ParameterBag( $jmccreiUserManagementConfiguration );
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $password
     * @param array  $roles
     * @param bool   $superAdmin
     * @param bool   $mandatory
     * @return AbstractUser|mixed|object
     */
    public function getOrCreateUser( string $username,
                                     string $email,
                                     string $password,
                                     array $roles = [],
                                     bool $superAdmin = FALSE,
                                     bool $mandatory = FALSE )
    {
        $userRepository = $this->getRepository( $this->configuration->get( 'user_class' ) );
        $user           = $userRepository->findOneBy( [
            'username' => $username,
            'email'    => $email
        ] );

        if ( !empty( $user ) ) {
            return $user;
        }

        return $this->createUser( $username, $email, $password, $roles, $superAdmin, $mandatory );
    }

    /**
     * Create a new user
     *
     * @param string      $username
     * @param string      $email
     * @param string      $plainPassword
     * @param array|null  $roles
     * @param bool|null   $superAdmin
     * @param bool|null   $mandatory
     * @param string|null $userClass
     *
     * @return AbstractUser|mixed
     */
    public function createUser( string $username,
                                string $email,
                                string $plainPassword,
                                ?array $roles = [],
                                ?bool $superAdmin = FALSE,
                                ?bool $mandatory = FALSE,
                                ?string $userClass = NULL )
    {
        $configUserClass = $this->configuration->get( 'user_class' );
        $user            = !empty( $userClass ) ? new $userClass() : new $configUserClass();
        $user->setUsername( $username )
            ->setEmail( $email )
            ->setPlainPassword( $plainPassword )
            ->setMandatory( !!$mandatory );

        // check the roles
        $roles = is_array( $roles ) ? $roles : [ 'ROLE_USER' ];
        if ( !in_array( 'ROLE_USER', $roles ) ) {
            $roles[] = 'ROLE_USER';
        }

        // super admin
        if ( !!$superAdmin && !in_array( 'ROLE_SUPER_ADMINISTRATOR', $roles ) ) {
            if ( $this->kernel->getEnvironment() === 'test' ) {
                $roles[] = 'ROLE_TEST_SUPER_ADMINISTRATOR';
            } else {
                $roles[] = 'ROLE_SUPER_ADMINISTRATOR';
            }
        }

        $user->setRoles( $roles );

        $this->getObjectManager()->persist( $user );
        $this->getObjectManager()->flush();

        return $user;
    }

    /**
     * @param AbstractUser|null $user
     * @param bool              $admin
     * @return FormInterface
     */
    public function getUserForm( AbstractUser $user = NULL, bool $admin = FALSE )
    {
        $method    = 'PUT';
        $formClass = $admin ? AdminUserType::class : UserType::class;

        if ( $user === NULL ) {
            $method    = 'POST';
            $className = get_class( $user );
            $user      = new $className();
        }

        return $this->createForm(
            $formClass,
            $user,
            [ 'method' => $method ]
        );
    }

    /**
     * @param Form    $form
     * @param Request $request
     * @return bool
     */
    public function submitUserForm( Form $form, Request $request ): bool
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
     * Update the user
     *
     * @param AbstractUser $user
     * @param bool         $persist
     * @param bool         $flush
     * @return AbstractUser
     * @noinspection PhpUndefinedMethodInspection
     */
    public function updateUser( AbstractUser $user, bool $persist = TRUE, bool $flush = TRUE )
    {
        $plainPassword = $user->getPlainPassword();

        if ( !empty( $plainPassword ) ) {
            $user->setPassword( $this->passwordEncoder->encodePassword( $user, $plainPassword ) )
                ->setPlainPassword( NULL );
        }

        $this->updateUserSystemRoles( $user );

        if ( !!$persist ) {
            $this->getObjectManager()->persist( $user );

            if ( !!$flush ) {
                $this->getObjectManager()->flush();
            }
        }

        return $user;
    }

    /**
     * @param AbstractUser $user
     */
    public function updateUserSystemRoles( AbstractUser $user ): void
    {
        $this->syncUserRolesToSystemRoles( $user );
        $this->syncUserSystemRolesToRoles( $user );
    }

    /**
     * Sync roles to system roles
     *
     * @param AbstractUser $user
     */
    protected function syncUserRolesToSystemRoles( AbstractUser $user ): void
    {
        $systemRoles = $user->getSystemRoles();
        if ( is_iterable( $systemRoles ) ) {
            foreach ( $systemRoles as $systemRole ) {
                $user->addRole( $systemRole->getRoleName() );
            }
        }

        // and, again, lets ensure ROLE_USER exists
        $user->addRole( 'ROLE_USER' );
    }

    /**
     * Sync the system roles to roles
     *
     * @param AbstractUser $user
     */
    protected function syncUserSystemRolesToRoles( AbstractUser $user )
    {
        // add it again, cannot do it enough
        $user->addRole( 'ROLE_USER' );

        $roles      = $user->getRoles();
        $repository = $this->getRepository( SystemRole::class );

        if ( is_iterable( $roles ) ) {
            foreach ( $roles as $role ) {
                /** @var SystemRole $entity */
                $entity = $repository->findOneBy( [ 'roleName' => $role ] );

                if ( !empty( $entity ) ) {
                    $user->addSystemRole( $entity );
                } else {
                    // remove the role
                    $user->removeRole( $role );
                }
            }
        }
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }
}