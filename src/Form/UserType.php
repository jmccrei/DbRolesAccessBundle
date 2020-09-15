<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class UserType
 * @package Jmccrei\UserManagement\Form
 */
class UserType extends AbstractUserType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm( FormBuilderInterface $builder, array $options ): void
    {
        parent::buildForm( $builder, $options );
        $builder->add( 'firstName', TextType::class,
            [
                'required' => FALSE,
                'attr'     => []
            ] )
            ->add( 'lastName', TextType::class, [
                'required' => FALSE,
                'attr'     => []
            ] );
    }
}