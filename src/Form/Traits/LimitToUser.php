<?php

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Form\Traits;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Trait LimitToUser
 * @package Jmccrei\UserManagement\Form\Traits
 */
trait LimitToUser
{
    /**
     * @param FormBuilderInterface $builder
     * @param                      $fieldName
     * @param array                $options
     * @noinspection PhpUnused
     */
    public function queryEntityByUser( FormBuilderInterface $builder, $fieldName, array $options )
    {
        $options = array_merge( $options, [ 'query_builder' => function ( EntityRepository $em ) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $em->createQueryBuilder( 'e' )
                ->where( "e.user=:user" )
                ->setParameter( 'user', $this->getUser() );
        } ] );
        $builder->add( $fieldName, EntityType::class, $options );
    }
}