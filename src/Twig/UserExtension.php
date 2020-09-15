<?php
/** @noinspection PhpUnused */

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Twig;

use Jmccrei\UserManagement\Form\LoginFormType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class UserExtension
 * @package Jmccrei\UserManagement\Twig
 */
class UserExtension extends AbstractExtension
{
    /**
     * @var object|Environment|null
     */
    protected $twig;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ParameterBag
     */
    protected $configuration;

    /**
     * UserExtension constructor.
     * @param KernelInterface $kernel
     * @param array           $jmccreiUserManagementConfiguration
     */
    public function __construct( KernelInterface $kernel, array $jmccreiUserManagementConfiguration )
    {
        $this->container     = $kernel->getContainer();
        $this->twig          = $this->container->get( 'twig' );
        $this->configuration = new ParameterBag( $jmccreiUserManagementConfiguration );
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction( 'getUserLoginForm', [ $this, 'getUserLoginForm' ] )
        ];
    }

    /**
     * @return FormInterface|null
     */
    public function getUserLoginForm(): ?FormInterface
    {
        return $this->createForm( LoginFormType::class, NULL );
    }

    /**
     * Create a form
     *
     * @param string $type
     * @param null   $data
     * @param array  $options
     * @return FormInterface
     */
    protected function createForm( string $type, $data = NULL, array $options = [] ): FormInterface
    {
        return $this->container->get( 'form.factory' )->create( $type, $data, $options );
    }
}