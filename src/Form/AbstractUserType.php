<?php

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AbstractUserType
 * @package Jmccrei\UserManagement\Form
 */
abstract class AbstractUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm( FormBuilderInterface $builder, array $options )
    {
        $builder
            ->add( 'username', TextType::class, [] )
            ->add( 'email', EmailType::class, [] )
            ->add( 'plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'invalid_message' => 'The passwords must match',
                'required'        => FALSE,
                'first_options'   => [ 'label' => 'Password' ],
                'second_options'  => [ 'label' => 'Confirm Password' ]
            ] );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions( OptionsResolver $resolver )
    {
        $resolver->setDefaults( [
            'data_class' => UserInterface::class,
        ] );
    }
}