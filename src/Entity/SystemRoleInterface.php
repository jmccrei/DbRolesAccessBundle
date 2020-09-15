<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Entity;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface SystemRoleInterface
 * @package Jmccrei\UserManagement\Entity
 */
interface SystemRoleInterface
{
    /**
     * Get Id
     * @return integer
     */
    public function getId(): int;

    /**
     * Get Alias
     * @return string|null
     */
    public function getAlias(): ?string;

    /**
     * Set Alias
     * @param string|null $alias
     * @return SystemRoleInterface
     */
    public function setAlias( ?string $alias ): SystemRoleInterface;

    /**
     * Get Users
     *
     * @return Collection
     */
    public function getUsers(): Collection;

    /**
     * Set Users
     *
     * @param Collection $users
     * @return SystemRoleInterface
     */
    public function setUsers( Collection $users ): SystemRoleInterface;

    /**
     * Add a user
     *
     * @param UserInterface $user
     * @return SystemRoleInterface
     */
    public function addUser( UserInterface $user ): SystemRoleInterface;

    /**
     * Remove a user
     *
     * @param UserInterface $user
     * @return SystemRoleInterface
     */
    public function removeUser( UserInterface $user ): SystemRoleInterface;

    /**
     * Add an access
     *
     * @param AccessInterface $access
     * @return SystemRoleInterface
     */
    public function addAccess( AccessInterface $access ): SystemRoleInterface;

    /**
     * Remove an access
     *
     * @param AccessInterface $access
     * @return SystemRoleInterface
     */
    public function removeAccess( AccessInterface $access ): SystemRoleInterface;

    /**
     * Add a child role
     *
     * @param SystemRoleInterface $role
     * @return SystemRoleInterface
     */
    public function addChild( SystemRoleInterface $role ): SystemRoleInterface;

    /**
     * Remove a child role
     *
     * @param SystemRoleInterface $role
     * @return SystemRoleInterface
     */
    public function removeChild( SystemRoleInterface $role ): SystemRoleInterface;

    /**
     * Get Name
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set Name
     * @param string $name
     * @return SystemRoleInterface
     */
    public function setName( string $name ): SystemRoleInterface;

    /**
     * Get RoleName
     * @return string|null
     */
    public function getRoleName(): ?string;

    /**
     * Set RoleName
     * @param string $roleName
     * @return SystemRoleInterface
     */
    public function setRoleName( string $roleName ): SystemRoleInterface;

    /**
     * Get Mandatory
     * @return bool
     */
    public function isMandatory(): bool;

    /**
     * Set Mandatory
     * @param bool $mandatory
     * @return SystemRoleInterface
     */
    public function setMandatory( bool $mandatory ): SystemRoleInterface;

    /**
     * Get Active
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Set Active
     * @param bool $active
     * @return SystemRoleInterface
     */
    public function setActive( bool $active ): SystemRoleInterface;

    /**
     * Get Parent
     * @return SystemRoleInterface|null
     */
    public function getParent(): ?SystemRoleInterface;

    /**
     * Set Parent
     * @param SystemRoleInterface|null $parent
     * @return SystemRoleInterface
     */
    public function setParent( ?SystemRoleInterface $parent ): SystemRoleInterface;

    /**
     * Get Children
     * @return Collection
     */
    public function getChildren(): Collection;

    /**
     * Set Children
     * @param Collection $children
     * @return SystemRoleInterface
     */
    public function setChildren( Collection $children ): SystemRoleInterface;

    /**
     * Get Access
     * @return Collection
     */
    public function getAccess(): Collection;

    /**
     * Set Access
     * @param Collection $access
     * @return SystemRoleInterface
     */
    public function setAccess( Collection $access ): SystemRoleInterface;

    /**
     * Get Color
     * @return string
     */
    public function getColor(): string;

    /**
     * Set Color
     * @param string $color
     * @return SystemRoleInterface
     */
    public function setColor( string $color ): SystemRoleInterface;

    /**
     * Get Switch
     * @return bool
     * @noinspection PhpUnused
     */
    public function isSwitch(): bool;

    /**
     * Set Switch
     * @param bool $switch
     * @return SystemRoleInterface
     */
    public function setSwitch( bool $switch ): SystemRoleInterface;
}