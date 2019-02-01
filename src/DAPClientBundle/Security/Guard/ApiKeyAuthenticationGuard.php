<?php declare(strict_types=1);

namespace DAPClientBundle\Security\Guard;

use DAPClientBundle\Model\User;
use DAPClientBundle\Security\UserProvider\ApiKeyUserProvider;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException as SymfonyAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiKeyAuthenticationGuard extends AbstractGuardAuthenticator
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @param UrlGeneratorInterface $router
     */
    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return $request->cookies->has(ApiKeyUserProvider::COOKIE_ATTR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        /** @var Cookie $cookie */
        if (null === $cookie = $request->cookies->get(ApiKeyUserProvider::COOKIE_ATTR_NAME)) {
            return null;
        }

        $array = (array) json_decode(base64_decode($cookie));

        try {
            $user = User::fromArray($array);
        } catch (\Exception $exception) {
            $user = null;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials && !$credentials instanceof User) {
            throw new AuthenticationException('Invalid cookie');
        }

        return $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, SymfonyAuthenticationException $exception)
    {
        throw new AccessDeniedHttpException(strtr($exception->getMessageKey(), $exception->getMessageData()));
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, SymfonyAuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('login'));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}