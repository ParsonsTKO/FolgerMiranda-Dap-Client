<?php declare(strict_types=1);

namespace DAPClientBundle\Controller;

use DAPClientBundle\Provider\UserProvider;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController
{
    /**
     * @var UserProvider
     */
    private $userProvider;

    /**
     * @var string
     */
    private $apiSessionEndpoint;

    /**
     * @param UserProvider $userProvider
     * @param string $apiSessionEndpoint
     */
    public function __construct(
        UserProvider $userProvider,
        string $apiSessionEndpoint
    ) {
        $this->userProvider = $userProvider;
        $this->apiSessionEndpoint = $apiSessionEndpoint;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function login(Request $request) : RedirectResponse
    {
        $response = new RedirectResponse(sprintf(
            '%s/login?_redirect=client',
            $this->apiSessionEndpoint
        ));

        if (null !== $referer = $request->headers->get('referer')) {
            $response->headers->setCookie(new Cookie(
                '_referer',
                $referer,
                new \DateTimeImmutable('+ 15 minutes')
            ));
        }

        return $response;
    }

    /**
     * @return RedirectResponse
     */
    public function profile() : RedirectResponse
    {
        if (null === $user = $this->userProvider->getUserFromTokenStorage()) {
            throw new AccessDeniedHttpException();
        }

        return new RedirectResponse(sprintf(
            '%s/profile?_redirect=client&api-key=%s',
            $this->apiSessionEndpoint,
            $user->getApiKey() ?: ''
        ));
    }

    /**
     * @return RedirectResponse
     */
    public function register() : RedirectResponse
    {
        return new RedirectResponse(sprintf(
            '%s/register?_redirect=client',
            $this->apiSessionEndpoint
        ));
    }

}
