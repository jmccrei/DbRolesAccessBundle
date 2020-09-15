<?php

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Form;

use Jmccrei\UserManagement\Entity\SystemRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AdminUserType
 * @package Jmccrei\UserManagement\Form
 */
abstract class AdminUserType extends UserType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        parent::buildForm( $builder, $options );
        
        $builder->add( 'enabled', CheckboxType::class, [ 'required' => FALSE ] )
            ->add( 'systemRoles', EntityType::class, [
                'class'        => SystemRole::class,
                'choice_label' => 'name',
                'multiple'     => TRUE
            ] )
            ->add( 'mandatory', CheckboxType::class, [ 'required' => FALSE ] );
    }
}