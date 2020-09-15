<?php
/** @noinspection PhpUnused */

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Manager;

use Jmccrei\UserManagement\DependencyInjection\Configuration;
use Jmccrei\UserManagement\Entity\Access;
use Jmccrei\UserManagement\Entity\AccessInterface;
use Jmccrei\UserManagement\Form\AccessType;
use Jmccrei\UserManagement\Manager\Traits\CreateForm;
use Jmccrei\UserManagement\Manager\Traits\Doctrine;
use Jmccrei\UserManagement\Traits\JmccreiUserManagementConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AccessManager
 * @package Jmccrei\UserManagement\Manager
 */
class AccessManager
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
     * AccessManager constructor.
     * @param KernelInterface $kernel
     */
    public function __construct( KernelInterface $kernel )
    {
        $this->kernel = $kernel;
        $this->setConfigFromKernel( $kernel );
        self::$instance = $this;
    }

    /**
     * @param KernelInterface $kernel
     */
    static public function checkAccessFile( KernelInterface $kernel )
    {
        if ( !file_exists( $filename = self::getAccessYamlFile( $kernel ) ) ) {
            // let's build it
            if ( self::$instance ) {
                self::$instance->dumpAccesses();
                self::$reload = TRUE;
            }
        }
    }

    /**
     * @param KernelInterface $kernel
     * @return string
     */
    static public function getAccessYamlFile( KernelInterface $kernel )
    {
        $accessFile = ( ( $env = $kernel->getEnvironment() ) === 'test' )
            ? Configuration::ACCESS_FILE_TEST
            : ( $env === 'dev'
                ? Configuration::ACCESS_FILE_DEV
                : Configuration::ACCESS_FILE );

        return implode( DIRECTORY_SEPARATOR, [
            $kernel->getProjectDir(),
            'config',
            $accessFile
        ] );
    }

    /**
     * @return $this
     */
    public function dumpAccesses(): AccessManager
    {
        $this->writeAccessesFile();

        return $this;
    }

    /**
     * Write the access yaml to disk
     */
    protected function writeAccessesFile(): void
    {
        $data = $this->getDataStructure( TRUE );

        $fs = new Filesystem();
        $fs->dumpFile( $this->getAccessesFile(), $data );
    }

    /**
     * @param false $returnYml
     * @return array|string
     */
    protected function getDataStructure( $returnYml = FALSE )
    {
        $repository = $this->getRepository( $this->config( 'access_class' ) );
        $result     = [];
        $accesses   = $repository->findBy( [ 'active' => TRUE ] );

        if ( is_iterable( $accesses ) ) {
            foreach ( $accesses as $access ) {
                $result[] = $access->getData();
            }
        }

        if ( count( $result ) === 0 ) {
            $result = NULL;
        }

        return $returnYml ? Yaml::dump( $result, 1 ) : $result;
    }

    /**
     * @return string|null
     */
    protected function getAccessesFile(): ?string
    {
        $accessFile = ( ( $env = $this->kernel->getEnvironment() ) === 'test' )
            ? Configuration::ACCESS_FILE_TEST
            : ( $env === 'dev'
                ? Configuration::ACCESS_FILE_DEV
                : Configuration::ACCESS_FILE );

        return implode( DIRECTORY_SEPARATOR, [
            $this->kernel->getProjectDir(),
            'config',
            $accessFile
        ] );
    }

    /**
     * @param AccessInterface|null $access
     * @param string|null          $className
     * @return FormInterface
     */
    public function getAccessForm( AccessInterface $access = NULL, string $className = NULL ): FormInterface
    {
        $method = 'PUT';

        if ( $access === NULL ) {
            $method      = 'POST';
            $accessClass = $this->config( 'access_class' );
            $access      = !empty( $className ) ? new $className() : new $accessClass();
        }

        return $this->createForm(
            AccessType::class,
            $access,
            [ 'method' => $method ]
        );
    }

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @return bool
     */
    public function submitAccessForm( FormInterface $form, Request $request ): bool
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
     * @return array|object[]
     */
    public function getAllAccesses()
    {
        return $this->getRepository( Access::class )
            ->findAll();
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }
}