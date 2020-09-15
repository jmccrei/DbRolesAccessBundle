Jmccrei\UserManagementBundle
=======

[Installation](#installation)

[Override Security Controller](#overrideSecurityController)


<a name="installation"></a> Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require jmccrei/user-management-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require jmccrei/user-management-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Jmccrei\UserManagementBundle\JmccreiUserManagementBundle::class => ['all' => true],
];
```

### Step 3: Create User Entity

Create the file `src/Entity/User.php` and copy/paste the following

```php
// src/Entity/User.php

declare( strict_types = 1 );

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jmccrei\UserManagement\Entity\AbstractUser;

/**
 * Class User
 * @package App\Entity
 * @ORM\Entity()
 */
class User extends AbstractUser
{
    // stubs
}
```

### Step 4: Optional: Create Access and Role classes

tdb

### Step 5: Configuration

Create configuration file `config/packages/jmccrei/jmccrei.yaml` and copy/paste the following

```yaml
// config/packages/jmccrei/jmccrei.yaml

jmccrei_user_management:
    user_class: App\Entity\User
    # ...
```

Full Configuration
```yaml
jmccrei_user_management:
  login_route: app_login
  referrer_redirect: true
  successful_redirect_route: site_index
  user_class: App\Entity\User
  role_class: Jmccrei\UserManagement\Entity\SystemRole
  access_class: Jmccrei\UserManagement\Entity\Access
  invalid_credentials_message: Invalid security credentials supplied
  mappings:
    user:
      roles:
        cascade: [ 'persist' ]
        inversedBy: users
        joinTable:
          name: ~
          joinColumns:
            - { name: user_id, referencedColumnName: id }
          inverseJoinColumns:
            - { name: role_id, referencedColumnName: id }
    access:
      roles:
        cascade: [ ]
        inversedBy: access
        joinTable:
          name: ~
          joinColumns:
            - { name: access_id, referencedColumnName: id }
          inverseJoinColumns:
            - { name: role_id, referencedColumnName: id }
    role:
      parent:
        cascade: [ 'persist' ]
        inversedBy: children
      children:
        mappedBy: parent
      users:
        cascade: [ 'persist' ]
        mappedBy: systemRoles
      access:
        cascade: [ 'persist' ]
        mappedBy: systemRoles
```

### Step 6: Update the database

Update the database using migrations or whatever method you normally use.
In new environments: 
```cmd
bin/console doctrine:schema:update --force
```

Optional: 
```cmd 
bin/console doctrine:fixtures:load --group=jmccrei_user_management
```

### Step 7: Update twig configuration

```yaml
// config/packages/twig.yaml

twig:
    ...
    twig:
      ...
      paths:
        '%kernel.project_dir%/vendor/jmccrei/user-management-bundle/src/Resources/views': JmccreiUserManagement
```

### Step 8: Update security configuration

```yaml
// config/packages/security.yaml

security:
  encoders:
    ...
    App\Entity\User:
      algorithm: auto

  providers:
    ...
    app_user_provider:
      entity:
        class: App\Entity\User
        property: email

  firewalls:
    ...
    main:
      ...
      remember_me:
        secret: '%kernel.secret%'
        lifetime: 604800 # 1 week in seconds
        path: /
      anonymous: true
      guard:
        authenticators:
          - Jmccrei\UserManagement\Security\LoginFormAuthenticator
      form_login:
        csrf_token_generator: security.csrf.token_manager
        login_path: app_login
      logout:
        path: app_logout
        target: app_login
  ...

  access_control:
    - { path: ^/profile, roles: ROLE_USER }

```

### <a name="step9"></a>Step 9: Add routing

```yaml
// config/routes/jmccrei.yaml

jmccrei_user_management_routes:
  resource: '@JmccreiUserManagementBundle/Resources/config/routes.yaml'

```

## Step 10: Clear cache and verify login page

`symfony console cache:clear`

[http://localhost:8000/login](http://localhost:8000/login)

## <a name="overrideSecurityController"></a> Override Security Controller (Recommended)

Remove the entry from [Step 9](#step9) above.

Create file `src\Controller\SecurityController.php`

```php
// src\Controller\SecurityController

namespace App\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @param KernelInterface     $kernel
     * @return Response
     */
    public function login( AuthenticationUtils $authenticationUtils,
                           KernelInterface $kernel ): Response
    {
        /******************/
        /* Your code here */
        /******************/

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // render template under /templates/security/login.html.twig
        return $this->render( 'security/login.html.twig', [ 'last_username' => $lastUsername, 'error' => $error ] );
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new LogicException( 'This method can be blank - it will be intercepted by the logout key on your firewall.' );
    }
}

```

Create template file `templates\security\login.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}Log in!{% endblock %}

{% block body %}
    <form method="post">
        {% if error %}
            <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
        {% endif %}
    
        {% if app.user %}
            <div class="mb-3">
                You are logged in as {{ app.user.username }}, <a href="{{ path('app_logout') }}">Logout</a>
            </div>
        {% endif %}
    
        <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
        <label for="inputEmail">Email</label>
        <input type="email" value="{{ last_username }}" name="email" id="inputEmail" class="form-control" required
               autofocus>
        <label for="inputPassword">Password</label>
        <input type="password" name="password" id="inputPassword" class="form-control" required>
    
        <input type="hidden" name="_csrf_token"
               value="{{ csrf_token('authenticate') }}">
    
        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" name="_remember_me"> Remember me
            </label>
        </div>
    
        <button class="btn btn-lg btn-primary" type="submit">
            Sign in
        </button>
    </form>
{% endblock %}

```

Clear your cache `bin/console cache:clear` and go to `/login`