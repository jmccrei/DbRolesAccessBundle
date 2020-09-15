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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AbstractSystemRole
 * @package Jmccrei\UserManagement\Entity
 */
abstract class AbstractSystemRole implements SystemRoleInterface
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(length=32, unique=true)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="alias", length=64, nullable=true)
     */
    protected $alias;

    /**
     * @var string
     * @ORM\Column(length=32, unique=true)
     */
    protected $roleName;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $mandatory;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @var SystemRole|null
     * THIS IS DYNAMICALLY MAPPED IN AN ENTITY SUBSCRIBER BASED ON `role_class` AND `user_class` ON jmccrei.yaml
     * ORM\ManyToOne(targetEntity=self::class, inversedBy="children", cascade={"persist"})
     * ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var ArrayCollection
     * THIS IS DYNAMICALLY MAPPED IN AN ENTITY SUBSCRIBER BASED ON `role_class` AND `user_class` ON jmccrei.yaml
     * ORM\OneToMany(targetEntity=self::class, mappedBy="parent")
     */
    protected $children;

    /**
     * @var ArrayCollection
     * THIS IS DYNAMICALLY MAPPED IN AN ENTITY SUBSCRIBER BASED ON `role_class` AND `user_class` ON jmccrei.yaml
     * ORM\ManyToMany(targetEntity=UserInterface::class, mappedBy="systemRoles")
     */
    protected $users;

    /**
     * @var ArrayCollection
     * THIS IS DYNAMICALLY MAPPED IN AN ENTITY SUBSCRIBER BASED ON `role_class` AND `user_class` ON jmccrei.yaml
     * ORM\ManyToMany(targetEntity=AccessInterface::class, mappedBy="systemRoles")
     */
    protected $access;

    /**
     * @var string
     * @ORM\Column(length=12)
     */
    protected $color;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $switch;

    /**
     * Role constructor.
     */
    public function __construct()
    {
        $this->mandatory = FALSE;
        $this->children  = new ArrayCollection();
        $this->access    = new ArrayCollection();
        $this->users     = new ArrayCollection();
        $this->active    = TRUE;
        $this->color     = '#428BCA';
        $this->switch    = FALSE;
    }

    /**
     * Convert string name to role name
     *
     * @param null|string $roleName
     * @return null|string
     */
    public static function nameToRole( ?string $roleName = NULL ): ?string
    {
        if ( $roleName === NULL ) {
            // null is not appropriate here
            return $roleName;
        } else if ( substr( $roleName, 0, 5 ) === 'ROLE_' ) {
            // Already a role name
            return strtoupper( trim( $roleName ) );
        }

        $role = strtoupper( trim( $roleName ) );
        $role = str_replace( ' ', '_', $role );

        return 'ROLE_' . $role;
    }

    /**
     * Add an access
     *
     * @param AccessInterface $access
     * @return SystemRoleInterface
     */
    public function addAccess( AccessInterface $access ): SystemRoleInterface
    {
        if ( !$this->access->contains( $access ) ) {
            $this->access->add( $access );
        }

        return $this;
    }

    /**
     * Add a child role
     *
     * @param SystemRoleInterface $role
     * @return SystemRoleInterface
     */
    public function addChild( SystemRoleInterface $role ): SystemRoleInterface
    {
        if ( !$this->children->contains( $role ) ) {
            $role->setParent( $this );
            $this->children->add( $role );
        }

        return $this;
    }

    /**
     * Add a user
     *
     * @param UserInterface $user
     * @return SystemRoleInterface
     */
    public function addUser( UserInterface $user ): SystemRoleInterface
    {
        if ( !$this->users->contains( $user ) ) {
            $this->users->add( $user );
        }

        return $this;
    }

    /**
     * Get Access
     * @return Collection
     */
    public function getAccess(): Collection
    {
        return $this->access;
    }

    /**
     * Set Access
     * @param Collection $access
     * @return SystemRoleInterface
     */
    public function setAccess( Collection $access ): SystemRoleInterface
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get Alias
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * Set Alias
     * @param string|null $alias
     * @return SystemRoleInterface
     */
    public function setAlias( ?string $alias ): SystemRoleInterface
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get Children
     * @return Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Set Children
     * @param Collection $children
     * @return SystemRoleInterface
     */
    public function setChildren( Collection $children ): SystemRoleInterface
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get Color
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Set Color
     * @param string $color
     * @return SystemRoleInterface
     */
    public function setColor( string $color ): SystemRoleInterface
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get Id
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get Name
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set Name
     * @param string $name
     * @return SystemRoleInterface
     */
    public function setName( string $name ): SystemRoleInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Parent
     * @return SystemRoleInterface|null
     */
    public function getParent(): ?SystemRoleInterface
    {
        return $this->parent;
    }

    /**
     * Set Parent
     * @param SystemRoleInterface|null $parent
     * @return SystemRoleInterface
     */
    public function setParent( ?SystemRoleInterface $parent ): SystemRoleInterface
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get RoleName
     * @return string|null
     */
    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    /**
     * Set RoleName
     * @param string $roleName
     * @return SystemRoleInterface
     */
    public function setRoleName( string $roleName ): SystemRoleInterface
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * Get Users
     *
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Set Users
     *
     * @param Collection $users
     * @return SystemRoleInterface
     */
    public function setUsers( Collection $users ): SystemRoleInterface
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get Active
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Set Active
     * @param bool $active
     * @return SystemRoleInterface
     */
    public function setActive( bool $active ): SystemRoleInterface
    {
        $this->active = $active;

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
     * @return SystemRoleInterface
     */
    public function setMandatory( bool $mandatory ): SystemRoleInterface
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * Get Switch
     * @return bool
     * @noinspection PhpUnused
     */
    public function isSwitch(): bool
    {
        return !!$this->switch;
    }

    /**
     * Set Switch
     * @param bool $switch
     * @return SystemRoleInterface
     */
    public function setSwitch( bool $switch ): SystemRoleInterface
    {
        $this->switch = $switch;

        return $this;
    }

    /**
     * Remove an access
     *
     * @param AccessInterface $access
     * @return SystemRoleInterface
     */
    public function removeAccess( AccessInterface $access ): SystemRoleInterface
    {
        if ( $this->access->contains( $access ) ) {
            $access->removeRole( $this );
            $this->access->removeElement( $access );
        }

        return $this;
    }

    /**
     * Remove a child role
     *
     * @param SystemRoleInterface $role
     * @return SystemRoleInterface
     */
    public function removeChild( SystemRoleInterface $role ): SystemRoleInterface
    {
        if ( $this->children->contains( $role ) ) {
            $role->setParent( NULL );
            $this->children->removeElement( $role );
        }

        return $this;
    }

    /**
     * Remove a user
     *
     * @param UserInterface $user
     * @return SystemRoleInterface
     */
    public function removeUser( UserInterface $user ): SystemRoleInterface
    {
        if ( $this->users->contains( $user ) ) {
            $this->users->removeElement( $user );
        }

        return $this;
    }
}