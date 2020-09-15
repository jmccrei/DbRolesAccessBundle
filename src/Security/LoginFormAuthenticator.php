<?php

namespace Jmccrei\UserManagement\Security;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Jmccrei\UserManagement\Entity\AbstractUser;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Class LoginFormAuthenticator
 * @package Jmccrei\UserManagement\Security
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var CsrfTokenManagerInterface
     */
    protected $csrfTokenManager;

    /**
     * @var ParameterBag
     */
    protected $configuration;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $userPasswordEncoder;

    /**
     * LoginFormAuthenticator constructor.
     *
     * @param KernelInterface              $kernel
     * @param EntityManagerInterface       $entityManager
     * @param UrlGeneratorInterface        $urlGenerator
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param CsrfTokenManagerInterface    $csrfTokenManager
     */
    public function __construct(
        KernelInterface $kernel,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        UserPasswordEncoderInterface $userPasswordEncoder,
        CsrfTokenManagerInterface $csrfTokenManager )
    {
        $this->entityManager       = $entityManager;
        $this->urlGenerator        = $urlGenerator;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->csrfTokenManager    = $csrfTokenManager;
        $this->configuration       = new ParameterBag(
            $kernel->getContainer()->getParameter( 'jmccrei_user_management' )
        );
    }

    /**
     * Check the user credentials
     *
     * @param mixed         $credentials
     * @param UserInterface $user
     * @return bool|void
     * @throws Exception
     */
    public function checkCredentials( $credentials, UserInterface $user )
    {
        return $this->userPasswordEncoder->isPasswordValid( $user, $credentials[ 'password' ] );
    }

    /**
     * Get the credentials
     *
     * @param Request $request
     * @return array|mixed
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function getCredentials( Request $request )
    {
        if ( $request->request->has( 'login_form' ) ) {
            $data = new ParameterBag( $request->request->get( 'login_form', [] ) );
        } else {
            $data = $request->request;
        }

        $credentials = [
            'email'      => $data->get( 'email', $data->get( '_email' ) ),
            'password'   => $data->get( 'password', $data->get( '_password' ) ),
            'csrf_token' => $data->get( '_csrf_token', $data->get( '_token' ) ),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials[ 'email' ]
        );

        return $credentials;
    }

    /**
     * Get the login url
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(
            $this->configuration->get( 'login_route', 'app_login' )
        );
    }

    /**
     * Get the user
     *
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     * @return AbstractUser|object|UserInterface|null
     */
    public function getUser( $credentials, UserProviderInterface $userProvider )
    {
        $token = new CsrfToken( 'authenticate', $credentials[ 'csrf_token' ] );
        if ( !$this->csrfTokenManager->isTokenValid( $token ) ) {
            throw new InvalidCsrfTokenException();
        }

        $userClass = $this->configuration->get( 'user_class' );
        $user      = $this->entityManager->getRepository( $userClass )
            ->findOneBy( [ 'email' => $credentials[ 'email' ] ] );

        if ( !$user ) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException(
                $this->configuration->get( 'invalid_credentials_message', 'Invalid credentials' )
            );
        }

        return $user;
    }

    /**
     * On Authentication Success
     *
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     * @return RedirectResponse|Response|null
     * @throws Exception
     */
    public function onAuthenticationSuccess( Request $request, TokenInterface $token, string $providerKey )
    {
        if ( TRUE === !!$this->configuration->get( 'referrer_redirect', TRUE ) ) {
            if ( $targetPath = $this->getTargetPath( $request->getSession(), $providerKey ) ) {
                return new RedirectResponse( $targetPath );
            }
        }

        return new RedirectResponse( $this->urlGenerator->generate(
            $this->configuration->get( 'successful_redirect', 'site_index' )
        ) );
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports( Request $request )
    {
        return $this->configuration->get( 'login_route', 'app_login' ) === $request->attributes->get( '_route' )
            && $request->isMethod( 'POST' );
    }
}
