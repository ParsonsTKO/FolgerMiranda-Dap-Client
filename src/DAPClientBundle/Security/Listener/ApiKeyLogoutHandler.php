<?php declare(strict_types=1);

namespace DAPClientBundle\Security\Listener;

use DAPClientBundle\Security\UserProvider\ApiKeyUserProvider;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class ApiKeyLogoutHandler implements LogoutHandlerInterface
{
    /**
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $response->headers->setCookie(
            new Cookie(
                ApiKeyUserProvider::COOKIE_ATTR_NAME,
                '',
                new \DateTimeImmutable('- 1 year'),
                '/',
                null,
                false,
                true,
                true
            )
        );
    }
}