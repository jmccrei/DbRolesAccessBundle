<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * Interface AccessInterface
 * @package Jmccrei\UserManagement\Entity
 */
interface AccessInterface
{
    /**
     * Get Id
     * @return string
     */
    public function getId(): string;

    /**
     * Get Active
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Set Active
     * @param bool $active
     * @return AccessInterface
     */
    public function setActive( bool $active ): AccessInterface;

    /**
     * Get Ip
     * @return null|string
     */
    public function getIp(): ?string;

    /**
     * Set Ip
     * @param null|string $ip
     * @return AccessInterface
     */
    public function setIp( ?string $ip ): AccessInterface;

    /**
     * Add a system role to this access
     *
     * @param SystemRoleInterface $role
     * @return AccessInterface
     */
    public function addSystemRole( SystemRoleInterface $role ): AccessInterface;

    /**
     * Remove a system role from this access
     *
     * @param SystemRoleInterface $role
     * @return AccessInterface
     */
    public function removeRole( SystemRoleInterface $role ): AccessInterface;

    /**
     * Get the data necessary to build the access privileges
     *
     * @return array
     */
    public function getData();

    /**
     * Get SystemRoles
     * @return Collection
     */
    public function getSystemRoles(): Collection;

    /**
     * Set SystemRoles
     * @param Collection $systemRoles
     * @return AccessInterface
     */
    public function setSystemRoles( Collection $systemRoles ): AccessInterface;

    /**
     * Get Anonymous
     * @return bool
     */
    public function isAnonymous(): bool;

    /**
     * Set Anonymous
     * @param bool $anonymous
     * @return AccessInterface
     */
    public function setAnonymous( bool $anonymous ): AccessInterface;

    /**
     * Get Path
     * @return string
     */
    public function getPath(): string;

    /**
     * Set Path
     * @param string $path
     * @return AccessInterface
     */
    public function setPath( string $path ): AccessInterface;

    /**
     * Get Host
     * @return null|string
     */
    public function getHost(): ?string;

    /**
     * Set Host
     * @param null|string $host
     * @return AccessInterface
     */
    public function setHost( ?string $host ): AccessInterface;

    /**
     * Get Channel
     * @return null|string
     */
    public function getChannel(): ?string;

    /**
     * Set Channel
     * @param null|string $channel
     * @return AccessInterface
     */
    public function setChannel( ?string $channel ): AccessInterface;

    /**
     * Get Methods
     * @return array
     */
    public function getMethods(): array;

    /**
     * Set Methods
     * @param array $methods
     * @return AccessInterface
     */
    public function setMethods( array $methods ): AccessInterface;
}