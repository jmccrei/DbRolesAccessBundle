<?php
/** @noinspection PhpUnused */

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AbstractUser
 *
 * @package Jmccrei\UserManagement\Entity
 */
abstract class AbstractUser implements UserInterface, EquatableInterface, Serializable
{
    /**
     * @var integer Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string Email Address
     *
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @var array The roles array: string[]
     *
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     *
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @var string|null Plain password for password updates
     */
    protected $plainPassword;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $salt;

    /**
     * @var ArrayCollection
     * THIS IS DYNAMICALLY MAPPED IN AN ENTITY SUBSCRIBER BASED ON `role_class` AND `user_class` ON jmccrei.yaml
     */
    protected $systemRoles;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $mandatory;

    /**
     * @var string
     * @ORM\Column(length=32, nullable=false)
     */
    protected $username;

    /**
     * AbstractUser constructor.
     */
    public function __construct()
    {
        $this->salt        = uniqid();
        $this->roles       = [ 'ROLE_USER' ];
        $this->systemRoles = new ArrayCollection();
        $this->mandatory   = FALSE;
    }

    /**
     * Get Email
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set Email
     * @param string $email
     * @return AbstractUser
     */
    public function setEmail( string $email ): AbstractUser
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get PlainPassword
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Set PlainPassword
     * @param string|null $plainPassword
     * @return AbstractUser
     */
    public function setPlainPassword( ?string $plainPassword ): AbstractUser
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Get Mandatory
     * @return bool
     */
    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    /**
     * Set Mandatory
     * @param bool $mandatory
     * @return AbstractUser
     */
    public function setMandatory( bool $mandatory ): AbstractUser
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = NULL;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * Set the password
     *
     * @param string $password
     * @return AbstractUser
     */
    public function setPassword( string $password ): AbstractUser
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the users access permissions
     * JMS\VirtualProperty
     */
    public function getAccessPermissions()
    {
        $access = [ 'roles' => [] ];
        foreach ( $this->getSystemRoles() as $role ) {
            $alias = $role->getAlias();
            if ( !empty( $alias ) ) {
                $access[ 'roles' ][] = $alias;
            }
            $access[ 'roles' ][] = $role->getRole();
        }

        return $access;
    }

    /**
     * Get SystemRoles
     * @return Collection
     */
    public function getSystemRoles(): Collection
    {
        return $this->systemRoles;
    }

    /**
     * Set SystemRoles
     * @param Collection $systemRoles
     * @return AbstractUser
     */
    public function setSystemRoles( Collection $systemRoles ): AbstractUser
    {
        $this->systemRoles = $systemRoles;

        return $this;
    }

    /**
     * Add a system role to this user
     *
     * @param SystemRoleInterface $systemRole
     * @return AbstractUser
     */
    public function addSystemRole( SystemRoleInterface $systemRole ): AbstractUser
    {
        if ( !$this->systemRoles->contains( $systemRole ) ) {
            $this->systemRoles->add( $systemRole );
            $systemRole->addUser( $this );

            $this->addRole( $systemRole->getRoleName() );
        }

        return $this;
    }

    /**
     * Add a role
     *
     * @param string $roleName
     * @return AbstractUser
     */
    public function addRole( string $roleName ): AbstractUser
    {
        if ( !in_array( $roleNameUpper = strtoupper( trim( $roleName ) ), $this->roles ?? [] ) ) {
            $this->roles[] = $roleName;
        }

        return $this;
    }

    /**
     * Remove a system role from this user
     *
     * @param SystemRoleInterface $systemRole
     * @return AbstractUser
     */
    public function removeSystemRole( SystemRoleInterface $systemRole ): AbstractUser
    {
        if ( $this->systemRoles->contains( $systemRole ) ) {
            $this->systemRoles->removeElement( $systemRole );
            $systemRole->removeUser( $this );

            $this->removeRole( $systemRole->getRoleName() );
        }

        return $this;
    }

    /**
     * Remove a role
     *
     * @param string $roleName
     * @return AbstractUser
     */
    public function removeRole( string $roleName ): AbstractUser
    {
        $roleName = strtoupper( $roleName );
        if ( in_array( $roleName, $this->getRoles() ) ) {
            $newRoles = [];
            if ( is_iterable( $this->getRoles() ?? [] ) ) {
                foreach ( $this->getRoles() ?? [] as $role ) {
                    if ( $role !== $roleName ) {
                        $newRoles[] = $role;
                    }
                }
            }
            $this->setRoles( $newRoles );
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique( $roles );
    }

    /**
     * Set roles
     *
     * @param array $roles
     * @return AbstractUser
     */
    public function setRoles( array $roles ): AbstractUser
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set Salt
     * @param string|null $salt
     * @return AbstractUser
     */
    public function setSalt( ?string $salt ): AbstractUser
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * Set Username
     * @param string $username
     * @return AbstractUser
     */
    public function setUsername( string $username ): AbstractUser
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo( UserInterface $user )
    {
        return $user instanceof self && $user->getId() === $this->getId();
    }

    /**
     * Get Id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->email,
                $this->password,
                $this->roles
            ]
        );
    }

    /**
     * Unserialize
     *
     * @param string $serialized
     * @noinspection PhpMissingParamTypeInspection
     */
    public function unserialize( $serialized )
    {
        list(
            $this->id,
            $this->email,
            $this->password,
            $this->roles
            ) = unserialize( $serialized );
    }

    /**
     * @param SystemRoleInterface $role
     * @return bool
     */
    public function hasSystemRole( SystemRoleInterface $role ): bool
    {
        return $this->systemRoles->contains( $role );
    }

    /**
     * Has Access?
     *
     * @param array $roles
     * @return bool
     */
    public function hasAccess( array $roles = [] )
    {
        foreach ( $roles as $slug ) {
            if ( $this->hasRole( $slug ) ) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Has a role?
     *
     * @param null|string $role
     * @return bool
     */
    public function hasRole( ?string $role ): bool
    {
        return in_array( $role, $this->roles );
    }
}