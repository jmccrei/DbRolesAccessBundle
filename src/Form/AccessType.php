<?php

/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Form;

use Jmccrei\UserManagement\Entity\Access;
use Jmccrei\UserManagement\Entity\SystemRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AccessType
 * @package Jmccrei\UserManagement\Form
 */
class AccessType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm( FormBuilderInterface $builder, array $options )
    {
        $builder
            ->add( 'path', TextType::class, [
                'help' => 'The access path mask',
                'attr' => []
            ] )
            ->add( 'active', CheckboxType::class, [
                'required' => FALSE,
                'help'     => 'Is this access rule active?',
                'attr'     => []
            ] )
            ->add( 'methods', ChoiceType::class, [
                'required' => FALSE,
                'multiple' => TRUE,
                'choices'  => $this->getMethodChoices(),
                'help'     => 'Which methods does this access rule pertain to?',
                'attr'     => []
            ] )
            ->add( 'ip', TextType::class, [
                'required' => FALSE,
                'help'     => 'IP address for this access rule. Blank for all.',
                'attr'     => []
            ] )
            ->add( 'channel', ChoiceType::class,
                [ 'required' => FALSE,
                  'choices'  => [ 'HTTP' => 'Http', 'HTTPS' => 'Https' ],
                  'help'     => 'Which method is this access rule for?',
                  'attr'     => []
                ] )
            ->add( 'allowAnonymous', CheckboxType::class, [
                'required' => FALSE,
                'help'     => 'Allow anonymous access for this rule?',
                'attr'     => []
            ] )
            ->add( 'mandatory', CheckboxType::class, [
                'required' => FALSE,
                'help'     => 'Is this a mandatory access rule? If so, this cannot be deleted while it\'s mandatory.',
                'attr'     => []
            ] )
            ->add( 'host', TextType::class, [
                'required' => FALSE,
                'help'     => 'Which host to is this access rule for?',
                'attr'     => []
            ] )
            ->add( 'roles', EntityType::class, [
                'class'        => SystemRole::class,
                'choice_label' => 'name',
                'multiple'     => TRUE,
                'help'         => 'Which roles are required for this access rule?',
                'attr'         => []
            ] );
    }

    /**
     * Get the methods choices for the form
     *
     * @return array
     */
    public function getMethodChoices(): array
    {
        $methods = [];
        foreach ( Access::METHODS as $method ) {
            $methods[ ucfirst( strtolower( $method ) ) ] = $method;
        }

        return $methods;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions( OptionsResolver $resolver ): void
    {
        $resolver->setDefaults( [
            'data_class' => Access::class,
        ] );
    }
}