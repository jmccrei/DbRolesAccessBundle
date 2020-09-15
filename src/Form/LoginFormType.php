<?php

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LoginFormType
 * @package Jmccrei\UserManagement\Form
 */
class LoginFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        parent::buildForm( $builder, $options );
        $builder->add( 'email', EmailType::class,
            [
                'required' => TRUE,
                'attr'     => [
                    'autofocus' => TRUE
                ]
            ] )
            ->add( 'password', PasswordType::class, [
                'required' => TRUE,
                'attr'     => []
            ] )
            ->add( '_remember_me', CheckboxType::class, [
                'required' => FALSE,
                'attr'     => []
            ] );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions( OptionsResolver $resolver ): void
    {
        $resolver->setDefaults( [
            'csrf_protection' => TRUE,
            'csrf_field_name' => '_csrf_token'
        ] );
    }
}