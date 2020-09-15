<?php

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Form;

use Jmccrei\UserManagement\Entity\SystemRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SystemRoleType
 * @package Jmccrei\UserManagement\Form
 */
class SystemRoleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        $builder
            ->add( 'name', TextType::class, [
                'help' => 'Name for this role.',
                'attr' => [
                    'placeholder' => 'Role Name'
                ]
            ] )
            ->add( 'inherits', EntityType::class, [
                'class'        => SystemRole::class,
                'choice_label' => 'name',
                'multiple'     => FALSE,
                'required'     => FALSE,
                'help'         => 'Inherit properties from role',
                'attr'         => [
                    'placeholder' => 'Inherits',
                ]
            ] )
            ->add( 'alias', TextType::class, [
                'required' => FALSE,
                'help'     => 'An Alias for the Role (ie: `Super Administrator` with alias `Super Admin`)',
                'attr'     => [
                    'placeholder' => 'Role Alias',
                ]
            ] )
            ->add( 'active', CheckboxType::class,
                [ 'required' => FALSE,
                  'help'     => 'Is this role active?',
                  'attr'     => [
                      'data-switch' => NULL,
                      'data-size'   => 'mini'
                  ]
                ] );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions( OptionsResolver $resolver ): void
    {
        $resolver->setDefaults( [
            'data_class' => SystemRole::class,
        ] );
    }
}