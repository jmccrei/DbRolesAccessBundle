<?php
/** @noinspection PhpUnused */

namespace Jmccrei\UserManagement\Controller;

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
        if ( $this->getUser() ) {
            return $this->redirectToRoute(
                $kernel->getContainer()
                    ->getParameter( 'jmccrei_user_management' )[ 'successful_redirect_route' ]
            );
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Change this to point to your login form (Ensure to use email, password as field names)
        // You can include the template @JmccreiUserManagement\security\login_form.html.twig
        return $this->render( '@JmccreiUserManagement/security/login.html.twig', [ 'last_username' => $lastUsername, 'error' => $error ] );
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new LogicException( 'This method can be blank - it will be intercepted by the logout key on your firewall.' );
    }
}
