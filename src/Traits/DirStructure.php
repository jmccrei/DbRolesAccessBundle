<?php

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Traits;

/**
 * Trait DirStructure
 * @package Jmccrei\UserManagement\Traits
 */
trait DirStructure
{
    /**
     * The project directory
     *
     * @var string|null
     */
    protected $projectDir;

    /**
     * Get the config/packages dir
     *
     * @return string
     */
    public function getConfigPackagesDir(): string
    {
        return $this->getConfigDir()
            . DIRECTORY_SEPARATOR . 'packages';
    }

    /**
     * Get the config dir
     *
     * @return string
     */
    public function getConfigDir(): string
    {
        return $this->getProjectDir()
            . DIRECTORY_SEPARATOR . 'config';
    }

    /**
     * Get the project dir
     *
     * @return string
     */
    public function getProjectDir(): string
    {
        if ( !empty( $this->projectDir ) ) {
            return $this->projectDir;
        }

        $dir = __DIR__;
        while ( !is_file( $dir . DIRECTORY_SEPARATOR . '.env' ) ) {
            $dir = dirname( $dir );
        }

        return $this->projectDir = $dir;
    }

    /**
     * Get the public dir
     *
     * @return string
     */
    public function getPublicDir(): string
    {
        return $this->getProjectDir()
            . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Get the src dir
     *
     * @return string
     */
    public function getSrcDir(): string
    {
        return $this->getProjectDir()
            . DIRECTORY_SEPARATOR . 'src';
    }

    /**
     * Get the vendor dir
     *
     * @return string
     */
    public function getVendorDir(): string
    {
        return $this->getProjectDir()
            . DIRECTORY_SEPARATOR . 'vendor';
    }

    /**
     * Get the cache dir
     *
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->getVarDir()
            . DIRECTORY_SEPARATOR . 'cache';
    }

    /**
     * Get the var dir
     *
     * @return string
     */
    public function getVarDir(): string
    {
        return $this->getProjectDir()
            . DIRECTORY_SEPARATOR . 'var';
    }

    /**
     * Get the logs dir
     *
     * @return string
     */
    public function getLogsDir(): string
    {
        return $this->getVarDir()
            . DIRECTORY_SEPARATOR . 'log';
    }
}