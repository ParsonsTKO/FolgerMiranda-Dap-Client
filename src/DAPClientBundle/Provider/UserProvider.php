<?php declare(strict_types=1);

namespace DAPClientBundle\Provider;

use DAPClientBundle\Model\User;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserProvider
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return bool
     */
    public function isLoggedIn() : bool
    {
        return $this->authorizationChecker->isGranted(User::ROLE_USER);
    }

    /**
     * @return User|null
     */
    public function getUserFromTokenStorage() : ?User
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!$token instanceof TokenInterface) {
            return null;
        }

        $user = $token->getUser();

        if (!is_object($user) || !$user instanceof User) {
            return null;
        }

        return $user;
    }

}