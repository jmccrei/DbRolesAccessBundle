<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\EventSubscriber;

use Jmccrei\UserManagement\Manager\AccessManager;
use Jmccrei\UserManagement\Manager\SystemRoleManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class KernelSubscriber
 * @package Jmccrei\UserManagement\EventSubscriber
 */
class KernelSubscriber implements EventSubscriberInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * KernelSubscriber constructor.
     * @param KernelInterface $kernel
     */
    public function __construct( KernelInterface $kernel )
    {
        // The kernel on the events is not the kernel we will need
        $this->kernel = $kernel;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST  => [ [ 'onKernelRequest' ] ],
            KernelEvents::RESPONSE => [ [ 'onKernelResponse' ] ]
        ];
    }

    /**
     * @param RequestEvent $event
     * @noinspection PhpUnusedParameterInspection
     */
    public function onKernelRequest( RequestEvent $event )
    {
        SystemRoleManager::checkSystemRolesFile( $this->kernel );
        AccessManager::checkAccessFile( $this->kernel );
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse( ResponseEvent $event )
    {
        if ( SystemRoleManager::$reload === TRUE || AccessManager::$reload === TRUE ) {
            $response = new Response( NULL, 200, [ 'Refresh' => 0 ] );
            $event->setResponse( $response );
        }
    }
}