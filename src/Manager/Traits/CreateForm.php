<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Manager\Traits;

use Symfony\Component\Form\FormInterface;

/**
 * Trait CreateForm
 * @package Jmccrei\UserManagement\Manager\Traits
 */
trait CreateForm
{
    /**
     * Create a form
     * Assumes $this->getContainer() exists
     *
     * @param string $type
     * @param null   $data
     * @param array  $options
     * @return FormInterface
     */
    public function createForm( string $type, $data = NULL, array $options = [] ): FormInterface
    {
        return $this->getContainer()->get( 'form.factory' )->create( $type, $data, $options );
    }
}