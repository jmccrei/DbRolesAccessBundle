<?php
/**
 * (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace Jmccrei\UserManagement\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Access
 * @package Jmccrei\UserManagement\Entity
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Access extends AbstractAccess
{
}