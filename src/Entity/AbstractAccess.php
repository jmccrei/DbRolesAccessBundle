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

/**
 * Class AbstractAccess
 * @package Jmccrei\UserManagement\Entity
 */
abstract class AbstractAccess implements AccessInterface
{
    const METHODS = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE',
        'HEAD', 'OPTIONS', 'LINK', 'UNLINK'
    ];

    /**
     * @var string
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(length=255)
     */
    protected $path;

    /**
     * @var ArrayCollection
     * ORM\ManyToMany(targetEntity=SystemRole::class, inversedBy="access", cascade={"persist"})
     * ORM\JoinTable(name="access_system_roles")
     */
    protected $systemRoles;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @var string|null
     * @ORM\Column(length=255, nullable=true)
     */
    protected $host;

    /**
     * @var string|null
     * @ORM\Column(length=32, nullable=true)
     */
    protected $ip;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $anonymous;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $methods;

    /**
     * @var string|null
     * @ORM\Column(length=32, nullable=true)
     */
    protected $channel;

    /**
     * Access constructor.
     */
    public function __construct()
    {
        $this->active      = TRUE;
        $this->anonymous   = FALSE;
        $this->systemRoles = new ArrayCollection();
        $this->methods     = [ 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS' ];
    }

    /**
     * Add a system role to this access
     *
     * @param SystemRoleInterface $role
     * @return AccessInterface
     */
    public function addSystemRole( SystemRoleInterface $role ): AccessInterface
    {
        if ( !$this->systemRoles->contains( $role ) ) {
            // Add this access to the role
            $role->addAccess( $this );

            // Add the role to the roles list
            $this->systemRoles->add( $role );
        }

        return $this;
    }    /**
     * Get Id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
     * @return AccessInterface
     */
    public function setActive( bool $active ): AccessInterface
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get Ip
     * @return null|string
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Set Ip
     * @param null|string $ip
     * @return AccessInterface
     */
    public function setIp( ?string $ip ): AccessInterface
    {
        $this->ip = $ip;

        return $this;
    }



    /**
     * Remove a system role from this access
     *
     * @param SystemRoleInterface $role
     * @return AccessInterface
     */
    public function removeRole( SystemRoleInterface $role ): AccessInterface
    {
        // Remove this access from the role, regardless if its part of this Access or not
        $role->removeAccess( $this );

        if ( $this->systemRoles->contains( $role ) ) {
            $this->systemRoles->removeElement( $role );
        }

        return $this;
    }

    /**
     * Get the data necessary to build the access privileges
     *
     * @return array
     */
    public function getData()
    {
        $roles = [];
        foreach ( $this->getSystemRoles() as $role ) {
            $roles[] = $role->getRoleName();
        }
        if ( $this->isAnonymous() ) {
            $roles[] = 'IS_AUTHENTICATED_ANONYMOUSLY';
        }

        return [
            'path'    => $this->getPath(),
            'roles'   => $roles,
            'host'    => $this->getHost(),
            'anon'    => $this->isAnonymous(),
            'channel' => $this->getChannel(),
            'methods' => $this->getMethods()
        ];
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
     * @return AccessInterface
     */
    public function setSystemRoles( Collection $systemRoles ): AccessInterface
    {
        $this->systemRoles = $systemRoles;

        return $this;
    }

    /**
     * Get Anonymous
     * @return bool
     */
    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }

    /**
     * Set Anonymous
     * @param bool $anonymous
     * @return AccessInterface
     */
    public function setAnonymous( bool $anonymous ): AccessInterface
    {
        $this->anonymous = $anonymous;

        return $this;
    }

    /**
     * Get Path
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set Path
     * @param string $path
     * @return AccessInterface
     */
    public function setPath( string $path ): AccessInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get Host
     * @return null|string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Set Host
     * @param null|string $host
     * @return AccessInterface
     */
    public function setHost( ?string $host ): AccessInterface
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get Channel
     * @return null|string
     */
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * Set Channel
     * @param null|string $channel
     * @return AccessInterface
     */
    public function setChannel( ?string $channel ): AccessInterface
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get Methods
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Set Methods
     * @param array $methods
     * @return AccessInterface
     */
    public function setMethods( array $methods ): AccessInterface
    {
        $this->methods = $methods;

        return $this;
    }
}